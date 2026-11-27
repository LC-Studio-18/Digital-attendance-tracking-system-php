# S2-05-08 - QA Test Login/Logout Scenarios and Record Approval

## Information
- Parent ticket: S2-05 - Login and Logout with PHP Sessions
- Assigned developer: Team 1 QA
- QA status: Blocked (authentication is verified; route, cookie, logout/browser, and runtime blockers remain)
- Build / commit SHA: `f2aa1a6` (`origin/team1`)
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- Test environment: Windows/XAMPP; PHP 8.0.30 (README requires 8.2+); MySQL `attendance_test_db`; local PHP built-in server and browser.

## Objective
- Verify valid login, wrong password, unknown email, deactivated account, logout, and browser Back behavior; record results before QA approval.

## Acceptance Criteria
- [x] Valid staff and admin credentials tested against MySQL fixtures and the HTTP handler.
- [x] Wrong password, unknown email, and deactivated account tested against MySQL fixtures and the HTTP handler.
- [ ] Logout and browser Back behavior are tested.
- [x] Results and evidence are recorded; approval remains withheld until all criteria pass.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Run the complete login/logout scenario set from the objective | Each result is recorded against the expected behavior and outstanding defects are linked | On `f2aa1a6`, a valid staff login POST returned HTTP 501, then the authenticated protected page returned HTTP 200 and rendered “Attendance system setup complete.” A synthetic invalid login returned HTTP 302 to `frontend/login.php`, where “Invalid email or password.” was visibly rendered. A guest request still redirects to `/login.php` and ends at HTTP 404. No logout control/route is present in the tested UI; logout and browser Back remain untested. | Blocked | Do not approve: BUG-009, BUG-010, and BUG-011 remain open; successful-login routing returns 501, cookie attributes are missing, logout/browser coverage is incomplete, and PHP 8.0.30 is below the documented minimum. [Valid-login 501](../evidence/TC-003-valid-login-501-blocked.png); [generic failure](../evidence/TC-004-generic-login-error-pass.png); [authenticated page/no logout](../evidence/TC-005-authenticated-page-no-logout-blocked.png); [guest redirect 404](../evidence/TC-006-guest-redirect-404-team1.png) |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-26 | QA | backend test | `f16502f` | Blocked | Isolated checks recorded; full integration and browser acceptance criteria remain pending. |
| 2026-11-26 | QA | MySQL/HTTP retest | `f16502f` + MySQL QA fixtures | Blocked | Database setup and backend HTTP scenarios exercised; outstanding UI, route, SameSite, seeder, and runtime blockers prevent sign-off. |
| 2026-11-27 | QA | Backend regression retest | `f16502f` (matches `origin/backend1`) | Blocked | PHP lint, MySQL checks, staff/admin HTTP login, generic failures, and logout retested. BUG-009, HTTP 501 dashboard placeholder, and missing frontend/protected-route/browser coverage remain. |
| 2026-11-27 | QA | merged frontend/backend retest | `2ae8032` (`origin/team1`) | Blocked | Login UI/error rendering and protected dashboard guard tested. Valid login returns HTTP 501; frontend cookie omits HttpOnly/SameSite; logout/browser Back, admin-route coverage, and PHP 8.2+ validation remain outstanding. |
| 2026-11-27 | QA | latest team1 sign-in regression retest | `cbcf8ae` (`origin/team1`) | Blocked | Valid QA login authenticated and reached the protected page; HTTP 501 remains. Cookie flags are missing and the visitor redirect target returns 404. |
| 2026-11-27 | QA | isolated browser screenshot retest | `f2aa1a6` (`origin/team1`) | Blocked | Valid login POST returned 501; authenticated page returned 200. Synthetic invalid login displayed the generic error. Guest redirect ends at 404; no logout control/route is available. Screenshots are linked in the test result. |

## Linked Bugs
- BUG-009 - Session cookie SameSite=Lax is missing.
- Frontend login wrapper also omits HttpOnly on its session cookie.
- BUG-010 - Test-account seeder/schema mismatch; remains open.
- BUG-011 - Logged-out protected-page redirect points to a missing `/login.php` route.
