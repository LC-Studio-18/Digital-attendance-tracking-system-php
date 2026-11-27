# S2-04-08 - Add Empty-Field Validation Messages

## Information
- Parent ticket: S2-04 - Shared Layout and Login Page
- Assigned developer: Anela
- QA status: Passed
- Build / commit SHA: `2ae8032` (`origin/team1`)
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- Test environment: Windows/XAMPP; PHP 8.0.30; Chromium browser.

## Objective
- Provide validation messages when required login fields are left empty.

## Acceptance Criteria
- [ ] Empty email and empty password each receive clear feedback.
- [ ] Submitting both fields empty identifies all required missing values.
- [ ] Messages are accessible and do not obscure the form.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Submit with email empty, password empty, and both empty | Each missing required field is clearly identified | With both fields empty, both messages appeared. Email-only empty and password-only empty cases each identified the missing field, set `aria-invalid=true`, and stayed on the login page without sending a request. | Passed | Error messages are linked to inputs through `aria-describedby`; feedback did not obscure the form. [Empty-field screenshot](../evidence/TC-002-empty-validation-pass.png) |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-27 | QA | frontend retest | `2ae8032` (`origin/team1`) | Passed | Both-empty and each single-empty-field case verified in Chromium. |
| 2026-11-27 | QA | integrated browser screenshot capture | `f2aa1a6` (`origin/team1`) | Passed | Both empty-field messages visibly appeared; screenshot linked above. |

## Linked Bugs
None recorded.
