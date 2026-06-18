-- ============================================================
-- Dakari: Inventory Management Migration
-- ============================================================

CREATE TABLE IF NOT EXISTS stock_logs (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    product_id      INT UNSIGNED    NOT NULL,
    admin_id        INT UNSIGNED    NULL,
    type            ENUM('restock','sale','adjustment','return','damage') NOT NULL DEFAULT 'adjustment',
    quantity_change INT             NOT NULL,
    quantity_before INT             NOT NULL DEFAULT 0,
    quantity_after  INT             NOT NULL DEFAULT 0,
    note            VARCHAR(255)    NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_product    (product_id),
    KEY idx_created    (created_at),
    CONSTRAINT fk_stocklog_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_stocklog_admin   FOREIGN KEY (admin_id)   REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add low_stock_threshold to products (per-product alert level)
ALTER TABLE products
    ADD COLUMN IF NOT EXISTS low_stock_threshold INT UNSIGNED NOT NULL DEFAULT 5;

-- Settings: global low-stock threshold default
INSERT INTO settings (setting_key, setting_value)
VALUES ('low_stock_threshold', '5')
ON DUPLICATE KEY UPDATE setting_value = setting_value;
