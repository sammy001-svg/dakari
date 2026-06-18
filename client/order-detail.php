<?php
require_once __DIR__ . '/includes/client_init.php';
$user  = current_user();
$id    = (int)($_GET['id'] ?? 0);
$order = $id ? fetchOne('SELECT * FROM orders WHERE id=? AND user_id=?','ii',$id,$user['id']) : null;
if (!$order) { flash('error','Order not found.'); header('Location: orders.php'); exit; }
$items         = fetchAll('SELECT * FROM order_items WHERE order_id=?','i',$id);
$page_title    = 'Order ' . $order['order_number'];
$client_active = 'orders';
$extra_css     = '<link rel="stylesheet" href="' . BASE_URL . '/assets/css/client.css">';
include __DIR__ . '/includes/client_header.php';
?>

<div class="client-page-header">
    <div>
        <h1 class="client-page-title">Order <?= e($order['order_number']) ?></h1>
        <p class="client-page-sub">Placed on <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></p>
    </div>
    <a href="orders.php" class="btn-outline btn-sm">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Back to Orders
    </a>
</div>

<div class="order-meta-row">
    <span class="status-badge status-<?= e($order['status']) ?>" style="font-size:.8rem;padding:5px 14px"><?= ucfirst(e($order['status'])) ?></span>
    <?php if (!empty($order['payment_method'])): ?>
    <span class="order-meta-date">Paid via <?= e(ucfirst($order['payment_method'])) ?></span>
    <?php endif; ?>
</div>

<!-- Items table -->
<div class="c-card">
    <div class="c-card__header">
        <span class="c-card__title">Items Ordered</span>
        <span style="font-size:.82rem;color:var(--text-muted)"><?= count($items) ?> item<?= count($items) !== 1 ? 's' : '' ?></span>
    </div>
    <div class="c-table-wrap">
        <table class="c-table">
            <thead>
                <tr><th>Product</th><th>Unit Price</th><th>Qty</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td>
                    <strong><?= e($item['product_name']) ?></strong>
                    <?php if (!empty($item['options'])): ?>
                    <div style="font-size:.78rem;color:var(--text-muted);margin-top:2px"><?= e($item['options']) ?></div>
                    <?php endif; ?>
                </td>
                <td><?= money((float)$item['price']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><strong><?= money((float)$item['subtotal']) ?></strong></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Summary + Address -->
<div class="order-detail-grid">
    <div class="c-card" style="margin-bottom:0">
        <div class="c-card__header"><span class="c-card__title">Delivery Address</span></div>
        <div class="c-card__body">
            <div class="order-block">
                <strong><?= e($order['ship_name']) ?></strong>
                <?= e($order['ship_address']) ?><br>
                <?= e($order['ship_city']) ?><?= $order['ship_state'] ? ', '.e($order['ship_state']) : '' ?><br>
                <?= e($order['ship_country']) ?><br>
                <?php if (!empty($order['ship_email'])): ?><?= e($order['ship_email']) ?><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="c-card" style="margin-bottom:0">
        <div class="c-card__header"><span class="c-card__title">Order Summary</span></div>
        <div class="c-card__body">
            <table class="order-totals">
                <tr>
                    <td style="color:var(--text-muted)">Subtotal</td>
                    <td><?= money((float)$order['subtotal']) ?></td>
                </tr>
                <tr>
                    <td style="color:var(--text-muted)">Shipping</td>
                    <td><?= (float)$order['shipping_cost'] == 0 ? '<span style="color:var(--green);font-weight:600">Free</span>' : money((float)$order['shipping_cost']) ?></td>
                </tr>
                <?php if (!empty($order['discount']) && (float)$order['discount'] > 0): ?>
                <tr>
                    <td style="color:var(--text-muted)">Discount<?= !empty($order['coupon_code']) ? ' ('.$order['coupon_code'].')' : '' ?></td>
                    <td style="color:var(--green)">- <?= money((float)$order['discount']) ?></td>
                </tr>
                <?php endif; ?>
                <tr class="grand-total">
                    <td>Total</td>
                    <td><?= money((float)$order['total']) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($order['notes'])): ?>
<div class="c-card" style="margin-top:20px">
    <div class="c-card__header"><span class="c-card__title">Order Notes</span></div>
    <div class="c-card__body" style="color:var(--text-muted);font-size:.88rem"><?= e($order['notes']) ?></div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/client_footer.php'; ?>
