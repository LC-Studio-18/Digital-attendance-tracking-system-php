kdo# S2-05-01 - Create Test Staff and Admin Accounts

## Information
- Parent ticket: S2-05 - Login and Logout with PHP Sessions
- Assigned developer: Lisekho
- QA status: Pass (test data verified; repository seeder defect remains)
- Build / commit SHA: `cbcf8ae` (`origin/team1`)
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- Test environment: Windows/XAMPP; PHP 8.0.30; dedicated MySQL database `attendance_test_db`.

## Objective
- Prepare staging test data for staff and admin roles, including one deactivated account, with passwords hashed using `password_hash`.

## Acceptance Criteria
- [x] Staging has test staff and admin accounts with documented roles.
- [x] At least one account is deactivated for negative testing.
- [x] Test passwords are stored as hashes using `password_hash`.
- [x] No plaintext passwords are stored in the account records.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Inspect prepared staging test accounts | Required roles and deactivated account are available, and stored passwords are hashes | Applied the schema only to `attendance_test_db`; temporary QA setup created active staff, active admin, and deactivated staff records with required fields, valid roles, and password hashes. The repository seeder itself remains incompatible with the schema. | Pass | BUG-010 remains open; no seeder source changes were retained. |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-26 |  QA | backend test | `f16502f` | Blocked | Local database credentials are missing; seeder also conflicts with schema. |
| 2026-11-26 | QA | MySQL test-data verification | `f16502f` | Pass | Schema and three hashed QA accounts are present in the dedicated test database; temporary setup was used and no source seeder change was retained. |
| 2026-11-27 | QA | latest team1 QA account check | `cbcf8ae` (`origin/team1`) | Pass | The new account-check script confirmed its QA staff account exists, is active, and passes `password_verify()` against `attendance_test_db`; no account was created or modified. |

## Linked Bugs
BUG-010 - Test-account seeder is incompatible with the staff schema; remains open.
