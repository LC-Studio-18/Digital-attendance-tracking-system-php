# QR check-in API contract

**Status:** Frontend aligned with the S2-11b services implemented on
`origin/backend6-qr-checkin`. The HTTP route itself is not deployed yet.
**Owner:** QR check-in frontend work.
**Purpose:** Fix the request/response shape so the UI can translate error codes
into clear messages without guessing.

The check-in form in `frontend/check-in.php` carries `data-checkin-endpoint`,
built from `BASE_URL`. `frontend/assets/js/check-in.js` POSTs to it; clearing the
attribute disables the request so no fake success is shown before deployment.

---

## 1. Endpoint

| Item | Value |
|---|---|
| Method | `POST` |
| Route | `api/check-in.php` relative to the `frontend/` web root |
| Content-Type | `application/json` |
| Accept | `application/json` |
| Auth | Optional: anonymous scans are allowed, but a `401` is handled when the deployment requires a session |

Other methods should return `405 Method Not Allowed` with an error body in the
same format as section 4.

## 2. Request body

```json
{
  "qrToken": "64-character-hex-token"
}
```

Notes:

- `qrToken` is required and must be a non-empty string.
- The QR code may encode either the raw token or the issuer's clock-in URL
  (`<app-url>/clock-in?qrToken=<token>`). The UI extracts the `qrToken` query
  parameter before submitting, and also pre-fills it when that URL is opened.
- The token service accepts 64 lowercase hex characters; anything else returns
  `INVALID_QR`.
- `?qrToken=` on `check-in.php` pre-fills the form but never auto-submits; an
  empty `qrToken=` counts as no token, and repeat submits are ignored while a
  request is in flight.

## 3. Success response — HTTP 201

```json
{
  "ok": true,
  "code": "CLOCKED_IN",
  "data": {
    "id": 123,
    "user_id": 45,
    "site_id": 2,
    "qr_code_id": 9,
    "method": "QR",
    "clocked_in_at_utc": "2026-11-27 08:00:00"
  }
}
```

- This is the shape returned by `processQrClockIn()` in the backend service.
- `clock_in_utc` (ISO 8601) and `clock_in_time` are also read from `data`, so the
  proposed `{ "status": "success", "data": { ... } }` envelope keeps working.
- A top-level `message`, when present, is shown verbatim. Otherwise the UI shows
  a generic confirmation plus the clock-in time from `data`.

## 4. Error responses

Errors use the same envelope as success, with `ok: false`. `message` is safe to
show to a user; detailed diagnostics belong in server logs.

```json
{
  "ok": false,
  "code": "EXPIRED_QR",
  "message": "This QR code has expired."
}
```

The frontend also recognises `{ "status": "error", "code": ..., "message": ... }`.

| HTTP | `code` | Meaning |
|---|---|---|
| 400 | `INVALID_QR` | Malformed, unknown, or inactive token. |
| 400 | `EXPIRED_QR` | Token is known but past its UTC expiry. |
| 401 | `UNAUTHORIZED` | A session is required but the user is not logged in. |
| 409 | `ALREADY_CLOCKED_IN` | The user already has an active clock-in session. |
| 400 | `INVALID_REQUEST` | Malformed JSON or missing body (older manual-code contract). |
| 422 | `INVALID_CODE` | Manual code missing or malformed (older manual-code contract). |
| 409 | `DUPLICATE_SCAN` / `ALREADY_CHECKED_IN` | Older manual-code contract; `retryAfter` is shown when present. |
| 404 | `UNKNOWN_CODE` | Older manual-code contract: code not assigned to a staff member. |
| 429 | `RATE_LIMITED` | Too many attempts; include `retryAfter` in seconds. |
| 500 | `SERVER_ERROR` | Unexpected failure; show a generic message. |

## 5. Duplicate clock-in

- One active clock-in session per user is enforced by `ALREADY_CLOCKED_IN` (409).
- The S2-11b QR flow does not use a short rescan cooldown; `DUPLICATE_SCAN` is
  retained only for the older manual-code contract, including its `retryAfter`
  display.

## 6. Frontend message mapping

If `body.code` is known, the UI uses these messages; otherwise it falls back to
`body.message`, then to a generic message.

| Code | User-facing message |
|---|---|
| `INVALID_QR` | "This QR code is invalid. Check the code and try again." |
| `EXPIRED_QR` | "This QR code has expired. Ask for a new one." |
| `UNAUTHORIZED` | "Please log in first, then try your QR code again." |
| `ALREADY_CLOCKED_IN` | "You are already clocked in." |
| `ALREADY_CHECKED_IN` | "You are already checked in." |
| `INVALID_REQUEST` | "Something went wrong with the request. Please try again." |
| `INVALID_CODE` | "That code doesn't look right. Check it and try again." |
| `DUPLICATE_SCAN` | "That code was just scanned. Please wait a moment before trying again." |
| `UNKNOWN_CODE` | "We couldn't find that code. Ask your supervisor to check it." |
| `RATE_LIMITED` | "Too many attempts. Please wait a moment and try again." |
| `SERVER_ERROR` | "We couldn't clock you in right now. Please try again later." |

After a confirmed clock-in, the UI dispatches `attendance:clock-success` so the
live status badges from S2-12A refresh. Its detail is
`{ action: 'clock-in', clockInTime }`, so consumers do not need to parse the
confirmation text.

## 7. Example

Request:

```bash
curl -i -X POST http://localhost:8000/api/check-in.php \
  -H 'Content-Type: application/json' \
  -d '{"qrToken":"0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef"}'
```

Success:

```http
HTTP/1.1 201 Created
Content-Type: application/json
```

Error:

```http
HTTP/1.1 409 Conflict
Content-Type: application/json
```

## 8. Open items

- Deploy the HTTP route and confirm whether `BASE_URL` prefixes it in staging/production.
- Confirm whether the `{ status: "success" | "error" }` envelope is ever adopted;
  the UI already accepts both shapes.
- Confirm the timestamp format for `clock_in_utc` versus `clocked_in_at_utc`.
- Decide where the login page should live so `UNAUTHORIZED` can offer a link.
- S2-12B status contract is confirmed in `docs/s2-12b-status-contract.md`; the
  refresh-event agreement and S2-08a store are still open.
