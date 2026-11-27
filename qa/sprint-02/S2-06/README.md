# S2-06 - Staff and Admin Role Permissions

## Ticket Information
- Team: 1
- Sprint: 2
- QA tester: Ahlumile Totose
- Ticket link:
- Assigned developer(s): Rushin Presence; frontend coordination for access-denied wording
- Feature branch(es): `team1-role-checks` (PR #2, merged)
- Build / commit SHA: `f2aa1a6` (`origin/team1`; includes merged S2-06 changes)
- Figma design / version tested: Not Applicable
- QA status: Blocked - the latest guest redirect target returns 404, and no admin-protected route exists for end-to-end verification

## Requirements
- User story and context: As an admin, I want admin pages and actions to be blocked for staff, so they cannot view or modify other staff's attendance data. Visitors must be redirected to login, and staff must be denied access to admin pages and endpoints by server-side checks.

## Acceptance Criteria
- [ ] Visitors who are not logged in are redirected to the login page for every protected page and endpoint.
- [ ] Admin pages and endpoints reject staff users on the server, including direct URL or request access.
- [ ] Staff receive a clear access-denied message on admin pages, with wording agreed with Frontend.
- [ ] Role authorization uses session/database data and never trusts browser-supplied role values.
- [ ] Reusable logged-in and admin-only checks are applied to every existing protected page and endpoint.
- [ ] Every protected page is tested as an admin, staff user, and logged-out visitor, with results recorded and QA sign-off.

## Definition of Done
- [ ] All linked subtasks are complete.
- [ ] Every existing protected route is recorded in the test scope and tested for the required user states.
- [ ] Access-control results and any defects are recorded; QA sign-off is given only after acceptance criteria pass.

## Dependencies
- Hard dependencies: S2-03 (database and attendance tables, including staff role) and S2-05 (PHP login sessions).
- Blocks: S2-08, S2-09, S2-11B, S2-12B, S2-14, S2-16A, S2-16B, S2-17A, S2-17B, S2-18A, S2-19a, S2-19b, S2-20a, S2-20b, S2-22, and S2-29.
- Admin pages, staff account creation, login/logout, and roles beyond staff and admin are out of scope.

## Subtasks

| ID | Description | QA status | Report |
|---|---|---|---|
| S2-06-01 | Confirm role storage with the S2-03 owner | Blocked - owner confirmation pending | [Report](S2-06-01-confirm-role-storage.md) |
| S2-06-02 | Build the reusable logged-in check | Failed - guest redirect target returns 404 | [Report](S2-06-02-logged-in-check.md) |
| S2-06-03 | Build the reusable admin-only check | Passed at guard level | [Report](S2-06-03-admin-only-check.md) |
| S2-06-04 | Add access-denied message and agree wording with Frontend | Blocked - no admin page to verify display | [Report](S2-06-04-access-denied-message.md) |
| S2-06-05 | Apply checks to every existing protected page and endpoint | Partial - dashboard protected; no admin routes exist | [Report](S2-06-05-apply-protection.md) |
| S2-06-06 | QA test protected pages by role and record sign-off | Blocked - admin-route coverage unavailable | [Report](S2-06-06-qa-verification.md) |

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | As a logged-out visitor, request each protected page and endpoint | Each request is denied and redirects to the working login page | On latest `team1` (`f2aa1a6`), the Chrome guest request to `frontend/index.php` returned HTTP 302 to `/login.php`, then HTTP 404. After a valid staff login, the same browser session received HTTP 200 from `frontend/index.php`, which rendered “Attendance system setup complete.” This is the only protected page found. | Failed | BUG-011: `requireLogin()` points to `/login.php`, but the application login is `/frontend/login.php`. [Guest redirect 404 screenshot](../evidence/TC-006-guest-redirect-404-team1.png) |
| TC-02 | As a staff user, request each admin page and endpoint directly | Server denies access; admin data/actions are unavailable and pages show the agreed access-denied message | No admin page or endpoint exists. On `team1`, direct CLI invocation of `requireAdmin()` set response code 403 and emitted the expected message for staff and manager sessions. | Blocked | Guard behavior passed; route-level denial and data exposure cannot be tested without an admin route. No frontend screenshot is available because there is no admin route. |
| TC-03 | As an admin, request each admin page and endpoint | Access is allowed only where the admin role is required | No admin page or endpoint exists. On `team1`, direct invocation of `requireAdmin()` allowed an admin session. | Blocked | Guard behavior passed; no admin route is available for end-to-end verification. No frontend screenshot is available because there is no admin route. |
| TC-04 | Submit forged role values through URL, form data, or editable client-side state | Authorization remains based on trusted session/database role; forged values do not grant access | A visitor with `?role=admin` was redirected to login. On `team1`, direct CLI invocation of `requireAdmin()` set response code 403 for staff with `$_GET['role']='admin'`; admin passed and staff/manager were denied. | Passed | Guard-level test passed; no admin endpoint exists for a request-level test. No screenshot is attached because the pass is a direct guard-level check, not a verifiable admin-page UI flow. |

## Linked Bugs
BUG-010 remains open: the PR seeder still omits the schema-required `employee_number`, `first_name`, and `last_name` columns.
BUG-011 - The latest `requireLogin()` redirect sends guests to `/login.php`, which returns 404.

## Testing Summary
- Total: 4
- Passed: 1
- Failed: 1
- Blocked: 2
- Not Run: 0

## QA Sign-off
- Status: Pending - blocked; BUG-011 guest redirect failure, missing admin-route coverage, and PHP 8.2+ validation remain outstanding
- Tested version: `f2aa1a6` (`origin/team1`)
- QA tester: Ahlumile Totose
- Date: 2026-11-27
- Notes: PHP 8.0.30 was used; the project requires PHP 8.2+. On `f2aa1a6`, Chrome confirmed the valid staff session reached protected `frontend/index.php` (HTTP 200), but the login POST returned HTTP 501. A logged-out visitor is redirected to `/login.php`, which returns 404 (BUG-011). No admin-protected route exists, so `requireAdmin()` cannot be verified through an application endpoint. QA sign-off remains blocked pending the redirect fix, an admin route, and PHP 8.2+ validation.
