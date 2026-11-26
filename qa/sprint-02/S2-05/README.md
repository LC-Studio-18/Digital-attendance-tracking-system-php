# S2-05 - Login and Logout with PHP Sessions

## Ticket Information
- Team: 1
- Sprint: 2
- QA tester: Ahlumile Totose
- Ticket link:
- Assigned developer(s): Lisekho, Caleb; integration coordination: Avela
- Feature branch(es): `team1-login-handler`, `team1-session-logout`
- Build / commit SHA:
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- QA status: Not Run

## Requirements
- User story and context: Authenticate active staff using email and password, establish a secure PHP session on success, and allow the user to log out. Failure feedback must not disclose whether an account exists or is deactivated.

## Acceptance Criteria
- [ ] Login uses prepared database queries and accepts active accounts with valid passwords.
- [ ] Wrong password, unknown email, and deactivated account receive the same generic failure message.
- [ ] Successful login regenerates the session ID and stores the staff ID and role.
- [ ] Logout destroys the session, redirects to login, and private pages are not accessible through browser Back.
- [ ] Frontend and backend are integrated and the login/logout scenarios are QA tested.

## Definition of Done
- [ ] All linked subtasks are complete and reviewed.
- [ ] Login, logout, and failure scenarios have been verified and results recorded.

## Dependencies
- Anela and Lisekho must agree on the form field names and POST target.
- Caleb and Anela must agree on how the backend error message is passed to the login page.
- Nithaam's shared layout should be available on `team1` before frontend integration.

## Subtasks

| ID | Description | QA status | Report |
|---|---|---|---|
| S2-05-01 | Create test staff and admin accounts | Not Run | [Report](S2-05-01-test-accounts.md) |
| S2-05-02 | Build login form handler | Not Run | [Report](S2-05-02-login-handler.md) |
| S2-05-03 | Find staff by email and check active status | Not Run | [Report](S2-05-03-account-lookup.md) |
| S2-05-04 | Check password and return generic failure message | Not Run | [Report](S2-05-04-password-and-failure-message.md) |
| S2-05-05 | Start session and store staff ID and role | Not Run | [Report](S2-05-05-session-creation.md) |
| S2-05-06 | Build logout and protect private pages after logout | Not Run | [Report](S2-05-06-logout.md) |
| S2-05-07 | Display login error message | Not Run | [Report](S2-05-07-login-error-display.md) |
| S2-05-08 | QA test login/logout scenarios and record approval | Not Run | [Report](S2-05-08-qa-verification.md) |
| S2-05-09 | Integrate frontend and backend login/logout flows | Not Run | [Report](S2-05-09-integration.md) |

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Sign in with valid active staff and admin credentials | Each valid user is authenticated with a regenerated session ID and the correct staff ID and role | | Not Run | |
| TC-02 | Try a wrong password, unknown email, and deactivated account | Each attempt fails with the same generic message and creates no authenticated session | | Not Run | |
| TC-03 | Log out, then use browser Back to revisit a private page | Session is destroyed, user is redirected to login, and private content is not available | | Not Run | |

## Linked Bugs
None recorded.

## Testing Summary
- Total: 3
- Passed: 0
- Failed: 0
- Blocked: 0
- Not Run: 3

## QA Sign-off
- Status: Pending
- Tested version:
- QA tester: Ahlumile Totose
- Date:
- Notes: No tests have been run.
