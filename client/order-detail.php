<?php
require_once __DIR__ . '/includes/client_init.php';
$user  = current_user();
$id    = (int)($_GET['id'] ?? 0);
$order = $id ? fetchOne('SELECT * FROM orders WHERE id=? AND user_id=?','ii',$id,$user['id']) : null;
if (!$order) { flash('error','Order not found.'); header('Location: orders.php'); exit; }
$items = fetchAll('SELECT * FROM order_items WHERE order_id=?','i',$id);
$page_title    = 'Order #' . $order['order_number'];
$client_active = 'orders';
$extra_css     = '<link rel="stylesheet" href="' . BASE_URL . '/assets/css/client.css">';
include __DIR__ . '/includes/client_header.php';
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <h1 class="client-page-title" style="margin-bottom:0">Order #<?= e($order['order_number']) ?></h1>
    <a href="orders.php" class="btn btn-outline btn-sm">← Back</a>
</div>

<div style="display:flex;gap:16px;margin-bottom:24px;flex-wrap:wrap;align-items:center">
    <span class="status-badge status-<?= e($order['status']) ?>" style="font-size:.85rem;padding:6px 14px"><?= ucfirst(e($order['status'])) ?></span>
    <span style="color:var(--text-muted);font-size:.85rem"><?= date('F j, Y g:i A',strtotime($order['created_at'])) ?></span>
</div>

<div class="card" style="margin-bottom:20px">
    <div class="card-header"><span class="card-title">Items Ordered</span></div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><strong><?= e($item['product_name']) ?></strong></td>
                <td><?= money((float)$item['price']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= money((float)$item['subtotal']) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr><td colspan="3" style="text-align:right;padding:10px 16px;color:var(--text-muted)">Subtotal</td><td><?= money((float)$order['subtotal']) ?></td></tr>
                <tr><td colspan="3" style="text-align:right;padding:4px 16px;color:var(--text-muted)">Shipping</td><td><?= $order['shipping_cost']==0?'Free':money((float)$order['shipping_cost']) ?></td></tr>
                <tr><td colspan="3" style="text-align:right;padding:4px 16px 14px;font-weight:700;color:var(--green)">Total</td><td style="font-weight:700;color:var(--green)"><?= money((float)$order['total']) ?></td></tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><span class="card-title">Delivery Address</span></div>
    <div class="card-body" style="font-size:.9rem;line-height:1.8;color:var(--text-muted)">
        <strong style="color:var(--text)"><?= e($order['ship_name']) ?></strong><br>
        <?= e($order['ship_email']) ?><br>
        <?= e($order['ship_address']) ?>, <?= e($order['ship_city']) ?><?= $order['ship_state']?', '.e($order['ship_state']):'' ?><br>
        <?= e($order['ship_country']) ?>
    </div>
</div>

<?php include __DIR__ . '/includes/client_footer.php'; ?>
