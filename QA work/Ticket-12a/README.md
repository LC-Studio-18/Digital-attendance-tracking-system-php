# S2-12A - Live IN/OUT Badge (Frontend)

## Ticket Information
- **Ticket ID:** S2-12A
- **Assigned Developer(s):** Likhona Mkhatshana (`likhonamkhatshana10@gmail.com`)
- **QA:** Owam Gobingca
- **Scrum Master:** Not recorded
- **Branch Tested:** `frontend6-live-in-out` (Commit `c79e970`)
- **Last Updated:** 27 November 2026

## Ticket Goal
Build the reusable frontend Live IN/OUT attendance status badge (`render_status_badge()`, `status-badge.css`, `status-api.js`, `status-badge.js`) supporting 4 states (`IN`, `OUT`, `Checking...`, `Status unavailable`), connect it to the `S2-12B` status endpoint, refresh status after clock actions without full page reload, integrate shared `S2-13A` loading/error feedback components, and verify responsive layout (`360px` and desktop) plus greyscale/WCAG AA contrast.

## Assigned Subtasks

| Subtask | Description | QA Status |
|---|---|---|
| [ST-01.md](ST-01.md) | Build the badge with 4 states | Pass |
| [ST-02.md](ST-02.md) | Connect it to the S2-12B endpoint | Pending / Blocked (`config.php` has `status_endpoint => ''`; `S2-12B` contract/URL unconfirmed) |
| [ST-03.md](ST-03.md) | Refresh it after clock actions | Pending / Incomplete (`notifyClockSuccess` & event hook implemented; live post-clock refresh verification pending) |
| [ST-04.md](ST-04.md) | Use the shared loading and error components from S2-13A | Pending / Blocked (`StatusBadge.feedback` hooks defined; `S2-13A` shared components not yet wired in) |
| [ST-05.md](ST-05.md) | Test at 360px and desktop | Pass |
| [ST-06.md](ST-06.md) | Test greyscale and contrast | Pass |
| [ST-07.md](ST-07.md) | QA tested and approved | Pending / Blocked (3 open subtasks remaining) |

## Testing Scope
- PHP syntax validation (`php -l`) across `frontend/index.php`, `frontend/config.php`, `frontend/partials/status-badge.php`, `frontend/dev/harness.php`, and `frontend/dev/stub.php`
- Visual & functional verification of the 4 badge states (`IN`, `OUT`, `Checking...`, `Status unavailable`) via `dev/harness.php` and `dev/stub.php`
- Responsive layout check at `360px` mobile and desktop widths
- Greyscale distinguishability and WCAG AA text/background contrast verification
- Code & configuration audit for `S2-12B` endpoint wiring, post-clock refresh hook, and `S2-13A` shared component integration

## Dependencies
- **Hard / Integration Dependencies:**
  - `S2-12B` – Live status endpoint (Backend). **Not Connected** — `frontend/config.php` currently sets `'status_endpoint' => ''`, and `CONTRACT` in `status-api.js` notes that response fields (`status`, `IN`, `OUT`, `since`) are pending confirmation.
  - `S2-13A` – Shared loading, error, and retry UI components (Frontend). **Not Wired** — `status-badge.js` exposes `StatusBadge.feedback` (`onLoading`, `onResolved`, `onError`) hooks as placeholders rather than integrating shared `S2-13A` components.
  - Clock-in / Clock-out actions – **Pending Live Hookup** — `StatusBadge.notifyClockSuccess('in' | 'out')` dispatches `attendance:clock-success` to coalesce store refreshes, awaiting end-to-end wiring/testing with live clock actions.

## Testing Summary
- **Total test cases:** 7
- **Passed:** 4 (`ST-01`, `ST-05`, `ST-06`, plus PHP syntax validation on all 5 checked files)
- **Failed:** 0
- **Blocked:** 3 (`ST-02`, `ST-03`, and `ST-04` incomplete/pending; `ST-07` held pending completion)
- **Not Run:** 0 (Live `S2-12B` endpoint and end-to-end clock action integration remain pending backend/wiring completion)
- **Open bugs / QA findings:** 3 incomplete integration items (`ST-02` unconfigured `S2-12B` endpoint, `ST-03` pending live clock-action refresh verification, `ST-04` `S2-13A` shared feedback components not yet wired into `StatusBadge.feedback`)

## Outstanding Issues
1. **`S2-12B` Endpoint Not Connected (`ST-02`):** `frontend/config.php` has `'status_endpoint' => ''` and `status-api.js` marks `CONTRACT` (`status`, `IN`, `OUT`, `since`) as `TO CONFIRM WITH TEAM 6 (S2-12B)`.
2. **Post-Clock Refresh Not Yet Verified End-to-End (`ST-03`):** `StatusBadge.notifyClockSuccess()` and `attendance:clock-success` coalescing exist in code, but live verification after real clock-in/clock-out actions is still pending.
3. **`S2-13A` Shared Loading/Error Components Not Wired (`ST-04`):** `status-badge.js` provides `StatusBadge.feedback` hook functions, but `S2-13A` components are not yet connected.

## QA Sign-off
- **Status:** Pending / Not Approved (4/7 Subtasks Complete)
- **QA:** Owam Gobingca
- **Date:** 27 November 2026
- **Notes:** Badge states, `360px`/desktop layout, greyscale distinguishability, and WCAG AA contrast checks are in place and verified (`php -l` clean; `dev/harness.php` renders cleanly in browser). Held from final QA approval until `S2-12B` endpoint connection, live post-clock refresh verification, and `S2-13A` shared loading/error component integration are completed.