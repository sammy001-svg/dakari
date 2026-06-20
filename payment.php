<?php
require_once __DIR__ . '/includes/init.php';

$order_num = trim($_GET['order'] ?? '');
$order     = $order_num ? fetchOne("SELECT * FROM orders WHERE order_number=? AND payment_method='mpesa'",'s',$order_num) : null;
if (!$order) { header('Location: /'); exit; }

// Already paid → go straight to success
if ($order['payment_status'] === 'paid') {
    header('Location: order-success.php?order=' . urlencode($order_num)); exit;
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $code = strtoupper(trim($_POST['mpesa_code'] ?? ''));
    if (strlen($code) < 8) {
        $errors[] = 'Please enter a valid M-Pesa transaction code (e.g. QGH7X2Y3T1).';
    } else {
        query("UPDATE orders SET payment_status='paid', mpesa_code=? WHERE id=?",'si',$code,$order['id']);
        
        // Fetch fresh order details including the updated payment status and name
        $db_order = fetchOne("SELECT * FROM orders WHERE id = ?",'i',$order['id']);
        if ($db_order) {
            // 1. Send payment receipt confirmation to customer
            $cust_subject = "Payment Confirmed for Order #" . $db_order['order_number'];
            $cust_html = "<h3>Payment Confirmation</h3>" .
                         "<p>Dear " . htmlspecialchars($db_order['ship_name']) . ",</p>" .
                         "<p>We have successfully verified your payment of <strong>" . money((float)$db_order['total']) . "</strong> via M-Pesa (Transaction Code: <strong>$code</strong>) for order <strong>#" . $db_order['order_number'] . "</strong>.</p>" .
                         "<p>Your order is now being processed and prepared for delivery. We will notify you once it has been shipped.</p>" .
                         "<p><a href='" . BASE_URL . "/client/orders.php' style='display:inline-block; padding:10px 20px; background-color:#1B4332; color:#fff; text-decoration:none; font-weight:bold; border-radius:4px;'>View Order Status</a></p>";
            send_email($db_order['ship_email'], $cust_subject, email_layout($cust_subject, $cust_html));
            
            // 2. Send payment alert to administrator
            $admin_email = setting('site_email', 'info@dakari.com');
            $admin_subject = "Payment Received — Order #" . $db_order['order_number'];
            $admin_html = "<h3>Payment Alert</h3>" .
                          "<p>M-Pesa payment has been confirmed for order <strong>#" . $db_order['order_number'] . "</strong>.</p>" .
                          "<p><strong>Transaction Code:</strong> $code<br>" .
                          "<strong>Amount Paid:</strong> " . money((float)$db_order['total']) . "<br>" .
                          "<strong>Customer:</strong> " . htmlspecialchars($db_order['ship_name']) . " (" . htmlspecialchars($db_order['ship_email']) . ")</p>" .
                          "<p><a href='" . BASE_URL . "/admin/order-detail.php?id=" . $db_order['id'] . "' style='display:inline-block; padding:10px 20px; background-color:#1B4332; color:#fff; text-decoration:none; font-weight:bold; border-radius:4px;'>View Order Details</a></p>";
            send_email($admin_email, $admin_subject, email_layout($admin_subject, $admin_html));
        }
        
        header('Location: order-success.php?order=' . urlencode($order_num)); exit;
    }
}

$page_title  = 'Complete M-Pesa Payment';
$active_page = 'checkout';
include __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top:40px;padding-bottom:80px">
    <div class="container">
        <div class="mpesa-page">

            <!-- Header -->
            <div class="mpesa-page__header">
                <div class="mpesa-page__icon">
                    <svg width="32" height="32" fill="none" stroke="#C9A84C" stroke-width="1.8" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="9" y1="7" x2="15" y2="7"/><line x1="9" y1="11" x2="15" y2="11"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                </div>
                <h1 class="mpesa-page__title">Complete M-Pesa Payment</h1>
                <p class="mpesa-page__sub">Order <strong><?= e($order['order_number']) ?></strong> · <?= money((float)$order['total']) ?></p>
            </div>

            <?php foreach ($errors as $err): ?>
            <div class="alert alert-error" style="margin-bottom:20px"><?= e($err) ?></div>
            <?php endforeach; ?>

            <!-- Steps -->
            <div class="mpesa-steps">
                <div class="mpesa-step">
                    <div class="mpesa-step__num">1</div>
                    <div class="mpesa-step__body">
                        <strong>Go to M-Pesa on your phone</strong>
                        <span>Open Safaricom M-Pesa → Lipa na M-Pesa → Pay Bill</span>
                    </div>
                </div>
                <div class="mpesa-step">
                    <div class="mpesa-step__num">2</div>
                    <div class="mpesa-step__body">
                        <strong>Enter Business Number</strong>
                        <span>
                            Business No: <code class="mpesa-code-display"><?= e(setting('mpesa_paybill','174379')) ?></code>
                        </span>
                    </div>
                </div>
                <div class="mpesa-step">
                    <div class="mpesa-step__num">3</div>
                    <div class="mpesa-step__body">
                        <strong>Account Number</strong>
                        <span>
                            Account: <code class="mpesa-code-display"><?= e($order['order_number']) ?></code>
                        </span>
                    </div>
                </div>
                <div class="mpesa-step">
                    <div class="mpesa-step__num">4</div>
                    <div class="mpesa-step__body">
                        <strong>Amount to Pay</strong>
                        <span>Enter exactly <strong style="color:var(--green)"><?= money((float)$order['total']) ?></strong></span>
                    </div>
                </div>
                <div class="mpesa-step">
                    <div class="mpesa-step__num">5</div>
                    <div class="mpesa-step__body">
                        <strong>Enter your M-Pesa PIN and confirm</strong>
                        <span>You'll receive an SMS confirmation with a transaction code</span>
                    </div>
                </div>
            </div>

            <!-- Code entry -->
            <form method="post" class="mpesa-confirm-form">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label" for="mpesaCode">
                        M-Pesa Confirmation Code <span class="required">*</span>
                    </label>
                    <input type="text" id="mpesaCode" name="mpesa_code" class="form-control mpesa-code-input"
                           placeholder="e.g. QGH7X2Y3T1"
                           maxlength="20"
                           style="text-transform:uppercase;letter-spacing:.1em;font-size:1.1rem;font-weight:600"
                           value="<?= e($_POST['mpesa_code'] ?? '') ?>" required>
                    <span class="form-hint">Enter the code from the M-Pesa SMS you received (e.g. QGH7X2Y3T1).</span>
                </div>
                <button type="submit" class="btn btn-green btn-block" style="padding:14px;margin-top:8px">
                    Confirm Payment
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
            </form>

            <p style="text-align:center;font-size:.8rem;color:var(--text-muted);margin-top:16px">
                Paid already but having trouble?
                <a href="<?= BASE_URL ?>/contact.php" style="color:var(--green);font-weight:600">Contact support →</a>
            </p>

        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
