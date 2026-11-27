-- S2-09B: store total session duration when a clock-in session is closed.
-- Run once, as an admin user (the app user has no ALTER permission), after schema.sql.
USE attendance_db;

ALTER TABLE attendance
  ADD COLUMN duration_seconds INT UNSIGNED NULL AFTER clock_out;
