<?php
require_once __DIR__ . '/includes/client_init.php';
$page_title    = 'My Account';
$client_active = 'dashboard';
$extra_css     = '<link rel="stylesheet" href="' . BASE_URL . '/assets/css/client.css">';
$user          = current_user();

$order_count   = fetchOne('SELECT COUNT(*) as n FROM orders WHERE user_id=?','i',$user['id'])['n'] ?? 0;
$total_spent   = fetchOne('SELECT SUM(total) as n FROM orders WHERE user_id=? AND status NOT IN ("cancelled","refunded")','i',$user['id'])['n'] ?? 0;
$wish_count    = fetchOne('SELECT COUNT(*) as n FROM wishlist WHERE user_id=?','i',$user['id'])['n'] ?? 0;
$recent_orders = fetchAll('SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC LIMIT 5','i',$user['id']);

include __DIR__ . '/includes/client_header.php';
?>
<h1 class="client-page-title">Welcome back, <?= e($user['first_name']) ?></h1>

<div class="stat-mini-grid">
    <div class="stat-mini"><span class="stat-mini__val"><?= $order_count ?></span><span class="stat-mini__label">Total Orders</span></div>
    <div class="stat-mini"><span class="stat-mini__val"><?= money((float)$total_spent) ?></span><span class="stat-mini__label">Total Spent</span></div>
    <div class="stat-mini"><span class="stat-mini__val"><?= $wish_count ?></span><span class="stat-mini__label">Wishlist Items</span></div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Recent Orders</span>
        <a href="<?= BASE_URL ?>/client/orders.php" class="btn btn-sm btn-outline">View All</a>
    </div>
    <?php if (!empty($recent_orders)): ?>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Order #</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($recent_orders as $o):
                $n = fetchOne('SELECT SUM(quantity) as n FROM order_items WHERE order_id=?','i',$o['id'])['n']??0;
            ?>
            <tr>
                <td><strong><?= e($o['order_number']) ?></strong></td>
                <td style="font-size:.85rem;color:var(--text-muted)"><?= date('M j, Y',strtotime($o['created_at'])) ?></td>
                <td><?= $n ?></td>
                <td><?= money((float)$o['total']) ?></td>
                <td><span class="status-badge status-<?= e($o['status']) ?>"><?= ucfirst(e($o['status'])) ?></span></td>
                <td><a href="<?= BASE_URL ?>/client/order-detail.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline">View</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="card-body">
        <div class="empty-state" style="padding:40px 0">
            <h3>No orders yet</h3>
            <p>Start shopping to see your orders here.</p>
            <a href="<?= BASE_URL ?>/shop.php" class="btn btn-green">Browse Products</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/client_footer.php'; ?>
