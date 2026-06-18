<?php
$cart_qty   = cart_count();
$categories = get_categories();
$site_name  = setting('site_name', 'Dakari');
$cur_user   = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? $site_name) ?> — <?= e($site_name) ?></title>
    <meta name="description" content="<?= e(setting('site_tagline', 'Premium quality, curated for you.')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/jpeg" href="<?= BASE_URL ?>/assets/images/favicon.jpg">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/images/favicon.jpg">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <?php if (!empty($extra_css)) echo $extra_css; ?>
</head>
<body>

<!-- ── Announcement Bar ─────────────────────────────────────────────────── -->
<div class="announcement-bar">
    <div class="container announcement-bar__inner">
        <div class="announcement-bar__left">
            <span>
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.39 2 2 0 0 1 3.6 1.21h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.77a16 16 0 0 0 6.29 6.29l.97-.97a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7a2 2 0 0 1 1.72 2.03z"/></svg>
                <?= e(setting('site_phone', '+254 700 000 000')) ?>
            </span>
            <span>
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <?= e(setting('site_email', 'hello@dakari.com')) ?>
            </span>
        </div>
        <div class="announcement-bar__center">
            Free shipping on orders over <?= money(5000) ?> &nbsp;·&nbsp; Authentic, Premium Quality
        </div>
        <div class="announcement-bar__right">
            <a href="<?= e(setting('social_instagram', '#')) ?>" aria-label="Instagram">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
            </a>
            <a href="<?= e(setting('social_facebook', '#')) ?>" aria-label="Facebook">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            <a href="<?= e(setting('social_tiktok', '#')) ?>" aria-label="TikTok">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.18 8.18 0 0 0 4.78 1.52V6.76a4.85 4.85 0 0 1-1.01-.07z"/></svg>
            </a>
        </div>
    </div>
</div>

<!-- ── Main Header ──────────────────────────────────────────────────────── -->
<header class="header" id="header">
    <div class="container header__inner">

        <!-- Logo -->
        <a href="<?= BASE_URL ?>/" class="logo">
            <div class="logo__crop">
                <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="<?= e($site_name) ?>" class="logo__img">
            </div>
        </a>

        <!-- Nav -->
        <nav class="nav" id="nav">
            <ul class="nav__list">
                <li><a href="<?= BASE_URL ?>/" class="nav__link <?= ($active_page ?? '') === 'home' ? 'active' : '' ?>">Home</a></li>

                <li class="nav__dropdown">
                    <a href="<?= BASE_URL ?>/shop.php" class="nav__link <?= ($active_page ?? '') === 'shop' ? 'active' : '' ?>">
                        Collections
                        <svg class="nav__chevron" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="nav__mega">
                        <div class="nav__mega-inner">
                            <div class="nav__mega-col">
                                <p class="nav__mega-heading">Browse</p>
                                <ul>
                                    <li><a href="<?= BASE_URL ?>/shop.php">All Products</a></li>
                                    <li><a href="<?= BASE_URL ?>/shop.php?filter=new">New Arrivals</a></li>
                                    <li><a href="<?= BASE_URL ?>/shop.php?filter=sale">On Sale</a></li>
                                    <li><a href="<?= BASE_URL ?>/shop.php?filter=featured">Featured</a></li>
                                </ul>
                            </div>
                            <div class="nav__mega-col">
                                <p class="nav__mega-heading">Categories</p>
                                <ul>
                                    <?php foreach ($categories as $cat): ?>
                                    <li><a href="<?= BASE_URL ?>/shop.php?category=<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="nav__mega-promo">
                                <div class="nav__mega-promo-inner">
                                    <p class="nav__mega-promo-label">Exclusive</p>
                                    <p class="nav__mega-promo-title">New Season<br>Collection</p>
                                    <a href="<?= BASE_URL ?>/shop.php?filter=new" class="btn btn-gold btn-sm">Shop Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                <li><a href="<?= BASE_URL ?>/influencers.php" class="nav__link <?= ($active_page ?? '') === 'influencers' ? 'active' : '' ?>">Influencers</a></li>
                <li><a href="<?= BASE_URL ?>/about.php" class="nav__link <?= ($active_page ?? '') === 'about' ? 'active' : '' ?>">About</a></li>
                <li><a href="<?= BASE_URL ?>/contact.php" class="nav__link <?= ($active_page ?? '') === 'contact' ? 'active' : '' ?>">Contact</a></li>
            </ul>
        </nav>

        <!-- Header actions -->
        <div class="header__actions">
            <button class="header__icon-btn" id="searchToggle" aria-label="Search">
                <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </button>

            <?php if (is_logged_in()): ?>
            <a href="<?= BASE_URL ?>/client/wishlist.php" class="header__icon-btn" aria-label="Wishlist">
                <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            </a>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/cart.php" class="header__icon-btn header__cart" aria-label="Cart">
                <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <?php if ($cart_qty > 0): ?>
                <span class="cart-badge"><?= $cart_qty ?></span>
                <?php endif; ?>
            </a>

            <?php if (is_logged_in()): ?>
            <div class="header__user-menu">
                <button class="header__user-btn" id="userMenuToggle">
                    <div class="header__avatar"><?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?></div>
                    <span class="header__username"><?= e($_SESSION['user_name']) ?></span>
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="user-dropdown" id="userDropdown">
                    <div class="user-dropdown__header">
                        <strong><?= e($_SESSION['user_name']) ?></strong>
                        <span><?= e($_SESSION['user_email'] ?? '') ?></span>
                    </div>
                    <a href="<?= BASE_URL ?>/client/dashboard.php">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        My Account
                    </a>
                    <a href="<?= BASE_URL ?>/client/orders.php">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg>
                        My Orders
                    </a>
                    <a href="<?= BASE_URL ?>/client/wishlist.php">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        Wishlist
                    </a>
                    <?php if (is_admin()): ?>
                    <a href="<?= BASE_URL ?>/admin/index.php" class="admin-link">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        Admin Panel
                    </a>
                    <?php endif; ?>
                    <div class="user-dropdown__divider"></div>
                    <a href="<?= BASE_URL ?>/logout.php" class="logout-link">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                        Logout
                    </a>
                </div>
            </div>
            <?php else: ?>
            <a href="<?= BASE_URL ?>/login.php" class="header__login-btn">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Sign In
            </a>
            <?php endif; ?>

            <button class="hamburger" id="hamburger" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>

    <!-- Search bar -->
    <div class="search-bar" id="searchBar">
        <div class="container">
            <form action="<?= BASE_URL ?>/shop.php" method="get" class="search-form">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;color:var(--text-muted)"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="q" placeholder="Search products, categories…" class="search-input" autocomplete="off" id="searchInput">
                <button type="submit" class="btn btn-gold btn-sm">Search</button>
                <button type="button" id="searchClose" class="btn btn-outline-green btn-sm">Cancel</button>
            </form>
            <div id="searchResults" class="search-results"></div>
        </div>
    </div>
</header>

<!-- Flash messages -->
<div class="flash-container">
    <?php render_flash(); ?>
</div>
