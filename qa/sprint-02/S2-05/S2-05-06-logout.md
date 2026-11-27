# S2-05-06 - Build Logout and Protect Private Pages

## Information
- Parent ticket: S2-05 - Login and Logout with PHP Sessions
- Assigned developer: Caleb
- QA status: Blocked (logout helper was checked on `backend1`; no user-facing logout route/control exists in the latest integrated UI)
- Build / commit SHA: `f2aa1a6` (`origin/team1`)
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- Test environment: Windows/XAMPP; PHP 8.0.30; MySQL fixtures, local PHP built-in server, and Chrome on the isolated `team1` worktree.

## Objective
- Destroy the authenticated session on logout, redirect to the login page, and prevent access to private pages after logout.

## Acceptance Criteria
- [x] Logout destroys the authenticated session and redirects to login.
- [ ] Private pages require an authenticated session.
- [ ] Browser Back after logout does not reveal private page content.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Log out and use browser Back to revisit a private page | User is redirected to login and private content is not available | Earlier HTTP logout helper/endpoint checks on `backend1` cleared the session. On `f2aa1a6`, Chrome reached the protected `frontend/index.php` after login, but the tested UI exposes no logout control/route and the page contains only “Attendance system setup complete.” Browser Back after logout and reuse of a destroyed session therefore remain untested. | Blocked | No integrated logout route/control; private dashboard/browser coverage remains incomplete. [Authenticated page with no logout control](../evidence/TC-005-authenticated-page-no-logout-blocked.png) |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-26 | QA | backend test | `f16502f` | Blocked | Session destruction verified; HTTP redirect and protected-route behavior remain unverified. |
| 2026-11-26 | QA | MySQL/HTTP retest | `f16502f` + MySQL QA fixtures | Blocked | HTTP redirect, cookie expiry, and server-side session destruction passed; protected-route/browser checks remain blocked. |
| 2026-11-27 | QA | latest team1 Chrome retest | `f2aa1a6` (`origin/team1`) | Blocked | Authenticated protected route returned 200, but no logout route/control was available to test logout or browser Back. |

## Linked Bugs
None recorded.
