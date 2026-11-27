# S2-05 - Login and Logout with PHP Sessions

## Ticket Information
- Team: 1
- Sprint: 2
- QA tester: Ahlumile Totose
- Ticket link:
- Assigned developer(s): Lisekho, Caleb; integration coordination: Avela
- Feature branch(es): `team1-login-handler`, `team1-session-logout`
- Build / commit SHA: `f2aa1a6` (`origin/team1`)
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- QA status: Blocked (authentication succeeds, but dashboard routing returns HTTP 501; session cookie attributes and the guest redirect need fixes; logout/browser coverage and supported-runtime validation remain outstanding)

## Requirements
- User story and context: Authenticate active staff using email and password, establish a secure PHP session on success, and allow the user to log out. Failure feedback must not disclose whether an account exists or is deactivated.

## Acceptance Criteria
- [x] Login uses prepared database queries and accepts active accounts with valid passwords.
- [x] Wrong password, unknown email, and deactivated account receive the same generic failure message.
- [x] Successful login regenerates the session ID and stores the staff ID and role.
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
| S2-05-01 | Create test staff and admin accounts | Pass (data verified; BUG-010 remains open) | [Report](S2-05-01-test-accounts.md) |
| S2-05-02 | Build login form handler | Blocked (valid test account authenticates; dashboard route missing) | [Report](S2-05-02-login-handler.md) |
| S2-05-03 | Find staff by email and check active status | Pass (MySQL retest) | [Report](S2-05-03-account-lookup.md) |
| S2-05-04 | Check password and return generic failure message | Pass (MySQL/HTTP retest) | [Report](S2-05-04-password-and-failure-message.md) |
| S2-05-05 | Start session and store staff ID and role | Blocked (frontend cookie omits HttpOnly and SameSite on latest team1) | [Report](S2-05-05-session-creation.md) |
| S2-05-06 | Build logout and protect private pages after logout | Blocked (protected route/browser coverage missing) | [Report](S2-05-06-logout.md) |
| S2-05-07 | Display login error message | Pass (generic error rendered in browser) | [Report](S2-05-07-login-error-display.md) |
| S2-05-08 | QA test login/logout scenarios and record approval | Blocked | [Report](S2-05-08-qa-verification.md) |
| S2-05-09 | Integrate frontend and backend login/logout flows | Blocked (auth session works; login returns HTTP 501 and guest redirect resolves to 404) | [Report](S2-05-09-integration.md) |

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Sign in with valid active staff and admin credentials | Each valid user is authenticated with a regenerated session ID and the correct staff ID and role | In Chrome on `f2aa1a6`, the active QA staff login POST returned HTTP 501 (“Dashboard routing has not been implemented.”). The same browser session then received HTTP 200 from protected `frontend/index.php`, which rendered “Attendance system setup complete.” The login cookie issue previously captured on `cbcf8ae` remains applicable; no authentication code changed in `f2aa1a6`. | Blocked | BUG-009; the session helper's cookie settings are bypassed because `frontend/login-submit.php` starts the session first. Successful-login routing remains unimplemented; admin login was not tested in Chrome. [HTTP 501 screenshot](../evidence/TC-003-valid-login-501-blocked.png); [authenticated page screenshot](../evidence/TC-005-authenticated-page-no-logout-blocked.png) |
| TC-02 | Try a wrong password, unknown email, and deactivated account | Each attempt fails with the same generic message and creates no authenticated session | Earlier MySQL-backed attempts returned the same generic error for wrong password, unknown email, and inactive account. On the fresh `f2aa1a6` browser retest, a synthetic invalid login returned HTTP 302 to `frontend/login.php`, followed by HTTP 200 with “Invalid email or password.” visibly rendered. | Pass | The screenshot captures the generic message; the full wrong-password and inactive-account matrix remains covered by the earlier backend tests. [Generic login error screenshot](../evidence/TC-004-generic-login-error-pass.png) |
| TC-03 | Log out, then use browser Back to revisit a private page | Session is destroyed, user is redirected to login, and private content is not available | The authenticated Chrome request reached protected `frontend/index.php` with HTTP 200. No user-facing logout route/control is available in the tested `team1` pages; the page only renders “Attendance system setup complete.” Browser Back after logout could not be exercised. Earlier backend helper/endpoint checks were on `backend1`, not this integrated route. | Blocked | Logout and browser Back remain untestable on the current integrated UI; a logout endpoint/control and private dashboard route are needed. [Authenticated page with no logout control](../evidence/TC-005-authenticated-page-no-logout-blocked.png) |

## Linked Bugs
- BUG-009 - Session cookie SameSite=Lax is missing; the merged frontend login path also omits HttpOnly.
- BUG-010 - Test-account seeder is incompatible with the staff schema; remains open.
- BUG-011 - Logged-out protected-page redirect points to `/login.php`, which returns 404; the app login page is `/frontend/login.php`.

## Testing Summary
- Total: 3
- Passed: 1
- Failed: 0
- Blocked: 2
- Not Run: 0

## QA Sign-off
- Status: Pending - blocked
- Tested version: `f2aa1a6` (`origin/team1`)
- QA tester: Ahlumile Totose
- Date: 2026-11-27
- Notes: Retested the latest `team1` commit `f2aa1a6` in actual Chrome using the active QA account and an isolated invalid-login attempt. The valid POST returned HTTP 501, but the authenticated protected page returned HTTP 200 and rendered “Attendance system setup complete.” The invalid POST returned HTTP 302 to the login page, which returned HTTP 200; the current browser capture did not render its body, so visual error confirmation relies on the earlier verified browser test. A logged-out request to `frontend/index.php` still redirects to `/login.php`, which returns 404 (BUG-011). No logout control/route is available in the tested UI, so logout/browser Back remains blocked. QA sign-off remains blocked by BUG-009, BUG-010, BUG-011, the 501 dashboard placeholder, incomplete logout/browser coverage, and PHP 8.0.30 being below the documented PHP 8.2+ minimum. No developer source code was changed.
