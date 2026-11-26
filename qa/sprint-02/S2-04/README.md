# S2-04 - Shared Layout and Login Page

## Ticket Information
- Team: 1
- Sprint: 2
- QA tester: Ahlumile Totose
- Ticket link:
- Assigned developer(s): Nithaam, Anela; review and integration coordination: Avela
- Feature branch(es): `team1-shared-layout`, `team1-login-page`
- Build / commit SHA:
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- QA status: Not Run

## Requirements
- User story and context: Provide a responsive shared site layout and a login page with client-side validation that matches the approved design and uses the `email` and `password` form fields.

## Acceptance Criteria
- [ ] Shared header, navigation, footer, and base styles are reusable.
- [ ] Login page has the expected fields and validation/error feedback.
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
| S2-04-01 | Set up frontend file structure | Not Run | [Report](S2-04-01-frontend-structure.md) |
| S2-04-02 | Create reusable header | Not Run | [Report](S2-04-02-header.md) |
| S2-04-03 | Create navigation with mobile menu | Not Run | [Report](S2-04-03-navigation.md) |
| S2-04-04 | Create reusable footer | Not Run | [Report](S2-04-04-footer.md) |
| S2-04-05 | Develop login page HTML | Not Run | [Report](S2-04-05-login-html.md) |
| S2-04-06 | Style the login page | Not Run | [Report](S2-04-06-login-styling.md) |
| S2-04-07 | Implement basic form validation | Not Run | [Report](S2-04-07-form-validation.md) |
| S2-04-08 | Add empty-field validation messages | Not Run | [Report](S2-04-08-empty-field-messages.md) |
| S2-04-09 | Add invalid-input feedback | Not Run | [Report](S2-04-09-invalid-input-feedback.md) |
| S2-04-10 | Verify responsive layouts | Not Run | [Report](S2-04-10-responsive-layout.md) |
| S2-04-11 | Compare implementation against Figma | Not Run | [Report](S2-04-11-figma-comparison.md) |
| S2-04-12 | Test supported browsers | Not Run | [Report](S2-04-12-browser-testing.md) |
| S2-04-13 | Submit frontend for review | Not Run | [Report](S2-04-13-frontend-review.md) |

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Review shared layout and login page against the acceptance criteria | Reusable layout and login page work responsively, provide validation feedback, and match the approved design | | Not Run | |
| TC-02 | Submit the login page with empty, invalid, and validly formatted input | Appropriate client-side feedback is shown and validly formatted values use the agreed field names | | Not Run | |

## Linked Bugs
None recorded.

## Testing Summary
- Total: 2
- Passed: 0
- Failed: 0
- Blocked: 0
- Not Run: 2

## QA Sign-off
- Status: Pending
- Tested version:
- QA tester: Ahlumile Totose
- Date:
- Notes: No tests have been run.
