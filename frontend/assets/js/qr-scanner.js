/**
 * QR scanner component backed by the browser's BarcodeDetector API.
 *
 * The component owns only the camera and detection loop; the page decides what
 * to show for each state and what to do with a scanned value.
 *
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
 */
(function (global) {
    'use strict';

    const DEFAULT_FORMAT = 'qr_code';
    const SCAN_INTERVAL_MS = 200;
    const DUPLICATE_WINDOW_MS = 1500;

    class QrScanner {
        constructor(video, options = {}) {
            if (!(video instanceof HTMLVideoElement)) {
                throw new TypeError('QrScanner requires a video element.');
            }

            this.video = video;
            this.format = options.format || DEFAULT_FORMAT;
            this.scanIntervalMs = Number.isFinite(options.scanIntervalMs)
                ? options.scanIntervalMs
                : SCAN_INTERVAL_MS;
            this.onResult = typeof options.onResult === 'function' ? options.onResult : function () {};
            this.onStateChange = typeof options.onStateChange === 'function' ? options.onStateChange : function () {};

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

            this._loop = this._loop.bind(this);
        }

        static isSupported() {
            return typeof global.BarcodeDetector === 'function';
        }

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

        async _start() {
            if (!global.isSecureContext) {
                this._fail('insecure-context');
                return;
            }

            if (!QrScanner.isSupported() || !navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
                this._fail('unsupported');
                return;
            }

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

        _releaseCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach((track) => track.stop());
                this.stream = null;
            }
            this.video.srcObject = null;
        }

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

            if (!this.canvas) {
                this.canvas = document.createElement('canvas');
                this.context = this.canvas.getContext('2d', { willReadFrequently: true });
                if (!this.context) {
                    this._fail('detect-error');
                    return;
                }
            }

            if (this.canvas.width !== width) {
                this.canvas.width = width;
            }
            if (this.canvas.height !== height) {
                this.canvas.height = height;
            }

            this.context.drawImage(this.video, 0, 0, width, height);
            const codes = await this.detector.detect(this.canvas);

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

        _emit(state, reason) {
            this.onStateChange(state, reason);
        }

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

    global.QrScanner = QrScanner;
})(window);
