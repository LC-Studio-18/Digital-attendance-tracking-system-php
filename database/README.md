# Database Setup (Team 3)

MySQL 8.0.16+ database accessed from PHP through PDO.
All timestamps use South African Standard Time (SAST, Africa/Johannesburg, UTC+2).

## Files

| File | Purpose |
|---|---|
| `database/schema.sql` | Creates `attendance_db` and the `staff`, `attendance` and `audit_logs` tables. Safe to run again. |
| `database/create_app_user.sql` | Creates the limited `attendance_app` MySQL user. |
| `database/seed.sql` | Development test data only. Never run on the live server. |
| `backend/config/database.php` | `getDatabaseConnection()` returns the shared PDO connection. |
| `config/credentials.example.php` | Template for your private `config/credentials.php`. |
| `tests/test_connection.php` | Connectivity test. |

## Requirements

- PHP 8.2 or later with the `pdo_mysql` extension enabled
- MySQL 8.0.16 or later
- Git Bash or another terminal

## Setup steps

Run these once on your own machine. The database lives on your computer, so every developer sets up their own copy.

### 1. Create the database and tables

Open `database/schema.sql` in MySQL Workbench and run the whole script (Ctrl+Shift+Enter), or from a terminal:

```bash
mysql -u root -p < database/schema.sql
```

### 2. Create the application user

Open `database/create_app_user.sql`, replace `CHANGE_ME` with your own password in **both** places, then run it the same way. Pick a password made of letters and numbers.

The user can SELECT, INSERT and UPDATE only. It cannot delete records or change the table structure.

### 3. Optional: load test data

Run `database/seed.sql` once. It adds three test staff accounts (password `Password123!`), five attendance records (one open shift) and one audit log row. Running it twice fails, because staff emails are unique.

### 4. Create your credentials file

```bash
cp config/credentials.example.php config/credentials.php
```

Edit `config/credentials.php` and set:

```php
'host'     => '127.0.0.1',
'port'     => 3306,                  // your MySQL port (3307 if yours runs on 3307)
'database' => 'attendance_db',
'username' => 'attendance_app',
'password' => 'the password from step 2',
'timezone' => 'Africa/Johannesburg',
```

The password here must match the one in step 2 exactly. This file is git-ignored. Never commit it.

### 5. Run the connection test

```bash
php tests/test_connection.php
```

If `php` is not recognised and you use XAMPP:

```bash
/c/xampp/php/php.exe tests/test_connection.php
```

Expected output:

```
[PASS] Connected to MySQL
[PASS] SELECT 1 works
[PASS] PHP timezone is Africa/Johannesburg
[PASS] MySQL session time zone is +02:00 (+02:00)
[PASS] MySQL NOW() matches PHP time (within 5s)
[PASS] Table `staff` exists
[PASS] Table `attendance` exists
[PASS] Table `audit_logs` exists
[PASS] Foreign key `fk_attendance_staff` exists
[PASS] Foreign key `fk_audit_attendance` exists
[PASS] Foreign key `fk_audit_changed_by` exists
[PASS] Prepared statement runs

All checks passed.
```

## Using the connection in your code

```php
require_once __DIR__ . '/../backend/config/database.php';   // adjust the path to your file
$pdo = getDatabaseConnection();

$stmt = $pdo->prepare('SELECT * FROM attendance WHERE staff_id = :staff_id');
$stmt->execute(['staff_id' => $staffId]);   // $staffId comes from the session, never from user input
$rows = $stmt->fetchAll();
```

| Your file is in | Path to use |
|---|---|
| `frontend/` | `__DIR__ . '/../backend/config/database.php'` |
| `backend/controllers/`, `backend/models/` or `backend/includes/` | `__DIR__ . '/../config/database.php'` |

Rules for everyone:

- Use prepared statements with `:name` placeholders. Never put user input directly in a query.
- Take `staff_id` from the PHP session, not from the URL or form.
- Use `NOW()` for times. The connection already runs in SAST.
- Do not open your own PDO connection.
- Ask Team 3 before changing any table.

## Database structure

### staff

| Column | Type | Notes |
|---|---|---|
| `staff_id` | INT UNSIGNED | Primary key, auto increment |
| `employee_number` | VARCHAR(20) | Unique |
| `first_name`, `last_name` | VARCHAR(50) | |
| `email` | VARCHAR(100) | Unique |
| `password_hash` | VARCHAR(255) | Output of `password_hash()` |
| `role` | ENUM('staff','manager','admin') | Default `staff` |
| `is_active` | TINYINT(1) | Default 1 |
| `created_at` | DATETIME | Default current time |

### attendance

| Column | Type | Notes |
|---|---|---|
| `record_id` | INT UNSIGNED | Primary key, auto increment |
| `staff_id` | INT UNSIGNED | Foreign key to `staff` |
| `clock_in` | DATETIME | Required |
| `clock_out` | DATETIME | NULL means the shift is still open (status IN) |
| `created_at`, `updated_at` | DATETIME | Set automatically |

A check constraint makes sure `clock_out` is never earlier than `clock_in`.

### audit_logs

| Column | Type | Notes |
|---|---|---|
| `audit_id` | INT UNSIGNED | Primary key, auto increment |
| `record_id` | INT UNSIGNED | Foreign key to `attendance` |
| `changed_by` | INT UNSIGNED | Foreign key to `staff` (who made the correction) |
| `old_clock_in`, `old_clock_out` | DATETIME | Values before the correction |
| `new_clock_in`, `new_clock_out` | DATETIME | Values after the correction |
| `reason` | VARCHAR(255) | Required |
| `changed_at` | DATETIME | Default current time |

A record counts as corrected when at least one `audit_logs` row exists for it.

### Relationships

```
staff 1 ---< attendance 1 ---< audit_logs >--- 1 staff (changed_by)
```

| Foreign key | Behaviour |
|---|---|
| `attendance.staff_id` to `staff.staff_id` | Cannot delete a staff member who has records |
| `audit_logs.record_id` to `attendance.record_id` | Deleting a record deletes its audit rows |
| `audit_logs.changed_by` to `staff.staff_id` | Cannot delete a staff member who made corrections |

### Indexes

| Index | Used for |
|---|---|
| `attendance (staff_id, clock_in)` | Recent records, today and weekly hours |
| `attendance (staff_id, clock_out)` | Finding the open shift (`clock_out IS NULL`) |
| `audit_logs (record_id)` | Corrected-entry lookup |
| `audit_logs (changed_by)` | Who made corrections |
| UNIQUE `staff.employee_number`, `staff.email` | Login lookup and duplicate prevention |

## Security

- Real credentials live only in `config/credentials.php`, which is git-ignored. They are never committed and never placed inside `frontend/`.
- The application connects as `attendance_app` with SELECT, INSERT and UPDATE only, never as `root`.
- The connection uses real prepared statements (`ATTR_EMULATE_PREPARES = false`) and throws exceptions on errors.
- Connection errors are logged privately. Users only see a generic message.
- Passwords are stored with `password_hash()` and checked with `password_verify()`.

## SAST timezone

South Africa has no daylight saving, so a fixed offset is safe. On every connection, `getDatabaseConnection()`:

1. sets PHP to `Africa/Johannesburg`, and
2. runs `SET time_zone = '+02:00'` in MySQL.

PHP and MySQL therefore agree, and `NOW()` and `CURDATE()` return SAST. `tests/test_connection.php` checks this.

## Running the script on a clean database

`schema.sql` uses `IF NOT EXISTS`, so it can be run more than once. To start completely fresh:

```sql
DROP DATABASE attendance_db;
```

Then run `schema.sql` and `create_app_user.sql` again, and run the connection test.

## Troubleshooting

| Problem | Fix |
|---|---|
| `php: command not found` | Use the full path, for example `/c/xampp/php/php.exe tests/test_connection.php` |
| `Could not open input file: tests/test_connection.php` | You are not in the project root. Check with `pwd`. |
| `Failed opening required ... credentials.php` | Create `config/credentials.php` from the example file |
| `Access denied for user 'attendance_app'` | The password in `credentials.php` does not match MySQL. Run `ALTER USER` on both accounts (below) and update the file. |
| `Unknown database 'attendance_db'` | Run `schema.sql` |
| `could not find driver` | In `php.ini`, remove the `;` before `extension=pdo_mysql` and restart |
| Connection works for some people but not others | Check the `port` in `credentials.php` matches the MySQL server where you created the user (`SELECT @@port;`) |
| Syntax error when pasting commands | Chat apps turn quotes into curly quotes. Type the quotes by hand or copy from a code block. |

Reset the application user's password (run as `root`, same password on both lines):

```sql
ALTER USER 'attendance_app'@'localhost'  IDENTIFIED BY 'your-password';
ALTER USER 'attendance_app'@'127.0.0.1' IDENTIFIED BY 'your-password';
FLUSH PRIVILEGES;
```

## Reference queries

Use prepared statements and take `:staff_id` from the session.

```sql
-- Current status (1 = IN, 0 = OUT)
SELECT EXISTS (SELECT 1 FROM attendance
               WHERE staff_id = :staff_id AND clock_out IS NULL) AS is_clocked_in;

-- Last 10 records with the corrected flag
SELECT r.record_id, r.clock_in, r.clock_out,
       EXISTS (SELECT 1 FROM audit_logs a WHERE a.record_id = r.record_id) AS is_corrected
FROM attendance r
WHERE r.staff_id = :staff_id
ORDER BY r.clock_in DESC
LIMIT 10;
```