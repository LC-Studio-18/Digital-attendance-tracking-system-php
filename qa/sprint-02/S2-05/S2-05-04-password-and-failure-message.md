# S2-05-04 - Check Password and Return Generic Failure Message

## Information
- Parent ticket: S2-05 - Login and Logout with PHP Sessions
- Assigned developer: Lisekho
- QA status: Pass (MySQL and HTTP integration retested)
- Build / commit SHA: `f16502f` (`backend1`)
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- Test environment: Windows/XAMPP; PHP 8.0.30; dedicated MySQL fixtures and local PHP built-in server.

## Objective
- Verify the supplied password against the stored hash and use one failure message for wrong passwords, unknown emails, and deactivated accounts.

## Acceptance Criteria
- [x] Password verification uses the stored hash safely.
- [x] Invalid password, unknown email, and deactivated account produce the same generic message.
- [x] Failed authentication does not create an authenticated session.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Attempt login with a wrong password, unknown email, and deactivated account | All attempts show the same generic failure response and do not authenticate | MySQL-backed controller checks verified active staff/admin hashes. HTTP wrong-password, unknown-email, and deactivated-account attempts all redirected and stored the same generic login error; no password hash was returned to callers. | Pass | Database-backed controller and HTTP failure paths passed. |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-26 | QA | backend test | `f16502f` | Pass | Negative responses were uniform; valid hashes authenticated in isolation. |
| 2026-11-26 | QA | MySQL/HTTP retest | `f16502f` + MySQL QA fixtures | Pass | Valid hashes and uniform HTTP failure feedback verified with the dedicated MySQL fixtures. |

## Linked Bugs
None recorded.
