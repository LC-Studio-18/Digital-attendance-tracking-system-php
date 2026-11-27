# S2-05-03 - Find Staff by Email and Check Active Status

## Information
- Parent ticket: S2-05 - Login and Logout with PHP Sessions
- Assigned developer: Lisekho
- QA status: Pass (MySQL integration retested)
- Build / commit SHA: `f16502f` (`backend1`)
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- Test environment: Windows/XAMPP; PHP 8.0.30; dedicated MySQL database `attendance_test_db`.

## Objective
- Look up a staff member by email using a PDO prepared statement and verify the account is active.

## Acceptance Criteria
- [x] Email lookup uses a PDO prepared statement.
- [x] Existing active accounts can be distinguished internally from missing or inactive accounts.
- [x] Missing and deactivated accounts are not authenticated.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Look up active, unknown, and deactivated account emails | Only the active existing account is eligible to continue authentication | MySQL-backed prepared lookups authenticated active staff/admin accounts and rejected unknown, inactive, and SQL-shaped email inputs. | Pass | MySQL fixture test passed; QA fixtures were provisioned separately because BUG-010 remains open. |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-26 | QA | backend test | `f16502f` | Pass | Prepared lookup and account eligibility verified with isolated PDO fixtures. |
| 2026-11-26 | QA | MySQL retest | `f16502f` + MySQL QA fixtures | Pass | Prepared lookup and active-account eligibility verified against MySQL test fixtures. |

## Linked Bugs
BUG-010 - Test-account seeder/schema mismatch; remains open.
