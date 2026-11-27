# S2-04 - Shared Layout and Login Page

## Ticket Information
- Team: 1
- Sprint: 2
- QA tester: Ahlumile Totose
- Ticket link:
- Assigned developer(s): Nithaam, Anela; review and integration coordination: Avela
- Feature branch(es): `team1-shared-layout`, `team1-login-page`
- Build / commit SHA: `2ae8032` (`origin/team1`)
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- QA status: Blocked - form behavior and responsive checks passed; shared-component integration, design comparison, browser scope, and review evidence remain incomplete

## Requirements
- User story and context: Provide a responsive shared site layout and a login page with client-side validation that matches the approved design and uses the `email` and `password` form fields.

## Acceptance Criteria
- [x] Shared header, navigation, footer, and base styles are reusable (rendered using a temporary QA fixture).
- [x] Login page has the expected fields and validation/error feedback.
- [ ] Layout and login page are responsive and checked against the Figma design.
- [ ] Frontend is tested in supported browsers and submitted for review.

## Definition of Done
- [ ] All linked subtasks are complete and reviewed.
- [ ] Frontend acceptance criteria have been verified and results recorded.

## Dependencies
- Nithaam's shared layout and styles should be pushed to `team1` before Anela completes login styling.
- Anela and Lisekho must agree on form field names and POST target.
- Avela coordinates review and integration.

## Subtasks

| ID | Description | QA status | Report |
|---|---|---|---|
| S2-04-01 | Set up frontend file structure | Pass (isolated include/render test) | [Report](S2-04-01-frontend-structure.md) |
| S2-04-02 | Create reusable header | Partial (responsive rendering checked; not integrated on current pages) | [Report](S2-04-02-header.md) |
| S2-04-03 | Create navigation with mobile menu | Partial (mobile interaction works; role mapping and destinations need integration) | [Report](S2-04-03-navigation.md) |
| S2-04-04 | Create reusable footer | Partial (renders at mobile/desktop; small-screen text readability needs review) | [Report](S2-04-04-footer.md) |
| S2-04-05 | Develop login page HTML | Pass | [Report](S2-04-05-login-html.md) |
| S2-04-06 | Style the login page | Partial (responsive checks passed; Figma comparison pending) | [Report](S2-04-06-login-styling.md) |
| S2-04-07 | Implement basic form validation | Pass | [Report](S2-04-07-form-validation.md) |
| S2-04-08 | Add empty-field validation messages | Pass | [Report](S2-04-08-empty-field-messages.md) |
| S2-04-09 | Add invalid-input feedback | Pass (client and generic backend message checked) | [Report](S2-04-09-invalid-input-feedback.md) |
| S2-04-10 | Verify responsive layouts | Partial (360px/desktop checks passed; footer text size needs review) | [Report](S2-04-10-responsive-layout.md) |
| S2-04-11 | Compare implementation against Figma | Blocked (no inspectable reference frame/version) | [Report](S2-04-11-figma-comparison.md) |
| S2-04-12 | Test supported browsers | Blocked (supported browser list not specified; Chromium only) | [Report](S2-04-12-browser-testing.md) |
| S2-04-13 | Submit frontend for review | Blocked (PR/review evidence not available in QA materials) | [Report](S2-04-13-frontend-review.md) |

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Review shared layout and login page against the acceptance criteria | Reusable layout and login page work responsively, provide validation feedback, and match the approved design | Login rendered on `team1` commit `2ae8032`; shared includes rendered in a temporary QA fixture. No horizontal overflow at 360px, 390px, or 1440px. The actual login/dashboard pages do not include the shared layout components. The Figma wireframe board opened, but the intended frame/version was not identified; supported-browser list is unspecified. At 360px, footer text measures 7-9px. GitHub search/list found no PR targeting `team1`. | Blocked | Chromium/XAMPP only; identify design target and review small-screen footer readability. Navigation emits no markup for role `staff` (it accepts `admin`/`employee`); destination pages are not present. [Login page screenshot](../evidence/TC-001-team1-login-page-blocked.png) |
| TC-02 | Submit the login page with empty, invalid, and validly formatted input | Appropriate client-side feedback is shown and validly formatted values use the agreed field names | Empty email/password and each individually empty field were identified; malformed email showed an error and sent no request. Valid-format input passed validation; form uses POST to `login-submit.php` with `email` and `password`. Password visibility toggle and generic backend error rendering were confirmed in the latest browser retest. | Passed | Tested in integrated Chromium. Login POST still returns HTTP 501 after valid authentication (tracked under S2-05). [Empty-field screenshot](../evidence/TC-002-empty-validation-pass.png); [invalid-email screenshot](../evidence/TC-002-invalid-email-validation-pass.png); [generic login error screenshot](../evidence/TC-004-generic-login-error-pass.png) |

## Linked Bugs
No new bug IDs assigned. Integration observations are recorded in the subtask reports.

## Testing Summary
- Total: 2
- Passed: 1
- Failed: 0
- Blocked: 1
- Not Run: 0

## QA Sign-off
- Status: Pending - blocked
- Tested version: `2ae8032` (`origin/team1`)
- QA tester: Ahlumile Totose
- Date: 2026-11-27
- Notes: PHP lint passed for the login page and shared includes; HTTP and browser checks ran on XAMPP PHP 8.0.30 using Chromium. Client-side validation and responsive widths passed. Sign-off remains blocked by missing application call sites for shared components, backend `staff` versus navigation `employee` role mismatch, 7-9px footer text at 360px, unidentified Figma target frame/version, unspecified supported browsers, and no matching PR/review record in GitHub. No developer source code was changed; the navigation fixture was temporary and isolated.
