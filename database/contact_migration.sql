-- Contact Messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL,
    phone       VARCHAR(30)   DEFAULT NULL,
    subject     VARCHAR(150)  DEFAULT NULL,
    category    VARCHAR(60)   DEFAULT NULL,
    message     TEXT          NOT NULL,
    status      ENUM('new','read','replied') NOT NULL DEFAULT 'new',
    admin_note  TEXT          DEFAULT NULL,
    ip_address  VARCHAR(45)   DEFAULT NULL,
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
