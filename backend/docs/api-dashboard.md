# GET /api/dashboard

Returns the logged-in staff member's dashboard data. All times are SAST (Africa/Johannesburg, UTC+2).

**Auth:** PHP session only. `staff_id` comes from the session (set by login). Anything sent in the URL or body is ignored.
**Method:** GET only.

## Example responses by state

### IN (open shift, with a corrected record)

`status` is `"IN"`, the open shift has `clock_out: null` and `hours: null`, and record 2 is corrected.

```json
{
  "staff": { "first_name": "Test", "last_name": "Staff" },
  "status": "IN",
  "hours": { "today": 7.34, "week": 32.34 },
  "days_present": 4,
  "attendance_rate": 21.1,
  "chart": {
    "labels": ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
    "hours": [8, 9, 8, 7.34, 0, 0, 0]
  },
  "recent_records": [
    { "record_id": 4, "clock_in": "2026-11-26 08:00:00", "clock_out": null,
      "hours": , "is_corrected": false, "attendance_status": "Present" },
    { "record_id": 3, "clock_in": "2026-11-25 09:00:00", "clock_out": "2026-11-25 17:00:00",
      "hours": 8, "is_corrected": false, "attendance_status": "Late" },
    { "record_id": 2, "clock_in": "2026-11-24 08:00:00", "clock_out": "2026-11-24 17:00:00",
      "hours": 9, "is_corrected": true, "attendance_status": "Present" }
  ],
  "generated_at": "2026-11-26 15:20:16"
}
```

### OUT (no open shift)

`status` is `"OUT"`, every record has a `clock_out`, and `hours.today` is 0.

```json
{
  "staff": { "first_name": "Other", "last_name": "Staff" },
  "status": "OUT",
  "hours": { "today": 0, "week": 8 },
  "days_present": 1,
  "attendance_rate": 5.3,
  "chart": {
    "labels": ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
    "hours": [0, 0, 8, 0, 0, 0, 0]
  },
  "recent_records": [
    { "record_id": 5, "clock_in": "2026-11-25 08:00:00", "clock_out": "2026-11-25 16:00:00",
      "hours": 8, "is_corrected": false, "attendance_status": "Present" }
  ],
  "generated_at": "2026-11-26 12:49:05"
}
```

### Empty (new staff member, no records)

This is still a `200`, not an error. `recent_records` is `[]`, all numbers are 0 and the chart is seven zeros.

```json
{
  "staff": { "first_name": "Test", "last_name": "Manager" },
  "status": "OUT",
  "hours": { "today": 0, "week": 0 },
  "days_present": 0,
  "attendance_rate": 0,
  "chart": {
    "labels": ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
    "hours": [0, 0, 0, 0, 0, 0, 0]
  },
  "recent_records": [],
  "generated_at": "2026-11-26 12:49:40"
}
```

### Error (no session)

HTTP 401:

```json
{ "error": "Not authenticated" }
```
## Fields

| Field | Type | Notes |
|---|---|---|
| `staff.first_name` | string | For the dashboard greeting |
| `staff.last_name` | string | |
| `status` | string | `"IN"` or `"OUT"` |
| `hours.today` | number | Hours worked today. An open shift counts up to now. |
| `hours.week` | number | Hours worked this week (Monday to Sunday). |
| `days_present` | number | Distinct days this month with at least one clock-in. |
| `attendance_rate` | number | Percentage (0 to 100, one decimal): `days_present` divided by Mon-Fri days elapsed this month, including today. Public holidays are not handled. |
| `chart.labels` | string[7] | `Mon` to `Sun`, always 7 items |
| `chart.hours` | number[7] | Hours per day this week, same order as `labels`. A shift that crosses midnight is split across the days it covers. |
| `recent_records` | array | Last 10 records, newest first. `[]` if there are none. |
| `recent_records[].record_id` | number | |
| `recent_records[].clock_in` | string | `YYYY-MM-DD HH:MM:SS` |
| `recent_records[].clock_out` | string or null | `null` means the shift is still open. |
| `recent_records[].hours` | number or null | `null` for an open shift. |
| `recent_records[].is_corrected` | boolean | `true` if the record has at least one audit log. |
| `recent_records[].attendance_status` | string | `"Late"` if clock-in is after 08:00:00 SAST, otherwise `"Present"`. |
| `generated_at` | string | When the response was created. |

## Errors

All errors return `{ "error": "message" }`.

| Status | When | Body |
|---|---|---|
| 401 | No valid session | `{"error":"Not authenticated"}` |
| 405 | Method is not GET (`Allow: GET` header) | `{"error":"Method not allowed"}` |
| 500 | Server or database problem | `{"error":"Something went wrong. Please try again."}` |

## Frontend notes

- Show an open shift (`clock_out: null`) as "In progress".
- Use `is_corrected` for the corrected-entry flag and `attendance_status` for the Late/Present badge.
- `chart.labels` and `chart.hours` always have 7 items, so they can be plotted directly.
- Handle an empty `recent_records` array (a new staff member has none).
- On a 401, redirect to login.
- Rule values to confirm with the team: the late cutoff (08:00) and the attendance rate formula.