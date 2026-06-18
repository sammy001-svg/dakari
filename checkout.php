<?php
require_once __DIR__ . '/includes/init.php';

$items = get_cart_items();
if (empty($items)) { header('Location: cart.php'); exit; }

$subtotal = cart_total();
$shipping = $subtotal > 5000 ? 0 : (float)setting('shipping_cost', '250');
$tax_rate = (float)setting('tax_rate', '0');

// Resolve coupon
$applied_coupon = session_get_coupon();
$discount       = 0.0;
if ($applied_coupon) {
    $res = validate_coupon($applied_coupon['code'], $subtotal);
    if ($res['valid']) {
        $discount = calculate_discount($applied_coupon, $subtotal, $shipping);
        if ($applied_coupon['type'] === 'free_shipping') $shipping = 0;
    } else {
        session_clear_coupon();
        $applied_coupon = null;
    }
}

$tax   = ($subtotal - $discount) * ($tax_rate / 100);
$total = max(0, $subtotal + $shipping - $discount + $tax);
$user  = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name'  => trim($_POST['last_name']  ?? ''),
        'email'      => trim($_POST['email']       ?? ''),
        'phone'      => trim($_POST['phone']       ?? ''),
        'address'    => trim($_POST['address']     ?? ''),
        'city'       => trim($_POST['city']        ?? ''),
        'state'      => trim($_POST['state']       ?? ''),
        'zip'        => trim($_POST['zip']         ?? ''),
        'country'    => trim($_POST['country']     ?? 'Kenya'),
        'notes'      => trim($_POST['notes']       ?? ''),
    ];

    if (!$data['first_name']) $errors[] = 'First name is required.';
    if (!$data['last_name'])  $errors[] = 'Last name is required.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (!$data['address'])    $errors[] = 'Address is required.';
    if (!$data['city'])       $errors[] = 'City is required.';

    if (empty($errors)) {
        $order_number = generate_order_number();

        query(
            'INSERT INTO orders
             (order_number,user_id,guest_email,subtotal,shipping_cost,tax,discount,total,
              coupon_id,coupon_code,
              ship_name,ship_email,ship_phone,ship_address,ship_city,ship_state,ship_zip,ship_country,notes)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            'siidddddiissssssss',
            $order_number,
            $user['id'] ?? null,
            $user ? null : $data['email'],
            $subtotal, $shipping, $tax, $discount, $total,
            $applied_coupon['id']   ?? null,
            $applied_coupon['code'] ?? null,
            $data['first_name'] . ' ' . $data['last_name'],
            $data['email'], $data['phone'],
            $data['address'], $data['city'], $data['state'], $data['zip'], $data['country'],
            $data['notes']
        );
        $order_id = lastInsertId();

        // Save order items + reduce stock
        foreach ($items as $item) {
            $price = is_on_sale($item) ? (float)$item['sale_price'] : (float)$item['price'];
            query(
                'INSERT INTO order_items (order_id,product_id,product_name,price,quantity,subtotal) VALUES (?,?,?,?,?,?)',
                'iisdid', $order_id, $item['product_id'], $item['name'], $price, $item['quantity'], $price * $item['quantity']
            );
            $before_stock = (int)(fetchOne('SELECT stock FROM products WHERE id = ?', 'i', $item['product_id'])['stock'] ?? 0);
            $deduct       = min($item['quantity'], $before_stock);
            query('UPDATE products SET stock = stock - ? WHERE id = ? AND stock > 0', 'ii', $item['quantity'], $item['product_id']);
            if ($deduct > 0) {
                query(
                    'INSERT INTO stock_logs (product_id, type, quantity_change, quantity_before, quantity_after, note)
                     VALUES (?,?,?,?,?,?)',
                    'isiiis',
                    $item['product_id'], 'sale', -$deduct, $before_stock, $before_stock - $deduct,
                    'Order #' . $order_number
                );
            }
        }

        // Record coupon usage and increment counter
        if ($applied_coupon) {
            query(
                'INSERT INTO coupon_uses (coupon_id, user_id, order_id) VALUES (?,?,?)',
                'iii', $applied_coupon['id'], $user['id'] ?? 0, $order_id
            );
            query('UPDATE coupons SET uses_count = uses_count + 1 WHERE id = ?', 'i', $applied_coupon['id']);
            session_clear_coupon();
        }

        // Clear cart
        if ($user) {
            query('DELETE FROM cart WHERE user_id = ?', 'i', $user['id']);
        } else {
            query('DELETE FROM cart WHERE session_id = ? AND user_id IS NULL', 's', session_id());
        }

        header('Location: order-success.php?order=' . urlencode($order_number));
        exit;
    }
}

$page_title = 'Checkout';
include __DIR__ . '/includes/header.php';
?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb__list">
            <li><a href="<?= BASE_URL ?>/">Home</a></li>
            <li><a href="cart.php">Cart</a></li>
            <li>Checkout</li>
        </ul>
    </div>
</div>
<div class="page-hero"><div class="container"><h1>Checkout</h1></div></div>

<section class="section">
    <div class="container">
        <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="post" action="checkout.php">
            <?= csrf_field() ?>
            <div class="checkout-layout">

                <!-- Left: address fields -->
                <div>
                    <div class="form-section">
                        <h3 class="form-section-title">Contact Information</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">First Name <span class="required">*</span></label>
                                <input type="text" name="first_name" class="form-control" value="<?= e($_POST['first_name'] ?? $user['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Last Name <span class="required">*</span></label>
                                <input type="text" name="last_name" class="form-control" value="<?= e($_POST['last_name'] ?? $user['last_name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email <span class="required">*</span></label>
                                <input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? $user['email'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control" value="<?= e($_POST['phone'] ?? $user['phone'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">Shipping Address</h3>
                        <div class="form-grid">
                            <div class="form-group full">
                                <label class="form-label">Street Address <span class="required">*</span></label>
                                <input type="text" name="address" class="form-control" value="<?= e($_POST['address'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">City <span class="required">*</span></label>
                                <input type="text" name="city" class="form-control" value="<?= e($_POST['city'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">State / County</label>
                                <input type="text" name="state" class="form-control" value="<?= e($_POST['state'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Postal Code</label>
                                <input type="text" name="zip" class="form-control" value="<?= e($_POST['zip'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" value="<?= e($_POST['country'] ?? 'Kenya') ?>">
                            </div>
                            <div class="form-group full">
                                <label class="form-label">Order Notes (optional)</label>
                                <textarea name="notes" class="form-control"><?= e($_POST['notes'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: order summary -->
                <div class="order-summary" style="position:sticky;top:88px">
                    <h3 class="summary-title">Your Order</h3>

                    <?php foreach ($items as $item):
                        $price = is_on_sale($item) ? (float)$item['sale_price'] : (float)$item['price'];
                    ?>
                    <div style="display:flex;gap:12px;align-items:center;padding:10px 0;border-bottom:1px solid var(--border)">
                        <div style="width:50px;height:50px;border-radius:4px;overflow:hidden;background:var(--light);flex-shrink:0">
                            <img src="<?= product_thumb($item) ?>" alt="" style="width:100%;height:100%;object-fit:cover">
                        </div>
                        <div style="flex:1;font-size:.88rem;min-width:0">
                            <p style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($item['name']) ?></p>
                            <p style="color:var(--text-muted)">Qty: <?= $item['quantity'] ?></p>
                        </div>
                        <span style="font-size:.9rem;font-weight:600;flex-shrink:0"><?= money($price * $item['quantity']) ?></span>
                    </div>
                    <?php endforeach; ?>

                    <div class="summary-row"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
                    <div class="summary-row"><span>Shipping</span><span><?= $shipping == 0 ? '<span style="color:var(--green);font-weight:600">Free</span>' : money($shipping) ?></span></div>

                    <?php if ($discount > 0 && $applied_coupon): ?>
                    <div class="summary-row" style="color:var(--green)">
                        <span style="display:flex;align-items:center;gap:6px">
                            Discount
                            <span style="font-size:.7rem;background:var(--green);color:var(--white);padding:1px 7px;border-radius:2px;font-weight:700"><?= e($applied_coupon['code']) ?></span>
                        </span>
                        <span style="font-weight:700">−<?= money($discount) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($tax > 0): ?>
                    <div class="summary-row"><span>Tax (<?= $tax_rate ?>%)</span><span><?= money($tax) ?></span></div>
                    <?php endif; ?>

                    <div class="summary-row" style="border-top:2px solid var(--border);padding-top:14px">
                        <strong class="summary-total">Total</strong>
                        <strong class="summary-total"><?= money($total) ?></strong>
                    </div>

                    <?php if (!$applied_coupon): ?>
                    <!-- Quick coupon apply on checkout -->
                    <div style="margin:16px 0 0;padding-top:14px;border-top:1px solid var(--border)">
                        <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:8px">Have a promo code?
                            <a href="cart.php" style="color:var(--gold);font-weight:600">Apply in cart →</a>
                        </p>
                    </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-green btn-block btn-lg" style="margin-top:16px">Place Order</button>
                    <p style="font-size:.72rem;color:var(--text-muted);text-align:center;margin-top:10px">
                        By placing your order you agree to our terms of service.
                    </p>
                </div>

            </div>
        </form>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
