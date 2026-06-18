<?php
require_once __DIR__ . '/includes/client_init.php';
$page_title    = 'My Orders';
$client_active = 'orders';
$extra_css     = '<link rel="stylesheet" href="' . BASE_URL . '/assets/css/client.css">';
$user          = current_user();

$status_filter = $_GET['status'] ?? '';
$allowed = ['pending','processing','shipped','delivered','cancelled','refunded'];
if (!in_array($status_filter, $allowed)) $status_filter = '';

if ($status_filter) {
    $orders = fetchAll('SELECT * FROM orders WHERE user_id=? AND status=? ORDER BY created_at DESC','is',$user['id'],$status_filter);
} else {
    $orders = fetchAll('SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC','i',$user['id']);
}

include __DIR__ . '/includes/client_header.php';
?>

<div class="client-page-header">
    <h1 class="client-page-title">My Orders</h1>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a href="orders.php" class="btn-outline btn-sm <?= !$status_filter ? 'active' : '' ?>">All</a>
        <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?>
        <a href="orders.php?status=<?= $s ?>" class="btn-outline btn-sm" style="<?= $status_filter===$s ? 'background:var(--off-white);border-color:var(--green);color:var(--green)' : '' ?>"><?= ucfirst($s) ?></a>
        <?php endforeach; ?>
    </div>
</div>

<?php if (!empty($orders)): ?>
<div class="c-card">
    <div class="c-table-wrap">
        <table class="c-table">
            <thead>
                <tr><th>Order #</th><th>Date</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $o):
                $qty = (int)(fetchOne('SELECT COALESCE(SUM(quantity),0) n FROM order_items WHERE order_id=?','i',$o['id'])['n'] ?? 0);
            ?>
            <tr>
                <td><strong><?= e($o['order_number']) ?></strong></td>
                <td style="color:var(--text-muted);font-size:.84rem"><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
                <td><?= $qty ?> item<?= $qty !== 1 ? 's' : '' ?></td>
                <td><strong><?= money((float)$o['total']) ?></strong></td>
                <td style="font-size:.8rem;color:var(--text-muted);text-transform:capitalize"><?= e($o['payment_method'] ?? '—') ?></td>
                <td><span class="status-badge status-<?= e($o['status']) ?>"><?= ucfirst(e($o['status'])) ?></span></td>
                <td><a href="order-detail.php?id=<?= $o['id'] ?>" class="btn-outline btn-sm">Details</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="empty-state" style="padding:72px 0">
    <svg width="52" height="52" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg>
    <h3><?= $status_filter ? 'No '.ucfirst($status_filter).' orders' : 'No orders yet' ?></h3>
    <p><?= $status_filter ? 'You have no orders with this status.' : 'Your order history will appear here once you shop with us.' ?></p>
    <?php if (!$status_filter): ?>
    <a href="<?= BASE_URL ?>/shop.php" class="btn btn-green btn-sm" style="margin-top:4px">Browse Products</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/client_footer.php'; ?>
