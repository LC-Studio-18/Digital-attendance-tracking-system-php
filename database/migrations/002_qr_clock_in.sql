USE attendance_db;

CREATE TABLE IF NOT EXISTS qr_tokens (
qr_token_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
token          CHAR(64)     NOT NULL,
site_id        INT UNSIGNED NOT NULL,
qr_code_id     INT UNSIGNED NOT NULL,
is_active      TINYINT(1)   NOT NULL DEFAULT 1,
expires_at_utc DATETIME     NOT NULL,
created_at_utc DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
CONSTRAINT uq_qr_tokens_token UNIQUE (token),
INDEX idx_qr_tokens_lookup (token, is_active, expires_at_utc),
INDEX idx_qr_tokens_site   (site_id, qr_code_id)
) ENGINE=InnoDB;

ALTER TABLE attendance
ADD COLUMN site_id    INT UNSIGNED NULL AFTER staff_id,
ADD COLUMN qr_code_id INT UNSIGNED NULL AFTER site_id,
ADD COLUMN method     VARCHAR(20)  NOT NULL DEFAULT 'MANUAL' AFTER qr_code_id,
ADD INDEX idx_attendance_qr_lookup (staff_id, clock_out, method);
