<?php
require_once __DIR__ . '/includes/init.php';
$order_num = trim($_GET['order'] ?? '');
$order     = $order_num ? fetchOne('SELECT * FROM orders WHERE order_number=?','s',$order_num) : null;
$items     = $order ? fetchAll('SELECT * FROM order_items WHERE order_id=?','i',$order['id']) : [];
$page_title = 'Order Confirmed — ' . ($order_num ?: 'Dakari');
include __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top:40px;padding-bottom:80px">
    <div class="container">
        <?php if ($order): ?>

        <!-- Success header -->
        <div class="success-hero">
            <div class="success-hero__icon">
                <svg width="36" height="36" fill="none" stroke="#C9A84C" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h1 class="success-hero__title">Order Confirmed!</h1>
            <p class="success-hero__sub">
                Thank you for shopping with Dakari. Your order has been received and is being prepared.
            </p>
            <div class="success-hero__order-num">
                Order #<strong><?= e($order['order_number']) ?></strong>
            </div>
        </div>

        <!-- Payment status banner (M-Pesa) -->
        <?php if ($order['payment_method'] === 'mpesa'): ?>
        <div class="success-payment-banner success-payment-banner--<?= $order['payment_status'] === 'paid' ? 'paid' : 'pending' ?>">
            <?php if ($order['payment_status'] === 'paid'): ?>
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            M-Pesa payment confirmed · Code: <strong><?= e($order['mpesa_code'] ?? '') ?></strong>
            <?php elseif (!empty($order['mpesa_code'])): ?>
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            M-Pesa code received · Awaiting verification · Code: <strong><?= e($order['mpesa_code']) ?></strong>
            <?php else: ?>
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            M-Pesa payment pending · <a href="<?= BASE_URL ?>/payment.php?order=<?= urlencode($order['order_number']) ?>" style="color:inherit;font-weight:700;text-decoration:underline">Complete payment →</a>
            <?php endif; ?>
        </div>
        <?php elseif ($order['payment_method'] === 'cod'): ?>
        <div class="success-payment-banner success-payment-banner--cod">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            Cash on Delivery · Pay when your order arrives
        </div>
        <?php endif; ?>

        <!-- Content grid -->
        <div class="success-grid">

            <!-- Items -->
            <div>
                <div class="success-card">
                    <div class="success-card__header">
                        <span class="success-card__title">Items Ordered</span>
                        <span style="font-size:.82rem;color:var(--text-muted)"><?= count($items) ?> item<?= count($items) !== 1 ? 's' : '' ?></span>
                    </div>
                    <?php foreach ($items as $item): ?>
                    <div class="success-item">
                        <div class="success-item__name"><?= e($item['product_name']) ?></div>
                        <div class="success-item__qty">× <?= $item['quantity'] ?></div>
                        <div class="success-item__price"><?= money((float)$item['subtotal']) ?></div>
                    </div>
                    <?php endforeach; ?>
                    <div class="success-totals">
                        <div class="success-total-row"><span>Subtotal</span><span><?= money((float)$order['subtotal']) ?></span></div>
                        <div class="success-total-row"><span>Shipping</span><span><?= (float)$order['shipping_cost'] == 0 ? '<span style="color:var(--green);font-weight:600">Free</span>' : money((float)$order['shipping_cost']) ?></span></div>
                        <?php if ((float)$order['discount'] > 0): ?>
                        <div class="success-total-row" style="color:var(--green)">
                            <span>Discount<?= $order['coupon_code'] ? ' ('.$order['coupon_code'].')' : '' ?></span>
                            <span>− <?= money((float)$order['discount']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ((float)$order['tax'] > 0): ?>
                        <div class="success-total-row"><span>Tax</span><span><?= money((float)$order['tax']) ?></span></div>
                        <?php endif; ?>
                        <div class="success-total-row success-total-row--grand">
                            <span>Total Paid</span><span><?= money((float)$order['total']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right column -->
            <div>
                <!-- Delivery address -->
                <div class="success-card" style="margin-bottom:18px">
                    <div class="success-card__header"><span class="success-card__title">Delivery Address</span></div>
                    <div style="padding:16px 20px;font-size:.875rem;line-height:1.9;color:var(--text-muted)">
                        <strong style="color:var(--text);display:block"><?= e($order['ship_name']) ?></strong>
                        <?= e($order['ship_address']) ?><br>
                        <?= e($order['ship_city']) ?><?= $order['ship_state'] ? ', '.e($order['ship_state']) : '' ?><br>
                        <?= e($order['ship_country']) ?><br>
                        <?php if ($order['ship_phone']): ?><?= e($order['ship_phone']) ?><br><?php endif; ?>
                        <?= e($order['ship_email']) ?>
                    </div>
                </div>

                <!-- What's next -->
                <div class="success-card">
                    <div class="success-card__header"><span class="success-card__title">What Happens Next?</span></div>
                    <div class="success-next-steps">
                        <div class="success-next-step">
                            <div class="success-next-step__num">1</div>
                            <div>
                                <strong>Order Processing</strong>
                                <span>We'll verify your order and prepare it for dispatch within 24 hours.</span>
                            </div>
                        </div>
                        <div class="success-next-step">
                            <div class="success-next-step__num">2</div>
                            <div>
                                <strong>Shipping</strong>
                                <span>Your order will be shipped and you'll receive tracking details.</span>
                            </div>
                        </div>
                        <div class="success-next-step">
                            <div class="success-next-step__num">3</div>
                            <div>
                                <strong>Delivery</strong>
                                <span>Estimated 2–5 business days within Kenya.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTAs -->
                <div style="display:flex;flex-direction:column;gap:10px;margin-top:18px">
                    <?php if (is_logged_in()): ?>
                    <a href="<?= BASE_URL ?>/client/orders.php" class="btn btn-green">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg>
                        View My Orders
                    </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/shop.php" class="btn btn-outline-green">Continue Shopping</a>
                </div>
            </div>

        </div>

        <?php else: ?>
        <div class="empty-state" style="padding:80px 0">
            <svg width="52" height="52" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <h3>Order not found</h3>
            <p>This order doesn't exist or may have been removed.</p>
            <a href="<?= BASE_URL ?>/" class="btn btn-green" style="margin-top:4px">Go to Home</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
