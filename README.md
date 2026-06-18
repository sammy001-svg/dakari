# Dakari — Corporate E-Commerce Platform

A full-featured, corporate-grade e-commerce platform built with PHP and MySQL. Dakari is designed for premium retail brands that need a polished storefront, a powerful admin backend, and a seamless customer experience.

---

## Features

### Storefront
- **Hero Carousel** — Full-width image slider with custom slides managed from the admin panel
- **Product Catalogue** — Category browsing, search, filters (price, stock, rating), and sort
- **Product Pages** — Image gallery, tabs (description, shipping, reviews), related products, star ratings
- **Shopping Cart** — Session-based cart with quantity management, coupon application
- **Checkout** — Full checkout flow with address capture and order placement
- **Wishlist** — Save products for later, accessible from the customer portal
- **Customer Portal** — Account dashboard, order history, order detail, profile management

### Admin Panel
- **Dashboard** — Key metrics: revenue, orders, customers, products; recent activity
- **Product Management** — Add/edit/delete products, image uploads, stock levels, categories
- **Category Management** — Full CRUD with slug generation
- **Order Management** — View, filter, and update order statuses
- **Inventory Management** — Real-time stock tracking, low-stock alerts, bulk updates, full stock history log
- **Review Moderation** — Approve, reject, and bulk-manage customer reviews; auto-approve toggle
- **Coupon / Discount System** — Percentage and fixed-amount codes, usage limits, date ranges, minimum spend
- **Contact Messages** — Inbox for customer enquiries submitted via the contact form; mark read/replied, internal notes
- **Influencer / Ambassador Pages** — Showcase brand ambassadors with bio, social links, follower count
- **Carousel Management** — Upload and order hero slider images
- **User Management** — Admin and customer user accounts
- **Site Settings** — Site name, contact details, social links, review preferences

### Corporate Pages
- **Homepage** — Carousel, trust strip, category grid, featured products, new arrivals, Why Dakari section, brand ambassadors, newsletter
- **About Us** — Company story, mission & vision, core values, milestone timeline, CTA
- **Contact Us** — Contact form (saved to DB), store info, social links, map placeholder
- **FAQ** — Accordion FAQ with 5 categories and 22+ questions, sticky sidebar navigation

---

## Tech Stack

| Layer       | Technology                        |
|-------------|-----------------------------------|
| Backend     | PHP 8.2                           |
| Database    | MySQL / MariaDB 10.4+             |
| Frontend    | Vanilla HTML5, CSS3, JavaScript   |
| Typography  | Google Fonts — Playfair Display + Inter |
| Server      | Apache (via XAMPP)                |
| Auth        | PHP sessions + bcrypt passwords   |
| CSRF        | Token-based protection on all forms |

**Design system:** Dark Green `#1B4332` · Gold `#C9A84C` · White `#FFFFFF`. No gradients used anywhere.

---

## Requirements

- PHP 8.0 or higher
- MySQL 5.7+ / MariaDB 10.4+
- Apache with `mod_rewrite` enabled (included in XAMPP)

---

## Installation

### 1 — Clone the repository

```bash
git clone https://github.com/sammy001-svg/dakari.git
cd dakari
```

### 2 — Set up the database

1. Create a database named `dakari_db` (or any name you prefer):
   ```sql
   CREATE DATABASE dakari_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Import the main schema:
   ```bash
   mysql -u root -p dakari_db < database/schema.sql
   ```

3. Run the feature migrations in order:
   ```bash
   mysql -u root -p dakari_db < database/reviews_migration.sql
   mysql -u root -p dakari_db < database/inventory_migration.sql
   mysql -u root -p dakari_db < database/coupons_migration.sql
   mysql -u root -p dakari_db < database/contact_migration.sql
   ```

### 3 — Configure the database connection

```bash
cp config/database.example.php config/database.php
```

Edit `config/database.php` and fill in your credentials:

```php
define('DB_HOST',  'localhost');
define('DB_USER',  'root');
define('DB_PASS',  '');
define('DB_NAME',  'dakari_db');
```

### 4 — Set up file permissions (Linux/Mac)

```bash
chmod -R 755 uploads/
```

### 5 — Configure the web server

**XAMPP (Windows):** Place the project folder inside `C:\xampp\htdocs\` and visit `http://localhost/dakari/`.

**Apache vhost (Linux/Mac):**
```apache
<VirtualHost *:80>
    ServerName dakari.local
    DocumentRoot /var/www/html/dakari
    <Directory /var/www/html/dakari>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 6 — Generate an admin password hash

The default admin password in `database/schema.sql` was hashed on a specific machine. Regenerate it for your environment:

```bash
php -r "echo password_hash('Admin@1234', PASSWORD_BCRYPT, ['cost'=>12]);"
```

Then update it in the database:
```sql
UPDATE users SET password = '<your-new-hash>' WHERE email = 'admin@dakari.com';
```

---

## Default Admin Credentials

| Field    | Value                |
|----------|----------------------|
| URL      | `/admin/login.php`   |
| Email    | `admin@dakari.com`   |
| Password | `Admin@1234`         |

> **Important:** Change the admin password immediately after first login.

---

## Project Structure

```
dakari/
├── admin/                  # Admin panel pages
│   ├── includes/           # admin_header, admin_footer, admin_init
│   ├── index.php           # Dashboard
│   ├── products.php        # Product management
│   ├── orders.php          # Order management
│   ├── inventory.php       # Stock management
│   ├── reviews.php         # Review moderation
│   ├── coupons.php         # Discount codes
│   ├── messages.php        # Contact message inbox
│   └── settings.php        # Site settings
├── api/                    # AJAX endpoints
│   ├── cart.php
│   ├── coupon.php
│   ├── search.php
│   └── wishlist.php
├── assets/
│   ├── css/
│   │   ├── style.css       # Main storefront stylesheet
│   │   ├── admin.css       # Admin panel stylesheet
│   │   └── client.css      # Customer portal stylesheet
│   ├── js/
│   │   ├── main.js         # Storefront JS (carousel, cart, search)
│   │   └── admin.js        # Admin JS
│   └── images/
├── client/                 # Customer portal
│   ├── dashboard.php
│   ├── orders.php
│   ├── order-detail.php
│   ├── wishlist.php
│   └── profile.php
├── config/
│   ├── database.example.php  # Template — copy to database.php
│   └── database.php          # Your local credentials (gitignored)
├── database/
│   ├── schema.sql            # Main schema + seed data
│   ├── reviews_migration.sql
│   ├── inventory_migration.sql
│   ├── coupons_migration.sql
│   └── contact_migration.sql
├── includes/
│   ├── init.php            # Bootstrap (DB, session, functions)
│   ├── functions.php       # All helper functions
│   ├── header.php          # Site header + navigation
│   ├── footer.php          # Site footer
│   └── auth.php            # Auth helpers
├── uploads/                # User-uploaded files (gitignored except .gitkeep)
├── about.php
├── cart.php
├── checkout.php
├── contact.php
├── faq.php
├── index.php
├── login.php
├── logout.php
├── product.php
├── register.php
├── shop.php
└── .htaccess
```

---

## Key Modules

### Inventory Management
Every stock change is recorded in `stock_logs` (restock, sale, adjustment, return, damage). Low-stock thresholds are configurable per product. The admin inventory page shows real-time stock status, bulk-update tools, and a per-product history log.

### Reviews & Ratings
Customers can leave star ratings and written reviews. Admins moderate from `admin/reviews.php`. A denormalized `avg_rating` and `review_count` column on the products table is updated synchronously whenever a review is approved or removed — no expensive joins on product listing pages.

### Coupon System
Supports percentage and fixed-amount codes, per-user and global usage limits, minimum order values, and active date ranges. Applied at checkout and validated via AJAX on the cart page.

### Contact & Support
Contact form submissions are saved to `contact_messages` and appear in the admin inbox. Messages are auto-marked as "read" when an admin views them. Admins can add internal notes and mark messages as "replied".

---

## Security

- All forms use CSRF tokens
- Passwords hashed with `bcrypt` (cost 12)
- SQL queries use prepared statements throughout
- `config/database.php` is excluded from version control
- Admin routes check session role before rendering
- File uploads validated by MIME type and extension

---

## License

MIT — free to use and modify for personal or commercial projects.
