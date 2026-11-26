# Digital Attendance Tracking System

A PHP and MySQL-based attendance tracking system for onsite staff. The system will allow staff to check in and check out, while providing a live dashboard showing who is currently onsite.

## Project overview

This project is being developed to provide a cost-effective, real-time attendance tracking solution. It will support digital check-in and check-out, user authentication, live attendance visibility, duplicate-entry prevention, secure data transmission, and cross-platform use.

The project deadline is **4 December 2026**.

## Tech stack

- PHP
- MySQL
- PDO for database connectivity
- HTML
- CSS
- JavaScript
- Xneelo hosting

## Project structure

```text
Digital-attendance-tracking-system-php/
├── backend/
│   ├── config/
│   ├── includes/
│   ├── controllers/      (planned)
│   ├── models/           (planned)
│   └── views/            (planned)
├── frontend/
│   ├── assets/
│   │   ├── css/
│   │   └── js/
│   └── index.php
├── database/
│   ├── schema.sql
│   ├── seed.sql
│   └── create_app_user.sql
├── tests/
│   ├── test_connection.php
│   └── test_idempotency.php
├── config/
├── storage/              (planned)
│   └── logs/
├── .env.example
├── .gitignore
└── README.md
```

## Folder explanation

| Folder | Purpose |
|---|---|
| `backend/` | Contains server-side PHP application code |
| `backend/config/` | Contains reusable application and database configuration |
| `backend/includes/` | Contains shared PHP helper functions and error handling |
| `backend/controllers/` | Will contain files that handle user requests and responses |
| `backend/models/` | Will contain database queries and business logic |
| `backend/views/` | Will contain PHP page templates |
| `frontend/` | Contains files served to the browser |
| `frontend/assets/css/` | Contains CSS stylesheets |
| `frontend/assets/js/` | Contains browser JavaScript files |
| `database/` | Contains the MySQL schema, development seed data and app-user setup SQL |
| `tests/` | Contains CLI verification scripts |
| `config/` | Contains protected server-specific configuration and credentials |
| `storage/logs/` | Will contain application logs |
| `.env.example` | Shows the environment variables required to run the project locally |
| `.gitignore` | Prevents sensitive files and unnecessary files from being committed |

## Security

- Database credentials must not be committed to GitHub.
- Real credentials must be stored in `config/credentials.php` or environment variables.
- `config/credentials.php` and `.env` must remain outside version control.
- The `frontend/` folder should contain only files intended to be accessed through the browser.
- Backend configuration, credentials, controllers, models, and logs must not be placed inside `frontend/`.
- PDO prepared statements must be used for database queries that include user input.
- Detailed errors should be logged, while users should receive generic error messages.

## Request tokens and idempotency (S2-13B)

Attendance writes can include a `request_token`. `database/schema.sql` defines this as `VARCHAR(64) NULL` with a unique constraint, so a token can be used for only one attendance row. Generate a fresh token for each intended attendance action, and resend the same token when retrying a request after a timeout or network failure.

### Implemented backend pieces

- `backend/includes/IdempotencyService.php` checks tokens, reuses original records and handles concurrent duplicate inserts using the database constraint.
- `backend/includes/error-handler.php` provides reusable JSON response helpers: `sendJsonResponse()`, `sendJsonSuccess()`, `sendJsonError()` and `sendIdempotencyResponse()`.
- S2-13B does **not** add an attendance HTTP endpoint. Controllers can pass an `IdempotencyService` result to `sendIdempotencyResponse()` when an endpoint is implemented.

A token must be non-empty after trimming and no longer than 64 characters. Invalid tokens, tokens owned by another staff member and database failures are rejected without creating an attendance row.

### JSON response format (proposed, pending frontend confirmation)

The S2-13B backend helpers emit the shape below. The cross-team frontend agreement is being handled separately; no approval is recorded in this repository, so treat this as a **proposed** contract until the frontend team confirms it.

Every response from the helpers uses the same three fields:

- `status` — `success` or `error`
- `message` — a short, frontend-safe explanation
- `code` — a stable machine-readable code

A successful result may also include `data.record`. Responses are sent with `Content-Type: application/json; charset=utf-8` and an appropriate HTTP status code.

Successful creation example:

```http
HTTP/1.1 201 Created
Content-Type: application/json; charset=utf-8

{
  "status": "success",
  "message": "Attendance recorded successfully.",
  "code": "ATTENDANCE_RECORDED",
  "data": {
    "record": {
      "record_id": 12,
      "staff_id": 1,
      "request_token": "S2-13B-NEW-A1B2C3D4E5F6",
      "clock_in": "2026-11-26 08:00:00",
      "clock_out": null,
      "created_at": "2026-11-26 08:00:00",
      "updated_at": "2026-11-26 08:00:00"
    }
  }
}
```

A retry with the same token and staff member returns the original record instead of inserting another row:

```http
HTTP/1.1 200 OK
Content-Type: application/json; charset=utf-8

{
  "status": "success",
  "message": "Attendance already recorded for this request.",
  "code": "CLOCK_ALREADY_RECORDED",
  "data": {
    "record": {
      "record_id": 12,
      "staff_id": 1,
      "request_token": "S2-13B-NEW-A1B2C3D4E5F6",
      "clock_in": "2026-11-26 08:00:00",
      "clock_out": null,
      "created_at": "2026-11-26 08:00:00",
      "updated_at": "2026-11-26 08:00:00"
    }
  }
}
```

### Error responses

| HTTP status | `code` | `message` |
|---|---|---|
| `400 Bad Request` | `INVALID_REQUEST_TOKEN` | A valid request token is required. |
| `403 Forbidden` | `TOKEN_USER_MISMATCH` | This request token cannot be used for your account. |
| `500 Internal Server Error` | `REQUEST_FAILED` | Unable to process your request right now. Please try again. |
| `500 Internal Server Error` | `INTERNAL_SERVER_ERROR` | An unexpected error occurred. Please try again later. |

Example:

```http
HTTP/1.1 403 Forbidden
Content-Type: application/json; charset=utf-8

{
  "status": "error",
  "message": "This request token cannot be used for your account.",
  "code": "TOKEN_USER_MISMATCH"
}
```

API responses never include technical details such as exception messages, SQL errors, stack traces or credentials. Uncaught exceptions are written to the PHP error log; `APP_DEBUG=true` only adds trace detail to that internal log entry, never to the response.

### How retries and cross-user reuse are handled

- **Same staff, same token:** `IdempotencyService` finds the existing row and returns it with `CLOCK_ALREADY_RECORDED`. `sendIdempotencyResponse()` sends HTTP `200`, so a retry is safe.
- **Simultaneous retries:** the unique database constraint allows only one insert. A duplicate-key collision is converted into the same replay result.
- **Different staff, same token:** the stored `staff_id` is compared with the requesting staff member. A mismatch returns `TOKEN_USER_MISMATCH` with HTTP `403`; no new row is created.
- **Invalid or missing token:** blank, whitespace-only and over-long tokens return `INVALID_REQUEST_TOKEN` with HTTP `400`.

### Running the S2-13B test script

Run from the project root after applying the tables from `database/schema.sql` and configuring `config/credentials.php`. `tests/test_idempotency.php` creates two temporary staff accounts and needs `DELETE` permission on the database it connects to so it can remove only its own test data.

Prefer a disposable test database with a dedicated test-only account that has `SELECT`, `INSERT`, `UPDATE` and `DELETE` on that database only. Do **not** grant `DELETE` to the limited application user used by the running application. The script checks the token column, unique index and cleanup privilege before creating any data, then removes only the staff and attendance rows it created.

```powershell
C:\xampp\php\php.exe tests\test_connection.php
C:\xampp\php\php.exe tests\test_idempotency.php
```

If PHP is on your `PATH`, `php tests\test_idempotency.php` is equivalent. The script prints `[PASS]`/`[FAIL]` for each check and exits non-zero if any check fails.

## Local setup

### Prerequisites

Before setting up the project locally, install:

- PHP 8.2 or later
- MySQL
- Git
- Visual Studio Code
- A local development environment such as XAMPP, Laragon, or PHP’s built-in server

### Steps

1. Clone the repository:

```bash
git clone [https://github.com/LC-Studio-18/Digital-attendance-tracking-system-php.git](https://github.com/LC-Studio-18/Digital-attendance-tracking-system-php.git)
```

2. Move into the project folder:

```bash
cd Digital-attendance-tracking-system-php
```

3. Switch to the team branch:

```bash
git checkout team1
```

4. Pull the latest changes:

```bash
git pull origin team1
```

5. Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

6. Open `.env` and add your local MySQL database details.

7. Create `config/credentials.php` locally using the required database credentials.

8. Start the PHP development server:

```bash
php -S localhost:8000 -t frontend
```

9. Open the application in your browser:

```text
http://localhost:8000
```

## Environment variables

Do not commit the `.env` file or real credentials.

`.env.example` contains placeholders only:

```env
APP_ENV=production
APP_DEBUG=false
BASE_URL=/

DB_HOST=localhost
DB_NAME=attendance_db
DB_USER=your_mysql_username
DB_PASSWORD=your_mysql_password
```

## Xneelo deployment

The application will be deployed to Xneelo. Deployment setup will include:

- Uploading the project files to Xneelo
- Configuring the web root
- Creating or connecting to the MySQL database
- Adding database credentials outside the publicly accessible web root
- Testing the application through the Xneelo test URL
- Confirming that sensitive configuration files cannot be accessed through the browser

## Current status

The project is currently in the initial setup phase. The folder structure and backend environment are being established before authentication, staff management, attendance tracking, and dashboard features are developed.

## Future work

- Create the MySQL database and attendance tables
- Implement user authentication
- Build the login page and shared page layout
- Implement staff management
- Implement attendance check-in and check-out
- Build the live attendance dashboard
- Add duplicate-entry prevention
- Add error handling and monitoring
- Configure automated MySQL backups
- Deploy the completed application to Xneelo

## Team workflow

- Work should be done on the agreed team branch.
- Do not commit real credentials, database dumps, or environment files.
- Create a pull request for review before merging changes.
- Raise blockers early so dependent tickets are not delayed.
- Keep ticket status and blockers updated in the project tracker.