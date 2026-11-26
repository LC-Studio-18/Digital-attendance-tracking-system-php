-- Creates the limited MySQL user the PHP app connects with (never use root).
-- Replace CHANGE_ME with a strong private password BEFORE running,
-- and put the same password in config/credentials.php.
-- Do not commit a real password to this file.
CREATE USER IF NOT EXISTS 'attendance_app'@'localhost'  IDENTIFIED BY 'CHANGE_ME';
CREATE USER IF NOT EXISTS 'attendance_app'@'127.0.0.1' IDENTIFIED BY 'CHANGE_ME';
GRANT SELECT, INSERT, UPDATE ON attendance_db.* TO 'attendance_app'@'localhost';
GRANT SELECT, INSERT, UPDATE ON attendance_db.* TO 'attendance_app'@'127.0.0.1';
FLUSH PRIVILEGES;