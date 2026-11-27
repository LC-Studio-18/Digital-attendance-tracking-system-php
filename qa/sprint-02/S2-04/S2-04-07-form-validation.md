# S2-04-07 - Implement Basic Form Validation

## Information
- Parent ticket: S2-04 - Shared Layout and Login Page
- Assigned developer: Anela
- QA status: Passed
- Build / commit SHA: `2ae8032` (`origin/team1`)
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- Test environment: Windows/XAMPP; PHP 8.0.30; Chromium browser with local submission interception.

## Objective
- Implement basic JavaScript validation for the login form.

## Acceptance Criteria
- [ ] Invalid or incomplete input is identified before submission as specified.
- [ ] Validly formatted input is not incorrectly rejected.
- [ ] Validation feedback is clear and associated with the relevant field or form.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Submit empty, malformed, and validly formatted form values | Invalid values receive useful feedback and validly formatted values can proceed | Empty and malformed values were prevented before any POST; email and password errors were shown. A validly formatted email/password pair passed the submit handler without `preventDefault`; the button changed to “Signing in…”. | Passed | Client-side validation verified without sending test credentials to the backend. |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-27 | QA | frontend retest | `2ae8032` (`origin/team1`) | Passed | Empty, malformed, and validly formatted values exercised in Chromium. |

## Linked Bugs
None recorded.
