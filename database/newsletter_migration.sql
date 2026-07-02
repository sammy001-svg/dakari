-- ============================================================
-- Newsletter subscribers table
-- Run once in cPanel > phpMyAdmin > SQL tab
-- ============================================================

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(120)  DEFAULT NULL,
    email        VARCHAR(191)  NOT NULL,
    ip_address   VARCHAR(45)   DEFAULT NULL,
    subscribed_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
