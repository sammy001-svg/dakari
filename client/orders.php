<?php
require_once __DIR__ . '/includes/client_init.php';
$page_title    = 'My Orders';
$client_active = 'orders';
$extra_css     = '<link rel="stylesheet" href="' . BASE_URL . '/assets/css/client.css">';
$user          = current_user();
$orders        = fetchAll('SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC','i',$user['id']);
include __DIR__ . '/includes/client_header.php';
?>
<h1 class="client-page-title">My Orders</h1>

<?php if (!empty($orders)): ?>
<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Order #</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td><strong><?= e($o['order_number']) ?></strong></td>
                <td style="color:var(--text-muted);font-size:.85rem"><?= date('F j, Y',strtotime($o['created_at'])) ?></td>
                <td><?= money((float)$o['total']) ?></td>
                <td><span class="status-badge status-<?= e($o['status']) ?>"><?= ucfirst(e($o['status'])) ?></span></td>
                <td><a href="order-detail.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline">View Details</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="empty-state" style="padding:60px 0">
    <svg width="56" height="56" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg>
    <h3>No orders yet</h3>
    <p>You haven't placed any orders. Explore our collection!</p>
    <a href="<?= BASE_URL ?>/shop.php" class="btn btn-green">Start Shopping</a>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/client_footer.php'; ?>
