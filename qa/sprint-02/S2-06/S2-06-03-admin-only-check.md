# S2-06-03 - Build the Reusable Admin-Only Check

## Information
- Parent ticket: S2-06 - Staff and Admin Role Permissions
- Assigned developer: Avela Gxotiwe
- QA status: Passed at guard level - admin allowed; staff and manager denied
- Build / commit SHA: `2ae8032` (`origin/team1`)
- Figma design / version tested: Not Applicable
- Test environment: Windows/XAMPP; PHP 8.0.30; direct guard invocation with isolated session fixtures.

## Objective
- Build a reusable server-side authorization check that permits admins and rejects staff users.

## Acceptance Criteria
- [ ] Check can be reused by admin-only pages and endpoints.
- [ ] Admin access is determined from trusted session/database role data.
- [ ] Staff and unauthenticated requests are denied; browser-supplied role values cannot grant access.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Call the check with admin, staff, and forged browser role values | Only a verified admin is authorized; staff and forged role attempts are denied | On `team1`, direct CLI invocation allowed an admin session. Staff and manager sessions received response code 403 and the expected denial message. Setting `$_GET['role']` to `admin` did not elevate staff. | Passed | Guard behavior verified directly; no admin-protected endpoint exists for HTTP route testing. |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-27 | QA | backend retest | `2b44dcb` | Passed | Admin allowed; staff/manager denied; forged browser role ignored by guard. |
| 2026-11-27 | QA | merged branch guard retest | `2ae8032` (`origin/team1`) | Passed | Admin allowed; staff/manager denied with 403; forged query role ignored. |

## Linked Bugs
None recorded.
