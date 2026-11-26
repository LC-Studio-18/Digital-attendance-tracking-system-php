/**
 * Check-in page controller for check-in.php.
 *
 * What it does:
 *  - finds the page elements through `data-checkin-*` hooks,
 *  - turns scanner states and error reasons into visible messages/button states,
 *  - places a scanned QR token into the manual form for user confirmation,
 *  - accepts either a raw token or the clock-in URL encoded in the QR code,
 *  - offers manual entry whenever the camera cannot be used,
 *  - POSTs the token to the configured check-in route and maps API errors.
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

    // User-facing wording for API error codes. The backend sends a safe
    // `message` too, but a known code always wins so wording stays consistent.
    // The older manual-code codes are kept for the draft contract in docs/.
    const ERROR_MESSAGES = {
        INVALID_QR: 'This QR code is invalid. Check the code and try again.',
        EXPIRED_QR: 'This QR code has expired. Ask for a new one.',
        UNAUTHORIZED: 'Please log in first, then try your QR code again.',
        ALREADY_CLOCKED_IN: 'You are already clocked in.',
        ALREADY_CHECKED_IN: 'You are already checked in.',
        INVALID_REQUEST: "Something went wrong with the request. Please try again.",
        INVALID_CODE: "That code doesn't look right. Check it and try again.",
        DUPLICATE_SCAN: "That code was just scanned. Please wait a moment before trying again.",
        UNKNOWN_CODE: "We couldn't find that code. Ask your supervisor to check it.",
        RATE_LIMITED: 'Too many attempts. Please wait a moment and try again.',
        SERVER_ERROR: "We couldn't clock you in right now. Please try again later."
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

    /**
     * Accepts either a raw QR token or the clock-in URL that the token issuer
     * encodes in the QR code, e.g. .../clock-in?qrToken=<token>.
     */
    function extractQrToken(rawValue) {
        const trimmed = String(rawValue == null ? '' : rawValue).trim();
        const match = trimmed.match(/[?&]qrToken=([^&#]+)/i);

        if (!match) {
            return trimmed;
        }

        try {
            return decodeURIComponent(match[1]).trim();
        } catch (error) {
            return match[1].trim();
        }
    }

    /** Finds a clock-in timestamp in either supported success envelope. */
    function clockInTimeFrom(body) {
        const data = (body && body.data) || {};
        return data.clock_in_utc || data.clocked_in_at_utc || data.clock_in_time || '';
    }

    /** Announces a confirmed clock-in so live status badges can refresh. */
    function announceClockIn() {
        if (typeof window.CustomEvent !== 'function' || typeof window.dispatchEvent !== 'function') {
            return;
        }
        window.dispatchEvent(new window.CustomEvent('attendance:clock-success'));
    }

    /** Handles a scanned QR value: stop the camera, fill the form, ask to confirm. */
    function handleScan(code) {
        hasCapturedCode = true;
        if (scanner) {
            scanner.stop();
        }

        // textContent (not innerHTML) is used everywhere for scanned values.
        codeInput.value = extractQrToken(code);
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
     * POSTs the token to the configured check-in route. Backend services return
     * { ok, code, data }; the proposed { status, data } envelope is accepted as
     * well so the UI keeps working if that shape is adopted later. An empty
     * endpoint returns { pending: true } so no fake success is shown.
     */
    async function submitCheckIn(qrToken) {
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
            body: JSON.stringify({ qrToken: qrToken })
        });

        // A non-JSON reply is treated as an empty body; the response status is
        // still authoritative for success or failure.
        const body = await response.json().catch(function () {
            return null;
        });

        const reportedFailure = body && (body.ok === false || body.status === 'error');

        if (!response.ok || reportedFailure) {
            const error = new Error(
                (body && body.message) || 'Check-in failed. Please try again.'
            );
            error.code = (body && body.code) || '';
            error.retryAfter = (body && body.retryAfter) || null;
            error.httpStatus = response.status;
            throw error;
        }

        const clockInTime = clockInTimeFrom(body);
        let message = (body && body.message) || 'Clocked in successfully.';
        if (clockInTime && !(body && body.message)) {
            message += ' Clock-in time: ' + clockInTime + '.';
        }

        return {
            message: message,
            clockInTime: clockInTime,
            pending: false
        };
    }

    /** Turns an API error into a safe, consistent user-facing message. */
    function messageForError(error) {
        const code = String((error && error.code) || '');
        let message = ERROR_MESSAGES[code] ||
            (error && error.message) ||
            'Check-in failed. Please try again.';

        const retryAfter = Number(error && error.retryAfter);
        if (Number.isFinite(retryAfter) && retryAfter > 0) {
            message += ' Try again in ' + Math.ceil(retryAfter) + ' seconds.';
        }

        return message;
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

        const qrToken = extractQrToken(codeInput.value);
        if (!qrToken) {
            setResult('Enter your check-in code before confirming.', 'error');
            codeInput.focus();
            return;
        }

        submitButton.disabled = true;
        setResult('Checking in…', 'idle');

        try {
            const response = await submitCheckIn(qrToken);
            if (response.pending) {
                setResult('Code ready. Backend check-in will be connected in the next task.', 'idle');
            } else {
                setResult(response.message, 'success');
                announceClockIn();
            }
        } catch (error) {
            setResult(messageForError(error), 'error');
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

    // The token issuer encodes .../clock-in?qrToken=<token> in the QR code.
    // Opening that link pre-fills the token for confirmation; it never submits
    // automatically, so a stray visit cannot clock someone in by accident.
    const locationSearch = (window.location && window.location.search) || '';
    const initialToken = extractQrToken(locationSearch);
    if (initialToken) {
        codeInput.value = initialToken;
        result.hidden = true;
        showFallback(MESSAGES.captured);
        setStatus(MESSAGES.captured, 'success');
    }
})();
