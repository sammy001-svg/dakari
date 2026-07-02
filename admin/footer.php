<?php
require_once __DIR__ . '/includes/admin_init.php';
$admin_page_title = 'Footer';
$admin_active     = 'footer';

/* ── Save ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {

    // Company links repeater → JSON
    $co_labels = $_POST['co_label'] ?? [];
    $co_urls   = $_POST['co_url']   ?? [];
    $co = [];
    foreach ($co_labels as $i => $lbl) {
        if (trim($lbl)) $co[] = ['label' => trim($lbl), 'url' => trim($co_urls[$i] ?? '#')];
    }
    $cj = json_encode($co, JSON_UNESCAPED_UNICODE);
    query('INSERT INTO settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?',
          'sss', 'footer_company_links', $cj, $cj);

    // Customer Care links repeater → JSON
    $cc_labels = $_POST['cc_label'] ?? [];
    $cc_urls   = $_POST['cc_url']   ?? [];
    $cc = [];
    foreach ($cc_labels as $i => $lbl) {
        if (trim($lbl)) $cc[] = ['label' => trim($lbl), 'url' => trim($cc_urls[$i] ?? '#')];
    }
    $ccj = json_encode($cc, JSON_UNESCAPED_UNICODE);
    query('INSERT INTO settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?',
          'sss', 'footer_care_links', $ccj, $ccj);

    // Legal + payment
    $simple = ['footer_legal_privacy','footer_legal_terms','footer_legal_cookies','footer_payment_methods'];
    foreach ($simple as $key) {
        $val = trim($_POST[$key] ?? '');
        query('INSERT INTO settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?',
              'sss', $key, $val, $val);
    }

    flash('success', 'Footer saved.');
    header('Location: footer.php'); exit;
}

/* ── Load ── */
$rows = fetchAll('SELECT setting_key,setting_value FROM settings WHERE setting_key LIKE "footer_%" OR setting_key IN ("site_name","footer_about","social_instagram","social_facebook","site_address","site_phone","site_email","hours_weekday")');
$s = [];
foreach ($rows as $r) $s[$r['setting_key']] = $r['setting_value'];
$g = fn($k, $d='') => $s[$k] ?? $d;

$company_links = json_decode($g('footer_company_links', ''), true) ?: [
    ['label'=>'About Us',      'url'=>'/about.php'],
    ['label'=>'Our Services',  'url'=>'/services.php'],
    ['label'=>'Contact Us',    'url'=>'/contact.php'],
    ['label'=>'Careers',       'url'=>'#'],
    ['label'=>'Press & Media', 'url'=>'#'],
    ['label'=>'Sustainability', 'url'=>'#'],
];
$care_links = json_decode($g('footer_care_links', ''), true) ?: [
    ['label'=>'My Account',          'url'=>'/client/dashboard.php'],
    ['label'=>'Track My Order',      'url'=>'/client/orders.php'],
    ['label'=>'Shopping Cart',       'url'=>'/cart.php'],
    ['label'=>'Returns & Exchanges', 'url'=>'#'],
    ['label'=>'Shipping Policy',     'url'=>'#'],
    ['label'=>'FAQ',                 'url'=>'/faq.php'],
];

$all_badges   = ['visa'=>'VISA','mastercard'=>'Mastercard','mpesa'=>'M-Pesa','paypal'=>'PayPal','amex'=>'Amex','stripe'=>'Stripe'];
$active_badges = array_filter(explode(',', $g('footer_payment_methods', 'visa,mastercard,mpesa,paypal')));

$csrf = generate_csrf();
include __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Footer</h1>
        <p class="admin-page-sub">Edit navigation links, legal URLs, and payment badges shown in the site footer.</p>
    </div>
    <div style="display:flex;gap:10px">
        <a href="<?= BASE_URL ?>/" target="_blank"
           style="padding:9px 18px;border:1px solid #ccc;border-radius:4px;color:#555;text-decoration:none;font-size:.85rem">
            ↗ Preview
        </a>
        <button type="submit" form="footer-form"
                style="padding:10px 28px;background:#1B4332;color:#fff;border:none;border-radius:4px;font-size:.88rem;font-weight:600;cursor:pointer">
            ✓ Save Footer
        </button>
    </div>
</div>

<!-- Already-managed notice -->
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:12px 16px;margin-bottom:20px;font-size:.85rem;color:#166534">
    <strong>Note:</strong> About text, social links, address, phone, email and business hours are managed under
    <a href="settings.php" style="color:#1B4332;font-weight:600">Admin → Settings</a>.
</div>

<form id="footer-form" method="POST">
    <?= csrf_field() ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">

        <!-- Company links -->
        <div class="admin-card">
            <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:16px">
                <h3 style="font-family:var(--font-serif);color:var(--green);font-size:1rem;margin:0">Company Column</h3>
                <button type="button" onclick="addLink('co-wrap','co_label','co_url')"
                        style="padding:5px 12px;background:#1B4332;color:#fff;border:none;border-radius:4px;font-size:.78rem;cursor:pointer">+ Add Link</button>
            </div>
            <div id="co-wrap" style="display:flex;flex-direction:column;gap:8px">
                <?php foreach ($company_links as $lnk): ?>
                <div class="link-row" style="display:grid;grid-template-columns:1fr 1fr auto;gap:8px;align-items:center">
                    <input type="text" name="co_label[]" class="form-control" value="<?= e($lnk['label']) ?>" placeholder="Label" style="font-size:.82rem">
                    <input type="text" name="co_url[]"   class="form-control" value="<?= e($lnk['url']) ?>"   placeholder="/page.php or https://…" style="font-size:.82rem">
                    <button type="button" onclick="this.closest('.link-row').remove()"
                            style="padding:6px 10px;border:1px solid #fca5a5;color:#dc2626;background:#fff;border-radius:4px;cursor:pointer;font-size:.8rem">✕</button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Customer Care links -->
        <div class="admin-card">
            <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:16px">
                <h3 style="font-family:var(--font-serif);color:var(--green);font-size:1rem;margin:0">Customer Care Column</h3>
                <button type="button" onclick="addLink('cc-wrap','cc_label','cc_url')"
                        style="padding:5px 12px;background:#1B4332;color:#fff;border:none;border-radius:4px;font-size:.78rem;cursor:pointer">+ Add Link</button>
            </div>
            <div id="cc-wrap" style="display:flex;flex-direction:column;gap:8px">
                <?php foreach ($care_links as $lnk): ?>
                <div class="link-row" style="display:grid;grid-template-columns:1fr 1fr auto;gap:8px;align-items:center">
                    <input type="text" name="cc_label[]" class="form-control" value="<?= e($lnk['label']) ?>" placeholder="Label" style="font-size:.82rem">
                    <input type="text" name="cc_url[]"   class="form-control" value="<?= e($lnk['url']) ?>"   placeholder="/page.php or https://…" style="font-size:.82rem">
                    <button type="button" onclick="this.closest('.link-row').remove()"
                            style="padding:6px 10px;border:1px solid #fca5a5;color:#dc2626;background:#fff;border-radius:4px;cursor:pointer;font-size:.8rem">✕</button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">

        <!-- Legal links -->
        <div class="admin-card">
            <h3 style="font-family:var(--font-serif);color:var(--green);font-size:1rem;border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:16px">
                Legal Links (bottom bar)
            </h3>
            <div style="display:flex;flex-direction:column;gap:12px">
                <div>
                    <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:4px">Privacy Policy URL</label>
                    <input type="text" name="footer_legal_privacy" class="form-control"
                           value="<?= e($g('footer_legal_privacy','#')) ?>" placeholder="/privacy.php or https://…" style="width:100%">
                </div>
                <div>
                    <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:4px">Terms of Service URL</label>
                    <input type="text" name="footer_legal_terms" class="form-control"
                           value="<?= e($g('footer_legal_terms','#')) ?>" placeholder="/terms.php or https://…" style="width:100%">
                </div>
                <div>
                    <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:4px">Cookie Policy URL</label>
                    <input type="text" name="footer_legal_cookies" class="form-control"
                           value="<?= e($g('footer_legal_cookies','#')) ?>" placeholder="/cookies.php or https://…" style="width:100%">
                </div>
            </div>
        </div>

        <!-- Payment badges -->
        <div class="admin-card">
            <h3 style="font-family:var(--font-serif);color:var(--green);font-size:1rem;border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:16px">
                Payment Badges
            </h3>
            <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:14px">Tick the payment methods to display in the footer.</p>
            <div style="display:flex;flex-direction:column;gap:10px" id="badge-checks">
                <?php foreach ($all_badges as $key => $label): ?>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:.88rem">
                    <input type="checkbox" class="badge-cb" value="<?= $key ?>"
                           <?= in_array($key, $active_badges) ? 'checked' : '' ?>
                           style="accent-color:#1B4332;width:16px;height:16px">
                    <span class="payment-badge" style="font-size:.75rem;padding:3px 10px"><?= $label ?></span>
                </label>
                <?php endforeach; ?>
            </div>
            <!-- Collect checked values into one hidden field -->
            <input type="hidden" name="footer_payment_methods" id="payment-methods-val"
                   value="<?= e($g('footer_payment_methods','visa,mastercard,mpesa,paypal')) ?>">
        </div>
    </div>

    <!-- Bottom save bar -->
    <div style="background:#1B4332;border-radius:6px;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;margin-bottom:32px">
        <span style="color:rgba(255,255,255,.8);font-size:.88rem">Changes appear on all pages immediately after saving.</span>
        <button type="submit"
                style="padding:10px 32px;background:#C9A84C;color:#fff;border:none;border-radius:4px;font-size:.88rem;font-weight:700;cursor:pointer">
            ✓ Save Footer
        </button>
    </div>
</form>

<script>
function addLink(wrapId, labelName, urlName) {
    const wrap = document.getElementById(wrapId);
    const div  = document.createElement('div');
    div.className = 'link-row';
    div.style.cssText = 'display:grid;grid-template-columns:1fr 1fr auto;gap:8px;align-items:center';
    div.innerHTML = `
        <input type="text" name="${labelName}[]" class="form-control" placeholder="Label" style="font-size:.82rem">
        <input type="text" name="${urlName}[]"   class="form-control" placeholder="/page.php or https://…" style="font-size:.82rem">
        <button type="button" onclick="this.closest('.link-row').remove()"
                style="padding:6px 10px;border:1px solid #fca5a5;color:#dc2626;background:#fff;border-radius:4px;cursor:pointer;font-size:.8rem">✕</button>`;
    wrap.appendChild(div);
}

// Collect checked payment badges into the hidden input before submit
document.getElementById('footer-form').addEventListener('submit', function() {
    const checked = [...document.querySelectorAll('.badge-cb:checked')].map(cb => cb.value);
    document.getElementById('payment-methods-val').value = checked.join(',');
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
