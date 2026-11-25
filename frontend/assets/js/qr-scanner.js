/**
 * QrScanner — a small browser component that reads QR codes from a camera.
 *
 * What it does:
 *  1. Checks that the page is secure (HTTPS or localhost) and that the browser
 *     supports BarcodeDetector and getUserMedia.
 *  2. Requests camera access (preferring the rear/environment camera), draws
 *     each frame to an offscreen canvas, and asks BarcodeDetector for codes.
 *  3. Reports scanned values through onResult(code) and lifecycle changes
 *     through onStateChange(state, reason).
 *  4. Releases every camera track on stop(), on failure, or when the page hides.
 *
 * What it does NOT do: it never submits a check-in or stores a value. The page
 * controller (check-in.js) decides what to show and what happens after a scan.
 *
 * Usage:
 *   const scanner = new QrScanner(video, {
 *       onResult: (code) => { ... },
 *       onStateChange: (state, reason) => { ... }
 *   });
 *   await scanner.start();
 *   scanner.stop();
 *
 * States emitted by onStateChange: requesting, scanning, stopped, error.
 * Error reasons: insecure-context, unsupported, unsupported-format,
 * permission-denied, no-camera, camera-busy, camera-unavailable,
 * camera-error, detect-error.
 *
 * Browser note: BarcodeDetector is a native API with limited support. When it
 * is missing, start() reports 'unsupported' and the page shows manual entry.
 */
(function (global) {
    'use strict';

    // Only QR codes are relevant to check-in.
    const DEFAULT_FORMAT = 'qr_code';

    // Detect at most five times per second instead of on every display frame.
    const SCAN_INTERVAL_MS = 200;

    // Ignore the same code for a short window so a badge held in view is not
    // reported repeatedly.
    const DUPLICATE_WINDOW_MS = 1500;

    class QrScanner {
        /**
         * Creates a scanner bound to a <video> element.
         * Options: format, scanIntervalMs, onResult(code), onStateChange(state, reason).
         */
        constructor(video, options = {}) {
            if (!(video instanceof HTMLVideoElement)) {
                throw new TypeError('QrScanner requires a video element.');
            }

            // Configuration supplied by the caller.
            this.video = video;
            this.format = options.format || DEFAULT_FORMAT;
            this.scanIntervalMs = Number.isFinite(options.scanIntervalMs)
                ? options.scanIntervalMs
                : SCAN_INTERVAL_MS;
            this.onResult = typeof options.onResult === 'function' ? options.onResult : function () {};
            this.onStateChange = typeof options.onStateChange === 'function' ? options.onStateChange : function () {};

            // Runtime state: stream/detector/canvas are created during start().
            this.stream = null;
            this.detector = null;
            this.canvas = null;
            this.context = null;
            this.frameId = null;
            this.running = false;
            this.starting = false;
            this.scanningInProgress = false;
            this.lastScanAt = 0;
            this.lastResultAt = 0;
            this.lastValue = '';

            // Keep `this` correct when the loop is scheduled by requestAnimationFrame.
            this._loop = this._loop.bind(this);
        }

        /** True when this browser exposes the native barcode detection API. */
        static isSupported() {
            return typeof global.BarcodeDetector === 'function';
        }

        /** Lists detectable barcode formats; an empty list means "unknown". */
        static async getSupportedFormats() {
            if (!QrScanner.isSupported() || typeof global.BarcodeDetector.getSupportedFormats !== 'function') {
                return [];
            }

            try {
                return await global.BarcodeDetector.getSupportedFormats();
            } catch (error) {
                return [];
            }
        }

        /**
         * Starts the camera. Guarded so a second call while a permission prompt
         * is still open cannot open a second stream and leak the first one.
         */
        async start() {
            if (this.running || this.starting) {
                return;
            }

            this.starting = true;
            this._emit('requesting');

            try {
                await this._start();
            } finally {
                this.starting = false;
            }
        }

        /** Performs the actual start sequence; see start() for the public guard. */
        async _start() {
            // Cameras require a secure context. localhost counts as secure.
            if (!global.isSecureContext) {
                this._fail('insecure-context');
                return;
            }

            // No API or no mediaDevices -> tell the page to use manual entry.
            if (!QrScanner.isSupported() || !navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
                this._fail('unsupported');
                return;
            }

            // Some implementations omit getSupportedFormats(); if the list is
            // known, make sure it actually contains 'qr_code'.
            const formats = await QrScanner.getSupportedFormats();
            if (formats.length > 0 && formats.indexOf(this.format) === -1) {
                this._fail('unsupported-format');
                return;
            }

            try {
                this.detector = new global.BarcodeDetector({ formats: [this.format] });
            } catch (error) {
                this._fail('unsupported-format');
                return;
            }

            // Ask for permission. "ideal" hints are preferences, not hard
            // constraints, so devices without a rear camera still work.
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    audio: false,
                    video: {
                        facingMode: { ideal: 'environment' },
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                });
            } catch (error) {
                this._fail(QrScanner._cameraErrorReason(error));
                return;
            }

            // Attach the stream to the visible preview. muted + playsinline
            // prevent audio feedback and fullscreen playback on iOS.
            this.video.srcObject = this.stream;
            this.video.muted = true;
            this.video.playsInline = true;

            try {
                await this.video.play();
            } catch (error) {
                // Autoplay may wait for user interaction; frames will still be
                // available to detect once the browser starts rendering them.
            }

            this.running = true;
            this.lastValue = '';
            this.lastScanAt = 0;
            this._emit('scanning');
            this.frameId = global.requestAnimationFrame(this._loop);
        }

        /** Stops detection, closes the camera, and notifies the page. */
        stop() {
            const wasRunning = this.running;
            this.running = false;

            if (this.frameId !== null) {
                global.cancelAnimationFrame(this.frameId);
                this.frameId = null;
            }

            this._releaseCamera();
            this.detector = null;
            this.scanningInProgress = false;

            if (wasRunning) {
                this._emit('stopped');
            }
        }

        /** Stops every track so the camera indicator turns off. */
        _releaseCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach((track) => track.stop());
                this.stream = null;
            }
            this.video.srcObject = null;
        }

        /** Cleans up after a failed start/detection and reports the reason. */
        _fail(reason) {
            this.running = false;
            if (this.frameId !== null) {
                global.cancelAnimationFrame(this.frameId);
                this.frameId = null;
            }
            this._releaseCamera();
            this.detector = null;
            this.scanningInProgress = false;
            this._emit('error', reason);
        }

        /**
         * Animation-frame loop. Throttles detection to scanIntervalMs and
         * prevents overlapping detect() calls while one is still resolving.
         */
        async _loop(timestamp) {
            if (!this.running) {
                return;
            }

            if (!this.scanningInProgress && timestamp - this.lastScanAt >= this.scanIntervalMs) {
                this.lastScanAt = timestamp;
                this.scanningInProgress = true;

                try {
                    await this._detectFrame();
                } catch (error) {
                    this._fail('detect-error');
                    return;
                } finally {
                    this.scanningInProgress = false;
                }
            }

            if (this.running) {
                this.frameId = global.requestAnimationFrame(this._loop);
            }
        }

        /**
         * Copies the current video frame into a hidden canvas, runs the
         * detector, and hands the first valid QR value to onResult.
         */
        async _detectFrame() {
            if (!this.running || !this.detector) {
                return;
            }

            // HAVE_CURRENT_DATA: a frame is ready to be drawn.
            if (this.video.readyState < 2) {
                return;
            }

            const width = this.video.videoWidth;
            const height = this.video.videoHeight;
            if (!width || !height) {
                return;
            }

            // Create the offscreen canvas once, then reuse it for every scan.
            if (!this.canvas) {
                this.canvas = document.createElement('canvas');
                this.context = this.canvas.getContext('2d', { willReadFrequently: true });
                if (!this.context) {
                    this._fail('detect-error');
                    return;
                }
            }

            // Match canvas size to the camera frame to avoid stretched pixels.
            if (this.canvas.width !== width) {
                this.canvas.width = width;
            }
            if (this.canvas.height !== height) {
                this.canvas.height = height;
            }

            this.context.drawImage(this.video, 0, 0, width, height);
            const codes = await this.detector.detect(this.canvas);

            // stop() may have run while detect() was awaiting.
            if (!this.running) {
                return;
            }

            const now = Date.now();
            for (const code of codes || []) {
                const value = String(code.rawValue || '').trim();
                if (!value) {
                    continue;
                }

                // The same badge can stay in frame for several scans; only
                // report it again after the duplicate window has passed.
                if (value === this.lastValue && now - this.lastResultAt < DUPLICATE_WINDOW_MS) {
                    continue;
                }

                this.lastValue = value;
                this.lastResultAt = now;
                this.onResult(value);
                return;
            }
        }

        /** Sends a lifecycle update to the page controller. */
        _emit(state, reason) {
            this.onStateChange(state, reason);
        }

        /** Translates getUserMedia DOMException names into UI-friendly reasons. */
        static _cameraErrorReason(error) {
            switch (error && error.name) {
                case 'NotAllowedError':
                case 'PermissionDeniedError':
                    return 'permission-denied';
                case 'SecurityError':
                    return 'insecure-context';
                case 'NotFoundError':
                case 'DevicesNotFoundError':
                    return 'no-camera';
                case 'NotReadableError':
                case 'TrackStartError':
                    return 'camera-busy';
                case 'OverconstrainedError':
                case 'ConstraintNotSatisfiedError':
                    return 'camera-unavailable';
                default:
                    return 'camera-error';
            }
        }
    }

    // Expose the class to check-in.js (loaded after this file).
    global.QrScanner = QrScanner;
})(window);
