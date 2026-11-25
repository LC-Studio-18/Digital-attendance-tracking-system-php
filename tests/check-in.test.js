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

test('a configured endpoint receives a JSON POST with the scanned code', async () => {
    const harness = createControllerHarness({ endpoint: '/api/check-in.php' });
    harness.elements.codeInput.value = 'STAFF-42';

    await harness.elements.form.emit('submit');

    assert.equal(harness.fetchCalls.length, 1);
    assert.equal(harness.fetchCalls[0].url, '/api/check-in.php');
    assert.equal(harness.fetchCalls[0].init.method, 'POST');
    assert.deepEqual(JSON.parse(harness.fetchCalls[0].init.body), { code: 'STAFF-42' });
    assert.match(harness.elements.result.textContent, /Checked in successfully/);
    assert.equal(harness.elements.submitButton.disabled, false);
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
