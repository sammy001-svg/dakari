-- =============================================================================
-- Dakari — FULL DATABASE IMPORT FOR cPANEL
-- =============================================================================
-- HOW TO USE:
--   1. Create the database in cPanel > MySQL Databases first.
--   2. In phpMyAdmin, select your database on the left sidebar.
--   3. Click Import > Choose this file > Go.
-- This file safely drops and recreates all tables. Safe to run multiple times.
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Drop all tables in reverse dependency order (children before parents)
DROP TABLE IF EXISTS coupon_uses;
DROP TABLE IF EXISTS stock_logs;
DROP TABLE IF EXISTS product_reviews;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS wishlist;
DROP TABLE IF EXISTS addresses;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS carousel_slides;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS influencers;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS coupons;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS settings;



-- ─────────────────────────────────────────────────────────────────────────────
-- PART 1: schema.sql — Core tables
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name  VARCHAR(80) NOT NULL,
    last_name   VARCHAR(80) NOT NULL,
    email       VARCHAR(180) NOT NULL UNIQUE,
    phone       VARCHAR(30),
    password    VARCHAR(255) NOT NULL,
    role        ENUM('admin','client') NOT NULL DEFAULT 'client',
    avatar      VARCHAR(255),
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL,
    slug        VARCHAR(140) NOT NULL UNIQUE,
    description TEXT,
    image       VARCHAR(255),
    parent_id   INT UNSIGNED DEFAULT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE categories ADD CONSTRAINT fk_category_parent
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS products (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(220) NOT NULL,
    slug            VARCHAR(250) NOT NULL UNIQUE,
    description     TEXT,
    short_desc      VARCHAR(500),
    price           DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    sale_price      DECIMAL(12,2) DEFAULT NULL,
    sku             VARCHAR(80) UNIQUE,
    stock           INT NOT NULL DEFAULT 0,
    category_id     INT UNSIGNED DEFAULT NULL,
    thumbnail       VARCHAR(255),
    is_featured     TINYINT(1) NOT NULL DEFAULT 0,
    is_new          TINYINT(1) NOT NULL DEFAULT 0,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    views           INT UNSIGNED NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS product_images (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id  INT UNSIGNED NOT NULL,
    image_path  VARCHAR(255) NOT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_pimage_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS carousel_slides (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(180),
    subtitle    VARCHAR(300),
    image       VARCHAR(255) NOT NULL,
    link_url    VARCHAR(255),
    link_text   VARCHAR(80) DEFAULT 'Shop Now',
    product_id  INT UNSIGNED DEFAULT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_carousel_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS influencers (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(140) NOT NULL,
    title           VARCHAR(140),
    bio             TEXT,
    image           VARCHAR(255),
    instagram_url   VARCHAR(255),
    tiktok_url      VARCHAR(255),
    youtube_url     VARCHAR(255),
    twitter_url     VARCHAR(255),
    followers_count VARCHAR(30),
    is_featured     TINYINT(1) NOT NULL DEFAULT 0,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    sort_order      INT NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number    VARCHAR(30) NOT NULL UNIQUE,
    user_id         INT UNSIGNED DEFAULT NULL,
    guest_email     VARCHAR(180),
    status          ENUM('pending','processing','shipped','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending',
    subtotal        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    shipping_cost   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tax             DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total           DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    ship_name       VARCHAR(180),
    ship_email      VARCHAR(180),
    ship_phone      VARCHAR(30),
    ship_address    VARCHAR(300),
    ship_city       VARCHAR(100),
    ship_state      VARCHAR(100),
    ship_zip        VARCHAR(20),
    ship_country    VARCHAR(80) DEFAULT 'Kenya',
    notes           TEXT,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id    INT UNSIGNED NOT NULL,
    product_id  INT UNSIGNED DEFAULT NULL,
    product_name VARCHAR(220) NOT NULL,
    price       DECIMAL(12,2) NOT NULL,
    quantity    INT NOT NULL DEFAULT 1,
    subtotal    DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_oi_order   FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    CONSTRAINT fk_oi_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cart (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id  VARCHAR(128),
    user_id     INT UNSIGNED DEFAULT NULL,
    product_id  INT UNSIGNED NOT NULL,
    quantity    INT NOT NULL DEFAULT 1,
    added_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cart_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS wishlist (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    product_id  INT UNSIGNED NOT NULL,
    added_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_wishlist (user_id, product_id),
    CONSTRAINT fk_wish_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_wish_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS addresses (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    label       VARCHAR(60) DEFAULT 'Home',
    address     VARCHAR(300) NOT NULL,
    city        VARCHAR(100) NOT NULL,
    state       VARCHAR(100),
    zip         VARCHAR(20),
    country     VARCHAR(80) DEFAULT 'Kenya',
    is_default  TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_addr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS settings (
    setting_key     VARCHAR(100) PRIMARY KEY,
    setting_value   TEXT,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────────────────────────
-- PART 2: inventory_migration.sql
-- ─────────────────────────────────────────────────────────────────────────────

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

ALTER TABLE products
    ADD COLUMN IF NOT EXISTS low_stock_threshold INT UNSIGNED NOT NULL DEFAULT 5;

-- ─────────────────────────────────────────────────────────────────────────────
-- PART 3: reviews_migration.sql
-- ─────────────────────────────────────────────────────────────────────────────

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

ALTER TABLE products
    ADD COLUMN IF NOT EXISTS avg_rating   DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS review_count INT UNSIGNED NOT NULL DEFAULT 0;

-- ─────────────────────────────────────────────────────────────────────────────
-- PART 4: coupons_migration.sql
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS coupons (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(50) NOT NULL UNIQUE,
    description     VARCHAR(250),
    type            ENUM('percentage','fixed','free_shipping') NOT NULL DEFAULT 'fixed',
    value           DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    min_order       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    max_uses        INT UNSIGNED DEFAULT NULL,
    uses_count      INT UNSIGNED NOT NULL DEFAULT 0,
    per_user_limit  INT UNSIGNED NOT NULL DEFAULT 1,
    starts_at       DATETIME DEFAULT NULL,
    expires_at      DATETIME DEFAULT NULL,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

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

ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS coupon_id   INT UNSIGNED DEFAULT NULL AFTER notes,
    ADD COLUMN IF NOT EXISTS coupon_code VARCHAR(50)  DEFAULT NULL AFTER coupon_id,
    ADD COLUMN IF NOT EXISTS discount    DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER coupon_code;

ALTER TABLE orders
    ADD CONSTRAINT fk_order_coupon
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL;


-- ─────────────────────────────────────────────────────────────────────────────
-- PART 5: payment_migration.sql
-- ─────────────────────────────────────────────────────────────────────────────

ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS payment_method ENUM('cod','mpesa','card') NOT NULL DEFAULT 'cod' AFTER discount,
    ADD COLUMN IF NOT EXISTS payment_status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending' AFTER payment_method,
    ADD COLUMN IF NOT EXISTS mpesa_code VARCHAR(50) NULL AFTER payment_status;

-- ─────────────────────────────────────────────────────────────────────────────
-- PART 6: contact_migration.sql
-- ─────────────────────────────────────────────────────────────────────────────

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

-- ─────────────────────────────────────────────────────────────────────────────
-- PART 7: services_migration.sql
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS services (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150)  NOT NULL,
    slug        VARCHAR(150)  NOT NULL UNIQUE,
    tagline     VARCHAR(255)  DEFAULT NULL,
    description TEXT          DEFAULT NULL,
    icon        VARCHAR(60)   DEFAULT 'star',
    image       VARCHAR(255)  DEFAULT NULL,
    features    TEXT          DEFAULT NULL,
    price_label VARCHAR(80)   DEFAULT NULL,
    cta_text    VARCHAR(80)   DEFAULT 'Learn More',
    cta_url     VARCHAR(255)  DEFAULT NULL,
    is_featured TINYINT(1)    NOT NULL DEFAULT 0,
    sort_order  INT UNSIGNED  NOT NULL DEFAULT 0,
    status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- PART 8: Seed Data
-- ─────────────────────────────────────────────────────────────────────────────

-- Default admin user (password: Admin@1234)
INSERT IGNORE INTO users (first_name, last_name, email, password, role)
VALUES ('Site', 'Admin', 'admin@dakari.com',
        '$2y$12$a2.lI5StqJdSxrteDHH5CeIKANkyn0EKbN7QvS.q5F/LVsSPRkhyW', 'admin');

-- Default site settings
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('site_name',           'Dakari'),
('site_tagline',        'Premium Products, Exclusive Style'),
('site_email',          'info@dakari.com'),
('site_phone',          '+254 700 000 000'),
('site_address',        'Nairobi, Kenya'),
('currency_symbol',     'KSh'),
('currency_code',       'KES'),
('shipping_cost',       '250'),
('tax_rate',            '0'),
('maintenance_mode',    '0'),
('footer_about',        'Dakari is your premier destination for exclusive, high-quality products. We combine luxury with accessibility.'),
('social_instagram',    '#'),
('social_facebook',     '#'),
('social_twitter',      '#'),
('social_tiktok',       '#'),
('low_stock_threshold', '5'),
('mpesa_paybill',       '174379');

-- Sample categories
INSERT IGNORE INTO categories (name, slug, description) VALUES
('New Arrivals',    'new-arrivals',     'The latest additions to our collection'),
('Best Sellers',    'best-sellers',     'Our most popular products'),
('Men',             'men',              'Exclusive collection for men'),
('Women',           'women',            'Exclusive collection for women'),
('Accessories',     'accessories',      'Premium accessories');

-- Sample products
INSERT IGNORE INTO products (name, slug, description, short_desc, price, sale_price, stock, category_id, is_featured, is_new) VALUES
('Classic Signature Watch',       'classic-signature-watch',      'A timeless piece crafted for those who demand excellence. Premium stainless steel casing with sapphire crystal glass.', 'Premium stainless steel timepiece', 15000.00, 12000.00, 25, 5, 1, 1),
('Executive Leather Belt',        'executive-leather-belt',       'Full-grain leather belt with 24K gold-plated buckle. The definitive accessory for the modern professional.', 'Full-grain leather with gold buckle', 4500.00, NULL, 40, 5, 1, 0),
('Heritage Cologne 100ml',        'heritage-cologne-100ml',       'A bold, sophisticated fragrance blending cedar, oud and amber. Long-lasting 12+ hour wear.', 'Cedar, oud and amber blend fragrance', 8500.00, 7000.00, 60, 3, 0, 1),
('Structured Blazer - Onyx',      'structured-blazer-onyx',       'Italian-cut single-breasted blazer in premium wool blend. Clean lines, sharp silhouette.', 'Premium wool blend Italian-cut blazer', 22000.00, NULL, 15, 3, 1, 0),
('Silk Wrap Dress',               'silk-wrap-dress',              'Luxurious silk wrap dress with adjustable tie waist. Effortlessly elegant for any occasion.', 'Luxurious silk wrap dress', 18500.00, 15000.00, 20, 4, 1, 1),
('Gold Hoop Earrings Set',        'gold-hoop-earrings-set',       'Set of three 18K gold-plated hoops in graduating sizes. Hypoallergenic posts.', '18K gold-plated hoop set of three', 3200.00, NULL, 80, 5, 0, 1),
('Leather Tote Bag - Forest',     'leather-tote-bag-forest',      'Spacious genuine leather tote in deep forest green. Brass hardware, suede interior lining.', 'Genuine leather tote with brass hardware', 12500.00, 10000.00, 18, 4, 1, 0),
('Cashmere Crew Neck Sweater',    'cashmere-crew-neck-sweater',   'Pure Mongolian cashmere in a classic crew neck silhouette. Incredibly soft, ethically sourced.', 'Pure Mongolian cashmere crew neck', 16000.00, NULL, 22, 3, 0, 1);

-- Sample carousel slides
INSERT IGNORE INTO carousel_slides (title, subtitle, image, link_url, link_text, product_id, sort_order) VALUES
('New Season Collection', 'Discover our latest arrivals — where luxury meets everyday elegance.', 'carousel1.jpg', 'shop.php?category=new-arrivals', 'Explore Now', NULL, 1),
('Exclusive Timepieces',  'The Classic Signature Watch — crafted for those who demand the finest.', 'carousel2.jpg', 'product.php?slug=classic-signature-watch', 'Shop Now', 1, 2),
('Womens Luxury Edit',    'Curated pieces for the modern woman. Silk, leather, gold.', 'carousel3.jpg', 'shop.php?category=women', 'View Collection', NULL, 3);

-- Sample influencers
INSERT IGNORE INTO influencers (name, title, bio, image, instagram_url, followers_count, is_featured) VALUES
('Amara Osei',      'Fashion & Lifestyle',  'Nairobi-based fashion influencer with a passion for blending African heritage with contemporary style.', 'influencer1.jpg', '#', '245K', 1),
('James Mwangi',    'Menswear & Grooming',  'Helping modern African men dress sharp, smell great, and live well. Based in Nairobi.', 'influencer2.jpg', '#', '180K', 1),
('Sofia Njeri',     'Luxury & Travel',      'Documenting luxury experiences across Africa and beyond. Brand collaborator and style curator.', 'influencer3.jpg', '#', '320K', 1),
('Kevin Otieno',    'Streetwear & Culture', 'Where African street culture meets high fashion. Photographer, stylist, tastemaker.', 'influencer4.jpg', '#', '142K', 0);

-- Sample coupons
INSERT IGNORE INTO coupons (code, description, type, value, min_order, max_uses, expires_at) VALUES
('WELCOME10',  '10% off your first order',              'percentage',    10.00,    0.00,  NULL,   DATE_ADD(NOW(), INTERVAL 1 YEAR)),
('SAVE500',    'KSh 500 off orders over KSh 5,000',     'fixed',        500.00, 5000.00,   100,   DATE_ADD(NOW(), INTERVAL 6 MONTH)),
('FREESHIP',   'Free shipping on any order',            'free_shipping',  0.00,    0.00,   200,   DATE_ADD(NOW(), INTERVAL 3 MONTH));

-- Services
INSERT IGNORE INTO services (title, slug, tagline, description, icon, features, price_label, cta_text, is_featured, sort_order, status) VALUES
('Personal Shopping',    'personal-shopping',     'Your dedicated style advisor',          'Our personal shopping service pairs you with a dedicated Dakari style advisor who learns your taste, lifestyle, and budget — then hand-picks products from our catalogue just for you.', 'shopping_bag', 'One-on-one style consultation\nCurated product shortlist delivered to your inbox\nPriority access to new arrivals\nFree returns on first order', 'Free with any purchase', 'Book a Consultation', 1, 1, 'active'),
('Corporate Gifting',    'corporate-gifting',     'Premium gifts for teams & clients',     'Make a lasting impression with bespoke corporate gifts. Bulk orders, custom branding, flexible delivery.',                                                                              'briefcase',    'Minimum order 10 units\nCustom branding & packaging available\nDedicated account manager\nBulk pricing discounts from 15%',                    'From KES 1,500 / unit', 'Request a Quote',      1, 2, 'active'),
('Gift Wrapping',        'gift-wrapping',         'Packaging as premium as what is inside','Signature gift wrapping using premium kraft paper, satin ribbon, and handwritten cards.',                                                                                              'gift',         'Signature Dakari kraft wrap\nSatin ribbon in gold or green\nHandwritten personalised card\nSame-day wrapping for in-store orders',              'From KES 250 per item', 'Add to Order',         1, 3, 'active'),
('Express Delivery',     'express-delivery',      'Nairobi same-day, Kenya next-day',      'Same-day delivery in Nairobi, next-day to major towns across Kenya. All parcels tracked in real time.',                                                                               'truck',        'Same-day delivery in Nairobi (order before 10am)\nNext-day to Mombasa, Kisumu, Nakuru & Eldoret\nReal-time SMS tracking',                      'From KES 350',          'Learn More',           1, 4, 'active');

SET FOREIGN_KEY_CHECKS = 1;
