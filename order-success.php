<?php
require_once __DIR__ . '/includes/init.php';
$order_num = trim($_GET['order'] ?? '');
$order     = $order_num ? fetchOne('SELECT * FROM orders WHERE order_number = ?', 's', $order_num) : null;
$page_title = 'Order Confirmed';
include __DIR__ . '/includes/header.php';
?>
<section class="section" style="min-height:60vh;display:flex;align-items:center">
    <div class="container text-center">
        <?php if ($order): ?>
        <div style="background:var(--off-white);border:1px solid var(--border);border-radius:8px;padding:56px 40px;max-width:560px;margin:0 auto">
            <div style="width:70px;height:70px;background:var(--green);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px">
                <svg width="32" height="32" fill="none" stroke="#C9A84C" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h1 style="font-size:1.8rem;color:var(--green);margin-bottom:10px">Order Confirmed!</h1>
            <p style="color:var(--text-muted);margin-bottom:20px">Thank you for your order. We've received your request and will process it shortly.</p>
            <div style="background:var(--white);border:1px solid var(--border);border-radius:4px;padding:18px;margin-bottom:28px">
                <p style="font-size:.82rem;color:var(--text-muted)">Order Number</p>
                <p style="font-size:1.3rem;font-weight:700;color:var(--gold);letter-spacing:.05em"><?= e($order['order_number']) ?></p>
                <p style="font-size:.85rem;color:var(--text-muted);margin-top:6px">Total: <strong><?= money((float)$order['total']) ?></strong></p>
            </div>
            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
                <?php if (is_logged_in()): ?>
                <a href="client/orders.php" class="btn btn-green">View My Orders</a>
                <?php endif; ?>
                <a href="shop.php" class="btn btn-outline-green">Continue Shopping</a>
            </div>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <h3>Order not found</h3>
            <a href="/" class="btn btn-green">Go Home</a>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
