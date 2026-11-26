# S2-12B live IN/OUT status endpoint — frontend confirmation

**From:** Team 6 Backend
**Confirmed by:** Team 6 Frontend (QR check-in work)
**Status:** Contract accepted; endpoint implementation/deployment still pending.

## Endpoint

| Item | Value |
|---|---|
| Method | `GET` |
| Route | `/api/attendance/status.php` (build from `BASE_URL` in frontend config) |
| Auth | Active PHP session required |
| Staff identity | Session only; any staff ID supplied by the frontend must be ignored |

## Response format

Staff member is IN:

```json
{
  "status": "IN",
  "timestamp": "2026-11-26T08:00:00+02:00"
}
```

Staff member is OUT:

```json
{
  "status": "OUT",
  "timestamp": "2026-11-26T16:30:00+02:00"
}
```

No attendance history:

```json
{
  "status": "OUT",
  "timestamp": null
}
```

### Timestamp rules

- `IN`: time of the currently open clock-in.
- `OUT`: time of the most recent clock-out.
- `null`: no attendance history.
- Format: ISO 8601 with South African offset `+02:00`.
- An open clock-in from a previous day still returns `IN` with its original timestamp.

## Errors

| HTTP | Meaning | Frontend behaviour |
|---|---|---|
| 401 | Not logged in | "Status unavailable"; may offer login, no automatic redirect |
| 403 | Deactivated account / unauthorised | "Status unavailable" |
| 500 | Generic server error | "Status unavailable" |

Any non-2xx response is treated as unavailable, regardless of whether it has a JSON body.

## Frontend notes

- The shape matches the assumed `CONTRACT` in `frontend/assets/js/status-api.js`
  (`statusField: 'status'`, `inValue: 'IN'`, `outValue: 'OUT'`), so no value
  changes are needed there.
- The owner of S2-12A must set `frontend/config/config.php` `status_endpoint`
  to this route; it is currently empty.
- `status-api.js` currently ignores `timestamp`. If the badge must show the
  time, that display change belongs to the S2-12A owner.
- Our check-in screen dispatches `attendance:clock-success` with
  `{ action: 'clock-in', clockInTime }` after a confirmed clock-in, which
  S2-12A listens for. The frontend3 dashboard expects
  `attendance:clock-updated`; that naming is still unagreed.

## Still open

- Backend implementation/deployment of the endpoint.
- S2-08a global state store owner and implementation.
- Agreement on the refresh event name.
- Whether the badge UI must display `timestamp`.
