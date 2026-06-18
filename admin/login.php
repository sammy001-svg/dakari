<?php
require_once dirname(__DIR__) . '/includes/init.php';
if (is_admin()) { header('Location: ' . BASE_URL . '/admin/index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $result = login_user(trim($_POST['email'] ?? ''), $_POST['password'] ?? '');
    if ($result['success'] && $result['role'] === 'admin') {
        header('Location: ' . BASE_URL . '/admin/index.php'); exit;
    }
    $error = $result['success'] ? 'Access denied. Admin only.' : ($result['message'] ?? 'Invalid credentials.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Login — Dakari</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
    <style>
        body { background: #132d22; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .login-card { background: #fff; border-radius: 8px; padding: 52px 48px; width: 100%; max-width: 420px; box-shadow: 0 8px 40px rgba(0,0,0,.25); }
        .login-logo { font-family: 'Playfair Display', serif; font-size: 2rem; color: #1B4332; text-align: center; margin-bottom: 6px; }
        .login-sub  { text-align: center; font-size: .82rem; color: #999; text-transform: uppercase; letter-spacing: .1em; margin-bottom: 32px; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-logo">Dakari</div>
    <p class="login-sub">Admin Panel</p>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" action="login.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <div class="form-group" style="margin-bottom:16px">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required autofocus placeholder="admin@dakari.com">
        </div>
        <div class="form-group" style="margin-bottom:28px">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn btn-green" style="width:100%;padding:13px">Sign In to Admin</button>
    </form>
    <p style="text-align:center;margin-top:20px;font-size:.82rem;color:#999">
        <a href="<?= BASE_URL ?>/" style="color:#C9A84C">&larr; Back to Store</a>
    </p>
</div>
</body>
</html>
