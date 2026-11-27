# S2-05-05 - Start Session and Store Staff ID and Role

## Information
- Parent ticket: S2-05 - Login and Logout with PHP Sessions
- Assigned developer: Caleb
- QA status: Blocked (latest frontend login cookie omits HttpOnly and SameSite)
- Build / commit SHA: `f2aa1a6` (`origin/team1`)
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- Test environment: Windows/XAMPP; PHP 8.0.30; `attendance_test_db`; local PHP built-in server and browser.

## Objective
- On successful authentication, regenerate the session ID and save the authenticated staff ID and role.

## Acceptance Criteria
- [x] Session ID is regenerated after successful login.
- [x] Session stores the correct staff ID and role.
- [x] Failed login does not leave an authenticated session.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Log in successfully as staff and admin and inspect session state | Session ID changes and the correct user ID and role are stored | On `f2aa1a6`, Chrome's active QA staff login POST returned HTTP 501, then the same browser session accessed protected `frontend/index.php` with HTTP 200. The cookie captured on `cbcf8ae` included only `Path`; HttpOnly and SameSite were absent. No authentication code changed in `f2aa1a6`; `frontend/login-submit.php` still starts the session before the session helper can configure its cookie. | Blocked | BUG-009; successful-login routing remains HTTP 501. Chrome admin login was not tested. |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-26 | QA | backend test | `f16502f` | Blocked | Staff session path passed in CLI; admin path and cookie SameSite correction remain pending. |
| 2026-11-26 | QA | MySQL/HTTP retest | `f16502f` + MySQL QA fixtures | Blocked | Staff/admin identity and role storage verified; SameSite=Lax remains absent (BUG-009). |
| 2026-11-27 | QA | merged frontend/backend retest | `2ae8032` (`origin/team1`) | Blocked | Staff/admin sessions store the expected identity and role. Frontend login cookie omits HttpOnly and SameSite; direct backend login still omits SameSite. Successful login returns HTTP 501. |
| 2026-11-27 | QA | latest team1 session retest | `cbcf8ae` (`origin/team1`) | Blocked | QA staff session authenticated and reached the protected page; frontend login response cookie still had neither HttpOnly nor SameSite. |
| 2026-11-27 | QA | latest team1 Chrome session retest | `f2aa1a6` (`origin/team1`) | Blocked | Valid staff login POST returned 501, but the same Chrome session reached the protected page with HTTP 200. Cookie attributes were last directly captured on cbcf8ae; no auth code changed in f2aa1a6. |

## Linked Bugs
BUG-009 - Session cookie does not set the intended SameSite=Lax attribute. The merged frontend login path also omits HttpOnly.
