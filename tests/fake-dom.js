/**
 * Minimal browser test doubles for the QR check-in browser scripts.
 *
 * Why this exists: qr-scanner.js and check-in.js are plain browser scripts with
 * no framework or bundler. Instead of adding a heavyweight dependency (jsdom),
 * this file runs them in Node's `vm` with small stubs for the browser APIs they
 * actually use. That keeps the tests fast and dependency-free.
 *
 * Exports two harness builders:
 *  - createScannerHarness(): loads qr-scanner.js with a fake camera/detector.
 *  - createControllerHarness(): loads check-in.js with fake page elements.
 */

'use strict';

const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const PROJECT_ROOT = path.resolve(__dirname, '..');
const SCANNER_SOURCE = fs.readFileSync(
    path.join(PROJECT_ROOT, 'frontend/assets/js/qr-scanner.js'),
    'utf8'
);
const CONTROLLER_SOURCE = fs.readFileSync(
    path.join(PROJECT_ROOT, 'frontend/assets/js/check-in.js'),
    'utf8'
);

/** Small stand-in for a DOM element: attributes, listeners, text and visibility. */
class FakeElement {
    constructor(tagName = 'div') {
        this.tagName = tagName.toUpperCase();
        this.dataset = {};
        this.hidden = false;
        this.textContent = '';
        this.value = '';
        this.disabled = false;
        this.focused = false;
        this._listeners = new Map();
    }

    addEventListener(type, handler) {
        if (!this._listeners.has(type)) {
            this._listeners.set(type, []);
        }
        this._listeners.get(type).push(handler);
    }

    /** Calls listeners; async so the controller's submit handler can settle. */
    async emit(type, event = {}) {
        const eventObject = Object.assign({ preventDefault() {} }, event);
        for (const handler of this._listeners.get(type) || []) {
            await handler(eventObject);
        }
    }

    focus() {
        this.focused = true;
    }

    setAttribute(name, value) {
        this[name] = value;
    }

    getAttribute(name) {
        return this[name];
    }
}

/** Video stub with the readyState/size properties the scanner reads. */
class FakeVideo extends FakeElement {
    constructor() {
        super('video');
        this.srcObject = null;
        this.muted = false;
        this.playsInline = false;
        this.readyState = 2; // HAVE_CURRENT_DATA
        this.videoWidth = 640;
        this.videoHeight = 480;
    }

    play() {
        return Promise.resolve();
    }
}

/** Canvas stub; records drawImage calls so tests can confirm frames are copied. */
class FakeCanvas extends FakeElement {
    constructor() {
        super('canvas');
        this.width = 0;
        this.height = 0;
        this.drawCalls = 0;
        this.context = {
            drawImage: () => {
                this.drawCalls += 1;
            }
        };
    }

    getContext() {
        return this.context;
    }
}

/** A MediaStream with one track whose stop() is observable. */
class FakeTrack {
    constructor() {
        this.stopped = false;
    }

    stop() {
        this.stopped = true;
    }
}

class FakeStream {
    constructor() {
        this.tracks = [new FakeTrack()];
    }

    getTracks() {
        return this.tracks;
    }
}

/**
 * Manual animation-frame queue. Tests call run() to advance exactly one frame
 * instead of waiting on real time, which keeps detection tests deterministic.
 */
function createFrameScheduler() {
    let nextId = 1;
    const queued = new Map();

    return {
        requestAnimationFrame(callback) {
            const id = nextId;
            nextId += 1;
            queued.set(id, callback);
            return id;
        },
        cancelAnimationFrame(id) {
            queued.delete(id);
        },
        pending() {
            return queued.size;
        },
        async run(timestamp = 1000) {
            const callbacks = [...queued.values()];
            queued.clear();
            for (const callback of callbacks) {
                await callback(timestamp);
            }
        }
    };
}

/**
 * Loads qr-scanner.js into a fake browser and returns handles for assertions.
 * Options: secure, supported, formats, detectionResults, detectionError,
 * getUserMedia, and video.
 */
function createScannerHarness(options = {}) {
    const {
        secure = true,
        supported = true,
        formats = ['qr_code'],
        detectionResults = [],
        detectionError = null,
        getUserMedia = null,
        video = new FakeVideo()
    } = options;

    const frames = createFrameScheduler();
    const events = [];
    const stream = new FakeStream();
    let now = 0;
    let getUserMediaCalls = 0;

    // A controllable clock makes the duplicate-scan window testable.
    const Clock = class extends Date {
        static now() {
            return now;
        }
    };

    class BarcodeDetectorStub {
        constructor(config) {
            this.config = config;
        }

        static async getSupportedFormats() {
            return formats;
        }

        async detect() {
            if (detectionError) {
                throw detectionError;
            }
            return detectionResults;
        }
    }

    const sandbox = {
        console,
        Date: Clock,
        HTMLVideoElement: FakeVideo,
        isSecureContext: secure,
        navigator: {
            mediaDevices: {
                getUserMedia: async (constraints) => {
                    getUserMediaCalls += 1;
                    if (getUserMedia) {
                        return getUserMedia(constraints, stream);
                    }
                    return stream;
                }
            }
        },
        document: {
            createElement: (tagName) => (
                tagName === 'canvas' ? new FakeCanvas() : new FakeElement(tagName)
            )
        },
        requestAnimationFrame: frames.requestAnimationFrame,
        cancelAnimationFrame: frames.cancelAnimationFrame
    };

    if (supported) {
        sandbox.BarcodeDetector = BarcodeDetectorStub;
    }

    // The scripts use `window` both as a global and as the export target.
    sandbox.window = sandbox;
    vm.createContext(sandbox);
    vm.runInContext(SCANNER_SOURCE, sandbox);

    const scanner = new sandbox.QrScanner(video, {
        onResult: (code) => events.push({ type: 'result', code }),
        onStateChange: (state, reason) => events.push({ type: 'state', state, reason })
    });

    return {
        sandbox,
        scanner,
        video,
        stream,
        frames,
        events,
        getUserMediaCalls: () => getUserMediaCalls,
        advance: (milliseconds) => {
            now += milliseconds;
        }
    };
}

// data-checkin-* hooks used by check-in.js, mapped to friendly test keys.
const SELECTORS = {
    status: '[data-checkin-status]',
    video: '[data-checkin-video]',
    placeholder: '[data-checkin-placeholder]',
    cameraButton: '[data-checkin-camera]',
    stopButton: '[data-checkin-stop]',
    manualButton: '[data-checkin-manual]',
    fallback: '[data-checkin-fallback]',
    fallbackMessage: '[data-checkin-fallback-message]',
    form: '[data-checkin-form]',
    codeInput: '[data-checkin-code]',
    submitButton: '[data-checkin-submit]',
    result: '[data-checkin-result]'
};

/**
 * Loads check-in.js into a fake page and returns handles for assertions.
 * Options: scannerAvailable, endpoint, and fetchImpl (custom response).
 */
function createControllerHarness(options = {}) {
    const { scannerAvailable = true, endpoint = '', fetchImpl = null } = options;

    const bySelector = new Map();
    const elements = {};

    for (const [key, selector] of Object.entries(SELECTORS)) {
        const element = new FakeElement('div');
        bySelector.set(selector, element);
        elements[key] = element;
    }

    // Mirror the initial HTML: these three elements start with `hidden`.
    elements.status.dataset.state = 'idle';
    elements.result.dataset.state = 'idle';
    elements.result.hidden = true;
    elements.fallback.hidden = true;
    elements.stopButton.hidden = true;
    elements.form.dataset.checkinEndpoint = endpoint;

    const scanners = [];

    class StubScanner {
        constructor(video, callbacks) {
            this.video = video;
            this.callbacks = callbacks;
            this.stopCount = 0;
            scanners.push(this);
        }

        async start() {
            this.callbacks.onStateChange('requesting');
            this.callbacks.onStateChange('scanning');
        }

        stop() {
            this.stopCount += 1;
            this.callbacks.onStateChange('stopped');
        }
    }

    const fetchCalls = [];
    const fetchStub = async (url, init) => {
        fetchCalls.push({ url, init });
        if (fetchImpl) {
            return fetchImpl(url, init);
        }
        return {
            ok: true,
            json: async () => ({ message: 'Checked in successfully.' })
        };
    };

    const windowEvents = {};

    const sandbox = {
        console,
        document: {
            querySelector: (selector) => bySelector.get(selector) || null
        },
        fetch: fetchStub,
        addEventListener: (type, handler) => {
            windowEvents[type] = handler;
        }
    };

    if (scannerAvailable) {
        sandbox.QrScanner = StubScanner;
    }

    sandbox.window = sandbox;
    vm.createContext(sandbox);
    vm.runInContext(CONTROLLER_SOURCE, sandbox);

    return { elements, scanners, fetchCalls, windowEvents, sandbox };
}

module.exports = {
    FakeElement,
    FakeVideo,
    FakeCanvas,
    FakeStream,
    createFrameScheduler,
    createScannerHarness,
    createControllerHarness
};
