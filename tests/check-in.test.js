/**
 * Component tests for frontend/assets/js/check-in.js.
 *
 * These verify that scanner states become the right UI state, that a scanned
 * code is placed in the form for confirmation, and that the POST hook behaves
 * correctly. Run with: node --test tests
 */

'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const { createControllerHarness } = require('./fake-dom');

test('a permission error reveals manual entry with a helpful message', () => {
    const harness = createControllerHarness();

    harness.scanners[0].callbacks.onStateChange('error', 'permission-denied');

    assert.equal(harness.elements.fallback.hidden, false);
    assert.equal(harness.elements.status.dataset.state, 'error');
    assert.match(harness.elements.status.textContent, /blocked/i);
    assert.equal(harness.elements.cameraButton.textContent, 'Try camera again');
});

test('a scanned code stops the camera and fills the confirmation form', () => {
    const harness = createControllerHarness();

    harness.scanners[0].callbacks.onResult('STAFF-9');

    assert.equal(harness.scanners[0].stopCount, 1);
    assert.equal(harness.elements.codeInput.value, 'STAFF-9');
    assert.equal(harness.elements.fallback.hidden, false);
    assert.equal(harness.elements.status.dataset.state, 'success');
});

test('an empty submission is rejected locally without a network call', async () => {
    const harness = createControllerHarness({ endpoint: '/api/check-in.php' });

    await harness.elements.form.emit('submit');

    assert.equal(harness.fetchCalls.length, 0);
    assert.equal(harness.elements.result.dataset.state, 'error');
    assert.match(harness.elements.result.textContent, /Enter your check-in code/);
});

test('a configured endpoint receives a JSON POST with the QR token', async () => {
    const harness = createControllerHarness({ endpoint: '/api/check-in.php' });
    harness.elements.codeInput.value = 'STAFF-42';

    await harness.elements.form.emit('submit');

    assert.equal(harness.fetchCalls.length, 1);
    assert.equal(harness.fetchCalls[0].url, '/api/check-in.php');
    assert.equal(harness.fetchCalls[0].init.method, 'POST');
    assert.deepEqual(JSON.parse(harness.fetchCalls[0].init.body), { qrToken: 'STAFF-42' });
    assert.match(harness.elements.result.textContent, /Clocked in successfully/);
    assert.match(harness.elements.result.textContent, /2026-11-27 08:00:00/);
    assert.equal(harness.elements.result.dataset.state, 'success');
    assert.equal(harness.elements.submitButton.disabled, false);
    assert.equal(harness.dispatchedEvents.length, 1);
    assert.equal(harness.dispatchedEvents[0].type, 'attendance:clock-success');
    assert.equal(harness.dispatchedEvents[0].detail.action, 'clock-in');
    assert.equal(harness.dispatchedEvents[0].detail.clockInTime, '2026-11-27 08:00:00');
});

test('the proposed status/data envelope is also accepted', async () => {
    const harness = createControllerHarness({
        endpoint: '/api/check-in.php',
        fetchImpl: async () => ({
            ok: true,
            json: async () => ({
                status: 'success',
                data: { id: 9, clock_in_utc: '2026-11-27T08:00:00Z' }
            })
        })
    });
    harness.elements.codeInput.value = 'token-9';

    await harness.elements.form.emit('submit');

    assert.equal(harness.elements.result.dataset.state, 'success');
    assert.match(harness.elements.result.textContent, /Clocked in successfully/);
    assert.match(harness.elements.result.textContent, /2026-11-27T08:00:00Z/);
});

test('a backend error message is shown instead of a fake success', async () => {
    const harness = createControllerHarness({
        endpoint: '/api/check-in.php',
        fetchImpl: async () => ({
            ok: false,
            json: async () => ({ message: 'This code was already used.' })
        })
    });
    harness.elements.codeInput.value = 'STAFF-42';

    await harness.elements.form.emit('submit');

    assert.equal(harness.elements.result.dataset.state, 'error');
    assert.match(harness.elements.result.textContent, /already used/);
    assert.equal(harness.elements.submitButton.disabled, false);
    assert.equal(harness.dispatchedEvents.length, 0);
});

test('QR-specific error codes map to friendly messages', async () => {
    const cases = [
        ['INVALID_QR', 400, /invalid/i],
        ['EXPIRED_QR', 400, /expired/i],
        ['UNAUTHORIZED', 401, /log in first/i],
        ['ALREADY_CLOCKED_IN', 409, /already clocked in/i]
    ];

    for (const [code, status, pattern] of cases) {
        const harness = createControllerHarness({
            endpoint: '/api/check-in.php',
            fetchImpl: async () => ({
                ok: false,
                status,
                json: async () => ({ ok: false, code, message: 'Raw backend detail.' })
            })
        });
        harness.elements.codeInput.value = 'token';

        await harness.elements.form.emit('submit');

        assert.equal(harness.elements.result.dataset.state, 'error', code);
        assert.match(harness.elements.result.textContent, pattern, code);
        assert.equal(harness.dispatchedEvents.length, 0, code);
    }
});

test('a status:error body is a failure even when HTTP reports success', async () => {
    const harness = createControllerHarness({
        endpoint: '/api/check-in.php',
        fetchImpl: async () => ({
            ok: true,
            status: 200,
            json: async () => ({
                status: 'error',
                code: 'EXPIRED_QR',
                message: 'This QR code has expired.'
            })
        })
    });
    harness.elements.codeInput.value = 'token';

    await harness.elements.form.emit('submit');

    assert.equal(harness.elements.result.dataset.state, 'error');
    assert.match(harness.elements.result.textContent, /expired/i);
});

test('retryAfter is shown in the rate-limit message', async () => {
    const harness = createControllerHarness({
        endpoint: '/api/check-in.php',
        fetchImpl: async () => ({
            ok: false,
            status: 429,
            json: async () => ({
                ok: false,
                code: 'RATE_LIMITED',
                message: 'Too many attempts.',
                retryAfter: 25
            })
        })
    });
    harness.elements.codeInput.value = 'token';

    await harness.elements.form.emit('submit');

    assert.match(harness.elements.result.textContent, /Try again in 25 seconds/);
});

test('a scanned clock-in URL is reduced to its qrToken', () => {
    const harness = createControllerHarness();

    harness.scanners[0].callbacks.onResult(
        'https://attendance.example.com/clock-in?qrToken=abc123%2Ddef'
    );

    assert.equal(harness.elements.codeInput.value, 'abc123-def');
});

test('a qrToken query parameter pre-fills the form without submitting', async () => {
    const harness = createControllerHarness({
        endpoint: '/api/check-in.php',
        search: '?qrToken=from-url'
    });

    assert.equal(harness.elements.codeInput.value, 'from-url');
    assert.equal(harness.elements.fallback.hidden, false);
    assert.equal(harness.fetchCalls.length, 0);
});

test('no endpoint yet shows a pending message and makes no request', async () => {
    const harness = createControllerHarness();
    harness.elements.codeInput.value = 'STAFF-7';

    await harness.elements.form.emit('submit');

    assert.equal(harness.fetchCalls.length, 0);
    assert.match(harness.elements.result.textContent, /Backend check-in will be connected/);
});

test('manual toggle opens the form, stops the camera and focuses the input', async () => {
    const harness = createControllerHarness();

    await harness.elements.manualButton.emit('click');

    assert.equal(harness.elements.fallback.hidden, false);
    assert.equal(harness.scanners[0].stopCount, 1);
    assert.equal(harness.elements.codeInput.focused, true);
});

test('leaving the page releases the camera', () => {
    const harness = createControllerHarness();

    harness.windowEvents.pagehide();

    assert.equal(harness.scanners[0].stopCount, 1);
});

test('a missing scanner component disables camera use and offers manual entry', () => {
    const harness = createControllerHarness({ scannerAvailable: false });

    assert.equal(harness.elements.cameraButton.hidden, true);
    assert.equal(harness.elements.fallback.hidden, false);
    assert.equal(harness.elements.status.dataset.state, 'error');
    assert.match(harness.elements.status.textContent, /cannot scan/i);
});

test('a second submit is ignored while the first request is in flight', async () => {
    const harness = createControllerHarness({ endpoint: '/api/check-in.php' });
    harness.elements.codeInput.value = 'token';

    const first = harness.elements.form.emit('submit');
    const second = harness.elements.form.emit('submit');
    await Promise.all([first, second]);

    assert.equal(harness.fetchCalls.length, 1);
    assert.equal(harness.elements.submitButton.disabled, false);
});

test('an empty qrToken query parameter does not pre-fill with the URL', () => {
    const harness = createControllerHarness({ search: '?qrToken=' });

    assert.equal(harness.elements.codeInput.value, '');
    assert.equal(harness.elements.fallback.hidden, true);
});

test('a URL with a malformed percent escape keeps the raw token', () => {
    const harness = createControllerHarness();

    harness.scanners[0].callbacks.onResult(
        'https://attendance.example.com/clock-in?qrToken=%E0%A4%A'
    );

    assert.equal(harness.elements.codeInput.value, '%E0%A4%A');
});

test('a server success message is shown instead of the generic text', async () => {
    const harness = createControllerHarness({
        endpoint: '/api/check-in.php',
        fetchImpl: async () => ({
            ok: true,
            status: 201,
            json: async () => ({
                ok: true,
                message: 'Welcome back, Alice.',
                data: { clocked_in_at_utc: '2026-11-27 08:00:00' }
            })
        })
    });
    harness.elements.codeInput.value = 'token';

    await harness.elements.form.emit('submit');

    assert.match(harness.elements.result.textContent, /Welcome back, Alice/);
    assert.doesNotMatch(harness.elements.result.textContent, /Clocked in successfully/);
});

test('a non-JSON error reply falls back to a generic message', async () => {
    const harness = createControllerHarness({
        endpoint: '/api/check-in.php',
        fetchImpl: async () => ({
            ok: false,
            status: 500,
            json: async () => { throw new Error('not JSON'); }
        })
    });
    harness.elements.codeInput.value = 'token';

    await harness.elements.form.emit('submit');

    assert.equal(harness.elements.result.dataset.state, 'error');
    assert.match(harness.elements.result.textContent, /Check-in failed/);
});
