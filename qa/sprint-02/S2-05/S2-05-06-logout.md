# S2-05-06 - Build Logout and Protect Private Pages

## Information
- Parent ticket: S2-05 - Login and Logout with PHP Sessions
- Assigned developer: Caleb
- QA status: Not Run
- Build / commit SHA:
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- Test environment:

## Objective
- Destroy the authenticated session on logout, redirect to the login page, and prevent access to private pages after logout.

## Acceptance Criteria
- [ ] Logout destroys the authenticated session and redirects to login.
- [ ] Private pages require an authenticated session.
- [ ] Browser Back after logout does not reveal private page content.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Log out and use browser Back to revisit a private page | User is redirected to login and private content is not available | | Not Run | |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| | | | | Not Run | |

## Linked Bugs
None recorded.
