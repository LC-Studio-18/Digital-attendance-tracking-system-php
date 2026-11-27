# S2-04-09 - Add Invalid-Input Feedback

## Information
- Parent ticket: S2-04 - Shared Layout and Login Page
- Assigned developer: Anela
- QA status: Passed for error feedback; integration remains blocked by the S2-05 login response
- Build / commit SHA: `2ae8032` (`origin/team1`)
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- Test environment: Windows/XAMPP; PHP 8.0.30; Chromium browser; MySQL-backed backend flow previously verified.

## Objective
- Display useful feedback for invalid input and provide a location for the backend's generic authentication error.

## Acceptance Criteria
- [ ] Invalid email/input receives clear feedback.
- [ ] A generic backend authentication error can be displayed in the agreed location.
- [ ] Feedback is visible, understandable, and does not reveal sensitive account details.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Trigger client-side invalid input and display a backend generic error | Appropriate feedback appears without exposing whether an account exists | Malformed email displayed “Please enter a valid email address.” without a request. On `f2aa1a6`, a synthetic invalid login displayed “Invalid email or password.” after the failure redirect; neither state exposed account details. | Passed | [Invalid-email screenshot](../evidence/TC-002-invalid-email-validation-pass.png); [generic login error screenshot](../evidence/TC-004-generic-login-error-pass.png). Successful login still returns HTTP 501 and is recorded under S2-05. |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-27 | QA | frontend/backend retest | `2ae8032` (`origin/team1`) | Passed | Client invalid-email feedback and generic backend error display verified. |
| 2026-11-27 | QA | integrated browser screenshot retest | `f2aa1a6` (`origin/team1`) | Passed | Client invalid-email feedback and the generic error after a synthetic invalid login were captured. |

## Linked Bugs
None recorded.
