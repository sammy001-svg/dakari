<?php
require_once __DIR__ . '/includes/client_init.php';
$page_title    = 'My Profile';
$client_active = 'profile';
$extra_css     = '<link rel="stylesheet" href="' . BASE_URL . '/assets/css/client.css">';
$user          = current_user();
$errors = []; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'profile') {
        $first = trim($_POST['first_name'] ?? '');
        $last  = trim($_POST['last_name']  ?? '');
        $phone = trim($_POST['phone']      ?? '');
        if (!$first || !$last) {
            $errors[] = 'First and last name are required.';
        } else {
            query('UPDATE users SET first_name=?,last_name=?,phone=? WHERE id=?','sssi',$first,$last,$phone,$user['id']);
            $_SESSION['user_name'] = $first;
            $success = 'Profile updated successfully.';
            $user    = current_user();
        }

    } elseif ($action === 'password') {
        $current = $_POST['current_password']  ?? '';
        $new     = $_POST['new_password']      ?? '';
        $confirm = $_POST['confirm_password']  ?? '';
        if (!password_verify($current, $user['password'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($new) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        } elseif ($new !== $confirm) {
            $errors[] = 'Passwords do not match.';
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
            query('UPDATE users SET password=? WHERE id=?','si',$hash,$user['id']);
            $success = 'Password changed successfully.';
        }
    }
}
include __DIR__ . '/includes/client_header.php';
?>

<h1 class="client-page-title">My Profile</h1>

<?php foreach ($errors as $err): ?>
<div class="alert alert-error"><?= e($err) ?></div>
<?php endforeach; ?>
<?php if ($success): ?>
<div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<div class="profile-grid">
    <!-- Personal info -->
    <div class="c-card">
        <div class="c-card__header">
            <span class="c-card__title">Personal Information</span>
        </div>
        <div class="c-card__body">
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="profile">
                <div class="form-group" style="margin-bottom:14px">
                    <label class="form-label">First Name <span class="required">*</span></label>
                    <input type="text" name="first_name" class="form-control" value="<?= e($user['first_name']) ?>" required>
                </div>
                <div class="form-group" style="margin-bottom:14px">
                    <label class="form-label">Last Name <span class="required">*</span></label>
                    <input type="text" name="last_name" class="form-control" value="<?= e($user['last_name']) ?>" required>
                </div>
                <div class="form-group" style="margin-bottom:14px">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled style="background:var(--off-white);cursor:not-allowed">
                    <span class="form-hint">Email cannot be changed. Contact support if needed.</span>
                </div>
                <div class="form-group" style="margin-bottom:22px">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-green">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Change password -->
    <div class="c-card">
        <div class="c-card__header">
            <span class="c-card__title">Change Password</span>
        </div>
        <div class="c-card__body">
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="password">
                <div class="form-group" style="margin-bottom:14px">
                    <label class="form-label">Current Password <span class="required">*</span></label>
                    <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
                </div>
                <div class="form-group" style="margin-bottom:14px">
                    <label class="form-label">New Password <span class="required">*</span></label>
                    <input type="password" name="new_password" class="form-control" required minlength="8" autocomplete="new-password">
                    <span class="form-hint">Minimum 8 characters.</span>
                </div>
                <div class="form-group" style="margin-bottom:22px">
                    <label class="form-label">Confirm New Password <span class="required">*</span></label>
                    <input type="password" name="confirm_password" class="form-control" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-outline-green">Update Password</button>
            </form>
        </div>
    </div>
</div>

<!-- Account info card -->
<div class="c-card">
    <div class="c-card__header"><span class="c-card__title">Account Details</span></div>
    <div class="c-card__body">
        <div style="display:flex;gap:32px;flex-wrap:wrap;font-size:.88rem">
            <div>
                <div style="color:var(--text-muted);font-size:.76rem;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px">Member Since</div>
                <strong><?= date('F Y', strtotime($user['created_at'] ?? 'now')) ?></strong>
            </div>
            <div>
                <div style="color:var(--text-muted);font-size:.76rem;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px">Account Type</div>
                <strong style="text-transform:capitalize"><?= e($user['role'] ?? 'Client') ?></strong>
            </div>
            <div>
                <div style="color:var(--text-muted);font-size:.76rem;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px">Status</div>
                <span class="status-badge status-active">Active</span>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/client_footer.php'; ?>
