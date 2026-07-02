<?php
require_once __DIR__ . '/includes/admin_init.php';
$admin_page_title = 'Why Dakari Section';
$admin_active     = 'why';

/* ── Save ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {

    // Pillars repeater → JSON
    $p_titles = $_POST['pillar_title'] ?? [];
    $p_descs  = $_POST['pillar_desc']  ?? [];
    $pillars  = [];
    foreach ($p_titles as $i => $t) {
        if (trim($t)) $pillars[] = ['title' => trim($t), 'desc' => trim($p_descs[$i] ?? '')];
    }
    $pj = json_encode($pillars, JSON_UNESCAPED_UNICODE);
    query('INSERT INTO settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?',
          'sss', 'why_pillars', $pj, $pj);

    // Simple text fields
    $simple = [
        'why_eyebrow', 'why_heading', 'why_heading_accent', 'why_body',
        'why_btn_text', 'why_btn_url',
        'why_badge',
        'why_stat1_num', 'why_stat1_label',
        'why_stat2_num', 'why_stat2_label',
        'why_stat3_num', 'why_stat3_label',
    ];
    foreach ($simple as $key) {
        $val = trim($_POST[$key] ?? '');
        query('INSERT INTO settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?',
              'sss', $key, $val, $val);
    }

    flash('success', 'Why Dakari section saved.');
    header('Location: why.php'); exit;
}

/* ── Load ── */
$rows = fetchAll('SELECT setting_key,setting_value FROM settings WHERE setting_key LIKE "why_%"');
$s = [];
foreach ($rows as $r) $s[$r['setting_key']] = $r['setting_value'];
$g = fn($k, $d = '') => $s[$k] ?? $d;

$pillars = json_decode($g('why_pillars', ''), true) ?: [
    ['title' => 'Quality Assurance', 'desc' => 'Every product passes our rigorous quality inspection before listing.'],
    ['title' => 'Ethical Sourcing',  'desc' => 'We partner only with responsible, certified suppliers.'],
    ['title' => 'Customer First',    'desc' => 'Our team is available 24/7 to ensure your satisfaction.'],
    ['title' => 'Secure Shopping',   'desc' => 'End-to-end encryption and secure payment processing.'],
];

$csrf = generate_csrf();
include __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Why Dakari Section</h1>
        <p class="admin-page-sub">Edit the "Why Dakari" section displayed on the home page.</p>
    </div>
    <div style="display:flex;gap:10px">
        <a href="<?= BASE_URL ?>/" target="_blank"
           style="padding:9px 18px;border:1px solid #ccc;border-radius:4px;color:#555;text-decoration:none;font-size:.85rem">
            ↗ Preview
        </a>
        <button type="submit" form="why-form"
                style="padding:10px 28px;background:#1B4332;color:#fff;border:none;border-radius:4px;font-size:.88rem;font-weight:600;cursor:pointer">
            ✓ Save Changes
        </button>
    </div>
</div>

<form id="why-form" method="POST">
    <?= csrf_field() ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">

        <!-- Left: Text content -->
        <div style="display:flex;flex-direction:column;gap:20px">

            <!-- Heading -->
            <div class="admin-card">
                <h3 style="font-family:var(--font-serif);color:var(--green);font-size:1rem;border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:16px">Heading</h3>
                <div style="display:flex;flex-direction:column;gap:12px">
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:4px">Eyebrow Label</label>
                        <input type="text" name="why_eyebrow" class="form-control"
                               value="<?= e($g('why_eyebrow', 'Why Dakari')) ?>" placeholder="Why Dakari">
                        <p style="font-size:.72rem;color:var(--text-muted);margin-top:3px">Small uppercase label above the heading</p>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                        <div>
                            <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:4px">Heading</label>
                            <input type="text" name="why_heading" class="form-control"
                                   value="<?= e($g('why_heading', 'A Brand Built on')) ?>" placeholder="A Brand Built on">
                        </div>
                        <div>
                            <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:4px">Accent Word <span style="color:var(--gold)">(gold)</span></label>
                            <input type="text" name="why_heading_accent" class="form-control"
                                   value="<?= e($g('why_heading_accent', 'Excellence')) ?>" placeholder="Excellence">
                        </div>
                    </div>
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:4px">Body Paragraph</label>
                        <textarea name="why_body" class="form-control" rows="4"
                                  style="resize:vertical"><?= e($g('why_body', 'Since our founding, Dakari has stood at the intersection of quality and style. Every product in our collection is carefully sourced, quality-tested, and curated to reflect the premium standards our customers expect.')) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- CTA Button -->
            <div class="admin-card">
                <h3 style="font-family:var(--font-serif);color:var(--green);font-size:1rem;border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:16px">Call-to-Action Button</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:4px">Button Text</label>
                        <input type="text" name="why_btn_text" class="form-control"
                               value="<?= e($g('why_btn_text', 'Learn Our Story')) ?>" placeholder="Learn Our Story">
                    </div>
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:4px">Button URL</label>
                        <input type="text" name="why_btn_url" class="form-control"
                               value="<?= e($g('why_btn_url', '/about.php')) ?>" placeholder="/about.php">
                    </div>
                </div>
            </div>

        </div>

        <!-- Right: Pillars + Visual card -->
        <div style="display:flex;flex-direction:column;gap:20px">

            <!-- Visual card stats -->
            <div class="admin-card">
                <h3 style="font-family:var(--font-serif);color:var(--green);font-size:1rem;border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:16px">Stats Card (right side)</h3>
                <div style="display:flex;flex-direction:column;gap:14px">
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:4px">Badge Text</label>
                        <input type="text" name="why_badge" class="form-control"
                               value="<?= e($g('why_badge', 'Est. 2020')) ?>" placeholder="Est. 2020">
                    </div>
                    <?php
                    $stat_defaults = [
                        1 => ['12,000+', 'Customers Served'],
                        2 => ['4.9 ★',   'Average Rating'],
                        3 => ['99%',      'Satisfaction Rate'],
                    ];
                    foreach ($stat_defaults as $i => [$dn, $dl]): ?>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding-top:10px;border-top:1px solid var(--border)">
                        <div>
                            <label style="display:block;font-size:.78rem;color:var(--text-muted);margin-bottom:4px">Stat <?= $i ?> — Number</label>
                            <input type="text" name="why_stat<?= $i ?>_num" class="form-control"
                                   value="<?= e($g("why_stat{$i}_num", $dn)) ?>" placeholder="<?= $dn ?>">
                        </div>
                        <div>
                            <label style="display:block;font-size:.78rem;color:var(--text-muted);margin-bottom:4px">Stat <?= $i ?> — Label</label>
                            <input type="text" name="why_stat<?= $i ?>_label" class="form-control"
                                   value="<?= e($g("why_stat{$i}_label", $dl)) ?>" placeholder="<?= $dl ?>">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- Pillars full-width -->
    <div class="admin-card" style="margin-bottom:20px">
        <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:16px">
            <div>
                <h3 style="font-family:var(--font-serif);color:var(--green);font-size:1rem;margin:0">Feature Pillars</h3>
                <p style="font-size:.78rem;color:var(--text-muted);margin-top:4px">The bullet-point highlights shown below the body text</p>
            </div>
            <button type="button" onclick="addPillar()"
                    style="padding:5px 14px;background:#1B4332;color:#fff;border:none;border-radius:4px;font-size:.78rem;cursor:pointer">+ Add Pillar</button>
        </div>
        <div id="pillars-wrap" style="display:flex;flex-direction:column;gap:10px">
            <?php foreach ($pillars as $p): ?>
            <div class="pillar-row" style="display:grid;grid-template-columns:1fr 2fr auto;gap:10px;align-items:start;padding:12px;background:#fafaf8;border:1px solid var(--border);border-radius:6px">
                <div>
                    <label style="display:block;font-size:.75rem;font-weight:600;color:var(--text-muted);margin-bottom:4px">Title</label>
                    <input type="text" name="pillar_title[]" class="form-control" value="<?= e($p['title']) ?>" placeholder="Pillar title" style="font-size:.85rem">
                </div>
                <div>
                    <label style="display:block;font-size:.75rem;font-weight:600;color:var(--text-muted);margin-bottom:4px">Description</label>
                    <input type="text" name="pillar_desc[]" class="form-control" value="<?= e($p['desc']) ?>" placeholder="Short description" style="font-size:.85rem">
                </div>
                <div style="padding-top:22px">
                    <button type="button" onclick="this.closest('.pillar-row').remove()"
                            style="padding:7px 11px;border:1px solid #fca5a5;color:#dc2626;background:#fff;border-radius:4px;cursor:pointer;font-size:.8rem">✕</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bottom save bar -->
    <div style="background:#1B4332;border-radius:6px;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;margin-bottom:32px">
        <span style="color:rgba(255,255,255,.8);font-size:.88rem">Changes appear on the home page immediately after saving.</span>
        <button type="submit"
                style="padding:10px 32px;background:#C9A84C;color:#fff;border:none;border-radius:4px;font-size:.88rem;font-weight:700;cursor:pointer">
            ✓ Save Changes
        </button>
    </div>
</form>

<script>
function addPillar() {
    const wrap = document.getElementById('pillars-wrap');
    const div  = document.createElement('div');
    div.className = 'pillar-row';
    div.style.cssText = 'display:grid;grid-template-columns:1fr 2fr auto;gap:10px;align-items:start;padding:12px;background:#fafaf8;border:1px solid var(--border);border-radius:6px';
    div.innerHTML = `
        <div>
            <label style="display:block;font-size:.75rem;font-weight:600;color:var(--text-muted);margin-bottom:4px">Title</label>
            <input type="text" name="pillar_title[]" class="form-control" placeholder="Pillar title" style="font-size:.85rem">
        </div>
        <div>
            <label style="display:block;font-size:.75rem;font-weight:600;color:var(--text-muted);margin-bottom:4px">Description</label>
            <input type="text" name="pillar_desc[]" class="form-control" placeholder="Short description" style="font-size:.85rem">
        </div>
        <div style="padding-top:22px">
            <button type="button" onclick="this.closest('.pillar-row').remove()"
                    style="padding:7px 11px;border:1px solid #fca5a5;color:#dc2626;background:#fff;border-radius:4px;cursor:pointer;font-size:.8rem">✕</button>
        </div>`;
    wrap.appendChild(div);
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
