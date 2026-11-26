# S2-06-03 - Build the Reusable Admin-Only Check

## Information
- Parent ticket: S2-06 - Staff and Admin Role Permissions
- Assigned developer: Avela Gxotiwe
- QA status: Not Run
- Build / commit SHA:
- Figma design / version tested: Not Applicable
- Test environment:

## Objective
- Build a reusable server-side authorization check that permits admins and rejects staff users.

## Acceptance Criteria
- [ ] Check can be reused by admin-only pages and endpoints.
- [ ] Admin access is determined from trusted session/database role data.
- [ ] Staff and unauthenticated requests are denied; browser-supplied role values cannot grant access.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Call the check with admin, staff, and forged browser role values | Only a verified admin is authorized; staff and forged role attempts are denied | | Not Run | |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| | | | | Not Run | |

## Linked Bugs
None recorded.
