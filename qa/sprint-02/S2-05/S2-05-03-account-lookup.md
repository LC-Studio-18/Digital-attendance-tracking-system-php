# S2-05-03 - Find Staff by Email and Check Active Status

## Information
- Parent ticket: S2-05 - Login and Logout with PHP Sessions
- Assigned developer: Lisekho
- QA status: Not Run
- Build / commit SHA:
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- Test environment:

## Objective
- Look up a staff member by email using a PDO prepared statement and verify the account is active.

## Acceptance Criteria
- [ ] Email lookup uses a PDO prepared statement.
- [ ] Existing active accounts can be distinguished internally from missing or inactive accounts.
- [ ] Missing and deactivated accounts are not authenticated.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Look up active, unknown, and deactivated account emails | Only the active existing account is eligible to continue authentication | | Not Run | |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| | | | | Not Run | |

## Linked Bugs
None recorded.
