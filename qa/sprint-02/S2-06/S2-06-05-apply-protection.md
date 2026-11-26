# S2-06-05 - Apply Checks to Protected Pages and Endpoints

## Information
- Parent ticket: S2-06 - Staff and Admin Role Permissions
- Assigned developer: Avela Gxotiwe
- QA status: Not Run
- Build / commit SHA:
- Figma design / version tested: Not Applicable
- Test environment:

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
| TC-01 | Review every route in the protected-page/endpoint inventory | Each route applies the correct login-only or admin-only check before returning protected data or performing actions | | Not Run | |
| TC-02 | Request admin endpoints directly as staff and with forged role input | Server rejects requests and no protected data is returned or modified | | Not Run | |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| | | | | Not Run | |

## Linked Bugs
None recorded.
