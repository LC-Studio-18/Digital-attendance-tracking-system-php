/**
 * Check-in page controller for check-in.php.
 *
 * What it does:
 *  - finds the page elements through `data-checkin-*` hooks,
 *  - turns scanner states and error reasons into visible messages/button states,
 *  - places a scanned code into the manual form for user confirmation,
 *  - offers manual entry whenever the camera cannot be used,
 *  - handles form submission with a fetch call that is ready for a backend route.
 *
 * It is loaded after qr-scanner.js and creates one QrScanner instance. The
 * scanner never submits data itself: this file is the only place that decides
 * what a scanned value means for the check-in flow.
 */
(function () {
    'use strict';

    // Element hooks. Using data attributes instead of IDs keeps the markup and
    // behaviour loosely coupled; the guard below exits quietly if any are
    // missing (for example, if the page template changes).
    const status = document.querySelector('[data-checkin-status]');
    const video = document.querySelector('[data-checkin-video]');
    const placeholder = document.querySelector('[data-checkin-placeholder]');
    const cameraButton = document.querySelector('[data-checkin-camera]');
    const stopButton = document.querySelector('[data-checkin-stop]');
    const manualButton = document.querySelector('[data-checkin-manual]');
    const fallback = document.querySelector('[data-checkin-fallback]');
    const fallbackMessage = document.querySelector('[data-checkin-fallback-message]');
    const form = document.querySelector('[data-checkin-form]');
    const codeInput = document.querySelector('[data-checkin-code]');
    const submitButton = document.querySelector('[data-checkin-submit]');
    const result = document.querySelector('[data-checkin-result]');

    // All user-facing scanner messages live here so wording stays consistent.
    // Keys match the states/reasons emitted by QrScanner.
    const MESSAGES = {
        idle: 'Ready to scan. Start the camera when you are ready.',
        requesting: 'Requesting camera access…',
        scanning: 'Camera ready. Hold your QR code inside the frame.',
        stopped: 'Camera stopped. Start it again to scan another code.',
        captured: 'QR code captured. Confirm the check-in below.',
        manual: 'Enter the code printed on your staff badge instead.',
        'permission-denied': 'Camera access was blocked. Allow camera access in your browser settings, then try again, or enter your code manually below.',
        'no-camera': 'No camera was found on this device. Enter your code manually below.',
        'camera-busy': 'Your camera is in use by another app. Close it and try again, or enter your code manually below.',
        'camera-unavailable': 'The camera could not be started with the requested settings. Try again, or enter your code manually below.',
        'insecure-context': 'Camera access requires HTTPS or localhost. Enter your code manually below.',
        unsupported: 'This browser cannot scan QR codes with the camera. Enter your code manually below.',
        'unsupported-format': 'This browser cannot detect QR codes with the camera. Enter your code manually below.',
        'camera-error': 'The camera could not be started. Enter your code manually below.',
        'detect-error': 'QR detection stopped unexpectedly. Try again, or enter your code manually below.'
    };

    // scanner is created at the end of this file; hasCapturedCode lets the
    // "stopped" event know whether to say "captured" or "camera stopped".
    let scanner = null;
    let hasCapturedCode = false;

    if (!status || !video || !placeholder || !cameraButton || !stopButton || !manualButton ||
        !fallback || !fallbackMessage || !form || !codeInput || !submitButton || !result) {
        return;
    }

    /** Updates the live status text and its styling state. */
    function setStatus(message, state) {
        status.textContent = message;
        status.dataset.state = state;
    }

    /** Reveals form feedback; data-state controls its colour. */
    function setResult(message, state) {
        result.textContent = message;
        result.dataset.state = state;
        result.hidden = false;
    }

    /** Opens manual entry and updates the toggle label. */
    function showFallback(message) {
        if (message) {
            fallbackMessage.textContent = message;
        }
        fallback.hidden = false;
        manualButton.textContent = 'Hide manual entry';
    }

    /** Closes manual entry and restores the toggle label. */
    function hideFallback() {
        fallback.hidden = true;
        manualButton.textContent = 'Enter code manually';
    }

    /** Shows only the controls that make sense for the current scanner state. */
    function setCameraControls(state) {
        const busy = state === 'requesting' || state === 'scanning';
        cameraButton.hidden = busy;
        stopButton.hidden = state !== 'scanning';
        cameraButton.textContent = state === 'error' ? 'Try camera again' : 'Start camera';
    }

    /**
     * Called by QrScanner for every lifecycle change. Errors always reveal the
     * manual form so a camera problem never blocks check-in.
     */
    function handleState(state, reason) {
        setCameraControls(state);
        placeholder.hidden = state === 'requesting' || state === 'scanning';

        if (state === 'error') {
            const message = MESSAGES[reason] || MESSAGES['camera-error'];
            setStatus(message, 'error');
            showFallback(MESSAGES.manual);
            return;
        }

        if (state === 'requesting') {
            setStatus(MESSAGES.requesting, 'pending');
            return;
        }

        if (state === 'scanning') {
            setStatus(MESSAGES.scanning, 'scanning');
            return;
        }

        if (state === 'stopped') {
            setStatus(
                hasCapturedCode ? MESSAGES.captured : MESSAGES.stopped,
                hasCapturedCode ? 'success' : 'idle'
            );
            return;
        }

        setStatus(MESSAGES.idle, 'idle');
    }

    /** Handles a scanned QR value: stop the camera, fill the form, ask to confirm. */
    function handleScan(code) {
        hasCapturedCode = true;
        if (scanner) {
            scanner.stop();
        }

        // textContent (not innerHTML) is used everywhere for scanned values.
        codeInput.value = code;
        result.hidden = true;
        showFallback(MESSAGES.captured);
        setStatus(MESSAGES.captured, 'success');
        codeInput.focus();
    }

    /** Starts the camera and shows a generic error if start() itself rejects. */
    function startCamera() {
        if (!scanner) {
            return;
        }

        hasCapturedCode = false;
        result.hidden = true;

        try {
            const started = scanner.start();
            if (started && typeof started.catch === 'function') {
                started.catch(function () {
                    setStatus(MESSAGES['camera-error'], 'error');
                    showFallback(MESSAGES.manual);
                });
            }
        } catch (error) {
            setStatus(MESSAGES['camera-error'], 'error');
            showFallback(MESSAGES.manual);
        }
    }

    /** Releases the camera; also used when the page is being left. */
    function stopCamera() {
        if (scanner) {
            scanner.stop();
        }
    }

    /**
     * TODO(backend): the check-in API does not exist yet. Set
     * data-checkin-endpoint on the form in check-in.php to its route, then this
     * POST sends { code } and the caller displays the response message.
     * An empty endpoint returns { pending: true } so no fake success is shown.
     */
    async function submitCheckIn(code) {
        const endpoint = form.dataset.checkinEndpoint;

        if (!endpoint) {
            return { pending: true };
        }

        const response = await fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ code: code })
        });

        // A non-JSON reply is treated as an empty body; the response status is
        // still authoritative for success or failure.
        const body = await response.json().catch(function () {
            return null;
        });

        if (!response.ok) {
            throw new Error((body && body.message) || 'Check-in failed. Please try again.');
        }

        return body || {};
    }

    // Camera controls.
    cameraButton.addEventListener('click', startCamera);
    stopButton.addEventListener('click', function () {
        if (scanner) {
            scanner.stop();
        }
    });

    // Manual toggle: opening it also stops the camera to save battery.
    manualButton.addEventListener('click', function () {
        if (fallback.hidden) {
            showFallback(MESSAGES.manual);
            stopCamera();
            codeInput.focus();
        } else {
            hideFallback();
        }
    });

    // Clear old feedback once the user edits the code.
    codeInput.addEventListener('input', function () {
        result.hidden = true;
    });

    // Form submission: validate locally, disable the button while awaiting the
    // network, and always re-enable it afterwards.
    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const code = codeInput.value.trim();
        if (!code) {
            setResult('Enter your check-in code before confirming.', 'error');
            codeInput.focus();
            return;
        }

        submitButton.disabled = true;
        setResult('Checking in…', 'idle');

        try {
            const response = await submitCheckIn(code);
            setResult(
                response.pending
                    ? 'Code ready. Backend check-in will be connected in the next task.'
                    : (response.message || 'Checked in successfully.'),
                response.pending ? 'idle' : 'success'
            );
        } catch (error) {
            setResult(error.message || 'Check-in failed. Please try again.', 'error');
        } finally {
            submitButton.disabled = false;
        }
    });

    // Browsers keep the camera indicator on until tracks stop, so release it
    // when navigating away.
    window.addEventListener('pagehide', stopCamera);

    // Final wiring: construct the scanner if the component file loaded,
    // otherwise disable the camera button and go straight to manual entry.
    if (typeof window.QrScanner === 'function') {
        scanner = new window.QrScanner(video, {
            onResult: handleScan,
            onStateChange: handleState
        });
    } else {
        cameraButton.hidden = true;
        setStatus(MESSAGES.unsupported, 'error');
        showFallback(MESSAGES.manual);
    }
})();
