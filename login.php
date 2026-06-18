<?php
require_once __DIR__ . '/includes/init.php';
if (is_logged_in()) { header('Location: ' . BASE_URL . '/'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) {
        $errors[] = 'Email and password are required.';
    } else {
        $result = login_user($email, $password);
        if ($result['success']) {
            $redirect = $_SESSION['redirect_after_login'] ?? null;
            unset($_SESSION['redirect_after_login']);
            if ($result['role'] === 'admin') {
                header('Location: ' . BASE_URL . '/admin/index.php'); exit;
            }
            header('Location: ' . ($redirect ?? BASE_URL . '/client/dashboard.php')); exit;
        } else {
            $errors[] = $result['message'];
        }
    }
}

$page_title = 'Login';
include __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">Dakari</div>
        <p class="auth-tagline">Welcome back — sign in to your account</p>

        <?php foreach ($errors as $e): ?>
        <div class="alert alert-error"><?= e($e) ?></div>
        <?php endforeach; ?>

        <form method="post" action="login.php">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom:16px">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@example.com" required autofocus>
            </div>
            <div class="form-group" style="margin-bottom:24px">
                <label class="form-label" style="display:flex;justify-content:space-between">
                    Password
                    <a href="#" style="font-size:.82rem;color:var(--gold)">Forgot password?</a>
                </label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-green btn-block btn-lg">Sign In</button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="register.php">Create one</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
