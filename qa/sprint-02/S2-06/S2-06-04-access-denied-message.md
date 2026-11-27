# S2-06-04 - Add Access-Denied Message and Agree Wording

## Information
- Parent ticket: S2-06 - Staff and Admin Role Permissions
- Assigned developer: Avela Gxotiwe (coordinate wording with Frontend)
- QA status: Blocked - message verified in guard; no admin page to verify user-facing display
- Build / commit SHA: `2b44dcb` (PR head; merged into `backend1` at `7d5e232`)
- Figma design / version tested: Not Applicable
- Test environment: Windows/XAMPP; PHP 8.0.30; direct `requireAdmin()` invocation.

## Objective
- Provide a clear access-denied message to staff who try to open an admin page and agree on its wording with Frontend.

## Acceptance Criteria
- [ ] Access-denied wording is agreed with Frontend.
- [ ] Staff see the agreed message when denied access to an admin page.
- [ ] Denied requests do not reveal protected information or perform the requested action.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Open an admin page as a staff user | Agreed access-denied message is shown and no protected data or action is exposed | Direct CLI invocation of `requireAdmin()` set response code 403 and emitted `Access denied. You do not have permission to access this page.` for staff. No admin page exists to verify rendered display or protected-data handling. | Blocked | Guard message verified; Frontend display and route behavior remain unverified. |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-27 | QA | backend retest | `2b44dcb` | Blocked | Guard sets response code 403 and emits the agreed text; no admin page is available for end-to-end verification. |

## Linked Bugs
None recorded.
