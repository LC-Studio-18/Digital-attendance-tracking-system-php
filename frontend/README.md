# S2-12A Live IN/OUT Badge (PHP + HTML/CSS/JS)

## Run locally
Requires PHP 8+. From this folder:

    php -S localhost:8000

- Clock screen: http://localhost:8000/index.php (badge shows "Status unavailable" until `config.php` has the real S2-12B URL)
- Test page (temporary stub): http://localhost:8000/dev/harness.php

## Files
| File | Purpose |
|---|---|
| `partials/status-badge.php` | `render_status_badge()` - the one reusable markup (`['show_detail' => true]` adds "You are currently Clocked In / Since 08:30") |
| `assets/css/status-badge.css` | The four visual states (LC Attends palette, Inter) |
| `assets/css/status-card.css` | Placeholder dark card + header from the Figma. Replace with S2-04 shared styles |
| `assets/js/status-api.js` | Fetch + response parsing + refresh logic (no DOM) |
| `assets/js/status-badge.js` | Rendering, screen-reader announcements, S2-13A hook points |
| `assets/img/lc-favicon.png` | LC logo (page favicon + header mark) |
| `config.php` | `status_endpoint` (set once S2-12B is agreed) |
| `dev/stub.php`, `dev/harness.php` | TEMPORARY test tools. Do not commit or deploy. |

## Use on another page (dashboard, S2-23, S2-16B)
    <?php require 'partials/status-badge.php'; ?>
    <link rel="stylesheet" href="assets/css/status-badge.css">
    <script src="assets/js/status-api.js" defer></script>
    <script src="assets/js/status-badge.js" defer></script>
    ...
    <?php render_status_badge(); ?>

## Hook into the existing clock-in/out code
Call this only after the backend confirms the action succeeded:

    StatusBadge.notifyClockSuccess('in');   // or 'out'

Do not call it when the action fails; the badge then stays as it is.

## Still to confirm (blocked)
1. S2-12B: endpoint URL, auth method and JSON format. Assumed `{"status":"IN"|"OUT","since":"..."}` in one place (`CONTRACT` in `status-api.js`). `since` is optional (the real time of the last clock action, "HH:MM" or ISO 8601; ISO values are shown in the browser's local time). If absent, the "Since" line is hidden.
2. S2-13A: shared loading/error components. Wire them into `StatusBadge.feedback.*` in `status-badge.js`.
3. Label wording: pill says IN/OUT (ticket); Figma says "Clocked In/Out". Change in `LABELS` in `status-badge.js` if the designer/PO decides otherwise.
4. Figma shows a lime pill for BOTH states; here OUT is white (ink text) so colour also separates the states. Change in `status-badge.css` if the Figma version is required.
5. Mobile Figma frames: layout follows a standard single-column phone layout.

## Contrast (calculated, WCAG AA needs 4.5:1 for text)
| State | Text/background |
|---|---|
| IN (ink on lime) | 12.85:1 |
| OUT (ink on white) | 17.96:1 |
| Checking... (white on teal) | 6.03:1 |
| Status unavailable (ink on white) | 17.96:1 |

## Test results (do not tick Jira subtasks from this table until you have re-run these yourself)
Browser checks were run in headless Chromium against a static copy of the markup, with the status
responses intercepted by the test script. PHP is not installed in the build environment, so the
PHP files, the stub and the harness have NOT been run.

| Test | Result |
|---|---|
| Parsing/format logic (13 cases: IN, OUT, lowercase, missing, null, bad time) | Pass (Node) |
| Loading shows "Checking..." and not IN/OUT | Pass (Chromium, 360px) |
| IN and OUT shown as returned | Pass (Chromium) |
| Unusable data (wrong field, lowercase "in", not JSON) -> "Status unavailable" | Pass (Chromium) |
| Server error on refresh -> "Status unavailable" | Pass (Chromium) |
| Update after success, no page reload | Pass; 0.04 s with an instant mocked response. Re-test with the real endpoint for the 2-second limit |
| Failed clock action leaves badge unchanged, no extra request | Pass for the badge logic. Not tested against your real clock code |
| Two rapid success events -> one request | Pass (Chromium) |
| No horizontal scroll, badge inside card at 360px and 1280px | Pass (Chromium) |
| Greyscale: all four states distinguishable | Pass on screenshots (text + icon + border differ) |
| Contrast ratios | Calculated above |
| Screen reader announcements | Live-region text checked only. Needs a real screen reader (NVDA/VoiceOver) |
| PHP partial, stub, harness, index.php | Not run |
| Real S2-12B endpoint | Not run (blocked) |
| Xneelo test URL | Not run |
