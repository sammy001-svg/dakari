<?php
require_once dirname(__DIR__) . '/includes/init.php';
header('Content-Type: application/json');

if (!is_logged_in()) { echo json_encode(['success'=>false,'message'=>'Login required']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'message'=>'POST only']); exit; }
if (!verify_csrf()) { echo json_encode(['success'=>false,'message'=>'Invalid token']); exit; }

$product_id = (int)($_POST['product_id'] ?? 0);
$user_id    = (int)$_SESSION['user_id'];
if (!$product_id) { echo json_encode(['success'=>false,'message'=>'Invalid product']); exit; }

$exists = fetchOne('SELECT id FROM wishlist WHERE user_id=? AND product_id=?','ii',$user_id,$product_id);
if ($exists) {
    query('DELETE FROM wishlist WHERE id=?','i',$exists['id']);
    echo json_encode(['success'=>true,'action'=>'removed']);
} else {
    query('INSERT INTO wishlist (user_id,product_id) VALUES (?,?)','ii',$user_id,$product_id);
    echo json_encode(['success'=>true,'action'=>'added']);
}
