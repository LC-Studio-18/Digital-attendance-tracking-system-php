# S2-05-09 - Integrate Frontend and Backend Login/Logout Flows

## Information
- Parent ticket: S2-05 - Login and Logout with PHP Sessions
- Assigned developer: Avela
- QA status: Blocked - authentication establishes a session, but routing, cookie attributes, and guest redirect need fixes
- Build / commit SHA: `f2aa1a6` (`origin/team1`)
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- Test environment: Windows/XAMPP; PHP 8.0.30; `attendance_test_db`; local PHP built-in server and browser.

## Objective
- Integrate the frontend and backend login/logout work, including the agreed form field names, POST target, and generic error-message handoff.

## Acceptance Criteria
- [ ] Login form fields and POST target match the backend handler.
- [ ] Successful login, failure feedback, and logout work across frontend/backend boundaries.
- [ ] Integration changes are reviewed and any blockers are documented.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Exercise the integrated login success, failure, and logout flows | Frontend and backend exchange the agreed data and each flow behaves as specified | On `f2aa1a6`, a valid staff login POST returned HTTP 501; the same session then reached protected `frontend/index.php` with HTTP 200 and rendered “Attendance system setup complete.” A synthetic invalid login returned HTTP 302 to `frontend/login.php`, followed by HTTP 200 with the generic error visibly rendered. An anonymous request still redirects to `/login.php` and returns 404. No logout UI/route is available in the tested flow, so logout/browser Back could not be exercised. | Blocked | BUG-009 (cookie attributes) and BUG-011 (invalid guest redirect); successful-login routing and logout/browser coverage remain incomplete. [Valid-login 501](../evidence/TC-003-valid-login-501-blocked.png); [generic failure](../evidence/TC-004-generic-login-error-pass.png); [authenticated page/no logout](../evidence/TC-005-authenticated-page-no-logout-blocked.png); [guest redirect 404](../evidence/TC-006-guest-redirect-404-team1.png) |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-26 | QA | backend test | `f16502f` | Blocked | Component checks only; end-to-end integration remains pending. |
| 2026-11-26 | QA | MySQL/HTTP retest | `f16502f` + MySQL QA fixtures | Blocked | Backend HTTP flow tested; frontend integration and dashboard routing remain incomplete. |
| 2026-11-27 | QA | merged frontend/backend retest | `2ae8032` (`origin/team1`) | Blocked | Browser login form and generic failure display verified; valid login still returns HTTP 501. Cookie checks found missing HttpOnly/SameSite on the frontend path and missing SameSite on direct backend login. |
| 2026-11-27 | QA | latest team1 integration retest | `cbcf8ae` (`origin/team1`) | Blocked | Valid QA login authenticated and passed the protected page, but the form response remained HTTP 501; cookie attributes were still missing, and the guest redirect led to 404. |
| 2026-11-27 | QA | isolated browser screenshot retest | `f2aa1a6` (`origin/team1`) | Blocked | Valid POST returned 501, authenticated protected route returned 200, and synthetic invalid login displayed the generic error. Guest redirect still ends at 404; no integrated logout route/control is available. Screenshots are linked above. |

## Linked Bugs
- BUG-009 - Session cookie SameSite=Lax is missing; frontend login path also omits HttpOnly.
- BUG-011 - Logged-out protected-page redirect points to `/login.php`, which returns 404.
