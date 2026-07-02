<?php
require_once __DIR__ . '/includes/admin_init.php';
$admin_page_title = 'About Page';
$admin_active     = 'about';

// Icon map shared with about.php
$about_icons = [
    'shield'  => ['Shield / Trust',       'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
    'star'    => ['Star / Excellence',    'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z'],
    'users'   => ['People / Team',        'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75'],
    'check'   => ['Check / Authentic',    'M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
    'bulb'    => ['Lightbulb / Ideas',    'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 1 1 7.072 0l-.548.547A3.374 3.374 0 0 0 14 18.469V19a2 2 0 1 1-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'],
    'globe'   => ['Globe / Sustainable',  'M3.055 11H5a2 2 0 0 1 2 2v1a2 2 0 0 0 2 2 2 2 0 0 1 2 2v2.945M8 3.935V5.5A2.5 2.5 0 0 0 10.5 8h.5a2 2 0 0 1 2 2 2 2 0 0 0 4 0 2 2 0 0 1 2-2h1.064M15 20.488V18a2 2 0 0 1 2-2h3.064M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
    'heart'   => ['Heart / Passion',      'M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z'],
    'award'   => ['Award / Quality',      'M5 3h14a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2zm7 9v9m-4 0h8'],
    'target'  => ['Target / Mission',     'M22 12h-4m-8 0H2m10-8v4m0 8v4M12 6a6 6 0 1 0 0 12A6 6 0 0 0 12 6z'],
    'leaf'    => ['Leaf / Eco',           'M17 8C8 10 5.9 16.17 3.82 19.95M5 19a7.96 7.96 0 0 0 8-8c0-5.56-5.07-10-5.07-10S3 6.31 3 11c0 3.12 1.12 6 3.07 8'],
];

/* ── Save ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $simple_keys = [
        'about_hero_eyebrow','about_hero_headline','about_hero_sub',
        'about_story_p1','about_story_p2','about_story_p3',
        'about_stat1_num','about_stat1_label',
        'about_stat2_num','about_stat2_label',
        'about_stat3_num','about_stat3_label',
        'about_stat4_num','about_stat4_label',
        'about_mission','about_vision',
        'about_cta_headline','about_cta_sub',
    ];
    foreach ($simple_keys as $key) {
        $val = trim($_POST[$key] ?? '');
        query('INSERT INTO settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?',
              'sss', $key, $val, $val);
    }

    // Values repeater → JSON
    $val_icons  = $_POST['val_icon']  ?? [];
    $val_titles = $_POST['val_title'] ?? [];
    $val_texts  = $_POST['val_text']  ?? [];
    $values_arr = [];
    foreach ($val_titles as $i => $t) {
        if (trim($t)) $values_arr[] = ['icon' => $val_icons[$i] ?? 'star', 'title' => trim($t), 'text' => trim($val_texts[$i] ?? '')];
    }
    $vj = json_encode($values_arr, JSON_UNESCAPED_UNICODE);
    query('INSERT INTO settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?',
          'sss', 'about_values', $vj, $vj);

    // Milestones repeater → JSON
    $ms_years = $_POST['ms_year']    ?? [];
    $ms_qts   = $_POST['ms_quarter'] ?? [];
    $ms_titles= $_POST['ms_title']   ?? [];
    $ms_texts = $_POST['ms_text']    ?? [];
    $ms_arr   = [];
    foreach ($ms_titles as $i => $t) {
        if (trim($t)) $ms_arr[] = ['year' => trim($ms_years[$i] ?? ''), 'quarter' => trim($ms_qts[$i] ?? ''), 'title' => trim($t), 'text' => trim($ms_texts[$i] ?? '')];
    }
    $mj = json_encode($ms_arr, JSON_UNESCAPED_UNICODE);
    query('INSERT INTO settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?',
          'sss', 'about_milestones', $mj, $mj);

    flash('success', 'About page saved.');
    header('Location: about.php'); exit;
}

/* ── Load current values ── */
$rows = fetchAll('SELECT setting_key,setting_value FROM settings WHERE setting_key LIKE "about_%"');
$s = [];
foreach ($rows as $r) $s[$r['setting_key']] = $r['setting_value'];
$g = fn($k, $d='') => $s[$k] ?? $d;

$values    = json_decode($g('about_values',    '[]'), true) ?: [
    ['icon'=>'shield','title'=>'Integrity',      'text'=>'We operate with complete transparency and honesty in every interaction — with customers, partners, and each other.'],
    ['icon'=>'star',  'title'=>'Excellence',     'text'=>'We never settle for "good enough". Every product, every interaction must be exceptional and exceed expectations.'],
    ['icon'=>'users', 'title'=>'Customer First', 'text'=>'Our customers are the centre of every decision we make. Their satisfaction is our primary measure of success.'],
    ['icon'=>'check', 'title'=>'Authenticity',   'text'=>'We stock only genuine, verified products from trusted suppliers. No counterfeits, no compromises — ever.'],
    ['icon'=>'bulb',  'title'=>'Innovation',     'text'=>'We continuously improve our platform, processes, and product selection to deliver a better experience.'],
    ['icon'=>'globe', 'title'=>'Sustainability', 'text'=>'We are committed to responsible sourcing, minimal environmental footprint, and giving back to our community.'],
];
$milestones = json_decode($g('about_milestones', '[]'), true) ?: [
    ['year'=>'2020','quarter'=>'Q1','title'=>'Company Founded',        'text'=>'Dakari was incorporated in Nairobi with a vision to bring premium products to East Africa.'],
    ['year'=>'2020','quarter'=>'Q4','title'=>'First 500 Customers',    'text'=>'We hit our first major milestone — 500 satisfied customers and a 4.8-star average rating.'],
    ['year'=>'2021','quarter'=>'Q2','title'=>'Influencer Programme',   'text'=>'Launched our brand ambassador programme, partnering with top Kenyan content creators.'],
    ['year'=>'2022','quarter'=>'Q1','title'=>'Online Platform Launch', 'text'=>'Rolled out our full e-commerce platform with cart, wishlist, and client portal.'],
    ['year'=>'2022','quarter'=>'Q3','title'=>'5,000+ Customers',       'text'=>'Surpassed 5,000 customers and expanded our product catalogue to 300+ SKUs.'],
    ['year'=>'2023','quarter'=>'Q2','title'=>'Regional Expansion',     'text'=>'Extended delivery coverage to Uganda, Tanzania, and Rwanda.'],
    ['year'=>'2024','quarter'=>'Q1','title'=>'10,000+ Orders',         'text'=>'Celebrated over 10,000 fulfilled orders with a 99% customer satisfaction rate.'],
    ['year'=>'2025','quarter'=>'Q3','title'=>'Platform Redesign',      'text'=>'Launched our new corporate platform with advanced inventory, coupons, and reviews.'],
];

// Build icon options HTML for JS template
$icon_opts = '';
foreach ($about_icons as $key => [$label]) $icon_opts .= "<option value=\"{$key}\">{$label}</option>\n";

$csrf = generate_csrf();
include __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">About Page</h1>
        <p class="admin-page-sub">Edit all content shown on the public About Us page.</p>
    </div>
    <div style="display:flex;gap:10px">
        <a href="<?= BASE_URL ?>/about.php" target="_blank"
           style="padding:9px 18px;border:1px solid #ccc;border-radius:4px;color:#555;text-decoration:none;font-size:.85rem">
            ↗ Preview
        </a>
        <button type="submit" form="about-form"
                style="padding:10px 28px;background:#1B4332;color:#fff;border:none;border-radius:4px;font-size:.88rem;font-weight:600;cursor:pointer">
            ✓ Save Changes
        </button>
    </div>
</div>

<form id="about-form" method="POST">
    <?= csrf_field() ?>

    <!-- ── Hero ── -->
    <div class="admin-card" style="margin-bottom:20px">
        <h3 style="font-family:var(--font-serif);color:var(--green);font-size:1rem;border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:18px">
            Page Hero (top banner)
        </h3>
        <div style="display:grid;gap:14px">
            <div>
                <label class="form-label" style="font-size:.82rem;font-weight:600">Eyebrow text</label>
                <input type="text" name="about_hero_eyebrow" class="form-control"
                       value="<?= e($g('about_hero_eyebrow','Our Story')) ?>" style="width:100%;margin-top:4px"
                       placeholder="Our Story">
            </div>
            <div>
                <label class="form-label" style="font-size:.82rem;font-weight:600">Headline</label>
                <input type="text" name="about_hero_headline" class="form-control"
                       value="<?= e($g('about_hero_headline','Built on Quality. Driven by Excellence.')) ?>" style="width:100%;margin-top:4px"
                       placeholder="Built on Quality. Driven by Excellence.">
            </div>
            <div>
                <label class="form-label" style="font-size:.82rem;font-weight:600">Sub-headline paragraph</label>
                <textarea name="about_hero_sub" class="form-control" rows="2" style="width:100%;margin-top:4px"
                          placeholder="Dakari was founded with a simple belief…"><?= e($g('about_hero_sub','Dakari was founded with a simple belief: every person deserves access to premium quality products without compromise.')) ?></textarea>
            </div>
        </div>
    </div>

    <!-- ── Our Story ── -->
    <div class="admin-card" style="margin-bottom:20px">
        <h3 style="font-family:var(--font-serif);color:var(--green);font-size:1rem;border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:18px">
            Our Story Section
        </h3>
        <div style="display:grid;gap:14px">
            <div>
                <label class="form-label" style="font-size:.82rem;font-weight:600">Paragraph 1</label>
                <textarea name="about_story_p1" class="form-control" rows="3" style="width:100%;margin-top:4px"><?= e($g('about_story_p1','Founded in 2020 in Nairobi, Kenya, Dakari began as a passion project between a group of entrepreneurs who believed the East African market deserved access to curated, premium-quality products.')) ?></textarea>
            </div>
            <div>
                <label class="form-label" style="font-size:.82rem;font-weight:600">Paragraph 2</label>
                <textarea name="about_story_p2" class="form-control" rows="3" style="width:100%;margin-top:4px"><?= e($g('about_story_p2','What started as a small boutique has grown into one of the region\'s most trusted e-commerce brands, serving over 12,000 satisfied customers across Kenya and beyond. Our name — Dakari — is Swahili-inspired, evoking pride, joy, and excellence.')) ?></textarea>
            </div>
            <div>
                <label class="form-label" style="font-size:.82rem;font-weight:600">Paragraph 3</label>
                <textarea name="about_story_p3" class="form-control" rows="3" style="width:100%;margin-top:4px"><?= e($g('about_story_p3','Today, our team of passionate professionals works tirelessly to source, curate, and deliver products that meet the highest standards of quality, authenticity, and value.')) ?></textarea>
            </div>
        </div>
    </div>

    <!-- ── Stats ── -->
    <div class="admin-card" style="margin-bottom:20px">
        <h3 style="font-family:var(--font-serif);color:var(--green);font-size:1rem;border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:18px">
            Stats Card (4 key numbers)
        </h3>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:16px">
            <?php for ($i = 1; $i <= 4; $i++):
                $dn = ['2020','12K+','500+','50+'];
                $dl = ['Year Founded','Customers','Products','Partners'];
            ?>
            <div style="border:1px solid var(--border);border-radius:var(--radius);padding:14px">
                <p style="font-size:.75rem;font-weight:600;color:var(--text-muted);margin-bottom:10px">Stat <?= $i ?></p>
                <input type="text" name="about_stat<?= $i ?>_num" class="form-control"
                       value="<?= e($g("about_stat{$i}_num", $dn[$i-1])) ?>"
                       placeholder="<?= $dn[$i-1] ?>" style="width:100%;margin-bottom:8px;font-size:.9rem">
                <input type="text" name="about_stat<?= $i ?>_label" class="form-control"
                       value="<?= e($g("about_stat{$i}_label", $dl[$i-1])) ?>"
                       placeholder="<?= $dl[$i-1] ?>" style="width:100%;font-size:.85rem">
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- ── Mission & Vision ── -->
    <div class="admin-card" style="margin-bottom:20px">
        <h3 style="font-family:var(--font-serif);color:var(--green);font-size:1rem;border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:18px">
            Mission &amp; Vision
        </h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
            <div>
                <label class="form-label" style="font-size:.82rem;font-weight:600">Our Mission</label>
                <textarea name="about_mission" class="form-control" rows="5" style="width:100%;margin-top:4px"><?= e($g('about_mission','To democratise access to premium products in East Africa by delivering an exceptional shopping experience built on trust, authenticity, and world-class customer service.')) ?></textarea>
            </div>
            <div>
                <label class="form-label" style="font-size:.82rem;font-weight:600">Our Vision</label>
                <textarea name="about_vision" class="form-control" rows="5" style="width:100%;margin-top:4px"><?= e($g('about_vision','To be the most trusted and beloved premium retail brand in Africa — a name synonymous with quality, integrity, and the celebration of African excellence on the world stage.')) ?></textarea>
            </div>
        </div>
    </div>

    <!-- ── Core Values repeater ── -->
    <div class="admin-card" style="margin-bottom:20px">
        <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:18px">
            <h3 style="font-family:var(--font-serif);color:var(--green);font-size:1rem;margin:0">Core Values</h3>
            <button type="button" onclick="addValue()"
                    style="padding:6px 14px;background:#1B4332;color:#fff;border:none;border-radius:4px;font-size:.82rem;cursor:pointer">
                + Add Value
            </button>
        </div>
        <div id="values-wrap" style="display:flex;flex-direction:column;gap:12px">
            <?php foreach ($values as $v): ?>
            <div class="val-row" style="border:1px solid var(--border);border-radius:var(--radius);padding:14px 16px">
                <div style="display:grid;grid-template-columns:160px 1fr;gap:12px;margin-bottom:10px">
                    <div>
                        <label style="font-size:.75rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Icon</label>
                        <select name="val_icon[]" class="form-control" style="width:100%;font-size:.82rem">
                            <?php foreach ($about_icons as $ik => [$il]): ?>
                            <option value="<?= $ik ?>" <?= ($v['icon'] ?? '') === $ik ? 'selected' : '' ?>><?= $il ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:.75rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Title</label>
                        <input type="text" name="val_title[]" class="form-control"
                               value="<?= e($v['title'] ?? '') ?>" style="width:100%">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr auto;gap:12px;align-items:end">
                    <div>
                        <label style="font-size:.75rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Description</label>
                        <textarea name="val_text[]" class="form-control" rows="2" style="width:100%"><?= e($v['text'] ?? '') ?></textarea>
                    </div>
                    <button type="button" onclick="this.closest('.val-row').remove()"
                            style="padding:8px 12px;background:#fff;border:1px solid #fca5a5;color:#dc2626;border-radius:4px;cursor:pointer;font-size:.8rem;white-space:nowrap;margin-bottom:1px">
                        Remove
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── Milestones repeater ── -->
    <div class="admin-card" style="margin-bottom:20px">
        <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:18px">
            <h3 style="font-family:var(--font-serif);color:var(--green);font-size:1rem;margin:0">Milestones Timeline</h3>
            <button type="button" onclick="addMilestone()"
                    style="padding:6px 14px;background:#1B4332;color:#fff;border:none;border-radius:4px;font-size:.82rem;cursor:pointer">
                + Add Milestone
            </button>
        </div>
        <div id="ms-wrap" style="display:flex;flex-direction:column;gap:12px">
            <?php foreach ($milestones as $m): ?>
            <div class="ms-row" style="border:1px solid var(--border);border-radius:var(--radius);padding:14px 16px">
                <div style="display:grid;grid-template-columns:100px 80px 1fr auto;gap:12px;align-items:end">
                    <div>
                        <label style="font-size:.75rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Year</label>
                        <input type="text" name="ms_year[]" class="form-control"
                               value="<?= e($m['year'] ?? '') ?>" placeholder="2024" style="width:100%">
                    </div>
                    <div>
                        <label style="font-size:.75rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Quarter</label>
                        <input type="text" name="ms_quarter[]" class="form-control"
                               value="<?= e($m['quarter'] ?? '') ?>" placeholder="Q1" style="width:100%">
                    </div>
                    <div>
                        <label style="font-size:.75rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Title</label>
                        <input type="text" name="ms_title[]" class="form-control"
                               value="<?= e($m['title'] ?? '') ?>" placeholder="Milestone title" style="width:100%">
                    </div>
                    <button type="button" onclick="this.closest('.ms-row').remove()"
                            style="padding:8px 12px;background:#fff;border:1px solid #fca5a5;color:#dc2626;border-radius:4px;cursor:pointer;font-size:.8rem;margin-bottom:1px">
                        Remove
                    </button>
                </div>
                <div style="margin-top:10px">
                    <label style="font-size:.75rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Description</label>
                    <textarea name="ms_text[]" class="form-control" rows="2" style="width:100%"><?= e($m['text'] ?? '') ?></textarea>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── CTA ── -->
    <div class="admin-card" style="margin-bottom:20px">
        <h3 style="font-family:var(--font-serif);color:var(--green);font-size:1rem;border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:18px">
            Bottom CTA Banner
        </h3>
        <div style="display:grid;gap:14px">
            <div>
                <label class="form-label" style="font-size:.82rem;font-weight:600">Headline</label>
                <input type="text" name="about_cta_headline" class="form-control"
                       value="<?= e($g('about_cta_headline','Ready to Experience Dakari?')) ?>"
                       style="width:100%;margin-top:4px" placeholder="Ready to Experience Dakari?">
            </div>
            <div>
                <label class="form-label" style="font-size:.82rem;font-weight:600">Sub-text</label>
                <input type="text" name="about_cta_sub" class="form-control"
                       value="<?= e($g('about_cta_sub','Join thousands of satisfied customers who trust us for premium quality.')) ?>"
                       style="width:100%;margin-top:4px">
            </div>
        </div>
    </div>

    <!-- Bottom save bar -->
    <div style="background:#1B4332;border-radius:6px;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;margin-bottom:32px">
        <span style="color:rgba(255,255,255,.8);font-size:.88rem">All changes will appear on the public About page immediately after saving.</span>
        <button type="submit"
                style="padding:10px 32px;background:#C9A84C;color:#fff;border:none;border-radius:4px;font-size:.88rem;font-weight:700;cursor:pointer">
            ✓ Save Changes
        </button>
    </div>
</form>

<script>
const iconOpts = `<?php foreach ($about_icons as $k => [$l]) echo "<option value=\"{$k}\">{$l}</option>"; ?>`;

function addValue() {
    const wrap = document.getElementById('values-wrap');
    const div  = document.createElement('div');
    div.className = 'val-row';
    div.style.cssText = 'border:1px solid #e5e7eb;border-radius:6px;padding:14px 16px';
    div.innerHTML = `
        <div style="display:grid;grid-template-columns:160px 1fr;gap:12px;margin-bottom:10px">
            <div>
                <label style="font-size:.75rem;font-weight:600;color:#6b7280;display:block;margin-bottom:4px">Icon</label>
                <select name="val_icon[]" class="form-control" style="width:100%;font-size:.82rem">${iconOpts}</select>
            </div>
            <div>
                <label style="font-size:.75rem;font-weight:600;color:#6b7280;display:block;margin-bottom:4px">Title</label>
                <input type="text" name="val_title[]" class="form-control" style="width:100%" placeholder="Value title">
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr auto;gap:12px;align-items:end">
            <div>
                <label style="font-size:.75rem;font-weight:600;color:#6b7280;display:block;margin-bottom:4px">Description</label>
                <textarea name="val_text[]" class="form-control" rows="2" style="width:100%" placeholder="Describe this value…"></textarea>
            </div>
            <button type="button" onclick="this.closest('.val-row').remove()"
                    style="padding:8px 12px;background:#fff;border:1px solid #fca5a5;color:#dc2626;border-radius:4px;cursor:pointer;font-size:.8rem;white-space:nowrap;margin-bottom:1px">
                Remove
            </button>
        </div>`;
    wrap.appendChild(div);
}

function addMilestone() {
    const wrap = document.getElementById('ms-wrap');
    const div  = document.createElement('div');
    div.className = 'ms-row';
    div.style.cssText = 'border:1px solid #e5e7eb;border-radius:6px;padding:14px 16px';
    div.innerHTML = `
        <div style="display:grid;grid-template-columns:100px 80px 1fr auto;gap:12px;align-items:end">
            <div>
                <label style="font-size:.75rem;font-weight:600;color:#6b7280;display:block;margin-bottom:4px">Year</label>
                <input type="text" name="ms_year[]" class="form-control" placeholder="2025" style="width:100%">
            </div>
            <div>
                <label style="font-size:.75rem;font-weight:600;color:#6b7280;display:block;margin-bottom:4px">Quarter</label>
                <input type="text" name="ms_quarter[]" class="form-control" placeholder="Q1" style="width:100%">
            </div>
            <div>
                <label style="font-size:.75rem;font-weight:600;color:#6b7280;display:block;margin-bottom:4px">Title</label>
                <input type="text" name="ms_title[]" class="form-control" placeholder="Milestone title" style="width:100%">
            </div>
            <button type="button" onclick="this.closest('.ms-row').remove()"
                    style="padding:8px 12px;background:#fff;border:1px solid #fca5a5;color:#dc2626;border-radius:4px;cursor:pointer;font-size:.8rem;margin-bottom:1px">
                Remove
            </button>
        </div>
        <div style="margin-top:10px">
            <label style="font-size:.75rem;font-weight:600;color:#6b7280;display:block;margin-bottom:4px">Description</label>
            <textarea name="ms_text[]" class="form-control" rows="2" style="width:100%" placeholder="Describe this milestone…"></textarea>
        </div>`;
    wrap.appendChild(div);
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
