# QR check-in API contract (proposed)

**Status:** Draft for backend review — not yet implemented.
**Owner:** QR check-in frontend work.
**Purpose:** Define the request/response shape our check-in screen expects, so the
backend can be built against it and we can translate error codes into clear user
messages without guessing.

The frontend currently leaves `data-checkin-endpoint` empty in
`frontend/check-in.php`. Once this contract is agreed, set that attribute to the
route below (built from `BASE_URL`) and the existing POST hook in
`frontend/assets/js/check-in.js` will start using it.

---

## 1. Endpoint

| Item | Value |
|---|---|
| Method | `POST` |
| Route | `api/check-in.php` relative to the `frontend/` web root |
| Content-Type | `application/json` |
| Accept | `application/json` |
| Auth | None for v1 (staff identity comes from the scanned code) |

Other methods should return `405 Method Not Allowed` with an error body in the
same format as section 4.

## 2. Request body

```json
{
  "code": "STAFF-1234"
}
```

Validation rules:

- `code` is required and must be a string.
- Trim surrounding whitespace; reject an empty result with `INVALID_CODE`.
- Maximum length 128 characters after trimming.
- Allowed characters: letters, digits, `-`, `_`, `:` (adjust if badge codes differ).
- Normalise for storage/locking by uppercasing the trimmed value.

## 3. Success response — HTTP 200

```json
{
  "ok": true,
  "code": "STAFF-1234",
  "message": "Checked in successfully.",
  "checkedInAt": "2026-11-25T14:30:00+02:00",
  "state": "checked_in"
}
```

- `ok` is always present on success.
- `message` is shown directly in the confirmation UI.
- `checkedInAt` is an ISO 8601 timestamp from the server clock.
- `state` is `"checked_in"` for v1. Later versions may add `"checked_out"`.

## 4. Error responses

Errors use the same envelope. `message` should be safe to show to a user;
detailed diagnostics belong in server logs.

```json
{
  "ok": false,
  "code": "DUPLICATE_SCAN",
  "message": "That code was just scanned. Please wait a moment.",
  "retryAfter": 25
}
```

| HTTP | `code` | Meaning |
|---|---|---|
| 400 | `INVALID_REQUEST` | Malformed JSON, wrong method, or missing body. |
| 422 | `INVALID_CODE` | Code missing, empty, too long, or invalid characters. |
| 409 | `DUPLICATE_SCAN` | Same code scanned again inside the cooldown window. |
| 409 | `ALREADY_CHECKED_IN` | An active check-in already exists for this staff member. |
| 404 | `UNKNOWN_CODE` | Code is well-formed but not assigned to a staff member. |
| 429 | `RATE_LIMITED` | Too many attempts from one client; include `retryAfter`. |
| 500 | `SERVER_ERROR` | Unexpected failure; show a generic message to the user. |

## 5. Duplicate-scan lock

- Lock key: `hash('sha256', strtoupper(trim($code)))`.
- Cooldown window: **30 seconds** by default; backend may expose it as config.
- The check-and-set must be atomic (file `flock()`, DB unique constraint, or
  equivalent) so two simultaneous scans cannot both succeed.
- A duplicate returns `409` with `code: DUPLICATE_SCAN` and `retryAfter` in
  seconds remaining.
- The lock is released automatically when the window expires; no manual unlock.
- If backend also enforces one active check-in, use `ALREADY_CHECKED_IN` for
  that case (distinct from a rapid rescan).

## 6. Frontend message mapping (to implement)

If `body.code` is present, use these friendly messages; otherwise fall back to
`body.message`, then to a generic message.

| Code | User-facing message |
|---|---|
| `INVALID_REQUEST` | "Something went wrong with the request. Please try again." |
| `INVALID_CODE` | "That code doesn't look right. Check it and try again." |
| `DUPLICATE_SCAN` | "That code was just scanned. Please wait a moment before trying again." |
| `ALREADY_CHECKED_IN` | "This staff member is already checked in." |
| `UNKNOWN_CODE` | "We couldn't find that code. Ask your supervisor to check it." |
| `RATE_LIMITED` | "Too many attempts. Please wait a moment and try again." |
| `SERVER_ERROR` | "We couldn't check you in right now. Please try again later." |

The confirmation UI should show `message` from the success response, plus the
code and `checkedInAt` when available.

## 7. Example

Request:

```bash
curl -i -X POST http://localhost:8000/api/check-in.php \
  -H 'Content-Type: application/json' \
  -d '{"code":"STAFF-1234"}'
```

Success:

```http
HTTP/1.1 200 OK
Content-Type: application/json
```

Duplicate:

```http
HTTP/1.1 409 Conflict
Content-Type: application/json
```

## 8. Open items for backend

- Confirm the route and whether `BASE_URL` prefixes it in staging/production.
- Confirm the exact staff-code format and normalisation rules.
- Confirm whether `ALREADY_CHECKED_IN` is enforced in v1.
- Decide the lock implementation (file vs. database) and cooldown duration.
- Provide the MySQL schema/credentials if lock state lives in the database;
  local PHP here has `pdo_sqlite` but no `pdo_mysql`.
- Confirm the final error codes so the frontend mapping can be locked in.
