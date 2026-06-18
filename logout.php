<?php
require_once __DIR__ . '/includes/init.php';
logout_user();
header('Location: ' . BASE_URL . '/');
exit;
