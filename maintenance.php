<?php
// This page is exempt from maintenance redirect (see init.php)
require_once __DIR__ . '/includes/init.php';
http_response_code(503);
header('Retry-After: 3600');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance — Dakari</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --green: #1B4332; --gold: #C9A84C; --text: #1a1a1a; --muted: #6b7280; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f9f7f4;
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .wrap {
            max-width: 520px;
            width: 100%;
            text-align: center;
        }
        .logo-bar {
            margin-bottom: 40px;
        }
        .logo-bar a {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--green);
            text-decoration: none;
            letter-spacing: .02em;
        }
        .icon-wrap {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--green);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 28px;
        }
        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--green);
            margin-bottom: 16px;
            line-height: 1.2;
        }
        .subtitle {
            font-size: 1.05rem;
            color: var(--muted);
            line-height: 1.6;
            margin-bottom: 36px;
        }
        .divider {
            width: 60px;
            height: 3px;
            background: var(--gold);
            margin: 0 auto 36px;
            border-radius: 2px;
        }
        .info-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 28px 32px;
            margin-bottom: 28px;
        }
        .info-card p {
            font-size: .95rem;
            color: var(--muted);
            line-height: 1.6;
        }
        .info-card a {
            color: var(--green);
            font-weight: 600;
            text-decoration: none;
        }
        .info-card a:hover { text-decoration: underline; }
        .badge {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            font-size: .78rem;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 24px;
        }
        footer {
            margin-top: 40px;
            font-size: .82rem;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="logo-bar">
            <a href="<?= BASE_URL ?>/"><?= e(setting('site_name', 'Dakari')) ?></a>
        </div>

        <div class="icon-wrap">
            <svg width="36" height="36" fill="none" stroke="#C9A84C" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
        </div>

        <span class="badge">Maintenance in Progress</span>

        <h1>We'll Be Back Soon</h1>

        <div class="divider"></div>

        <p class="subtitle">
            We're currently performing scheduled maintenance to improve your experience.
            The store will be back online shortly.
        </p>

        <div class="info-card">
            <p>
                Need urgent assistance? Reach us at
                <a href="mailto:<?= e(setting('site_email', 'info@dakari.com')) ?>"><?= e(setting('site_email', 'info@dakari.com')) ?></a>
                <?php if (setting('site_phone')): ?>
                or call <strong><?= e(setting('site_phone')) ?></strong>
                <?php endif; ?>
            </p>
        </div>

        <footer>
            &copy; <?= date('Y') ?> <?= e(setting('site_name', 'Dakari')) ?>. All rights reserved.
        </footer>
    </div>
</body>
</html>
