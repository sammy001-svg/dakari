-- ============================================================
-- Dakari: Product Reviews & Ratings Migration
-- ============================================================

CREATE TABLE IF NOT EXISTS product_reviews (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    product_id  INT UNSIGNED    NOT NULL,
    user_id     INT UNSIGNED    NULL,
    guest_name  VARCHAR(100)    NULL,
    guest_email VARCHAR(150)    NULL,
    rating      TINYINT UNSIGNED NOT NULL DEFAULT 5,
    title       VARCHAR(150)    NULL,
    body        TEXT            NULL,
    is_approved TINYINT(1)      NOT NULL DEFAULT 0,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_product  (product_id, is_approved),
    KEY idx_user     (user_id),
    CONSTRAINT fk_review_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_review_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add denormalised rating columns to products for fast card queries
ALTER TABLE products
    ADD COLUMN IF NOT EXISTS avg_rating   DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS review_count INT UNSIGNED NOT NULL DEFAULT 0;
