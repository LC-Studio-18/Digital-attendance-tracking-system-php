# S2-05-07 - Display Login Error Message

## Information
- Parent ticket: S2-05 - Login and Logout with PHP Sessions
- Assigned developer: Caleb (coordinate with Anela)
- QA status: Passed - generic error visibly rendered on the merged login page
- Build / commit SHA: `f2aa1a6` (`origin/team1`)
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- Test environment: Windows/XAMPP; PHP 8.0.30; `attendance_test_db`; local PHP built-in server and browser.

## Objective
- Display the backend's generic login failure message in the location agreed with the frontend developer.

## Acceptance Criteria
- [x] Backend failure feedback is passed to and displayed on the login page.
- [x] Message is visible and understandable.
- [x] Message does not reveal whether the email is unknown, the password is wrong, or the account is deactivated.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Trigger backend authentication failure | Login page displays the agreed generic message without exposing account status | On `f2aa1a6`, a synthetic invalid login returned HTTP 302 to `frontend/login.php`; the page returned HTTP 200 and visibly rendered “Invalid email or password.” Earlier backend attempts for wrong password, unknown email, and deactivated account returned the same generic message. | Passed | [Fresh Chrome screenshot of generic failure](../evidence/TC-004-generic-login-error-pass.png). |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-26 | QA | backend test | `f16502f` | Blocked | Generic backend response checked; page rendering not verified. |
| 2026-11-26 | QA | MySQL/HTTP retest | `f16502f` + MySQL QA fixtures | Blocked | Session error handoff verified over HTTP; frontend display remains unavailable. |
| 2026-11-27 | QA | merged frontend/backend retest | `2ae8032` (`origin/team1`) | Passed | Invalid login displayed the agreed generic message in the browser; backend wrong-password, unknown-email, and deactivated-account cases remain uniform. |
| 2026-11-27 | QA | integrated browser invalid-login retest | `f2aa1a6` (`origin/team1`) | Passed | Synthetic invalid POST returned 302; generic error was visibly rendered and captured. |

## Linked Bugs
None recorded.
