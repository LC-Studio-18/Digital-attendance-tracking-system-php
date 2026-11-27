# S2-11B - QR Code Check-In Flow (Backend)

## Ticket Information
- **Ticket ID:** S2-11B
- **Assigned Developer(s):** Stephan Bennett (`stephanbennett42-ctrl`)
- **QA:** Owam Gobingca
- **Scrum Master:** Not recorded
- **Branch Tested:** `backend6-qr-checkin` (Commit `2b64096`)
- **Last Updated:** 27 November 2026

## Ticket Goal
Implement the backend QR code check-in flow: standalone QR token issuer and validator (`QrTokenService.php`), QR clock-in service and `POST /backend/api/attendance/qr-clock-in.php` endpoint integrating `S2-08-BE` attendance persistence and `S2-10-BE` duplicate active-shift prevention (`SELECT ... FOR UPDATE`), MySQL migration (`002_qr_clock_in.sql`), unit/endpoint test suites, and a shared JSON API/error contract with Frontend (`S2-11A`).

## Assigned Subtasks

| Subtask | Description | QA Status |
|---|---|---|
| [ST-01.md](ST-01.md) | Build standalone QR token issuer and validator (active status + expiration + site mapping) | Pass |
| [ST-02.md](ST-02.md) | Implement QR clock-in endpoint integrating S2-08-BE persistence and S2-10-BE duplicate checks | Blocked / Needs Integration (Inline `$_SESSION`, `INSERT`, and `FOR UPDATE` logic written instead of reusing `S2-06`, `S2-08B`, and `S2-10B`) |
| [ST-03.md](ST-03.md) | Add schema/migration for QR tracking fields and write unit/integration tests | Pass (15/15 unit/endpoint tests pass & `002_qr_clock_in.sql` DDL verified; live DB apply/execution still to run) |
| [ST-04.md](ST-04.md) | Share API request/response and error code contract with Frontend | Blocked / Needs Integration (Contract shared with Frontend, but `qr-clock-in.php` bypasses `S2-13B` `error-handler.php` and `IdempotencyService.php`) |
| [ST-05.md](ST-05.md) | QA tested and approved | Blocked / Needs Integration |

## Testing Scope
- Standalone QR token generation (64-char hex), redirect URL formatting (`<app-url>/clock-in?qrToken=...`), expiration, active flag, and site/QR ID mapping (`QrTokenServiceTest.php`)
- QR clock-in business rules & HTTP endpoint flow (`QrClockInServiceTest.php`, `QrClockInEndpointTest.php`, `backend/api/attendance/qr-clock-in.php`)
- Database migration DDL inspection (`database/migrations/002_qr_clock_in.sql`)
- Cross-team dependency reuse audit (`S2-03`, `S2-05`, `S2-06`, `S2-08B`, `S2-10B`, `S2-13B`) and API contract alignment with Frontend (`S2-11A`)

## Dependencies
- **Hard Dependencies:**
  - `S2-03` – Set up MySQL database and attendance tables. **Met / Schema Pulled** (`database/schema.sql` pulled in `50a26ee` and `002_qr_clock_in.sql` added; uses `staff_id`).
  - `S2-05` & `S2-06` – Login/logout with PHP sessions and reusable "logged in" check (Team 1). **Not Reused** — `qr-clock-in.php` checks `$_SESSION` keys (`user_id`, `staff_id`, `id`) inline rather than calling a shared `S2-06` helper.
  - `S2-08` (`S2-08B`) – Develop Clock-in functionality (Team 2: reuse core clock-in persistence). **Not Reused** — `qr-clock-in.php` runs a direct `INSERT INTO attendance` statement inside the endpoint instead of reusing `S2-08B` persistence.
  - `S2-10` (`S2-10B`) – Prevent duplicate clock-ins and clock-outs (Team 2: enforce duplicate-session checks). **Not Reused** — `qr-clock-in.php` runs its own `SELECT ... FOR UPDATE` check for `clock_out IS NULL` inline rather than reusing `S2-10B`.
  - `S2-13B` – Loading, error messages, weak-signal retry (Team 6: standardized error responses & idempotent retry handling). **Not Reused** — `qr-clock-in.php` does not include `backend/includes/error-handler.php` or `IdempotencyService.php` (`request_token`), returning custom `{ ok, code, message }` JSON directly.

## Testing Summary
- **Total test cases:** 15
- **Passed:** 15 (all unit and endpoint tests pass in isolation)
- **Failed:** 0
- **Blocked:** 3 (`ST-02`, `ST-04`, and `ST-05` blocked from full approval due to unintegrated/bypassed dependencies)
- **Not Run:** 0
- **Open bugs / QA findings:** 1 (Dependency reuse violation — reimplemented `S2-06`/`S2-08B`/`S2-10B` logic inline and bypassed Team 6's `S2-13B` `error-handler.php` and `IdempotencyService.php`)

## Outstanding Issues
1. **Hard Dependencies Not Reused (`S2-06`, `S2-08B`, `S2-10B`, `S2-13B`):** Rather than reusing existing team helpers/services—including Team 6's own `backend/includes/error-handler.php` and `IdempotencyService.php` (`request_token`)—`backend/api/attendance/qr-clock-in.php` embeds inline session lookup, duplicate-session `FOR UPDATE` query, direct `INSERT`, and custom `json_encode` responses.
2. **Column Naming & Migration Merge Note:** `002_qr_clock_in.sql` uses `staff_id` on `attendance` (matching `schema.sql`), whereas acceptance criteria refer to `user_id`. Integration (`S2-14`) must also combine `002_qr_clock_in.sql` cleanly with `S2-13B`'s `request_token` column.

## QA Sign-off
- **Status:** Blocked / Needs Dependency Integration
- **QA:** Owam Gobingca
- **Date:** 27 November 2026
- **Notes:** All 15 unit and endpoint tests (`6/6` in `QrTokenServiceTest.php`, `5/5` in `QrClockInServiceTest.php`, and `4/4` in `QrClockInEndpointTest.php`) pass, and `002_qr_clock_in.sql` is structurally sound, but `qr-clock-in.php` must integrate with `S2-06`, `S2-08B`, `S2-10B`, and `S2-13B` (`error-handler.php` / `IdempotencyService.php`) before final QA approval.