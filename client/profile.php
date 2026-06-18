<?php
require_once __DIR__ . '/includes/client_init.php';
$page_title    = 'My Profile';
$client_active = 'profile';
$extra_css     = '<link rel="stylesheet" href="' . BASE_URL . '/assets/css/client.css">';
$user          = current_user();
$errors = []; $success = false;

if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'profile') {
        $first = trim($_POST['first_name']??'');
        $last  = trim($_POST['last_name']??'');
        $phone = trim($_POST['phone']??'');
        if (!$first || !$last) { $errors[] = 'Name is required.'; }
        else {
            query('UPDATE users SET first_name=?,last_name=?,phone=? WHERE id=?','sssi',$first,$last,$phone,$user['id']);
            $_SESSION['user_name'] = $first;
            $success = 'Profile updated.';
            $user = current_user();
        }
    } elseif ($action === 'password') {
        $current = $_POST['current_password']??'';
        $new     = $_POST['new_password']??'';
        $confirm = $_POST['confirm_password']??'';
        if (!password_verify($current,$user['password'])) { $errors[] = 'Current password is incorrect.'; }
        elseif (strlen($new)<8) { $errors[] = 'New password must be at least 8 characters.'; }
        elseif ($new!==$confirm) { $errors[] = 'Passwords do not match.'; }
        else {
            $hash = password_hash($new,PASSWORD_BCRYPT,['cost'=>12]);
            query('UPDATE users SET password=? WHERE id=?','si',$hash,$user['id']);
            $success = 'Password changed successfully.';
        }
    }
}
include __DIR__ . '/includes/client_header.php';
?>
<h1 class="client-page-title">My Profile</h1>

<?php foreach ($errors as $e): ?><div class="alert alert-error"><?= e($e) ?></div><?php endforeach; ?>
<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
    <div class="card">
        <div class="card-header"><span class="card-title">Personal Information</span></div>
        <div class="card-body">
            <form method="post" action="profile.php">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="profile">
                <div class="form-group" style="margin-bottom:14px"><label class="form-label">First Name</label><input type="text" name="first_name" class="form-control" value="<?= e($user['first_name']) ?>" required></div>
                <div class="form-group" style="margin-bottom:14px"><label class="form-label">Last Name</label><input type="text" name="last_name" class="form-control" value="<?= e($user['last_name']) ?>" required></div>
                <div class="form-group" style="margin-bottom:14px"><label class="form-label">Email</label><input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled style="background:var(--off-white)"></div>
                <div class="form-group" style="margin-bottom:20px"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control" value="<?= e($user['phone']??'') ?>"></div>
                <button type="submit" class="btn btn-green">Save Changes</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><span class="card-title">Change Password</span></div>
        <div class="card-body">
            <form method="post" action="profile.php">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="password">
                <div class="form-group" style="margin-bottom:14px"><label class="form-label">Current Password</label><input type="password" name="current_password" class="form-control" required></div>
                <div class="form-group" style="margin-bottom:14px"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control" required></div>
                <div class="form-group" style="margin-bottom:20px"><label class="form-label">Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required></div>
                <button type="submit" class="btn btn-outline-green">Update Password</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/client_footer.php'; ?>
