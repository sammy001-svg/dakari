-- Add payment columns to orders table
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS payment_method ENUM('cod','mpesa','card') NOT NULL DEFAULT 'cod' AFTER discount,
    ADD COLUMN IF NOT EXISTS payment_status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending' AFTER payment_method,
    ADD COLUMN IF NOT EXISTS mpesa_code VARCHAR(50) NULL AFTER payment_status;

-- Add M-Pesa paybill number to settings (update to your real number)
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('mpesa_paybill', '174379');
