<?php
require_once __DIR__ . '/includes/init.php';
if (is_logged_in()) { header('Location: ' . BASE_URL . '/'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name'  => trim($_POST['last_name'] ?? ''),
        'email'      => trim($_POST['email'] ?? ''),
        'phone'      => trim($_POST['phone'] ?? ''),
        'password'   => $_POST['password'] ?? '',
        'confirm'    => $_POST['confirm'] ?? '',
    ];
    if (!$data['first_name'])  $errors[] = 'First name is required.';
    if (!$data['last_name'])   $errors[] = 'Last name is required.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email address required.';
    if (strlen($data['password']) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($data['password'] !== $data['confirm']) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $result = register_user($data);
        if ($result['success']) {
            flash('success', 'Welcome to Dakari! Your account has been created.');
            header('Location: ' . BASE_URL . '/client/dashboard.php'); exit;
        } else {
            $errors[] = $result['message'];
        }
    }
}

$page_title = 'Create Account';
include __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card" style="max-width:520px">
        <div class="auth-logo">Dakari</div>
        <p class="auth-tagline">Create your account to start shopping</p>

        <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="post" action="register.php">
            <?= csrf_field() ?>
            <div class="form-grid" style="margin-bottom:16px">
                <div class="form-group">
                    <label class="form-label">First Name <span class="required">*</span></label>
                    <input type="text" name="first_name" class="form-control" value="<?= e($_POST['first_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name <span class="required">*</span></label>
                    <input type="text" name="last_name" class="form-control" value="<?= e($_POST['last_name'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:16px">
                <label class="form-label">Email Address <span class="required">*</span></label>
                <input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group" style="margin-bottom:16px">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-control" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="+254 700 000 000">
            </div>
            <div class="form-group" style="margin-bottom:16px">
                <label class="form-label">Password <span class="required">*</span></label>
                <input type="password" name="password" class="form-control" placeholder="At least 8 characters" required>
            </div>
            <div class="form-group" style="margin-bottom:28px">
                <label class="form-label">Confirm Password <span class="required">*</span></label>
                <input type="password" name="confirm" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-green btn-block btn-lg">Create Account</button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
