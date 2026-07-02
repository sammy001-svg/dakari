<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($admin_page_title ?? 'Admin') ?> — Dakari Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/jpeg" href="<?= BASE_URL ?>/assets/images/favicon.jpg">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/images/favicon.jpg">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
    <style><?= brand_css_vars() ?></style>
</head>
<body>

<!-- Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <a href="<?= BASE_URL ?>/admin/index.php" class="sidebar-brand__logo-link">
            <div class="sidebar-logo-wrap">
                <div class="sidebar-logo-crop">
                    <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="Dakari" class="sidebar-logo-img">
                </div>
            </div>
        </a>
        <span class="sidebar-brand__sub">Admin Panel</span>
    </div>
    <nav class="sidebar-nav">
        <div class="sidebar-section">Main</div>
        <a href="<?= BASE_URL ?>/admin/index.php" class="sidebar-link <?= ($admin_active ?? '') === 'dashboard' ? 'active' : '' ?>">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>

        <div class="sidebar-section">Catalogue</div>
        <a href="<?= BASE_URL ?>/admin/products.php" class="sidebar-link <?= ($admin_active ?? '') === 'products' ? 'active' : '' ?>">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
            Products
        </a>
        <a href="<?= BASE_URL ?>/admin/categories.php" class="sidebar-link <?= ($admin_active ?? '') === 'categories' ? 'active' : '' ?>">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            Categories
        </a>

        <div class="sidebar-section">Sales</div>
        <a href="<?= BASE_URL ?>/admin/orders.php" class="sidebar-link <?= ($admin_active ?? '') === 'orders' ? 'active' : '' ?>">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg>
            Orders
        </a>
        <a href="<?= BASE_URL ?>/admin/inventory.php" class="sidebar-link <?= ($admin_active ?? '') === 'inventory' ? 'active' : '' ?>">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
            Inventory
        </a>

        <div class="sidebar-section">Content</div>
        <a href="<?= BASE_URL ?>/admin/services.php" class="sidebar-link <?= ($admin_active ?? '') === 'services' ? 'active' : '' ?>">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
            Services
        </a>
        <a href="<?= BASE_URL ?>/admin/carousel.php" class="sidebar-link <?= ($admin_active ?? '') === 'carousel' ? 'active' : '' ?>">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="10" rx="2"/><path d="M7 2l-5 5m5 10l-5 5M17 2l5 5m-5 10 5 5"/></svg>
            Carousel
        </a>
        <a href="<?= BASE_URL ?>/admin/faqs.php" class="sidebar-link <?= ($admin_active ?? '') === 'faqs' ? 'active' : '' ?>">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            FAQs
        </a>
        <a href="<?= BASE_URL ?>/admin/about.php" class="sidebar-link <?= ($admin_active ?? '') === 'about' ? 'active' : '' ?>">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            About Page
        </a>
        <a href="<?= BASE_URL ?>/admin/why.php" class="sidebar-link <?= ($admin_active ?? '') === 'why' ? 'active' : '' ?>">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            Why Dakari
        </a>
        <a href="<?= BASE_URL ?>/admin/footer.php" class="sidebar-link <?= ($admin_active ?? '') === 'footer' ? 'active' : '' ?>">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
            Footer
        </a>

        <div class="sidebar-section">Marketing</div>
        <a href="<?= BASE_URL ?>/admin/reviews.php" class="sidebar-link <?= ($admin_active ?? '') === 'reviews' ? 'active' : '' ?>" style="position:relative">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Reviews
        </a>
        <a href="<?= BASE_URL ?>/admin/coupons.php" class="sidebar-link <?= ($admin_active ?? '') === 'coupons' ? 'active' : '' ?>">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            Coupons
        </a>
        <?php
        $unread_msgs = (int)(fetchOne("SELECT COUNT(*) n FROM contact_messages WHERE status='new'")['n'] ?? 0);
        ?>
        <a href="<?= BASE_URL ?>/admin/messages.php" class="sidebar-link <?= ($admin_active ?? '') === 'messages' ? 'active' : '' ?>">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Messages<?php if ($unread_msgs > 0): ?><span class="sidebar-badge"><?= $unread_msgs ?></span><?php endif; ?>
        </a>

        <div class="sidebar-section">System</div>
        <a href="<?= BASE_URL ?>/admin/users.php" class="sidebar-link <?= ($admin_active ?? '') === 'users' ? 'active' : '' ?>">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Users
        </a>
        <a href="<?= BASE_URL ?>/admin/settings.php" class="sidebar-link <?= ($admin_active ?? '') === 'settings' ? 'active' : '' ?>">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            Settings
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>/" target="_blank">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
            View Store
        </a>
    </div>
</aside>

<!-- Main -->
<div class="admin-main">
<header class="admin-header">
    <div class="admin-header__left">
        <h1 class="admin-header__title"><?= e($admin_page_title ?? 'Dashboard') ?></h1>
    </div>
    <div class="admin-header__right">
        <div class="admin-user">
            <div class="admin-avatar"><?= strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)) ?></div>
            <span><?= e($_SESSION['user_name'] ?? 'Admin') ?></span>
        </div>
        <a href="<?= BASE_URL ?>/logout.php">Logout</a>
    </div>
</header>
<div class="admin-content">
<?php if (!empty($_SESSION['flash'])): ?>
    <?php foreach ($_SESSION['flash'] as $f):
        $cls = $f['type'] === 'success' ? 'alert-success' : ($f['type'] === 'error' ? 'alert-error' : 'alert-info'); ?>
    <div class="alert <?= $cls ?>"><?= e($f['message']) ?></div>
    <?php endforeach; unset($_SESSION['flash']); ?>
<?php endif; ?>
