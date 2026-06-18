<?php
require_once dirname(__DIR__) . '/includes/init.php';
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }
if (!verify_csrf()) { echo json_encode(['success'=>false,'message'=>'Invalid CSRF token']); exit; }

$action     = trim($_POST['action'] ?? '');
$product_id = (int)($_POST['product_id'] ?? 0);
$qty        = max(1,(int)($_POST['quantity'] ?? 1));
$session_id = session_id();
$user_id    = $_SESSION['user_id'] ?? null;

if (!$product_id) { echo json_encode(['success'=>false,'message'=>'Invalid product']); exit; }
$product = fetchOne('SELECT id,stock FROM products WHERE id=? AND is_active=1','i',$product_id);
if (!$product) { echo json_encode(['success'=>false,'message'=>'Product not found']); exit; }

if ($action === 'add') {
    if ($user_id) {
        $existing = fetchOne('SELECT id,quantity FROM cart WHERE user_id=? AND product_id=?','ii',$user_id,$product_id);
        if ($existing) { query('UPDATE cart SET quantity=quantity+? WHERE id=?','ii',$qty,$existing['id']); }
        else { query('INSERT INTO cart (user_id,product_id,quantity) VALUES (?,?,?)','iii',$user_id,$product_id,$qty); }
    } else {
        $existing = fetchOne('SELECT id,quantity FROM cart WHERE session_id=? AND product_id=? AND user_id IS NULL','si',$session_id,$product_id);
        if ($existing) { query('UPDATE cart SET quantity=quantity+? WHERE id=?','ii',$qty,$existing['id']); }
        else { query('INSERT INTO cart (session_id,product_id,quantity) VALUES (?,?,?)','sii',$session_id,$product_id,$qty); }
    }
    echo json_encode(['success'=>true,'cart_count'=>cart_count(),'message'=>'Added to cart']); exit;
}

if ($action === 'remove') {
    $cart_id = (int)($_POST['cart_id'] ?? 0);
    if ($user_id) query('DELETE FROM cart WHERE id=? AND user_id=?','ii',$cart_id,$user_id);
    else query('DELETE FROM cart WHERE id=? AND session_id=? AND user_id IS NULL','isi',$cart_id,$session_id);
    echo json_encode(['success'=>true,'cart_count'=>cart_count()]); exit;
}

echo json_encode(['success'=>false,'message'=>'Unknown action']);
