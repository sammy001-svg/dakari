<?php
require_once __DIR__ . '/includes/init.php';

$items = get_cart_items();
if (empty($items)) { header('Location: cart.php'); exit; }

$subtotal = cart_total();
$shipping = (float)setting('shipping_cost', '250');
$tax_rate = (float)setting('tax_rate','0');

$applied_coupon = session_get_coupon();
$discount       = 0.0;
if ($applied_coupon) {
    $res = validate_coupon($applied_coupon['code'], $subtotal);
    if ($res['valid']) {
        $discount = calculate_discount($applied_coupon, $subtotal, $shipping);
        if ($applied_coupon['type'] === 'free_shipping') $shipping = 0.0;
    } else {
        session_clear_coupon();
        $applied_coupon = null;
    }
}
$tax   = ($subtotal - $discount) * ($tax_rate / 100);
$total = max(0.0, $subtotal + $shipping - $discount + $tax);
$user  = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $data = [
        'first_name'     => trim($_POST['first_name']     ?? ''),
        'last_name'      => trim($_POST['last_name']      ?? ''),
        'email'          => trim($_POST['email']          ?? ''),
        'phone'          => trim($_POST['phone']          ?? ''),
        'address'        => trim($_POST['address']        ?? ''),
        'city'           => trim($_POST['city']           ?? ''),
        'state'          => trim($_POST['state']          ?? ''),
        'zip'            => trim($_POST['zip']            ?? ''),
        'country'        => trim($_POST['country']        ?? 'Kenya'),
        'notes'          => trim($_POST['notes']          ?? ''),
        'payment_method' => in_array($_POST['payment_method'] ?? '', ['cod','mpesa','card']) ? $_POST['payment_method'] : 'cod',
    ];

    if (!$data['first_name'])                              $errors[] = 'First name is required.';
    if (!$data['last_name'])                               $errors[] = 'Last name is required.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
    if (!$data['address'])                                 $errors[] = 'Street address is required.';
    if (!$data['city'])                                    $errors[] = 'City is required.';
    if ($data['payment_method'] === 'card')                $errors[] = 'Card payments are not yet available. Please choose M-Pesa or Cash on Delivery.';

    if (empty($errors)) {
        $order_number    = generate_order_number();
        $payment_status  = $data['payment_method'] === 'cod' ? 'pending' : 'pending';

        query(
            'INSERT INTO orders
             (order_number,user_id,guest_email,subtotal,shipping_cost,tax,discount,
              payment_method,payment_status,total,
              coupon_id,coupon_code,
              ship_name,ship_email,ship_phone,ship_address,ship_city,ship_state,ship_zip,ship_country,notes)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            'sisddddssdisssssssss',
            $order_number,
            $user['id'] ?? null,
            $user ? null : $data['email'],
            $subtotal, $shipping, $tax, $discount,
            $data['payment_method'], $payment_status,
            $total,
            $applied_coupon['id']   ?? null,
            $applied_coupon['code'] ?? null,
            $data['first_name'] . ' ' . $data['last_name'],
            $data['email'], $data['phone'],
            $data['address'], $data['city'], $data['state'], $data['zip'], $data['country'],
            $data['notes']
        );
        $order_id = lastInsertId();

        foreach ($items as $item) {
            $price  = is_on_sale($item) ? (float)$item['sale_price'] : (float)$item['price'];
            $before = (int)(fetchOne('SELECT stock FROM products WHERE id=?','i',$item['product_id'])['stock'] ?? 0);
            $deduct = min($item['quantity'], $before);
            query('INSERT INTO order_items (order_id,product_id,product_name,price,quantity,subtotal) VALUES (?,?,?,?,?,?)',
                  'iisdid', $order_id, $item['product_id'], $item['name'], $price, $item['quantity'], $price * $item['quantity']);
            query('UPDATE products SET stock = stock - ? WHERE id = ? AND stock > 0','ii',$item['quantity'],$item['product_id']);
            if ($deduct > 0) {
                query('INSERT INTO stock_logs (product_id,type,quantity_change,quantity_before,quantity_after,note) VALUES (?,?,?,?,?,?)',
                      'isiiis',$item['product_id'],'sale',-$deduct,$before,$before-$deduct,'Order #'.$order_number);
            }
        }

        if ($applied_coupon) {
            query('INSERT INTO coupon_uses (coupon_id,user_id,order_id) VALUES (?,?,?)','iii',$applied_coupon['id'],$user['id']??0,$order_id);
            query('UPDATE coupons SET uses_count=uses_count+1 WHERE id=?','i',$applied_coupon['id']);
            session_clear_coupon();
        }

        if ($user) {
            query('DELETE FROM cart WHERE user_id=?','i',$user['id']);
        } else {
            query('DELETE FROM cart WHERE session_id=? AND user_id IS NULL','s',session_id());
        }

        // Fetch order details for automated email notifications
        $db_order = fetchOne('SELECT * FROM orders WHERE id = ?', 'i', $order_id);
        $db_items = fetchAll('SELECT * FROM order_items WHERE order_id = ?', 'i', $order_id);
        if ($db_order && !empty($db_items)) {
            // 1. Send confirmation email to customer
            $email_subject = "Dakari Store — Order Confirmation #" . $db_order['order_number'];
            $email_html = email_template_order_invoice($db_order, $db_items);
            send_email($db_order['ship_email'], $email_subject, $email_html);
            
            // 2. Send alert email to administrator
            $admin_email = setting('site_email', 'info@dakari.com');
            $admin_subject = "New Order Received — #" . $db_order['order_number'];
            $admin_html = "<h3>New Order Notification</h3>" .
                          "<p>A new order has been placed on Dakari Store.</p>" .
                          "<p><strong>Order Number:</strong> #" . $db_order['order_number'] . "<br>" .
                          "<strong>Customer:</strong> " . htmlspecialchars($db_order['ship_name']) . " (" . htmlspecialchars($db_order['ship_email']) . ")<br>" .
                          "<strong>Total Amount:</strong> " . money((float)$db_order['total']) . "<br>" .
                          "<strong>Payment Method:</strong> " . strtoupper($db_order['payment_method']) . "</p>" .
                          "<p><a href='" . BASE_URL . "/admin/order-detail.php?id=" . $order_id . "' style='display:inline-block; padding:10px 20px; background-color:#1B4332; color:#fff; text-decoration:none; font-weight:bold; border-radius:4px;'>View Order Details</a></p>";
            send_email($admin_email, $admin_subject, email_layout($admin_subject, $admin_html));
        }

        if ($data['payment_method'] === 'mpesa') {
            header('Location: payment.php?order=' . urlencode($order_number));
        } else {
            header('Location: order-success.php?order=' . urlencode($order_number));
        }
        exit;
    }
}

$page_title = 'Checkout';
$active_page = 'checkout';
include __DIR__ . '/includes/header.php';
?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb__list">
            <li><a href="<?= BASE_URL ?>/">Home</a></li>
            <li><a href="<?= BASE_URL ?>/cart.php">Cart</a></li>
            <li>Checkout</li>
        </ul>
    </div>
</div>

<section class="section" style="padding-top:36px">
    <div class="container">

        <!-- Checkout steps indicator -->
        <div class="checkout-steps">
            <div class="checkout-step checkout-step--done">
                <span class="checkout-step__num">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
                <span class="checkout-step__label">Cart</span>
            </div>
            <div class="checkout-step__line checkout-step__line--done"></div>
            <div class="checkout-step checkout-step--active">
                <span class="checkout-step__num">2</span>
                <span class="checkout-step__label">Details</span>
            </div>
            <div class="checkout-step__line"></div>
            <div class="checkout-step">
                <span class="checkout-step__num">3</span>
                <span class="checkout-step__label">Payment</span>
            </div>
            <div class="checkout-step__line"></div>
            <div class="checkout-step">
                <span class="checkout-step__num">4</span>
                <span class="checkout-step__label">Confirm</span>
            </div>
        </div>

        <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="post" id="checkoutForm">
            <?= csrf_field() ?>
            <div class="checkout-layout">

                <!-- ── Left: details + payment ── -->
                <div>

                    <!-- Contact -->
                    <div class="co-section">
                        <h3 class="co-section__title">
                            <span class="co-section__num">1</span>
                            Contact Information
                        </h3>
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
                                <label class="form-label">Email Address <span class="required">*</span></label>
                                <input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? $user['email'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" value="<?= e($_POST['phone'] ?? $user['phone'] ?? '') ?>" placeholder="+254 7XX XXX XXX">
                            </div>
                        </div>
                    </div>

                    <!-- Shipping address -->
                    <div class="co-section">
                        <h3 class="co-section__title">
                            <span class="co-section__num">2</span>
                            Delivery Address
                        </h3>
                        <div class="form-grid">
                            <div class="form-group full">
                                <label class="form-label">Street Address <span class="required">*</span></label>
                                <input type="text" name="address" class="form-control" value="<?= e($_POST['address'] ?? '') ?>" placeholder="House / Apt number, Street name" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">City / Town <span class="required">*</span></label>
                                <input type="text" name="city" class="form-control" value="<?= e($_POST['city'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">County / State</label>
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
                                <label class="form-label">Delivery Notes <small style="color:var(--text-muted);font-weight:400">(optional)</small></label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Any special instructions for delivery…"><?= e($_POST['notes'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Payment method -->
                    <div class="co-section">
                        <h3 class="co-section__title">
                            <span class="co-section__num">3</span>
                            Payment Method
                        </h3>
                        <div class="pay-options">

                            <label class="pay-option <?= ($_POST['payment_method'] ?? 'mpesa') === 'mpesa' ? 'pay-option--selected' : '' ?>">
                                <input type="radio" name="payment_method" value="mpesa" <?= ($_POST['payment_method'] ?? 'mpesa') === 'mpesa' ? 'checked' : '' ?>>
                                <div class="pay-option__icon pay-option__icon--green">
                                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                </div>
                                <div class="pay-option__body">
                                    <strong>M-Pesa</strong>
                                    <span>Pay via Safaricom M-Pesa STK Push</span>
                                </div>
                                <div class="pay-option__check">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                            </label>

                            <label class="pay-option <?= ($_POST['payment_method'] ?? '') === 'cod' ? 'pay-option--selected' : '' ?>">
                                <input type="radio" name="payment_method" value="cod" <?= ($_POST['payment_method'] ?? '') === 'cod' ? 'checked' : '' ?>>
                                <div class="pay-option__icon">
                                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                </div>
                                <div class="pay-option__body">
                                    <strong>Cash on Delivery</strong>
                                    <span>Pay in cash when your order arrives</span>
                                </div>
                                <div class="pay-option__check">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                            </label>

                            <label class="pay-option pay-option--disabled">
                                <input type="radio" name="payment_method" value="card" disabled>
                                <div class="pay-option__icon">
                                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                                </div>
                                <div class="pay-option__body">
                                    <strong>Credit / Debit Card</strong>
                                    <span>Coming soon</span>
                                </div>
                                <span class="pay-option__badge">Soon</span>
                            </label>

                        </div>

                        <div class="mpesa-info" id="mpesaInfo">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            After placing your order you'll be prompted to enter your M-Pesa confirmation code on the next page.
                        </div>
                    </div>

                </div><!-- /left -->

                <!-- ── Right: order summary ── -->
                <div class="co-summary">
                    <h3 class="co-summary__title">Order Summary</h3>

                    <div class="co-summary__items">
                        <?php foreach ($items as $item):
                            $price = is_on_sale($item) ? (float)$item['sale_price'] : (float)$item['price'];
                        ?>
                        <div class="co-summary__item">
                            <div class="co-summary__item-img">
                                <img src="<?= product_thumb($item) ?>" alt="<?= e($item['name']) ?>">
                                <span class="co-summary__item-qty"><?= $item['quantity'] ?></span>
                            </div>
                            <div class="co-summary__item-name"><?= e($item['name']) ?></div>
                            <div class="co-summary__item-price"><?= money($price * $item['quantity']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="co-summary__lines">
                        <div class="co-summary__line">
                            <span>Subtotal</span><span><?= money($subtotal) ?></span>
                        </div>
                        <div class="co-summary__line">
                            <span>Shipping</span>
                            <span><?= money($shipping) ?></span>
                        </div>
                        <?php if ($discount > 0 && $applied_coupon): ?>
                        <div class="co-summary__line co-summary__line--discount">
                            <span>
                                Discount
                                <span class="co-coupon-badge"><?= e($applied_coupon['code']) ?></span>
                            </span>
                            <span>− <?= money($discount) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($tax > 0): ?>
                        <div class="co-summary__line">
                            <span>Tax (<?= $tax_rate ?>%)</span><span><?= money($tax) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!$applied_coupon): ?>
                        <div style="padding:10px 0 4px">
                            <a href="<?= BASE_URL ?>/cart.php" style="font-size:.78rem;color:var(--gold);font-weight:600">Have a promo code? Apply in cart →</a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="co-summary__total">
                        <span>Total</span>
                        <span><?= money($total) ?></span>
                    </div>

                    <button type="submit" class="btn btn-green btn-block" style="margin-top:20px;padding:14px">
                        Place Order
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </button>
                    <p style="font-size:.72rem;color:var(--text-muted);text-align:center;margin-top:10px;line-height:1.5">
                        By placing your order you agree to our
                        <a href="#" style="color:var(--green)">Terms of Service</a> and
                        <a href="#" style="color:var(--green)">Privacy Policy</a>.
                    </p>

                    <!-- Trust strip -->
                    <div class="co-trust">
                        <div class="co-trust__item">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            Secure Checkout
                        </div>
                        <div class="co-trust__item">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 3h15v13H1zM16 8h4l3 3v5h-7V8z"/></svg>
                            Fast Delivery
                        </div>
                        <div class="co-trust__item">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                            Easy Returns
                        </div>
                    </div>

                </div><!-- /summary -->

            </div><!-- /checkout-layout -->
        </form>
    </div>
</section>

<script>
document.querySelectorAll('.pay-options .pay-option input[type="radio"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.pay-option').forEach(function(el) { el.classList.remove('pay-option--selected'); });
        if (this.checked) this.closest('.pay-option').classList.add('pay-option--selected');
        document.getElementById('mpesaInfo').style.display = this.value === 'mpesa' ? 'flex' : 'none';
    });
});
var initMethod = document.querySelector('.pay-options input[type="radio"]:checked');
if (initMethod) document.getElementById('mpesaInfo').style.display = initMethod.value === 'mpesa' ? 'flex' : 'none';
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
