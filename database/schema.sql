-- =====================================================
-- Digital Attendance Tracking System - database schema
-- MySQL 8.0.16+ | InnoDB | utf8mb4
-- All timestamps are stored in SAST (UTC+2, no DST).
-- Safe to re-run: uses IF NOT EXISTS.
-- =====================================================

CREATE DATABASE IF NOT EXISTS attendance_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE attendance_db;

CREATE TABLE IF NOT EXISTS staff (
  staff_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_number VARCHAR(20)  NOT NULL,
  first_name      VARCHAR(50)  NOT NULL,
  last_name       VARCHAR(50)  NOT NULL,
  email           VARCHAR(100) NOT NULL,
  password_hash   VARCHAR(255) NOT NULL,
  role            ENUM('staff','manager','admin') NOT NULL DEFAULT 'staff',
  is_active       TINYINT(1)   NOT NULL DEFAULT 1,
  created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uq_staff_employee_number UNIQUE (employee_number),
  CONSTRAINT uq_staff_email           UNIQUE (email)
) ENGINE=InnoDB;

-- clock_out IS NULL means the shift is still open (status = IN)
CREATE TABLE IF NOT EXISTS attendance (
  record_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  staff_id    INT UNSIGNED NOT NULL,
  clock_in    DATETIME     NOT NULL,
  clock_out   DATETIME     NULL,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_attendance_staff
    FOREIGN KEY (staff_id) REFERENCES staff (staff_id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_attendance_clock_order
    CHECK (clock_out IS NULL OR clock_out >= clock_in),
  INDEX idx_attendance_staff_clockin  (staff_id, clock_in),
  INDEX idx_attendance_staff_clockout (staff_id, clock_out)
) ENGINE=InnoDB;

-- A record is "corrected" if at least one audit_logs row exists for it
CREATE TABLE IF NOT EXISTS audit_logs (
  audit_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  record_id      INT UNSIGNED NOT NULL,
  changed_by     INT UNSIGNED NOT NULL,
  old_clock_in   DATETIME NULL,
  old_clock_out  DATETIME NULL,
  new_clock_in   DATETIME NULL,
  new_clock_out  DATETIME NULL,
  reason         VARCHAR(255) NOT NULL,
  changed_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_audit_attendance
    FOREIGN KEY (record_id) REFERENCES attendance (record_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_audit_changed_by
    FOREIGN KEY (changed_by) REFERENCES staff (staff_id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_audit_record     (record_id),
  INDEX idx_audit_changed_by (changed_by)
) ENGINE=InnoDB;