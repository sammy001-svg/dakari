<?php
require_once __DIR__ . '/includes/admin_init.php';
$id    = (int)($_GET['id'] ?? 0);
$order = $id ? fetchOne('SELECT * FROM orders WHERE id=?','i',$id) : null;
if (!$order) { flash('error','Order not found.'); header('Location: orders.php'); exit; }
$items = fetchAll('SELECT * FROM order_items WHERE order_id=?','i',$id);

$admin_page_title = 'Order #' . $order['order_number'];
$admin_active     = 'orders';

// Update status
if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf() && isset($_POST['status'])) {
    $valid = ['pending','processing','shipped','delivered','cancelled','refunded'];
    $ns = $_POST['status'];
    if (in_array($ns, $valid)) {
        if ($ns !== $order['status']) {
            query('UPDATE orders SET status=? WHERE id=?','si',$ns,$id);
            
            // Send email notification to customer on status update
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
