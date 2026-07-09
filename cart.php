<?php
require_once __DIR__ . '/includes/init.php';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'update') {
        foreach ($_POST['quantities'] ?? [] as $cart_id => $qty) {
            $qty = max(1, (int)$qty);
            query('UPDATE cart SET quantity = ? WHERE id = ?', 'ii', $qty, (int)$cart_id);
        }
        flash('success', 'Cart updated.');
        header('Location: cart.php'); exit;
    }

    if ($action === 'remove') {
        $cart_id = (int)($_POST['cart_id'] ?? 0);
        query('DELETE FROM cart WHERE id = ?', 'i', $cart_id);
        flash('success', 'Item removed.');
        header('Location: cart.php'); exit;
    }

    if ($action === 'clear_coupon') {
        session_clear_coupon();
        flash('info', 'Coupon removed.');
        header('Location: cart.php'); exit;
    }
}

$items    = get_cart_items();
$subtotal = cart_total();
$shipping = (float)setting('shipping_cost', '250');
$tax_rate = (float)setting('tax_rate', '0');

// Coupon from session
$applied_coupon = session_get_coupon();
$discount       = 0.0;
if ($applied_coupon) {
    // Re-validate min_order in case cart changed
    $res = validate_coupon($applied_coupon['code'], $subtotal);
    if ($res['valid']) {
        $discount = calculate_discount($applied_coupon, $subtotal, $shipping);
        // free_shipping coupon reduces shipping to 0 for display
        if ($applied_coupon['type'] === 'free_shipping') $shipping = 0;
    } else {
        session_clear_coupon();
        $applied_coupon = null;
        flash('info', 'Your coupon was removed: ' . $res['message']);
        header('Location: cart.php'); exit;
    }
}

$tax   = ($subtotal - $discount) * ($tax_rate / 100);
$total = max(0, $subtotal + $shipping - $discount + $tax);

$page_title  = 'Shopping Cart';
$active_page = 'shop';
include __DIR__ . '/includes/header.php';
?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb__list">
            <li><a href="<?= BASE_URL ?>/">Home</a></li>
            <li>Shopping Cart</li>
        </ul>
    </div>
</div>

<div class="page-hero">
    <div class="container"><h1>Shopping Cart</h1></div>
</div>

<section class="section">
    <div class="container">
        <?php if (!empty($items)): ?>
        <div class="cart-layout">

            <!-- Cart items table -->
            <div>
                <form method="post" action="cart.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item):
                                $price    = is_on_sale($item) ? (float)$item['sale_price'] : (float)$item['price'];
                                $item_sub = $price * $item['quantity'];
                            ?>
                            <tr>
                                <td>
                                    <div class="cart-item__info">
                                        <div class="cart-item__img">
                                            <img src="<?= product_thumb($item) ?>" alt="<?= e($item['name']) ?>">
                                        </div>
                                        <div>
                                            <p class="cart-item__name">
                                                <a href="product.php?slug=<?= e($item['slug']) ?>"><?= e($item['name']) ?></a>
                                            </p>
                                            <?php if (is_on_sale($item)): ?>
                                            <small style="color:var(--text-light);text-decoration:line-through"><?= money((float)$item['price']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= money($price) ?></td>
                                <td>
                                    <div class="qty-selector">
                                        <button type="button" class="qty-btn" data-action="dec">−</button>
                                        <input type="number" name="quantities[<?= $item['id'] ?>]" class="qty-input" value="<?= $item['quantity'] ?>" min="1" max="99">
                                        <button type="button" class="qty-btn" data-action="inc">+</button>
                                    </div>
                                </td>
                                <td><strong><?= money($item_sub) ?></strong></td>
                                <td>
                                    <form method="post" action="cart.php" style="display:inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="cart-item__remove" onclick="return confirm('Remove this item?')">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div style="display:flex;gap:12px;margin-top:16px;justify-content:space-between;flex-wrap:wrap">
                        <a href="shop.php" class="btn btn-outline-green">← Continue Shopping</a>
                        <button type="submit" class="btn btn-outline-green">Update Cart</button>
                    </div>
                </form>
            </div>

            <!-- Order summary + coupon -->
            <div>
                <!-- ── Coupon box ── -->
                <div class="order-summary" style="margin-bottom:16px">
                    <h3 class="summary-title" style="margin-bottom:16px">Promo Code</h3>

                    <?php if ($applied_coupon): ?>
                    <!-- Coupon applied state -->
                    <div id="couponApplied" style="display:flex;align-items:center;justify-content:space-between;background:var(--off-white);border:1px solid var(--border);border-left:4px solid var(--green);border-radius:var(--radius);padding:12px 14px">
                        <div>
                            <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:2px">Applied coupon</p>
                            <p style="font-weight:700;color:var(--green);letter-spacing:.08em"><?= e($applied_coupon['code']) ?></p>
                            <p style="font-size:.8rem;color:var(--gold);font-weight:600"><?= coupon_label($applied_coupon) ?></p>
                        </div>
                        <form method="post" action="cart.php">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="clear_coupon">
                            <button type="submit" title="Remove coupon" style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1.1rem;padding:4px" onmouseover="this.style.color='#c0392b'" onmouseout="this.style.color=''">✕</button>
                        </form>
                    </div>
                    <?php else: ?>
                    <!-- Coupon input -->
                    <div id="couponForm">
                        <div style="display:flex;gap:8px">
                            <input type="text" id="couponCode" placeholder="Enter coupon code" class="form-control" style="flex:1;text-transform:uppercase;letter-spacing:.06em" maxlength="50">
                            <button type="button" id="applyCouponBtn" class="btn btn-gold" style="flex-shrink:0;white-space:nowrap">Apply</button>
                        </div>
                        <p id="couponMsg" style="font-size:.82rem;margin-top:8px;display:none"></p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Order totals -->
                <div class="order-summary">
                    <h3 class="summary-title">Order Summary</h3>

                    <div class="summary-row">
                        <span>Subtotal (<?= count($items) ?> item<?= count($items) !== 1 ? 's' : '' ?>)</span>
                        <span><?= money($subtotal) ?></span>
                    </div>

                    <?php if ($discount > 0): ?>
                    <div class="summary-row" id="discountRow" style="color:var(--green)">
                        <span style="display:flex;align-items:center;gap:6px">
                            Discount
                            <span style="font-size:.72rem;background:var(--green);color:var(--white);padding:1px 7px;border-radius:2px;font-weight:700"><?= e($applied_coupon['code']) ?></span>
                        </span>
                        <span style="font-weight:700;color:var(--green)">−<?= money($discount) ?></span>
                    </div>
                    <?php else: ?>
                    <div class="summary-row" id="discountRow" style="display:none">
                        <span>Discount</span>
                        <span id="discountAmt"></span>
                    </div>
                    <?php endif; ?>

                    <div class="summary-row" id="shippingRow">
                        <span>Shipping</span>
                        <span id="shippingAmt">
                            <?php if ($applied_coupon && $applied_coupon['type'] === 'free_shipping'): ?>
                            <span style="color:var(--green);font-weight:600">Free <small style="text-decoration:line-through;color:var(--text-light)"><?= money((float)setting('shipping_cost','250')) ?></small></span>
                            <?php else: ?>
                            <?= money($shipping) ?>
                            <?php endif; ?>
                        </span>
                    </div>

                    <?php if ($tax > 0): ?>
                    <div class="summary-row">
                        <span>Tax (<?= $tax_rate ?>%)</span>
                        <span><?= money($tax) ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="summary-row" style="padding-top:14px;border-top:2px solid var(--border)">
                        <strong class="summary-total" id="totalLabel">Total</strong>
                        <strong class="summary-total" id="totalAmt"><?= money($total) ?></strong>
                    </div>


                    <a href="checkout.php" class="btn btn-green btn-block btn-lg" style="margin-top:12px">Proceed to Checkout</a>
                </div>
            </div>

        </div><!-- /cart-layout -->

        <?php else: ?>
        <div class="empty-state">
            <svg width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            <h3>Your cart is empty</h3>
            <p>Discover our premium collection and add something you love.</p>
            <a href="shop.php" class="btn btn-green">Start Shopping</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
window.__csrf = "<?= e(csrf_token()) ?>";
(function () {
    const btn   = document.getElementById('applyCouponBtn');
    const input = document.getElementById('couponCode');
    const msg   = document.getElementById('couponMsg');
    if (!btn) return;

    btn.addEventListener('click', async function () {
        const code = input.value.trim().toUpperCase();
        if (!code) { showMsg('Please enter a coupon code.', 'error'); return; }

        btn.disabled = true;
        btn.textContent = 'Checking…';
        msg.style.display = 'none';

        try {
            const res  = await fetch('/dakari/api/coupon.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=apply&code=' + encodeURIComponent(code) + '&csrf_token=' + encodeURIComponent(window.__csrf)
            });
            const data = await res.json();

            if (data.success) {
                // Reload so PHP recalculates totals server-side with the session coupon
                window.location.reload();
            } else {
                showMsg(data.message, 'error');
            }
        } catch (e) {
            showMsg('Connection error. Please try again.', 'error');
        }
        btn.disabled = false;
        btn.textContent = 'Apply';
    });

    input.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); btn.click(); } });

    function showMsg(text, type) {
        msg.textContent  = text;
        msg.style.display = 'block';
        msg.style.color   = type === 'error' ? '#c0392b' : 'var(--green)';
        msg.style.fontWeight = '500';
    }
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
