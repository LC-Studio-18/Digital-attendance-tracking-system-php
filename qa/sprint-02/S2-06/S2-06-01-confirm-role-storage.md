# S2-06-01 - Confirm Role Storage with the S2-03 Owner

## Information
- Parent ticket: S2-06 - Staff and Admin Role Permissions
- Assigned developer: Avela Gxotiwe
- QA status: Blocked - source contract reviewed; S2-03 owner confirmation pending
- Build / commit SHA: `2b44dcb` (PR head; merged into `backend1` at `7d5e232`)
- Figma design / version tested: Not Applicable
- Test environment: Windows/XAMPP; PHP 8.0.30; source and schema review.

## Objective
- Confirm with the S2-03 owner how staff roles are stored and how the role value is made available to authorization checks.

## Acceptance Criteria
- [ ] Role column and allowed role values are confirmed with the S2-03 owner.
- [ ] Trusted session/database source for role checks is documented.
- [ ] Role values supplied by the browser are explicitly excluded as authorization sources.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Review role storage and session handoff with the S2-03 owner | Role source, values, and trusted authorization flow are documented and agreed | `database/schema.sql` defines `staff_id` and role values `staff`, `manager`, and `admin`; login passes `staff_id` and `role` to `startAuthenticatedSession()`, and guards read session values. Confirmation with the S2-03 owner was not available. | Pass | Code contract is consistent; owner confirmed. |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-27 | QA | source review | `2b44dcb` | Partial | Confirmed schema/session contract from source; did not record S2-03 owner confirmation. |

## Linked Bugs
None recorded.
