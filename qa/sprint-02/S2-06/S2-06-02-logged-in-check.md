# S2-06-02 - Build the Reusable Logged-In Check

## Information
- Parent ticket: S2-06 - Staff and Admin Role Permissions
- Assigned developer: Avela Gxotiwe
- QA status: Failed - current guest redirect targets a missing login route
- Build / commit SHA: `f2aa1a6` (`origin/team1`)
- Figma design / version tested: Not Applicable
- Test environment: Windows/XAMPP; PHP 8.0.30; `attendance_test_db`; local PHP server, browser, and isolated sessions.

## Objective
- Build a reusable server-side check that confirms a user has an authenticated session and redirects unauthenticated visitors to the login page.

## Acceptance Criteria
- [ ] Check can be reused by protected pages and endpoints.
- [ ] Logged-out requests are denied and redirected to login.
- [ ] A valid logged-in staff or admin session passes the authentication check.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Call the check with logged-out, staff, and admin session states | Logged-out state is redirected to a working login page; authenticated roles pass this login-only check | On `f2aa1a6`, the Chrome guest request to `frontend/index.php` returned HTTP 302 to `/login.php`, then HTTP 404. After valid login, the same browser session received HTTP 200 and rendered “Attendance system setup complete.” | Failed | BUG-011: latest redirect path does not match the existing `/frontend/login.php` page. [Guest redirect 404 screenshot](../evidence/TC-006-guest-redirect-404-team1.png) |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-27 | QA | backend retest | `2b44dcb` | Passed | Visitor redirected; staff/admin passed the login-only guard on the existing dashboard. |
| 2026-11-27 | QA | merged frontend/backend retest | `2ae8032` (`origin/team1`) | Passed | Login form established staff/admin sessions; authenticated dashboard requests returned HTTP 200. Login POST still returns HTTP 501. |
| 2026-11-27 | QA | latest team1 redirect regression retest | `cbcf8ae` (`origin/team1`) | Failed | Guest redirect targets `/login.php` and ends at 404; valid QA staff session still passes the protected page. |
| 2026-11-27 | QA | Chrome redirect regression retest | `f2aa1a6` (`origin/team1`) | Failed | Guest redirect still targets `/login.php` and ends at 404; authenticated Chrome session passes the protected route with HTTP 200. |

## Linked Bugs
BUG-011 - `requireLogin()` redirects logged-out visitors to `/login.php`, which returns 404.
