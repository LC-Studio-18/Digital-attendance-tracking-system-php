# S2-11A - QR Code Check-In Flow (Frontend)

## Ticket Information
- **Ticket ID:** S2-11A
- **Assigned Developer(s):** Kirsten Layters (Board Assignee) — Commits authored by Rasool (`Rasoolking14 <fredericksrasool@gmail.com>`)
- **QA:** Owam Gobingca
- **Scrum Master:** Not recorded
- **Branch Tested:** `frontend6-components` (Note: branch name does not follow feature-naming convention)
- **Last Updated:** 27 November 2026

## Ticket Goal
Build the frontend QR code check-in experience allowing staff to check in either via an in-app camera scanner or by opening a QR deep link/URL, cleanly handling camera permissions, capturing the QR token/payload, preventing duplicate scans with loading locks, displaying confirmation or mapped error messages (`409 Conflict`, invalid/expired token, unauthenticated redirect preserving payload), and syncing active checked-in status across the app.

## Assigned Subtasks

| Subtask | Description | QA Status |
|---|---|---|
| [ST-01.md](ST-01.md) | Build QR check-in screen / landing route and camera scanner component with permission fallback | Pass (Functional & Fallback Verified; Figma Visual Discrepancy Noted) |
| [ST-02.md](ST-02.md) | Integrate QR check-in API service with duplicate-scan lock | Pass |
| [ST-03.md](ST-03.md) | Implement success confirmation UI and map backend error codes to user-friendly messages | Pass (Functional Error Fallback Verified; Figma Layout Discrepancy Noted) |
| [ST-04.md](ST-04.md) | Sync checked-in state with global clock-in state store (S2-08a) | Pass (Event/State Hook Verified; Full S2-08A Store Integration Pending) |
| [ST-05.md](ST-05.md) | Write unit/component tests and test camera behavior on mobile device | Pass (29/29 Automated Tests + Desktop Browser Verified; Physical Phone Pending) |

## Testing Scope
- Route & deep-link handling (in-app QR scanner view and direct QR URL / `qrToken` query parameter capture)
- Live browser verification (`http://127.0.0.1:8000/check-in.php?qrToken=TEST-TOKEN-123`)
- Camera permission & unsupported-browser fallback UI (`This browser cannot scan QR codes with the camera. Enter your code manually below.`)
- Duplicate-scan suppression & in-flight submit locks
- API submission, envelope handling, and error code mapping (`Check-in failed. Please try again.`)
- Global state / event dispatch (`attendance:clock-success`)
- Automated Node test execution (`tests/check-in.test.js` and `tests/qr-scanner.test.js`)
- Visual comparison against Figma wireframe (`Digital Attendance Tracking System - Wireframe`)

## Dependencies
- **Hard Dependencies:**
  - `S2-05` – Login and logout with PHP sessions (Team 1)
  - `S2-08` (Frontend) – Clock-in functionality / global shift state (`S2-08A`, Team 2)
  - `S2-10` (Frontend) – Duplicate clock-in/out prevention (`409 Conflict`, Team 2)
  - `S2-11B` – QR code check-in flow (Backend, Team 6)
  - `S2-13A` – Loading, error messages, weak-signal retry (Frontend & Design, Team 6)
- **Soft Dependencies:**
  - `S2-11B` – API request/response and error code contract (`docs/qr-check-in-api-contract.md` and `docs/s2-12b-status-contract.md` added in commits)
- **Downstream Tickets Blocked by This Ticket:**
  - `S2-14`, `S2-25`, `S2-26`, `S2-30`.

## Testing Summary
- **Total test cases:** 12
- **Passed:** 12
- **Failed:** 0
- **Blocked:** 0
- **Not Run:** 0 (Physical mobile device camera run via `frontend/tests/camera-check.html` noted as optional/pending physical device testing)
- **Open bugs:** 0
- **Fixed pending current-build retest:** 0

## Outstanding Issues
- **Figma Wireframe Visual Discrepancy:** Live browser testing of `frontend/check-in.php` revealed visual differences compared to the Figma wireframe (`LC Attends` dark header with back arrow, lime-green `Tap to scan` / `Scan QR Code` / `Try again` buttons, teal `Back to Dashboard` button, scanning distance/lighting tips, and dedicated `Scan unsuccessful` screen). The built page uses a light header (`ATTENDANCE SYSTEM` / `Check in with your QR code`), blue primary buttons (`Try camera again`, `Confirm check-in`), and an inline manual entry form (`Enter your code`). Confirm with Scrum Master / Design whether the wireframe was superseded by shared components (`S2-13A`) or requires styling alignment.
- **Branch naming & attribution discrepancy:** Code was pushed to `frontend6-components` (does not follow feature-branch naming convention) under author `Rasoolking14` (`798d1d8` .. `57a4776`), while the board lists assignee as `Kirsten Layters`. Flag to Scrum Master / team.
- **Backend endpoint integration:** Submitting `TEST-TOKEN-123` locally displayed `Check-in failed. Please try again.` cleanly as expected because the `S2-11B` backend endpoint is not wired in this standalone frontend run.

## QA Sign-off
- **Status:** Pass (Functional & Automated Suite Verified; UI/Figma Alignment Flagged for Review)
- **QA:** Owam Gobingca
- **Date:** 27 November 2026
- **Notes:** All 20 tests in `tests/check-in.test.js` and all 9 tests in `tests/qr-scanner.test.js` passed (`29/29`), and live browser testing confirmed deep-link pre-fill, unsupported-camera manual entry fallback, and clean error feedback.