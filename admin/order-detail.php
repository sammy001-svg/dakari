<?php
require_once __DIR__ . '/includes/admin_init.php';
$id    = (int)($_GET['id'] ?? 0);
$order = $id ? fetchOne('SELECT * FROM orders WHERE id=?','i',$id) : null;
if (!$order) { flash('error','Order not found.'); header('Location: orders.php'); exit; }
$items = fetchAll('SELECT * FROM order_items WHERE order_id=?','i',$id);

$admin_page_title = 'Order #' . $order['order_number'];
$admin_active     = 'orders';

if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {

    // Approve M-Pesa payment
    if (isset($_POST['action']) && $_POST['action'] === 'approve_payment') {
        if ($order['payment_method'] === 'mpesa' && $order['payment_status'] !== 'paid') {
            query("UPDATE orders SET payment_status='paid', status='processing' WHERE id=?", 'i', $id);
            $approved = fetchOne('SELECT * FROM orders WHERE id=?', 'i', $id);
            if ($approved) {
                $subj = "Payment Approved — Your Order #" . $approved['order_number'] . " is Being Processed";
                $html = "<h3>Payment Confirmed</h3>" .
                        "<p>Dear " . htmlspecialchars($approved['ship_name']) . ",</p>" .
                        "<p>Your M-Pesa payment of <strong>" . money((float)$approved['total']) . "</strong> (Code: <strong>" . htmlspecialchars($approved['mpesa_code']) . "</strong>) for order <strong>#" . $approved['order_number'] . "</strong> has been verified and approved.</p>" .
                        "<p>Your order is now being processed and will be shipped soon. We'll send you another update once it's on its way.</p>" .
                        "<p><a href='" . BASE_URL . "/client/orders.php' style='display:inline-block;padding:10px 20px;background:#1B4332;color:#fff;text-decoration:none;font-weight:bold;border-radius:4px'>View Order Status</a></p>";
                send_email($approved['ship_email'], $subj, email_layout($subj, $html));
            }
            flash('success', 'Payment approved. Order moved to Processing and customer notified.');
        }
        header('Location: order-detail.php?id=' . $id); exit;
    }

    // Update order status
    if (isset($_POST['status'])) {
        $valid = ['pending','processing','shipped','delivered','cancelled','refunded'];
        $ns = $_POST['status'];
        if (in_array($ns, $valid)) {
            if ($ns !== $order['status']) {
                query('UPDATE orders SET status=? WHERE id=?','si',$ns,$id);
                $updated_order = fetchOne('SELECT * FROM orders WHERE id=?','i',$id);
                if ($updated_order) {
                    $email_subject = "Dakari Store — Order #{$updated_order['order_number']} Status Update: " . ucfirst($ns);
                    $email_html = email_template_status_update($updated_order);
                    send_email($updated_order['ship_email'], $email_subject, $email_html);
                }
                flash('success','Order status updated and notification email sent.');
            } else {
                flash('success','Order status remains unchanged.');
            }
            header('Location: order-detail.php?id='.$id); exit;
        }
    }
}
include __DIR__ . '/includes/admin_header.php';
?>
<div class="page-header">
    <div>
        <div class="page-title">Order #<?= e($order['order_number']) ?></div>
        <div class="page-subtitle"><?= date('F j, Y g:i A', strtotime($order['created_at'])) ?></div>
    </div>
    <a href="orders.php" class="btn btn-outline">← Back to Orders</a>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start">
    <div>
        <div class="card" style="margin-bottom:20px">
            <div class="card-header"><span class="card-title">Order Items</span></div>
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
                        <tr><td colspan="3" style="text-align:right;padding:12px 16px;font-size:.85rem;color:var(--text-muted)">Subtotal</td><td><?= money((float)$order['subtotal']) ?></td></tr>
                        <tr><td colspan="3" style="text-align:right;padding:4px 16px;font-size:.85rem;color:var(--text-muted)">Shipping</td><td><?= $order['shipping_cost']==0?'Free':money((float)$order['shipping_cost']) ?></td></tr>
                        <tr><td colspan="3" style="text-align:right;padding:4px 16px 14px;font-weight:700;color:var(--green)">Total</td><td style="font-weight:700;color:var(--green)"><?= money((float)$order['total']) ?></td></tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><span class="card-title">Shipping Address</span></div>
            <div class="card-body" style="font-size:.9rem;line-height:1.8;color:var(--text-muted)">
                <strong style="color:var(--text)"><?= e($order['ship_name']) ?></strong><br>
                <?= e($order['ship_email']) ?><br>
                <?php if ($order['ship_phone']): ?><?= e($order['ship_phone']) ?><br><?php endif; ?>
                <?= e($order['ship_address']) ?><br>
                <?= e($order['ship_city']) ?><?= $order['ship_state'] ? ', '.e($order['ship_state']) : '' ?> <?= e($order['ship_zip']) ?><br>
                <?= e($order['ship_country']) ?>
                <?php if ($order['notes']): ?>
                <p style="margin-top:12px;padding:12px;background:var(--off-white);border-radius:4px"><strong>Notes:</strong> <?= e($order['notes']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div>

        <!-- Payment Information -->
        <div class="card" style="margin-bottom:20px">
            <div class="card-header"><span class="card-title">Payment Information</span></div>
            <div class="card-body" style="font-size:.9rem">

                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                    <span style="color:var(--text-muted)">Method</span>
                    <span style="font-weight:600;text-transform:uppercase;letter-spacing:.04em"><?= e($order['payment_method']) ?></span>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                    <span style="color:var(--text-muted)">Payment Status</span>
                    <?php
                        $ps = $order['payment_status'] ?? 'pending';
                        $ps_color = $ps === 'paid' ? 'var(--green)' : ($ps === 'failed' ? '#c0392b' : '#b7791f');
                        $ps_bg    = $ps === 'paid' ? 'rgba(27,67,50,.08)' : ($ps === 'failed' ? 'rgba(192,57,43,.08)' : 'rgba(201,168,76,.12)');
                    ?>
                    <span style="font-weight:700;color:<?= $ps_color ?>;background:<?= $ps_bg ?>;padding:2px 10px;border-radius:20px;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em"><?= ucfirst($ps) ?></span>
                </div>

                <?php if ($order['payment_method'] === 'mpesa'): ?>

                <?php if (!empty($order['mpesa_code'])): ?>
                <div style="margin:14px 0;padding:14px;background:var(--off-white);border:1px solid var(--border);border-radius:var(--radius)">
                    <p style="font-size:.72rem;color:var(--text-muted);margin-bottom:4px;text-transform:uppercase;letter-spacing:.06em">M-Pesa Transaction Code</p>
                    <p style="font-size:1.25rem;font-weight:700;letter-spacing:.12em;color:var(--green);font-family:monospace"><?= e($order['mpesa_code']) ?></p>
                </div>

                <?php if ($ps !== 'paid'): ?>
                <!-- Approve button — only when code submitted and not yet verified -->
                <form method="post" onsubmit="return confirm('Approve this M-Pesa payment and notify the customer?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="approve_payment">
                    <button type="submit" class="btn btn-green" style="width:100%;padding:11px;font-size:.95rem">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:5px"><polyline points="20 6 9 17 4 12"/></svg>
                        Approve Payment
                    </button>
                </form>
                <p style="font-size:.72rem;color:var(--text-muted);margin-top:8px;line-height:1.5;text-align:center">Approving marks the order as paid, moves it to Processing, and emails the customer.</p>
                <?php else: ?>
                <p style="font-size:.8rem;color:var(--green);font-weight:600;text-align:center">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="vertical-align:middle"><polyline points="20 6 9 17 4 12"/></svg>
                    Payment verified
                </p>
                <?php endif; ?>

                <?php else: ?>
                <p style="font-size:.82rem;color:var(--text-muted);font-style:italic">No M-Pesa code submitted yet.</p>
                <?php endif; ?>

                <?php endif; ?>

                <?php if ($order['coupon_code']): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;padding-top:12px;border-top:1px solid var(--border)">
                    <span style="color:var(--text-muted)">Coupon</span>
                    <span style="font-weight:600;color:var(--gold)"><?= e($order['coupon_code']) ?></span>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- Update Status -->
        <div class="card">
            <div class="card-header"><span class="card-title">Update Status</span></div>
            <div class="card-body">
                <p style="margin-bottom:12px">Current: <span class="status-badge status-<?= e($order['status']) ?>"><?= ucfirst(e($order['status'])) ?></span></p>
                <form method="post">
                    <?= csrf_field() ?>
                    <div class="form-group" style="margin-bottom:14px">
                        <label class="form-label">Change Status</label>
                        <select name="status" class="form-control">
                            <?php foreach (['pending','processing','shipped','delivered','cancelled','refunded'] as $s): ?>
                            <option value="<?= $s ?>" <?= $order['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-green" style="width:100%">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
