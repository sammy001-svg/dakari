-- Dakari Corporate E-Commerce Database Schema
-- Engine: MySQL 5.7+
--
-- SHARED HOSTING NOTE:
-- Do NOT run this file as-is on cPanel/shared hosting.
-- Step 1: Create the database manually via cPanel > MySQL Databases.
-- Step 2: Import this file into that database using phpMyAdmin.
-- (The CREATE DATABASE command has been intentionally removed.)

-- --------------------------------------------------------
-- Users table (admins, clients)
-- --------------------------------------------------------
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

-- --------------------------------------------------------
-- Categories
-- --------------------------------------------------------
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

-- --------------------------------------------------------
-- Products
-- --------------------------------------------------------
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

-- --------------------------------------------------------
-- Product images (multiple per product)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_images (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id  INT UNSIGNED NOT NULL,
    image_path  VARCHAR(255) NOT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_pimage_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Carousel slides
-- --------------------------------------------------------
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

-- --------------------------------------------------------
-- Influencers
-- --------------------------------------------------------
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

-- --------------------------------------------------------
-- Orders
-- --------------------------------------------------------
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
    -- Shipping address
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

-- --------------------------------------------------------
-- Order items
-- --------------------------------------------------------
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

-- --------------------------------------------------------
-- Cart (session-based for guests, user-based for logged in)
-- --------------------------------------------------------
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

-- --------------------------------------------------------
-- Wishlist
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS wishlist (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    product_id  INT UNSIGNED NOT NULL,
    added_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_wishlist (user_id, product_id),
    CONSTRAINT fk_wish_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_wish_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Addresses (client address book)
-- --------------------------------------------------------
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

-- --------------------------------------------------------
-- Site settings (key/value)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    setting_key     VARCHAR(100) PRIMARY KEY,
    setting_value   TEXT,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Seed: Default admin user  (password: Admin@1234)
-- --------------------------------------------------------
INSERT INTO users (first_name, last_name, email, password, role)
VALUES ('Site', 'Admin', 'admin@dakari.com',
        '$2y$12$7q5ihwEQgWp4gD7wnHm6/OTjEaJ5elFsArFlr7IJBdkmGAPNtU06K', 'admin');

-- --------------------------------------------------------
-- Seed: Default site settings
-- --------------------------------------------------------
INSERT INTO settings (setting_key, setting_value) VALUES
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
('social_tiktok',       '#');

-- --------------------------------------------------------
-- Seed: Sample categories
-- --------------------------------------------------------
INSERT INTO categories (name, slug, description) VALUES
('New Arrivals',    'new-arrivals',     'The latest additions to our collection'),
('Best Sellers',    'best-sellers',     'Our most popular products'),
('Men',             'men',              'Exclusive collection for men'),
('Women',           'women',            'Exclusive collection for women'),
('Accessories',     'accessories',      'Premium accessories');

-- --------------------------------------------------------
-- Seed: Sample products
-- --------------------------------------------------------
INSERT INTO products (name, slug, description, short_desc, price, sale_price, stock, category_id, is_featured, is_new) VALUES
('Classic Signature Watch',       'classic-signature-watch',      'A timeless piece crafted for those who demand excellence. Premium stainless steel casing with sapphire crystal glass.', 'Premium stainless steel timepiece', 15000.00, 12000.00, 25, 5, 1, 1),
('Executive Leather Belt',        'executive-leather-belt',       'Full-grain leather belt with 24K gold-plated buckle. The definitive accessory for the modern professional.', 'Full-grain leather with gold buckle', 4500.00, NULL, 40, 5, 1, 0),
('Heritage Cologne 100ml',        'heritage-cologne-100ml',       'A bold, sophisticated fragrance blending cedar, oud and amber. Long-lasting 12+ hour wear.', 'Cedar, oud and amber blend fragrance', 8500.00, 7000.00, 60, 3, 0, 1),
('Structured Blazer - Onyx',      'structured-blazer-onyx',       'Italian-cut single-breasted blazer in premium wool blend. Clean lines, sharp silhouette.', 'Premium wool blend Italian-cut blazer', 22000.00, NULL, 15, 3, 1, 0),
('Silk Wrap Dress',               'silk-wrap-dress',              'Luxurious silk wrap dress with adjustable tie waist. Effortlessly elegant for any occasion.', 'Luxurious silk wrap dress', 18500.00, 15000.00, 20, 4, 1, 1),
('Gold Hoop Earrings Set',        'gold-hoop-earrings-set',       'Set of three 18K gold-plated hoops in graduating sizes. Hypoallergenic posts.', '18K gold-plated hoop set of three', 3200.00, NULL, 80, 5, 0, 1),
('Leather Tote Bag - Forest',     'leather-tote-bag-forest',      'Spacious genuine leather tote in deep forest green. Brass hardware, suede interior lining.', 'Genuine leather tote with brass hardware', 12500.00, 10000.00, 18, 4, 1, 0),
('Cashmere Crew Neck Sweater',    'cashmere-crew-neck-sweater',   'Pure Mongolian cashmere in a classic crew neck silhouette. Incredibly soft, ethically sourced.', 'Pure Mongolian cashmere crew neck', 16000.00, NULL, 22, 3, 0, 1);

-- --------------------------------------------------------
-- Seed: Sample carousel slides
-- --------------------------------------------------------
INSERT INTO carousel_slides (title, subtitle, image, link_url, link_text, product_id, sort_order) VALUES
('New Season Collection', 'Discover our latest arrivals — where luxury meets everyday elegance.', 'carousel1.jpg', 'shop.php?category=new-arrivals', 'Explore Now', NULL, 1),
('Exclusive Timepieces',  'The Classic Signature Watch — crafted for those who demand the finest.', 'carousel2.jpg', 'product.php?slug=classic-signature-watch', 'Shop Now', 1, 2),
('Womens Luxury Edit',    'Curated pieces for the modern woman. Silk, leather, gold.', 'carousel3.jpg', 'shop.php?category=women', 'View Collection', NULL, 3);

-- --------------------------------------------------------
-- Seed: Sample influencers
-- --------------------------------------------------------
INSERT INTO influencers (name, title, bio, image, instagram_url, followers_count, is_featured) VALUES
('Amara Osei',      'Fashion & Lifestyle',  'Nairobi-based fashion influencer with a passion for blending African heritage with contemporary style.', 'influencer1.jpg', '#', '245K', 1),
('James Mwangi',    'Menswear & Grooming',  'Helping modern African men dress sharp, smell great, and live well. Based in Nairobi.', 'influencer2.jpg', '#', '180K', 1),
('Sofia Njeri',     'Luxury & Travel',      'Documenting luxury experiences across Africa and beyond. Brand collaborator and style curator.', 'influencer3.jpg', '#', '320K', 1),
('Kevin Otieno',    'Streetwear & Culture', 'Where African street culture meets high fashion. Photographer, stylist, tastemaker.', 'influencer4.jpg', '#', '142K', 0);
