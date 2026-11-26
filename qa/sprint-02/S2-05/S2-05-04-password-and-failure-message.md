# S2-05-04 - Check Password and Return Generic Failure Message

## Information
- Parent ticket: S2-05 - Login and Logout with PHP Sessions
- Assigned developer: Lisekho
- QA status: Not Run
- Build / commit SHA:
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- Test environment:

## Objective
- Verify the supplied password against the stored hash and use one failure message for wrong passwords, unknown emails, and deactivated accounts.

## Acceptance Criteria
- [ ] Password verification uses the stored hash safely.
- [ ] Invalid password, unknown email, and deactivated account produce the same generic message.
- [ ] Failed authentication does not create an authenticated session.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Attempt login with a wrong password, unknown email, and deactivated account | All attempts show the same generic failure response and do not authenticate | | Not Run | |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| | | | | Not Run | |

## Linked Bugs
None recorded.
