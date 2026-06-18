<?php
// Detect root: admin/includes/ is two levels below project root
$root = dirname(dirname(__DIR__));
define('ROOT_PATH', $root);
// Build BASE_URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script   = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
// go one level up from /admin
$base     = rtrim($protocol . '://' . $host . dirname($script), '/');
if (str_ends_with(str_replace('\\','/',$script), '/admin')) {
    $base = rtrim($protocol . '://' . $host . dirname($script), '/');
}
define('BASE_URL', $base);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

require_once $root . '/config/database.php';
require_once $root . '/includes/functions.php';
require_once $root . '/includes/auth.php';

require_admin();
