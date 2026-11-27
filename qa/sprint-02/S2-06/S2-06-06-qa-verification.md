# S2-06-06 - QA Test Protected Pages by Role and Record Sign-Off

## Information
- Parent ticket: S2-06 - Staff and Admin Role Permissions
- Assigned developer: Team 1 QA (Ahlumile Totose)
- QA status: Blocked - guest redirect fails; no admin-protected route for end-to-end sign-off
- Build / commit SHA: `cbcf8ae` (`origin/team1`)
- Figma design / version tested: Not Applicable
- Test environment: Windows/XAMPP; PHP 8.0.30; `attendance_test_db`; local PHP server, browser, and isolated session fixtures.

## Objective
- Test every protected page and endpoint as an admin, a staff user, and a logged-out visitor, record results, and provide QA sign-off when acceptance criteria pass.

## Acceptance Criteria
- [ ] Protected route inventory is complete and used as the test scope.
- [ ] Every route is tested with admin, staff, and logged-out states as applicable.
- [ ] Direct URL/request access and browser-supplied role tampering are included.
- [ ] Results, evidence, and defects are recorded; sign-off is withheld until failures are resolved.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Execute the role/access matrix for every route in the protected-route inventory | Admin, staff, and logged-out outcomes match the route's required access; results and evidence are recorded | On `f2aa1a6`, visitor access to `frontend/index.php` returned HTTP 302 to `/login.php`, which returned HTTP 404. A valid QA staff session returned HTTP 200 on the protected page after login POST returned HTTP 501. Earlier direct CLI calls to `requireAdmin()` allowed admin, returned 403 for staff/manager, and rejected forged role values. No admin-protected route exists. | Blocked | BUG-011 breaks the guest redirect; route-level admin coverage and final sign-off remain blocked. PHP 8.0.30 is below the project's PHP 8.2+ requirement. [Guest redirect 404 screenshot](../evidence/TC-006-guest-redirect-404-team1.png). No screenshots can demonstrate the missing admin-route cases. |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-26 | QA | backend test | `f16502f` | Blocked | Authorization acceptance criteria remain unverified. |
| 2026-11-27 | QA | backend retest | `2b44dcb` | Blocked | Login guard and admin guard behavior passed isolated tests; no admin-protected endpoint exists and runtime was PHP 8.0.30. |
| 2026-11-27 | QA | merged frontend/backend retest | `2ae8032` (`origin/team1`) | Blocked | Login form, visitor redirect, staff/admin dashboard access, and direct guard matrix tested. No admin route exists; PHP 8.2+ validation remains pending. |
| 2026-11-27 | QA | latest team1 sign-in/guard retest | `cbcf8ae` (`origin/team1`) | Blocked | Valid QA session passed the protected page; anonymous redirect target returned 404; no admin route exists. |

## Linked Bugs
BUG-011 - `requireLogin()` redirects logged-out visitors to `/login.php`, which returns 404.
