# S2-06 - Staff and Admin Role Permissions

## Ticket Information
- Team: 1
- Sprint: 2
- QA tester: Ahlumile Totose
- Ticket link:
- Assigned developer(s): Avela Gxotiwe; frontend coordination for access-denied wording
- Feature branch(es):
- Build / commit SHA:
- Figma design / version tested: Not Applicable
- QA status: Not Run

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
| S2-06-01 | Confirm role storage with the S2-03 owner | Not Run | [Report](S2-06-01-confirm-role-storage.md) |
| S2-06-02 | Build the reusable logged-in check | Not Run | [Report](S2-06-02-logged-in-check.md) |
| S2-06-03 | Build the reusable admin-only check | Not Run | [Report](S2-06-03-admin-only-check.md) |
| S2-06-04 | Add access-denied message and agree wording with Frontend | Not Run | [Report](S2-06-04-access-denied-message.md) |
| S2-06-05 | Apply checks to every existing protected page and endpoint | Not Run | [Report](S2-06-05-apply-protection.md) |
| S2-06-06 | QA test protected pages by role and record sign-off | Not Run | [Report](S2-06-06-qa-verification.md) |

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | As a logged-out visitor, request each protected page and endpoint | Each request is denied and redirects to the login page | | Not Run | |
| TC-02 | As a staff user, request each admin page and endpoint directly | Server denies access; admin data/actions are unavailable and pages show the agreed access-denied message | | Not Run | |
| TC-03 | As an admin, request each admin page and endpoint | Access is allowed only where the admin role is required | | Not Run | |
| TC-04 | Submit forged role values through URL, form data, or editable client-side state | Authorization remains based on trusted session/database role; forged values do not grant access | | Not Run | |

## Linked Bugs
None recorded.

## Testing Summary
- Total: 4
- Passed: 0
- Failed: 0
- Blocked: 0
- Not Run: 4

## QA Sign-off
- Status: Pending
- Tested version:
- QA tester: Ahlumile Totose
- Date:
- Notes: No tests have been run.
