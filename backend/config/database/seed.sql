-- =====================================================
-- DEVELOPMENT TEST DATA ONLY - never load this on the live server.
-- Run ONCE (staff emails are unique). All test passwords: Password123!
-- =====================================================
USE attendance_db;

INSERT INTO staff (employee_number, first_name, last_name, email, password_hash, role) VALUES
  ('EMP001', 'Test',  'Staff',   'staff1@example.com',  '$2y$10$tl2J5rrrUe7I3PnTxpjXB.O7FWV6XmY1F5NICRtSsUS/8KqDBP/v.', 'staff'),
  ('EMP002', 'Other', 'Staff',   'staff2@example.com',  '$2y$10$tl2J5rrrUe7I3PnTxpjXB.O7FWV6XmY1F5NICRtSsUS/8KqDBP/v.', 'staff'),
  ('EMP900', 'Test',  'Manager', 'manager@example.com', '$2y$10$tl2J5rrrUe7I3PnTxpjXB.O7FWV6XmY1F5NICRtSsUS/8KqDBP/v.', 'manager');

INSERT INTO attendance (staff_id, clock_in, clock_out) VALUES
  (1, DATE_SUB(CURDATE(), INTERVAL 3 DAY) + INTERVAL 8 HOUR, DATE_SUB(CURDATE(), INTERVAL 3 DAY) + INTERVAL 16 HOUR),
  (1, DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 8 HOUR, DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 17 HOUR),
  (1, DATE_SUB(CURDATE(), INTERVAL 1 DAY) + INTERVAL 9 HOUR, DATE_SUB(CURDATE(), INTERVAL 1 DAY) + INTERVAL 17 HOUR),
  (1, CURDATE() + INTERVAL 8 HOUR, NULL),
  (2, DATE_SUB(CURDATE(), INTERVAL 1 DAY) + INTERVAL 8 HOUR, DATE_SUB(CURDATE(), INTERVAL 1 DAY) + INTERVAL 16 HOUR);

INSERT INTO audit_logs (record_id, changed_by, old_clock_out, new_clock_out, reason) VALUES
  (2, 3,
   DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 16 HOUR,
   DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 17 HOUR,
   'Employee forgot to clock out on time');