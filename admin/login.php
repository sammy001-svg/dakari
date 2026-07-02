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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            display: flex;
            min-height: 100vh;
            background: #0d1f16;
        }

        /* ── Left panel – image ── */
        .login-image {
            flex: 1;
            position: relative;
            overflow: hidden;
            display: none;
        }
        @media (min-width: 900px) { .login-image { display: block; } }

        .login-image img {
            width: 100%; height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        /* Gradient overlay at bottom for brand text */
        .login-image__overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(10,26,17,.85) 0%, rgba(10,26,17,.15) 50%, transparent 100%);
        }

        .login-image__brand {
            position: absolute;
            bottom: 44px; left: 44px; right: 44px;
        }
        .login-image__brand-name {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 2.2rem; font-weight: 700;
            color: #ffffff; letter-spacing: .02em;
            margin-bottom: 8px;
        }
        .login-image__brand-name span { color: #C9A84C; }
        .login-image__tagline {
            font-size: .82rem; color: rgba(255,255,255,.65);
            letter-spacing: .12em; text-transform: uppercase;
        }

        /* ── Right panel – form ── */
        .login-panel {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
            background: #ffffff;
        }
        @media (min-width: 900px) {
            .login-panel { width: 420px; flex-shrink: 0; }
        }

        .login-inner { width: 100%; max-width: 360px; }

        /* Logo */
        .login-logo {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 40px;
        }
        .login-logo__img {
            height: 36px; width: auto;
        }
        .login-logo__text {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.55rem; font-weight: 700;
            color: #1B4332; letter-spacing: .02em;
        }

        .login-heading {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.6rem; font-weight: 700;
            color: #1B4332; margin-bottom: 6px;
        }
        .login-sub {
            font-size: .82rem; color: #888;
            margin-bottom: 32px; line-height: 1.5;
        }

        /* Alert */
        .login-alert {
            background: #fef2f2; border: 1px solid #fecaca;
            color: #b91c1c; border-radius: 6px;
            padding: 11px 14px; font-size: .83rem;
            margin-bottom: 22px;
        }

        /* Form */
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block; font-size: .78rem;
            font-weight: 600; color: #374151;
            margin-bottom: 6px; letter-spacing: .02em;
        }
        .form-control {
            width: 100%; padding: 11px 14px;
            border: 1.5px solid #d1d5db;
            border-radius: 6px; font-size: .9rem;
            font-family: inherit; color: #111;
            background: #fff;
            transition: border-color .18s, box-shadow .18s;
            outline: none;
        }
        .form-control:focus {
            border-color: #1B4332;
            box-shadow: 0 0 0 3px rgba(27,67,50,.12);
        }

        .btn-signin {
            width: 100%; padding: 13px;
            background: #1B4332; color: #fff;
            border: none; border-radius: 6px;
            font-size: .9rem; font-weight: 600;
            font-family: inherit; cursor: pointer;
            letter-spacing: .04em;
            transition: background .18s;
            margin-top: 8px;
        }
        .btn-signin:hover { background: #132d22; }

        .login-back {
            text-align: center; margin-top: 24px;
            font-size: .8rem; color: #999;
        }
        .login-back a { color: #C9A84C; font-weight: 500; text-decoration: none; }
        .login-back a:hover { text-decoration: underline; }

        .login-divider {
            border: none; border-top: 1px solid #e5e7eb;
            margin: 28px 0 20px;
        }
        .login-footer {
            font-size: .72rem; color: #bbb;
            text-align: center; line-height: 1.6;
        }
    </style>
</head>
<body>

    <!-- Image panel -->
    <div class="login-image">
        <img src="<?= BASE_URL ?>/assets/images/login-bg.png" alt="Dakari">
        <div class="login-image__overlay"></div>
        <div class="login-image__brand">
            <div class="login-image__brand-name">Dakari <span>Store</span></div>
            <p class="login-image__tagline">Premium Quality · Curated with Care</p>
        </div>
    </div>

    <!-- Form panel -->
    <div class="login-panel">
        <div class="login-inner">

            <div class="login-logo">
                <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="Dakari" class="login-logo__img">
                <span class="login-logo__text">Dakari</span>
            </div>

            <h1 class="login-heading">Welcome back</h1>
            <p class="login-sub">Sign in to your admin panel to manage your store.</p>

            <?php if ($error): ?>
            <div class="login-alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                <div class="form-group">
                    <label class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control"
                           required autofocus
                           placeholder="admin@dakari.com"
                           autocomplete="email">
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control"
                           required
                           placeholder="••••••••"
                           autocomplete="current-password">
                </div>

                <button type="submit" class="btn-signin">Sign In to Admin Panel</button>
            </form>

            <hr class="login-divider">

            <p class="login-back">
                <a href="<?= BASE_URL ?>/">&larr; Back to store</a>
            </p>

            <p class="login-footer">
                Dakari Admin Panel &mdash; Authorised access only
            </p>

        </div>
    </div>

</body>
</html>
