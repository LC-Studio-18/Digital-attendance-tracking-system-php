# S2-06-05 - Apply Checks to Protected Pages and Endpoints

## Information
- Parent ticket: S2-06 - Staff and Admin Role Permissions
- Assigned developer: Avela Gxotiwe
- QA status: Partial - existing dashboard is login-protected, but the latest guest redirect target returns 404; no admin routes exist
- Build / commit SHA: `cbcf8ae` (`origin/team1`)
- Figma design / version tested: Not Applicable
- Test environment: Windows/XAMPP; PHP 8.0.30; `origin/team1` route inventory and HTTP/browser tests.

## Objective
- Apply the reusable logged-in and admin-only checks to every protected page and endpoint that exists.

## Acceptance Criteria
- [ ] Inventory of existing protected pages and endpoints is recorded.
- [ ] Every protected route applies the appropriate reusable check server-side.
- [ ] Staff cannot access admin pages or invoke admin endpoints through direct requests.
- [ ] Role is never accepted from URL parameters, form fields, or editable browser state.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Review every route in the protected-page/endpoint inventory | Each route applies the correct login-only or admin-only check before returning protected data or performing actions | `frontend/index.php` is the only protected page found and calls `requireLogin()` before output. On `cbcf8ae`, anonymous access is denied with HTTP 302, but the target `/login.php` returns HTTP 404. The login page and submit handler are public. No admin-protected page or endpoint exists. | Partial | Dashboard remains protected; BUG-011 breaks the guest redirect, and admin-route coverage remains unavailable. |
| TC-02 | Request admin endpoints directly as staff and with forged role input | Server rejects requests and no protected data is returned or modified | No admin endpoint exists. On `team1`, direct CLI invocation of `requireAdmin()` set response code 403 for staff despite `$_GET['role']='admin'`. | Blocked | Guard logic passed, but route-level denial/data checks cannot be performed without an admin endpoint. |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-27 | QA | backend retest | `2b44dcb` | Partial | Dashboard login guard verified; admin route coverage remains blocked because no admin endpoint exists. |
| 2026-11-27 | QA | merged branch route retest | `2ae8032` (`origin/team1`) | Partial | Visitor redirect and authenticated staff/admin dashboard access verified; no admin-protected route exists. |
| 2026-11-27 | QA | latest team1 route regression retest | `cbcf8ae` (`origin/team1`) | Partial | Protected dashboard still denies guests, but redirects them to `/login.php` (404); no admin-protected route exists. |

## Linked Bugs
BUG-011 - `requireLogin()` redirects logged-out visitors to `/login.php`, which returns 404.
