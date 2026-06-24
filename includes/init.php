<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Detect base URL dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
// Strip /admin, /client sub-paths from script dir to get project root
$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
// Walk up until we reach the project root (where index.php lives)
$base      = rtrim($protocol . '://' . $host . $script_dir, '/');
// If in admin/ or client/ subdirectory, adjust one level up
if (str_ends_with($script_dir, '/admin') || str_ends_with($script_dir, '/client') || str_ends_with($script_dir, '/api')) {
    $base = rtrim($protocol . '://' . $host . dirname($script_dir), '/');
}
define('BASE_URL', $base);
define('ROOT_PATH', dirname(__DIR__ ?: __FILE__));

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/mail.php';

// Maintenance mode: redirect non-admin visitors to maintenance page
if (setting('maintenance_mode') === '1' && !is_admin()) {
    $current_script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $exempt = ['/maintenance.php', '/login.php', '/register.php'];
    $is_exempt = false;
    foreach ($exempt as $path) {
        if (str_ends_with($current_script, $path)) { $is_exempt = true; break; }
    }
    if (!$is_exempt) {
        http_response_code(503);
        header('Location: ' . BASE_URL . '/maintenance.php');
        exit;
    }
}
