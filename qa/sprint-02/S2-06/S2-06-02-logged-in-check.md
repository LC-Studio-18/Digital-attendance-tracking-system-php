# S2-06-02 - Build the Reusable Logged-In Check

## Information
- Parent ticket: S2-06 - Staff and Admin Role Permissions
- Assigned developer: Avela Gxotiwe
- QA status: Not Run
- Build / commit SHA:
- Figma design / version tested: Not Applicable
- Test environment:

## Objective
- Build a reusable server-side check that confirms a user has an authenticated session and redirects unauthenticated visitors to the login page.

## Acceptance Criteria
- [ ] Check can be reused by protected pages and endpoints.
- [ ] Logged-out requests are denied and redirected to login.
- [ ] A valid logged-in staff or admin session passes the authentication check.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Call the check with logged-out, staff, and admin session states | Logged-out state is redirected; both authenticated roles pass this login-only check | | Not Run | |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| | | | | Not Run | |

## Linked Bugs
None recorded.
