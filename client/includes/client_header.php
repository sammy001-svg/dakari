<?php
$cur_user = current_user();
include ROOT_PATH . '/includes/header.php';
?>
<div class="container">
    <div class="client-layout">
        <!-- Sidebar nav -->
        <aside class="client-sidebar">
            <nav class="client-nav">
                <div class="client-nav__user">
                    <div class="client-nav__avatar"><?= strtoupper(substr($cur_user['first_name'] ?? 'U', 0, 1)) ?></div>
                    <p class="client-nav__name"><?= e($cur_user['first_name'] . ' ' . $cur_user['last_name']) ?></p>
                    <p class="client-nav__email"><?= e($cur_user['email']) ?></p>
                </div>
                <div class="client-nav__links">
                    <a href="<?= BASE_URL ?>/client/dashboard.php" class="client-nav__link <?= ($client_active??'')==='dashboard'?'active':'' ?>">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        Dashboard
                    </a>
                    <a href="<?= BASE_URL ?>/client/orders.php" class="client-nav__link <?= ($client_active??'')==='orders'?'active':'' ?>">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg>
                        My Orders
                    </a>
                    <a href="<?= BASE_URL ?>/client/wishlist.php" class="client-nav__link <?= ($client_active??'')==='wishlist'?'active':'' ?>">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        Wishlist
                    </a>
                    <a href="<?= BASE_URL ?>/client/profile.php" class="client-nav__link <?= ($client_active??'')==='profile'?'active':'' ?>">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        My Profile
                    </a>
                    <a href="<?= BASE_URL ?>/logout.php" class="client-nav__link" style="color:#c0392b">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Logout
                    </a>
                </div>
            </nav>
        </aside>
        <div class="client-content">
