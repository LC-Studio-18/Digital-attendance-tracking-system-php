# GET /api/dashboard

Owner: Migcobo (Backend). All times are SAST (Africa/Johannesburg), with no timezone suffix.
Response header: `Content-Type: application/json`.
`staff_id` comes only from the PHP session. Nothing is read from the URL or body.

## Stub test cases (temporary)

The `?case=` parameter works on the stub only. The real endpoint reads nothing from the URL, so delete this section when the real endpoint replaces the stub.

| URL | State |
|---|---|
| `/api/dashboard.php?case=in` | IN, open shift, one corrected record |
| `/api/dashboard.php?case=out` | OUT, completed shift, no corrections |
| `/api/dashboard.php?case=empty` | OUT, no records |

### `?case=in`

```json
{
  "status": "IN",
  "hours": { "today": 4.5, "week": 22.25 },
  "recent_records": [
    {
      "record_id": 4,
      "clock_in": "2026-11-25 08:00:00",
      "clock_out": null,
      "hours": null,
      "is_corrected": false
    },
    {
      "record_id": 3,
      "clock_in": "2026-11-24 08:05:00",
      "clock_out": "2026-11-24 16:30:00",
      "hours": 8.42,
      "is_corrected": true
    }
  ],
  "generated_at": "2026-11-25 12:30:00"
}
```

### `?case=out`

```json
{
  "status": "OUT",
  "hours": { "today": 8, "week": 30.5 },
  "recent_records": [
    {
      "record_id": 5,
      "clock_in": "2026-11-25 07:30:00",
      "clock_out": "2026-11-25 15:30:00",
      "hours": 8,
      "is_corrected": false
    }
  ],
  "generated_at": "2026-11-25 12:30:00"
}
```

### `?case=empty`

```json
{
  "status": "OUT",
  "hours": { "today": 0, "week": 0 },
  "recent_records": [],
  "generated_at": "2026-11-25 12:30:00"
}
```

Whole-number hours may arrive without a decimal point (`8`, not `8.0`), because PHP's `json_encode` drops it. Format with `toFixed(2)` in the UI rather than relying on the raw value.

## Errors

Always `{ "error": "message" }`, never raw PHP output.

```json
{ "error": "Not authenticated" }
```

| HTTP code | When | `error` |
|---|---|---|
| 401 | No valid session | `"Not authenticated"` |
| 405 | Method other than GET | `"Method not allowed"` |
| 500 | Query or database failure | `"Something went wrong"` |

## Fields

| Field | Type | Notes |
|---|---|---|
| `status` | string | Only `"IN"` or `"OUT"` |
| `hours.today`, `hours.week` | number | Decimal hours, 2 d.p. Never null, `0` if nothing worked |
| `recent_records` | array | Max 10, newest first by `clock_in`, `[]` if none |
| `record_id` | number | |
| `clock_in` | string | `"YYYY-MM-DD HH:mm:ss"`. Never null |
| `clock_out` | string or null | `null` means the shift is still open |
| `hours` (per record) | number or null | `null` for an open shift |
| `is_corrected` | boolean | `true` if the record has an audit log. Always present, never omitted |
| `generated_at` | string | SAST, same format |

## Notes on calculations (pending Vuvu's confirmation)

- `hours.today` and `hours.week`: whether the running time of an open shift is included is TBC with Vuvu.
- Shifts crossing midnight: counting rule TBC with Vuvu.
- Week start day: TBC with Vuvu.