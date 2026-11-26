# Clock-out API contract (S2-09B)

`POST /api/clock-out`  (handled by `backend/controllers/clock-out.php`; the URL route is wired by whoever owns the web root)

Closes the logged-in user's active clock-in session, records the clock-out
time and saves the total session duration.

## Request

- Auth: PHP session cookie (user must be logged in)
- Body: none. The server sets the clock-out time, the client never sends one.

```js
fetch('/api/clock-out', { method: 'POST', credentials: 'same-origin' })
  .then(res => res.json());
```

## Success: 200

```json
{
  "success": true,
  "data": {
    "record_id": 4,
    "clock_in": "2026-11-26 08:30:15",
    "clock_out": "2026-11-26 17:00:00",
    "duration_seconds": 30585
  }
}
```

Timestamps are `YYYY-MM-DD HH:MM:SS` in SAST (Africa/Johannesburg).

## Errors

```json
{ "success": false, "error": { "code": "NO_ACTIVE_SESSION", "message": "..." } }
```

| HTTP | code                | When                                                               |
|------|---------------------|--------------------------------------------------------------------|
| 401  | `UNAUTHENTICATED`   | User is not logged in                                              |
| 409  | `NO_ACTIVE_SESSION` | No open clock-in session (never clocked in, or already clocked out) |

The error shape is provisional until S2-13B defines the standard format.
