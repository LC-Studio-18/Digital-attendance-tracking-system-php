# S2-05-02 - Build Login Form Handler

## Information
- Parent ticket: S2-05 - Login and Logout with PHP Sessions
- Assigned developer: Lisekho
- QA status: Blocked (valid login authenticates, but dashboard routing returns HTTP 501)
- Build / commit SHA: `f2aa1a6` (`origin/team1`)
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- Test environment: Windows/XAMPP; PHP 8.0.30; `attendance_test_db`; isolated `origin/team1` worktree and local PHP built-in server.

## Objective
- Implement the PHP handler that receives and processes the login form data.

## Acceptance Criteria
- [x] Handler accepts the agreed POST fields and method.
- [x] Missing or malformed values are handled without an authenticated session.
- [ ] Valid and invalid credentials follow the defined login flow.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Submit the login form with valid, missing, and malformed values | Handler processes valid input and safely rejects invalid submissions | On `f2aa1a6`, a valid staff login POST returned HTTP 501, while the authenticated protected page returned HTTP 200. A synthetic invalid login returned HTTP 302 to `frontend/login.php`, followed by HTTP 200 with the generic error visibly rendered. | Blocked | Authentication works; final sign-in destination is still unimplemented. The guest redirect to `/login.php` also resolves to 404 (BUG-011). [Valid-login HTTP 501](../evidence/TC-003-valid-login-501-blocked.png); [generic failure feedback](../evidence/TC-004-generic-login-error-pass.png) |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-26 | QA | backend test | `f16502f` | Blocked | Input checks passed in isolation; endpoint requires local database configuration. |
| 2026-11-26 | QA | MySQL/HTTP retest | `f16502f` + MySQL QA fixtures | Blocked | GET/POST behavior and database-backed authentication tested; successful authentication reaches the existing 501 dashboard placeholder. |
| 2026-11-27 | QA | latest team1 sign-in retest | `cbcf8ae` (`origin/team1`) | Blocked | QA account authenticated and session passed the protected page; form response still returns HTTP 501 before routing. |
| 2026-11-27 | QA | latest team1 Chrome sign-in retest | `f2aa1a6` (`origin/team1`) | Blocked | Valid login POST returned 501 and authenticated protected route returned 200; invalid login returned 302 then 200. Guest redirect remains broken. |

## Linked Bugs
None recorded.
