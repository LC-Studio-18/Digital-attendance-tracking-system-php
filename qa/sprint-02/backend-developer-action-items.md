# Backend Developer Action Items

## Current QA baseline

- Backend-only baseline: `backend1` at `f16502f`, matching `origin/backend1` when checked on 2026-11-27; latest integrated sign-in retest: `team1` at `f2aa1a6`.
- QA did not retain any backend source changes.
- MySQL connection, schema checks, staff/admin authentication, generic login failures, and logout cleanup passed.
- QA sign-off remains blocked by the items below.
- The frontend is merged into `team1`; latest `team1` (`f2aa1a6`) was exercised in Chrome. A valid login POST returns HTTP 501, the authenticated protected page returns HTTP 200, and the guest redirect ends at HTTP 404. No logout route/control is available; browser-back-after-logout remains outstanding.

## Backend work

- **BUG-009 - Set the session cookie's `SameSite` and `HttpOnly` attributes on the frontend path.**
  - The latest `team1` form-login response includes only `Path`; both HttpOnly and SameSite are absent.
  - `frontend/login-submit.php` starts the session before the session helper can apply its cookie settings.
  - Configure the cookie before starting the session, then retest the actual `Set-Cookie` response.

- **BUG-010 - Make the test-account seeder match the database schema.**
  - The current seeder expects a global PDO variable instead of using the configured database connection.
  - Supply required staff fields: `employee_number`, `first_name`, and `last_name`.
  - Use roles allowed by the schema (`staff`, `manager`, or `admin`), not `employee`.
  - Confirm accounts remain safely repeatable and passwords are stored as hashes.

- **Complete the successful login flow.**
  - On latest `team1` (`f2aa1a6`), a valid QA staff login verifies and creates a session, but the endpoint still returns HTTP 501 because dashboard routing is not implemented. Chrome then receives HTTP 200 from `frontend/index.php`, which renders only “Attendance system setup complete.”
  - Agree the success destination/response with the frontend developers, then replace the placeholder with the agreed behavior.

- **BUG-011 - Correct the logged-out redirect target on latest `team1`.**
  - `backend/includes/functions.php` redirects unauthenticated requests to `/login.php`, but the application login page is `/frontend/login.php`.
  - A guest request to `frontend/index.php` receives HTTP 302 and then HTTP 404 at the redirect target; reproduced on `f2aa1a6`.
  - Update the target and retest protected pages from both root and nested paths.

- **S2-06 guard implementation is merged; finish route-level verification when applicable.**
  - PR #2 added reusable `requireLogin()` and `requireAdmin()` checks using trusted session values (`staff_id` and `role`); guard behavior passed QA tests.
  - The existing dashboard uses `requireLogin()`. No admin-only page or endpoint is currently in scope, so end-to-end staff/admin access checks remain blocked until one exists.
  - Apply `requireAdmin()` to any future admin-only route before returning protected data or performing actions; never rely on browser-supplied role values.
  - The earlier `2ae8032` redirect was relative and worked from `frontend/index.php`; latest `f2aa1a6` still uses `/login.php`, which returns 404 (BUG-011).
  - Repeat the integration checks on PHP 8.2+ before QA sign-off.

- **Complete protected-route/logout coverage.**
  - Add or identify protected routes so QA can verify visitor denial, staff/admin permissions, and denial after logout.
  - Verify an old session cannot access protected content after logout. Browser Back testing also needs the frontend pages.

- **Run integration tests on the supported PHP version.**
  - The tested XAMPP runtime is PHP 8.0.30, while the project README requires PHP 8.2+.
  - Repeat the backend checks on PHP 8.2 or newer before sign-off.

## Coordinate with frontend

- Agree the login form’s POST target and field names, the successful-login destination, and how the generic login error is displayed.
- Frontend is merged, but logout has no route/control in the tested UI and browser Back after logout remains untestable. An invalid Chrome submission on `f2aa1a6` returned HTTP 302 to the login page (HTTP 200); its current browser capture was blank, while the generic error was visibly verified on an earlier run.
- Backend guard and seeder work can continue without waiting for frontend completion; final end-to-end QA cannot.
