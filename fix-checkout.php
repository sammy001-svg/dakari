<?php
// One-time patch — fixes the checkout.php bind_param type string on the server.
// Upload this file to your web root, visit it once in a browser, then it deletes itself.
if (php_sapi_name() === 'cli' || isset($_SERVER['HTTP_HOST'])) {
    $target = __DIR__ . '/checkout.php';
    if (!file_exists($target)) { die('checkout.php not found.'); }

    $before = file_get_contents($target);
    $after  = str_replace(
        "'siiddddssdissssssss'",
        "'sisddddssdisssssssss'",
        $before,
        $count
    );

    if ($count === 0) {
        die('Nothing to patch — type string already correct or not found. Safe to delete this file.');
    }

    file_put_contents($target, $after);
    unlink(__FILE__);
    echo 'Patched successfully. This file has been deleted.';
}
