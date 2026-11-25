/**
 * Controller for camera-check.html (Task 3 manual mobile QA page).
 *
 * What it does:
 *  - reports the browser/camera environment so failures are easy to diagnose,
 *  - runs the real QrScanner component against the phone's camera,
 *  - shows each scanner state and decoded value in the page for a tester to read.
 *
 * It never submits a check-in. Use this page to verify camera behaviour only,
 * then use ../check-in.php to verify the full check-in flow.
 */
(function () {
    'use strict';

    // Fetch each unique, prefixed element once. A missing element means the
    // page markup changed, so exit quietly rather than throwing for a tester.
    const byId = (id) => document.getElementById(id);
    const secureEl = byId('qr-checkin-test-secure');
    const detectorEl = byId('qr-checkin-test-detector');
    const formatsEl = byId('qr-checkin-test-formats');
    const permissionEl = byId('qr-checkin-test-permission');
    const browserEl = byId('qr-checkin-test-browser');
    const warningEl = byId('qr-checkin-test-warning');
    const statusEl = byId('qr-checkin-test-status');
    const video = byId('qr-checkin-test-video');
    const placeholderEl = byId('qr-checkin-test-placeholder');
    const startButton = byId('qr-checkin-test-start');
    const stopButton = byId('qr-checkin-test-stop');
    const codeEl = byId('qr-checkin-test-code');
    const logEl = byId('qr-checkin-test-log');

    if (!secureEl || !detectorEl || !formatsEl || !permissionEl || !browserEl ||
        !warningEl || !statusEl || !video || !placeholderEl ||
        !startButton || !stopButton || !codeEl || !logEl) {
        return;
    }

    const STATE_MESSAGES = {
        idle: 'Ready. Tap Start camera.',
        requesting: 'Waiting for camera permission…',
        scanning: 'Scanning. Point the camera at a QR code.',
        stopped: 'Camera stopped.'
    };

    const ERROR_MESSAGES = {
        'permission-denied': 'Camera permission was denied. Allow it in browser settings, then try again.',
        'no-camera': 'No camera was found on this device.',
        'camera-busy': 'The camera is in use by another app.',
        'camera-unavailable': 'The camera could not be started with the requested settings.',
        'insecure-context': 'Camera access needs HTTPS or localhost.',
        unsupported: 'This browser does not support camera QR scanning.',
        'unsupported-format': 'This browser cannot detect QR codes.',
        'camera-error': 'The camera could not be started.',
        'detect-error': 'QR detection stopped unexpectedly.'
    };

    /** Adds a timestamped entry to the on-screen log (textContent only). */
    function log(message) {
        const entry = document.createElement('li');
        entry.textContent = new Date().toLocaleTimeString() + ' — ' + message;
        logEl.prepend(entry);
    }

    /** Fills in the environment panel and shows a warning if scanning cannot work. */
    async function renderEnvironment() {
        const secure = Boolean(window.isSecureContext);
        const supported = typeof window.QrScanner === 'function' && window.QrScanner.isSupported();

        secureEl.textContent = secure ? 'yes' : 'no — camera will be blocked';
        detectorEl.textContent = supported ? 'available' : 'not available';
        browserEl.textContent = navigator.userAgent;
        warningEl.hidden = secure && supported;

        if (supported) {
            const formats = await window.QrScanner.getSupportedFormats();
            formatsEl.textContent = formats.length > 0
                ? formats.join(', ')
                : 'not reported (detection will still be attempted)';
        } else {
            formatsEl.textContent = 'not available';
        }

        // The Permissions API is optional; "unknown" is a valid result.
        if (navigator.permissions && typeof navigator.permissions.query === 'function') {
            try {
                const status = await navigator.permissions.query({ name: 'camera' });
                const show = () => {
                    permissionEl.textContent = status.state;
                };
                show();
                if (typeof status.addEventListener === 'function') {
                    status.addEventListener('change', show);
                } else {
                    status.onchange = show;
                }
            } catch (error) {
                permissionEl.textContent = 'unavailable';
            }
        } else {
            permissionEl.textContent = 'unknown (browser does not expose it)';
        }
    }

    /** Mirrors scanner lifecycle events into the status banner and log. */
    function handleState(state, reason) {
        statusEl.dataset.state = state === 'error' ? 'error' : state;
        statusEl.textContent = state === 'error'
            ? (ERROR_MESSAGES[reason] || ERROR_MESSAGES['camera-error'])
            : (STATE_MESSAGES[state] || state);

        const busy = state === 'requesting' || state === 'scanning';
        startButton.hidden = busy;
        stopButton.hidden = state !== 'scanning';
        placeholderEl.hidden = busy;

        log(state + (reason ? ': ' + reason : ''));
    }

    /** Displays the decoded QR value for the tester to compare with the code used. */
    function handleResult(code) {
        codeEl.textContent = code;
        log('decoded: ' + code);
    }

    if (typeof window.QrScanner !== 'function') {
        statusEl.dataset.state = 'error';
        statusEl.textContent = 'Scanner component did not load. Check the browser console.';
        startButton.hidden = true;
        return;
    }

    const scanner = new window.QrScanner(video, {
        onResult: handleResult,
        onStateChange: handleState
    });

    startButton.addEventListener('click', async () => {
        try {
            await scanner.start();
        } catch (error) {
            log('start failed: ' + (error && error.message ? error.message : 'unknown error'));
        }
    });

    stopButton.addEventListener('click', () => scanner.stop());

    // Release the camera when leaving the page so the indicator turns off.
    window.addEventListener('pagehide', () => scanner.stop());

    renderEnvironment();
})();
