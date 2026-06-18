<?php
require_once __DIR__ . '/includes/client_init.php';
$page_title    = 'My Account';
$client_active = 'dashboard';
$extra_css     = '<link rel="stylesheet" href="' . BASE_URL . '/assets/css/client.css">';
$user          = current_user();

$order_count = (int)(fetchOne('SELECT COUNT(*) n FROM orders WHERE user_id=?','i',$user['id'])['n'] ?? 0);
$total_spent = (float)(fetchOne("SELECT COALESCE(SUM(total),0) n FROM orders WHERE user_id=? AND status NOT IN ('cancelled','refunded')",'i',$user['id'])['n'] ?? 0);
$wish_count  = (int)(fetchOne('SELECT COUNT(*) n FROM wishlist WHERE user_id=?','i',$user['id'])['n'] ?? 0);
$recent      = fetchAll('SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC LIMIT 5','i',$user['id']);

include __DIR__ . '/includes/client_header.php';
?>

<div class="client-page-header">
    <div>
        <h1 class="client-page-title">Welcome back, <?= e($user['first_name']) ?></h1>
        <p class="client-page-sub">Here's a summary of your account activity.</p>
    </div>
    <a href="<?= BASE_URL ?>/shop.php" class="btn btn-green btn-sm">Browse Products</a>
</div>

<div class="stat-mini-grid">
    <div class="stat-mini">
        <div class="stat-mini__icon">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg>
        </div>
        <span class="stat-mini__val"><?= $order_count ?></span>
        <span class="stat-mini__label">Total Orders</span>
    </div>
    <div class="stat-mini">
        <div class="stat-mini__icon">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <span class="stat-mini__val"><?= money($total_spent) ?></span>
        <span class="stat-mini__label">Total Spent</span>
    </div>
    <div class="stat-mini">
        <div class="stat-mini__icon">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </div>
        <span class="stat-mini__val"><?= $wish_count ?></span>
        <span class="stat-mini__label">Wishlist Items</span>
    </div>
</div>

<div class="c-card">
    <div class="c-card__header">
        <span class="c-card__title">Recent Orders</span>
        <a href="<?= BASE_URL ?>/client/orders.php" class="btn-outline btn-sm">View All</a>
    </div>
    <?php if (!empty($recent)): ?>
    <div class="c-table-wrap">
        <table class="c-table">
            <thead>
                <tr><th>Order #</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($recent as $o):
                $qty = (int)(fetchOne('SELECT COALESCE(SUM(quantity),0) n FROM order_items WHERE order_id=?','i',$o['id'])['n'] ?? 0);
            ?>
            <tr>
                <td><strong><?= e($o['order_number']) ?></strong></td>
                <td style="color:var(--text-muted);font-size:.84rem"><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
                <td><?= $qty ?> item<?= $qty !== 1 ? 's' : '' ?></td>
                <td><strong><?= money((float)$o['total']) ?></strong></td>
                <td><span class="status-badge status-<?= e($o['status']) ?>"><?= ucfirst(e($o['status'])) ?></span></td>
                <td><a href="<?= BASE_URL ?>/client/order-detail.php?id=<?= $o['id'] ?>" class="btn-outline btn-sm">View</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg>
        <h3>No orders yet</h3>
        <p>Your order history will appear here once you've made a purchase.</p>
        <a href="<?= BASE_URL ?>/shop.php" class="btn btn-green btn-sm" style="margin-top:4px">Start Shopping</a>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/client_footer.php'; ?>
