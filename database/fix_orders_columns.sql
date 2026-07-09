-- =============================================================
-- Dakari — Fix missing orders columns (MariaDB compatible)
-- Run this once in phpMyAdmin. Safe to re-run.
-- =============================================================

-- 1. Payment columns
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS discount        DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER tax,
    ADD COLUMN IF NOT EXISTS payment_method  ENUM('cod','mpesa','card') NOT NULL DEFAULT 'cod' AFTER total,
    ADD COLUMN IF NOT EXISTS payment_status  ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending' AFTER payment_method,
    ADD COLUMN IF NOT EXISTS mpesa_code      VARCHAR(50) DEFAULT NULL AFTER payment_status;

-- 2. Coupon tracking columns
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS coupon_id   INT UNSIGNED DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS coupon_code VARCHAR(50)  DEFAULT NULL;

-- 3. Coupons table
CREATE TABLE IF NOT EXISTS coupons (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code           VARCHAR(50)  NOT NULL UNIQUE,
    description    VARCHAR(250),
    type           ENUM('percentage','fixed','free_shipping') NOT NULL DEFAULT 'fixed',
    value          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    min_order      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    max_uses       INT UNSIGNED  DEFAULT NULL,
    uses_count     INT UNSIGNED  NOT NULL DEFAULT 0,
    per_user_limit INT UNSIGNED  NOT NULL DEFAULT 1,
    starts_at      DATETIME DEFAULT NULL,
    expires_at     DATETIME DEFAULT NULL,
    is_active      TINYINT(1)   NOT NULL DEFAULT 1,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. Coupon uses tracking table
CREATE TABLE IF NOT EXISTS coupon_uses (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT UNSIGNED NOT NULL,
    user_id   INT UNSIGNED DEFAULT NULL,
    order_id  INT UNSIGNED DEFAULT NULL,
    used_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cu_coupon FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    CONSTRAINT fk_cu_user   FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE SET NULL,
    CONSTRAINT fk_cu_order  FOREIGN KEY (order_id)  REFERENCES orders(id)  ON DELETE SET NULL
) ENGINE=InnoDB;

-- 5. FK from orders.coupon_id → coupons
--    MariaDB does not support ADD CONSTRAINT IF NOT EXISTS for FKs,
--    so we drop first (ignore error if it didn't exist), then add.
ALTER TABLE orders DROP FOREIGN KEY IF EXISTS fk_order_coupon;
ALTER TABLE orders
    ADD CONSTRAINT fk_order_coupon
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL;
