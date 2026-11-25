# QR check-in tests (Task 3)

This folder contains the tests for the QR check-in scanner work. Everything here is
new and only exercises the QR check-in files; no other contributor's code is touched.

## What is here

| File | Purpose |
|---|---|
| `fake-dom.js` | Dependency-free stand-ins for the browser APIs used by our scripts. Loads the real browser files in Node's `vm` so we do not need jsdom or a bundler. |
| `qr-scanner.test.js` | Unit tests for `frontend/assets/js/qr-scanner.js`: support detection, permission errors, camera lifecycle, frame decoding, duplicate suppression and detector failures. |
| `check-in.test.js` | Component tests for `frontend/assets/js/check-in.js`: fallback UI, scan-to-form behaviour, local validation, POST hook, backend error display and camera cleanup. |
| `../frontend/tests/camera-check.html` | Manual QA page to run on a real phone with the real camera component. |
| `../frontend/tests/camera-check.js` | Controller for that page; reports environment facts and scanner events. |

## Running the automated tests

Requires Node 18+ (uses the built-in `node:test` runner; no `npm install` needed).

From the project root:

```powershell
node --test tests/*.test.js
```

Current result: 18 tests, all passing. The tests use fake cameras and fake
detector results, so they are deterministic and never ask for real permission.

## What the automated tests do not cover

They cannot prove that a physical camera works. For that, use the mobile page.

## Mobile camera check

1. Serve the project over HTTP from the `frontend/` web root, for example:

   ```powershell
   php -S 0.0.0.0:8000 -t frontend
   ```

2. Open `http://<your-computer-ip>:8000/tests/camera-check.html` on your phone.
   Plain HTTP on a phone will **not** grant camera access; use HTTPS (a tunnel
   such as ngrok, or your deployed HTTPS URL) if the phone is not on localhost.
3. Check the Environment panel:
   - Secure context: **yes**
   - BarcodeDetector: **available** (Safari/Firefox will usually say not available)
   - Formats should include `qr_code`
   - Camera permission should change to `granted` after you accept
4. Tap **Start camera** and accept the prompt. The rear camera should be used and
   the preview should appear.
5. Hold any QR code in front of the lens. The decoded value should appear next to
   "Last decoded code", and the log should show a `result` entry.
6. Tap **Stop camera**. The preview clears and the log shows `stopped`; the phone's
   camera indicator should turn off.
7. Revoke camera permission in the browser's site settings, reload, and start
   again. The log should show `error: permission-denied` and a clear message.
8. Open `../check-in.php` on the phone and confirm that blocking the camera
   reveals the manual-entry form with a readable explanation.

Record the phone model, OS version and browser for each run so failures can be
reproduced. If BarcodeDetector is unsupported, that is expected on some browsers;
the manual-entry fallback is the intended behaviour there.
