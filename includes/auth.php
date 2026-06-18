<?php
require_once __DIR__ . '/../config/database.php';

function auth_start(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
}

function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function is_admin(): bool {
    return is_logged_in() && ($_SESSION['user_role'] ?? '') === 'admin';
}

function current_user(): ?array {
    if (!is_logged_in()) return null;
    return fetchOne('SELECT * FROM users WHERE id = ?', 'i', $_SESSION['user_id']);
}

function require_login(string $redirect = '/login.php'): void {
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . BASE_URL . $redirect);
        exit;
    }
}

function require_admin(): void {
    if (!is_admin()) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

function login_user(string $email, string $password): array {
    $user = fetchOne('SELECT * FROM users WHERE email = ? AND is_active = 1', 's', $email);
    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name']  = $user['first_name'];
    $_SESSION['user_role']  = $user['role'];
    // Merge guest cart into user cart
    merge_guest_cart($user['id']);
    return ['success' => true, 'role' => $user['role']];
}

function register_user(array $data): array {
    $existing = fetchOne('SELECT id FROM users WHERE email = ?', 's', $data['email']);
    if ($existing) {
        return ['success' => false, 'message' => 'An account with this email already exists.'];
    }
    $hash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
    query(
        'INSERT INTO users (first_name, last_name, email, phone, password) VALUES (?,?,?,?,?)',
        'sssss', $data['first_name'], $data['last_name'], $data['email'], $data['phone'] ?? '', $hash
    );
    $id = lastInsertId();
    $_SESSION['user_id']    = $id;
    $_SESSION['user_email'] = $data['email'];
    $_SESSION['user_name']  = $data['first_name'];
    $_SESSION['user_role']  = 'client';
    return ['success' => true];
}

function logout_user(): void {
    session_destroy();
}

function merge_guest_cart(int $user_id): void {
    $session_id = session_id();
    $guest_items = fetchAll(
        'SELECT product_id, quantity FROM cart WHERE session_id = ? AND user_id IS NULL',
        's', $session_id
    );
    foreach ($guest_items as $item) {
        $exists = fetchOne(
            'SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?',
            'ii', $user_id, $item['product_id']
        );
        if ($exists) {
            query('UPDATE cart SET quantity = quantity + ? WHERE id = ?', 'ii', $item['quantity'], $exists['id']);
        } else {
            query('UPDATE cart SET user_id = ?, session_id = NULL WHERE session_id = ? AND product_id = ?',
                  'isi', $user_id, $session_id, $item['product_id']);
        }
    }
    query('DELETE FROM cart WHERE session_id = ? AND user_id IS NULL', 's', $session_id);
}
