# S2-04-05 - Develop Login Page HTML

## Information
- Parent ticket: S2-04 - Shared Layout and Login Page
- Assigned developer: Anela
- QA status: Passed
- Build / commit SHA: `2ae8032` (`origin/team1`)
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- Test environment: Windows/XAMPP; PHP 8.0.30; Chromium browser.

## Objective
- Develop the login page form using the agreed `email` and `password` field names.

## Acceptance Criteria
- [ ] Login form contains labeled email and password inputs.
- [ ] Form submits to the POST target agreed with the backend developer.
- [ ] Form structure is accessible and matches the intended page content.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Inspect and submit the login form | Email and password fields are present and submit using the agreed names and target | Login form has associated labels, `email` and `password` names, `type=email`/`type=password`, and POST action `./login-submit.php`. Invalid client input was prevented; the integrated form target was confirmed in the earlier backend retest. | Passed | Form contract and accessible labels verified on `team1` commit `2ae8032`. [Current login page screenshot](../evidence/TC-001-team1-login-page-blocked.png) |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-27 | QA | frontend retest | `2ae8032` (`origin/team1`) | Passed | Form names, method, target, labels, and browser rendering verified. |
| 2026-11-27 | QA | integrated browser screenshot capture | `f2aa1a6` (`origin/team1`) | Passed | Current login page captured; see the linked screenshot. |

## Linked Bugs
None recorded.
