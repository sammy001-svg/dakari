-- Dakari — Coupons Phase Migration
-- Import this into your cPanel database via phpMyAdmin


-- --------------------------------------------------------
-- Coupons table
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS coupons (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(50) NOT NULL UNIQUE,
    description     VARCHAR(250),
    type            ENUM('percentage','fixed','free_shipping') NOT NULL DEFAULT 'fixed',
    value           DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    min_order       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    max_uses        INT UNSIGNED DEFAULT NULL,          -- NULL = unlimited
    uses_count      INT UNSIGNED NOT NULL DEFAULT 0,
    per_user_limit  INT UNSIGNED NOT NULL DEFAULT 1,    -- times one user can use it
    starts_at       DATETIME DEFAULT NULL,
    expires_at      DATETIME DEFAULT NULL,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Track which user/order each coupon use belongs to
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS coupon_uses (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coupon_id   INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED DEFAULT NULL,
    order_id    INT UNSIGNED DEFAULT NULL,
    used_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cu_coupon FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    CONSTRAINT fk_cu_user   FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE SET NULL,
    CONSTRAINT fk_cu_order  FOREIGN KEY (order_id)  REFERENCES orders(id)  ON DELETE SET NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Add discount columns to orders
-- --------------------------------------------------------
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS coupon_id   INT UNSIGNED DEFAULT NULL AFTER notes,
    ADD COLUMN IF NOT EXISTS coupon_code VARCHAR(50)  DEFAULT NULL AFTER coupon_id,
    ADD COLUMN IF NOT EXISTS discount    DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER coupon_code;

ALTER TABLE orders
    ADD CONSTRAINT IF NOT EXISTS fk_order_coupon
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL;

-- --------------------------------------------------------
-- Seed: Sample coupons for testing
-- --------------------------------------------------------
INSERT IGNORE INTO coupons (code, description, type, value, min_order, max_uses, expires_at) VALUES
('WELCOME10',  '10% off your first order',              'percentage',    10.00,    0.00,  NULL,   DATE_ADD(NOW(), INTERVAL 1 YEAR)),
('SAVE500',    'KSh 500 off orders over KSh 5,000',     'fixed',        500.00, 5000.00,   100,   DATE_ADD(NOW(), INTERVAL 6 MONTH)),
('FREESHIP',   'Free shipping on any order',            'free_shipping',  0.00,    0.00,   200,   DATE_ADD(NOW(), INTERVAL 3 MONTH)),
('DAKARI20',   '20% off sitewide — limited time',       'percentage',    20.00, 2000.00,    50,   DATE_ADD(NOW(), INTERVAL 1 MONTH)),
('VIP1000',    'VIP exclusive: KSh 1,000 off',          'fixed',       1000.00,10000.00,    20,   DATE_ADD(NOW(), INTERVAL 2 MONTH));
