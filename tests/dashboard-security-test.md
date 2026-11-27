# Dashboard API Security Test

## Objective
Verify that dashboard data is restricted to the authenticated staff member and cannot be accessed by supplying another staff member's ID.

## Test accounts
- Staff 1: Test Staff (ID 1)
- Staff 2: Other Staff (ID 2)

## Test 1: Authenticated request
1. Log in as Staff 1.
2. Send GET `/api/dashboard`.
3. Include Staff 1's session cookie.

Expected:
- HTTP 200.
- Staff 1's information and attendance records are returned.

## Test 2: Different staff ID in URL
1. Keep Staff 1's authenticated session.
2. Send GET `/api/dashboard?staff_id=2`.
3. Include the same session cookie.

Expected:
- HTTP 200.
- Staff 1's information and records are returned.
- Staff 2's information and records are not returned.

## Test 3: Different staff ID in request body
1. Keep Staff 1's authenticated session.
2. Send GET `/api/dashboard`.
3. Include `staff_id=2` in the request body.
4. Include the same session cookie.

Expected:
- The endpoint continues to use Staff 1's session ID.
- Staff 2's information and records are not returned.

## Test 4: No authenticated session
1. Remove the session cookie.
2. Send GET `/api/dashboard`.

Expected:
- HTTP 401.
- JSON error response.
- No staff or attendance information is returned.

## Pass criteria
All four tests must pass. The authenticated session must determine which staff member's data is returned, regardless of any staff ID supplied by the requester.

## Current verification status
The endpoint source code uses the session-derived staff ID when calling the dashboard query functions. Runtime verification is still required.

## Database Query Isolation Test Results

**Test file:** `tests/dashboard-live-test.php`

**Database:** Local MySQL attendance database using seeded test data.

| Test | Result |
|---|---|
| Staff 1 receives their own records | PASS |
| Staff 2 receives their own records | PASS |
| Staff 3 receives no records | PASS |
| All returned records belong to the requested staff ID | PASS |

**Total:** 8 passed, 0 failed.

**Conclusion:** The dashboard query functions correctly isolate attendance records by the supplied staff ID when tested against the live database.

**Limitation:** This test does not verify PHP session enforcement or attempts to manipulate the API URL/request body. Those checks require an executable dashboard endpoint.

**Overall API security status:** Pending integration testing.