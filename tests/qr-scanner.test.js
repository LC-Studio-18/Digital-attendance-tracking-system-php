/**
 * Unit tests for frontend/assets/js/qr-scanner.js.
 *
 * These verify the camera lifecycle, permission/unsupported fallbacks, frame
 * detection and duplicate suppression without needing a real camera. Run with:
 *   node --test tests
 */

'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const { createScannerHarness } = require('./fake-dom');

function states(harness) {
    return harness.events
        .filter((event) => event.type === 'state')
        .map((event) => ({ state: event.state, reason: event.reason }));
}

function results(harness) {
    return harness.events
        .filter((event) => event.type === 'result')
        .map((event) => event.code);
}

test('isSupported() reflects BarcodeDetector availability', () => {
    assert.equal(createScannerHarness().sandbox.QrScanner.isSupported(), true);
    assert.equal(createScannerHarness({ supported: false }).sandbox.QrScanner.isSupported(), false);
});

test('start() requests permission, begins scanning and decodes a QR value', async () => {
    const harness = createScannerHarness({
        detectionResults: [{ rawValue: '  STAFF-123  ' }]
    });

    await harness.scanner.start();

    assert.deepEqual(states(harness), [
        { state: 'requesting', reason: undefined },
        { state: 'scanning', reason: undefined }
    ]);
    assert.equal(harness.video.srcObject, harness.stream);

    await harness.frames.run(1000);

    assert.deepEqual(results(harness), ['STAFF-123']);
});

test('denied camera permission reports permission-denied and never scans', async () => {
    const harness = createScannerHarness({
        getUserMedia: async () => {
            throw { name: 'NotAllowedError' };
        }
    });

    await harness.scanner.start();

    assert.deepEqual(states(harness), [
        { state: 'requesting', reason: undefined },
        { state: 'error', reason: 'permission-denied' }
    ]);
    assert.equal(harness.video.srcObject, null);
});

test('missing BarcodeDetector reports unsupported without prompting for a camera', async () => {
    const harness = createScannerHarness({ supported: false });

    await harness.scanner.start();

    assert.deepEqual(states(harness), [
        { state: 'requesting', reason: undefined },
        { state: 'error', reason: 'unsupported' }
    ]);
    assert.equal(harness.getUserMediaCalls(), 0);
});

test('an insecure context reports insecure-context before touching the camera', async () => {
    const harness = createScannerHarness({ secure: false });

    await harness.scanner.start();

    assert.equal(states(harness)[1].reason, 'insecure-context');
    assert.equal(harness.getUserMediaCalls(), 0);
});

test('a browser without QR format support reports unsupported-format', async () => {
    const harness = createScannerHarness({ formats: ['code_128'] });

    await harness.scanner.start();

    assert.equal(states(harness)[1].reason, 'unsupported-format');
    assert.equal(harness.getUserMediaCalls(), 0);
});

test('stop() releases camera tracks and cancels pending frames', async () => {
    const harness = createScannerHarness();

    await harness.scanner.start();
    await harness.scanner.stop();

    assert.equal(harness.stream.tracks[0].stopped, true);
    assert.equal(harness.video.srcObject, null);
    assert.equal(states(harness).at(-1).state, 'stopped');
    assert.equal(harness.frames.pending(), 0);
});

test('the same code is not reported twice inside the duplicate window', async () => {
    const harness = createScannerHarness({
        detectionResults: [{ rawValue: 'STAFF-1' }]
    });

    await harness.scanner.start();
    await harness.frames.run(1000); // first detection: now = 0
    harness.advance(500);
    await harness.frames.run(1200); // still inside the 1500 ms window
    assert.equal(results(harness).length, 1);

    harness.advance(1100); // now = 1600 ms, outside the window
    await harness.frames.run(1400);
    assert.equal(results(harness).length, 2);
});

test('a detector failure reports detect-error and stops the loop', async () => {
    const harness = createScannerHarness({
        detectionError: new Error('detector failed')
    });

    await harness.scanner.start();
    await harness.frames.run(1000);

    assert.equal(states(harness).at(-1).reason, 'detect-error');
    assert.equal(harness.frames.pending(), 0);
});
