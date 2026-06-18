<?php
require_once dirname(__DIR__) . '/includes/init.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) { echo json_encode(['results' => []]); exit; }

$results = fetchAll(
    'SELECT id,name,slug,price,sale_price,thumbnail FROM products WHERE is_active=1 AND (name LIKE ? OR short_desc LIKE ?) LIMIT 8',
    'ss', "%$q%", "%$q%"
);

$out = [];
foreach ($results as $p) {
    $out[] = [
        'id'    => $p['id'],
        'name'  => $p['name'],
        'url'   => BASE_URL . '/product.php?slug=' . $p['slug'],
        'price' => money(is_on_sale($p) ? (float)$p['sale_price'] : (float)$p['price']),
        'image' => product_thumb($p),
    ];
}
echo json_encode(['results' => $out]);
