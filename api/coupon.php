<?php
require_once dirname(__DIR__) . '/includes/init.php';
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']); exit;
}
if (!verify_csrf()) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']); exit;
}

$action = trim($_POST['action'] ?? '');

// ── Apply coupon ──────────────────────────────────────────────────────────────
if ($action === 'apply') {
    $code     = strtoupper(trim($_POST['code'] ?? ''));
    $subtotal = cart_total();
    $shipping = $subtotal > 5000 ? 0 : (float)setting('shipping_cost', '250');

    if (!$code) {
        echo json_encode(['success' => false, 'message' => 'Please enter a coupon code.']); exit;
    }

    $result = validate_coupon($code, $subtotal);
    if (!$result['valid']) {
        echo json_encode(['success' => false, 'message' => $result['message']]); exit;
    }

    $coupon   = $result['coupon'];
    $discount = calculate_discount($coupon, $subtotal, $shipping);
    session_apply_coupon($coupon);

    echo json_encode([
        'success'       => true,
        'message'       => 'Coupon applied! ' . coupon_label($coupon),
        'code'          => $coupon['code'],
        'label'         => coupon_label($coupon),
        'discount'      => $discount,
        'discount_fmt'  => money($discount),
        'type'          => $coupon['type'],
    ]);
    exit;
}

// ── Remove coupon ─────────────────────────────────────────────────────────────
if ($action === 'remove') {
    session_clear_coupon();
    echo json_encode(['success' => true, 'message' => 'Coupon removed.']); exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);
