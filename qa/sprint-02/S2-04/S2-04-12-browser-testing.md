# S2-04-12 - Test Supported Browsers

## Information
- Parent ticket: S2-04 - Shared Layout and Login Page
- Assigned developer: Anela (Nithaam retests shared layout)
- QA status: Blocked - supported browser matrix not specified
- Build / commit SHA: `2ae8032` (`origin/team1`)
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- Test environment: Windows/XAMPP; integrated Chromium browser.

## Objective
- Test the login page in supported browsers and retest the shared layout.

## Acceptance Criteria
- [ ] Supported browsers are identified and recorded.
- [ ] Login page and shared layout are checked in each supported browser.
- [ ] Browser-specific issues are recorded with reproducible details.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Open the login page and shared layout in each supported browser | Appearance and interactions work consistently, or differences are recorded as defects | Login and shared-component interactions were smoke-tested in the integrated Chromium browser. No supported-browser list is specified, so cross-browser coverage cannot be determined. | Blocked | Identify the supported browsers, then repeat the interaction and responsive checks in each. |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-27 | QA | browser smoke test | `2ae8032` (`origin/team1`) | Blocked | Chromium checked; required cross-browser matrix is unspecified. |

## Linked Bugs
None recorded.
