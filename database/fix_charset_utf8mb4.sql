-- ============================================================
-- FIX: Convert database from latin1 to utf8mb4
-- Run this ONCE in cPanel > phpMyAdmin > SQL tab
-- (select your database first, then paste and run)
-- ============================================================

-- 1. Set the database default character set
ALTER DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- 2. Convert every table and all its columns in one step per table
ALTER TABLE users           CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE categories      CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE products        CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE product_images  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE product_reviews CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE carousel_slides CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE orders          CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE order_items     CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE cart            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE wishlist        CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE addresses       CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE settings        CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE stock_logs      CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE coupons         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE coupon_uses     CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE contact_messages CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE services        CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Run this only if the influencers table still exists
ALTER TABLE influencers     CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- 3. Verify the result (run this SELECT after the ALTER statements)
-- SELECT table_name, table_collation
-- FROM information_schema.tables
-- WHERE table_schema = DATABASE()
-- ORDER BY table_name;
