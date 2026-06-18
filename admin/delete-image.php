<?php
require_once __DIR__ . '/includes/admin_init.php';
$id  = (int)($_GET['id'] ?? 0);
$pid = (int)($_GET['product'] ?? 0);
if ($id) {
    $img = fetchOne('SELECT * FROM product_images WHERE id=?','i',$id);
    if ($img) {
        query('DELETE FROM product_images WHERE id=?','i',$id);
        $file = ROOT_PATH . '/uploads/products/' . $img['image_path'];
        if (file_exists($file)) @unlink($file);
    }
}
header('Location: edit-product.php?id=' . $pid);
exit;
