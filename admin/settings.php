<?php
require_once __DIR__ . '/includes/admin_init.php';
$admin_page_title = 'Site Settings';
$admin_active     = 'settings';

if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $action = $_POST['action'] ?? 'save_settings';
    
    $checkbox_keys = ['maintenance_mode', 'auto_approve_reviews', 'smtp_enabled'];
    $allowed_keys = [
        'site_name','site_tagline','site_email','site_phone','site_address',
        'currency_symbol','currency_code','shipping_cost','tax_rate',
        'footer_about','social_instagram','social_facebook','social_twitter','social_tiktok',
        'maintenance_mode','auto_approve_reviews',
        'smtp_enabled','smtp_host','smtp_port','smtp_user','smtp_pass','smtp_secure',
        'mail_from_email','mail_from_name',
        'location_city','location_country','location_map_url',
        'hours_weekday','hours_saturday','hours_sunday',
        'home_stat1_num','home_stat1_label',
        'home_stat2_num','home_stat2_label',
        'home_stat3_num','home_stat3_label',
        'home_stat4_num','home_stat4_label',
        'color_primary','color_secondary',
        'shipping_tab_title','shipping_title','shipping_policy','returns_title','returns_policy',
    ];
    
    foreach ($allowed_keys as $key) {
        $val = isset($_POST[$key]) ? trim($_POST[$key]) : (in_array($key, $checkbox_keys) ? '0' : '');
        if ($key === 'smtp_pass' && $val === '') {
            continue; 
        }
        query('INSERT INTO settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?','sss',$key,$val,$val);
    }
    
    if ($action === 'test_email') {
        $test_to = trim($_POST['test_email_to'] ?? '');
        if (filter_var($test_to, FILTER_VALIDATE_EMAIL)) {
            $subject = "Dakari Store — Test Email Connection";
            $message_body = "<h3>SMTP Test Successful</h3><p>If you are reading this email, your email settings on Dakari Store are correctly configured and working.</p><p>Sent at: " . date('Y-m-d H:i:s') . "</p>";
            
            $sent = send_email($test_to, $subject, $message_body);
            if ($sent) {
                flash('success', 'Settings saved and test email sent successfully to <strong>' . htmlspecialchars($test_to) . '</strong>!');
            } else {
                flash('error', 'Settings saved, but test email <strong>failed</strong> to send. Please check your SMTP configuration and server logs.');
            }
        } else {
            flash('error', 'Settings saved, but test email address is invalid.');
        }
    } else {
        flash('success','Settings saved.');
    }
    header('Location: settings.php'); exit;
}

$settings = [];
foreach (fetchAll('SELECT setting_key,setting_value FROM settings') as $r) $settings[$r['setting_key']] = $r['setting_value'];
$s = fn($k,$d='') => $settings[$k] ?? $d;

include __DIR__ . '/includes/admin_header.php';
?>
<div class="page-header"><div><div class="page-title">Site Settings</div></div></div>

<form method="post" action="settings.php">
    <?= csrf_field() ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
        <div>
            <div class="card" style="margin-bottom:20px">
                <div class="card-header"><span class="card-title">General</span></div>
                <div class="card-body">
                    <div class="form-group" style="margin-bottom:14px"><label class="form-label">Site Name</label><input type="text" name="site_name" class="form-control" value="<?= e($s('site_name','Dakari')) ?>"></div>
                    <div class="form-group" style="margin-bottom:14px"><label class="form-label">Tagline</label><input type="text" name="site_tagline" class="form-control" value="<?= e($s('site_tagline')) ?>"></div>
                    <div class="form-group" style="margin-bottom:14px"><label class="form-label">Contact Email</label><input type="email" name="site_email" class="form-control" value="<?= e($s('site_email')) ?>"></div>
                    <div class="form-group" style="margin-bottom:14px"><label class="form-label">Phone</label><input type="text" name="site_phone" class="form-control" value="<?= e($s('site_phone')) ?>"></div>
                    <div class="form-group" style="margin-bottom:14px"><label class="form-label">Street Address</label><textarea name="site_address" class="form-control" rows="2" placeholder="e.g. Yaya Centre, Argwings Kodhek Rd"><?= e($s('site_address')) ?></textarea></div>
                    <div class="form-group"><label class="form-label">Footer About Text</label><textarea name="footer_about" class="form-control"><?= e($s('footer_about')) ?></textarea></div>
                </div>
            </div>
            <div class="card" style="margin-bottom:20px">
                <div class="card-header"><span class="card-title">Social Media</span></div>
                <div class="card-body">
                    <?php foreach (['instagram'=>'Instagram','facebook'=>'Facebook','twitter'=>'Twitter / X','tiktok'=>'TikTok'] as $key=>$label): ?>
                    <div class="form-group" style="margin-bottom:14px"><label class="form-label"><?= $label ?></label><input type="text" name="social_<?= $key ?>" class="form-control" value="<?= e($s('social_'.$key)) ?>" placeholder="URL or #"></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><span class="card-title">Location &amp; Business Hours</span></div>
                <div class="card-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                        <div class="form-group">
                            <label class="form-label">City</label>
                            <input type="text" name="location_city" class="form-control" value="<?= e($s('location_city','Nairobi')) ?>" placeholder="e.g. Nairobi">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Country</label>
                            <input type="text" name="location_country" class="form-control" value="<?= e($s('location_country','Kenya')) ?>" placeholder="e.g. Kenya">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:14px">
                        <label class="form-label">Google Maps Embed URL</label>
                        <input type="url" name="location_map_url" class="form-control" value="<?= e($s('location_map_url')) ?>" placeholder="Paste the src URL from Google Maps embed code">
                        <span class="form-hint" style="margin-top:4px;display:block">
                            Google Maps → Share → Embed a map → copy the <code>src="..."</code> URL only.
                        </span>
                    </div>
                    <hr style="border:none;border-top:1px solid var(--border);margin:18px 0">
                    <p class="form-label" style="margin-bottom:12px">Business Hours</p>
                    <div class="form-group" style="margin-bottom:12px">
                        <label class="form-label" style="font-weight:400;color:var(--text-muted)">Monday – Friday</label>
                        <input type="text" name="hours_weekday" class="form-control" value="<?= e($s('hours_weekday','Mon – Fri: 8am – 6pm EAT')) ?>" placeholder="Mon – Fri: 8am – 6pm EAT">
                    </div>
                    <div class="form-group" style="margin-bottom:12px">
                        <label class="form-label" style="font-weight:400;color:var(--text-muted)">Saturday</label>
                        <input type="text" name="hours_saturday" class="form-control" value="<?= e($s('hours_saturday','Sat: 9am – 4pm EAT')) ?>" placeholder="Sat: 9am – 4pm EAT">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight:400;color:var(--text-muted)">Sunday</label>
                        <input type="text" name="hours_sunday" class="form-control" value="<?= e($s('hours_sunday','Sun: Closed')) ?>" placeholder="Sun: Closed">
                    </div>
                </div>
            </div>
        </div>
        <div>
            <!-- Brand Colors -->
            <div class="admin-card" style="margin-bottom:20px">
                <div style="border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:18px">
                    <h3 style="font-family:var(--font-serif);color:var(--green);font-size:1rem;margin:0">Brand Colors</h3>
                    <p style="font-size:.78rem;color:var(--text-muted);margin-top:4px">Changes apply site-wide — navigation, buttons, badges, and accents update instantly after saving.</p>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                    <!-- Primary -->
                    <div>
                        <label class="form-label" style="margin-bottom:8px;display:block">Primary Color</label>
                        <div style="display:flex;gap:8px;align-items:center">
                            <input type="color" id="cp-primary" name="color_primary"
                                   value="<?= e($s('color_primary','#1B4332')) ?>"
                                   style="width:44px;height:44px;border:1px solid var(--border);border-radius:6px;padding:3px;cursor:pointer;background:#fff"
                                   oninput="syncColor(this,'ti-primary')">
                            <input type="text" id="ti-primary" class="form-control" maxlength="7"
                                   value="<?= e($s('color_primary','#1B4332')) ?>"
                                   placeholder="#1B4332"
                                   style="flex:1;font-family:monospace;font-size:.85rem"
                                   oninput="syncText(this,'cp-primary')">
                        </div>
                        <div style="margin-top:10px;border-radius:6px;height:32px;background:<?= e($s('color_primary','#1B4332')) ?>" id="prev-primary"></div>
                        <p style="font-size:.72rem;color:var(--text-muted);margin-top:5px">Nav bar, headings, buttons</p>
                    </div>
                    <!-- Secondary -->
                    <div>
                        <label class="form-label" style="margin-bottom:8px;display:block">Secondary / Accent Color</label>
                        <div style="display:flex;gap:8px;align-items:center">
                            <input type="color" id="cp-secondary" name="color_secondary"
                                   value="<?= e($s('color_secondary','#C9A84C')) ?>"
                                   style="width:44px;height:44px;border:1px solid var(--border);border-radius:6px;padding:3px;cursor:pointer;background:#fff"
                                   oninput="syncColor(this,'ti-secondary')">
                            <input type="text" id="ti-secondary" class="form-control" maxlength="7"
                                   value="<?= e($s('color_secondary','#C9A84C')) ?>"
                                   placeholder="#C9A84C"
                                   style="flex:1;font-family:monospace;font-size:.85rem"
                                   oninput="syncText(this,'cp-secondary')">
                        </div>
                        <div style="margin-top:10px;border-radius:6px;height:32px;background:<?= e($s('color_secondary','#C9A84C')) ?>" id="prev-secondary"></div>
                        <p style="font-size:.72rem;color:var(--text-muted);margin-top:5px">Highlights, badges, prices</p>
                    </div>
                </div>
                <!-- Combined preview bar -->
                <div style="margin-top:18px;border-radius:6px;overflow:hidden;display:flex;height:52px">
                    <div id="prev-bar" style="flex:3;background:<?= e($s('color_primary','#1B4332')) ?>;display:flex;align-items:center;padding:0 18px">
                        <span style="color:#fff;font-size:.85rem;font-weight:600;letter-spacing:.04em"><?= e($s('site_name','Dakari')) ?></span>
                    </div>
                    <div id="prev-btn" style="flex:1;display:flex;align-items:center;justify-content:center;background:<?= e($s('color_secondary','#C9A84C')) ?>">
                        <span style="color:#fff;font-size:.78rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase">Shop Now</span>
                    </div>
                </div>
                <p style="font-size:.72rem;color:var(--text-muted);text-align:center;margin-top:8px">Preview — save to apply to the live site</p>
            </div>

            <div class="card" style="margin-bottom:20px">
                <div class="card-header"><span class="card-title">Homepage Stats Banner</span></div>
                <div class="card-body">
                    <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:16px">The four stat blocks shown below the hero carousel on the home page.</p>
                    <?php
                    $stat_defaults = [
                        1 => ['500+',  'Premium Products'],
                        2 => ['12K+',  'Happy Customers'],
                        3 => ['50+',   'Brand Partners'],
                        4 => ['4',     'Years in Business'],
                    ];
                    foreach ($stat_defaults as $i => [$dn, $dl]): ?>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid var(--border)">
                        <div class="form-group">
                            <label class="form-label" style="font-size:.78rem;color:var(--text-muted)">Stat <?= $i ?> — Number</label>
                            <input type="text" name="home_stat<?= $i ?>_num" class="form-control"
                                   value="<?= e($s("home_stat{$i}_num", $dn)) ?>" placeholder="<?= $dn ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-size:.78rem;color:var(--text-muted)">Stat <?= $i ?> — Label</label>
                            <input type="text" name="home_stat<?= $i ?>_label" class="form-control"
                                   value="<?= e($s("home_stat{$i}_label", $dl)) ?>" placeholder="<?= $dl ?>">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card" style="margin-bottom:20px">
                <div class="card-header"><span class="card-title">Commerce</span></div>
                <div class="card-body">
                    <div class="form-group" style="margin-bottom:14px"><label class="form-label">Currency Symbol</label><input type="text" name="currency_symbol" class="form-control" value="<?= e($s('currency_symbol','KSh')) ?>"></div>
                    <div class="form-group" style="margin-bottom:14px"><label class="form-label">Currency Code</label><input type="text" name="currency_code" class="form-control" value="<?= e($s('currency_code','KES')) ?>"></div>
                    <div class="form-group" style="margin-bottom:14px"><label class="form-label">Shipping Cost</label><input type="number" name="shipping_cost" class="form-control" value="<?= e($s('shipping_cost','250')) ?>" step="0.01" min="0" placeholder="e.g. 250"></div>
                    <div class="form-group"><label class="form-label">Tax Rate (%)</label><input type="number" name="tax_rate" class="form-control" value="<?= e($s('tax_rate','0')) ?>" step="0.01"></div>
                </div>
            </div>
            <div class="card" style="margin-bottom:20px">
                <div class="card-header"><span class="card-title">Shipping &amp; Returns Policy</span></div>
                <div class="card-body">
                    <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:14px">This text appears on every product page under the "Shipping &amp; Returns" tab.</p>
                    <div class="form-group" style="margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid var(--border)">
                        <label class="form-label">Tab Label</label>
                        <input type="text" name="shipping_tab_title" class="form-control" value="<?= e($s('shipping_tab_title', 'Shipping & Returns')) ?>" placeholder="e.g. Shipping &amp; Returns">
                        <p class="form-hint" style="margin-top:4px">The tab name shown on the product page.</p>
                    </div>
                    <div class="form-group" style="margin-bottom:8px">
                        <label class="form-label">Shipping Section Title</label>
                        <input type="text" name="shipping_title" class="form-control" value="<?= e($s('shipping_title', 'Shipping Information')) ?>" placeholder="e.g. Shipping Information">
                    </div>
                    <div class="form-group" style="margin-bottom:14px">
                        <label class="form-label">Shipping Details</label>
                        <textarea name="shipping_policy" class="form-control" rows="4" placeholder="Describe your shipping times, regions, and costs…"><?= e($s('shipping_policy', 'Shipping is covered by the client. Standard delivery in 3–5 business days within Nairobi. Upcountry 5–7 business days. International shipping available — rates vary by destination.')) ?></textarea>
                    </div>
                    <div class="form-group" style="margin-bottom:8px">
                        <label class="form-label">Returns Section Title</label>
                        <input type="text" name="returns_title" class="form-control" value="<?= e($s('returns_title', 'Returns Policy')) ?>" placeholder="e.g. Returns Policy">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Returns Details</label>
                        <textarea name="returns_policy" class="form-control" rows="4" placeholder="Describe your returns process and conditions…"><?= e($s('returns_policy', 'We accept returns within 14 days of delivery. Items must be unused and in original packaging. Contact us at our store email to initiate a return.')) ?></textarea>
                    </div>
                </div>
            </div>
            <div class="card" style="margin-bottom:20px">
                <div class="card-header"><span class="card-title">Reviews</span></div>
                <div class="card-body">
                    <label class="form-check"><input type="checkbox" name="auto_approve_reviews" value="1" <?= $s('auto_approve_reviews')=='1'?'checked':'' ?>><label>Auto-approve reviews</label></label>
                    <p class="form-hint" style="margin-top:8px">When enabled, new reviews are published immediately without manual approval.</p>
                </div>
            </div>
            <div class="card" style="margin-bottom:20px">
                <div class="card-header"><span class="card-title">Maintenance</span></div>
                <div class="card-body">
                    <label class="form-check"><input type="checkbox" name="maintenance_mode" value="1" <?= $s('maintenance_mode')=='1'?'checked':'' ?>><label>Enable Maintenance Mode</label></label>
                    <p class="form-hint" style="margin-top:8px">When enabled, only admins can view the store front.</p>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header"><span class="card-title">Email & SMTP Settings</span></div>
                <div class="card-body">
                    <label class="form-check" style="margin-bottom:14px">
                        <input type="checkbox" name="smtp_enabled" value="1" <?= $s('smtp_enabled')=='1'?'checked':'' ?>>
                        <label>Enable SMTP (custom mail server)</label>
                    </label>
                    
                    <div class="form-group" style="margin-bottom:14px">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control" value="<?= e($s('smtp_host')) ?>" placeholder="smtp.mailtrap.io">
                    </div>
                    
                    <div class="form-group" style="margin-bottom:14px">
                        <label class="form-label">SMTP Port</label>
                        <input type="text" name="smtp_port" class="form-control" value="<?= e($s('smtp_port','587')) ?>" placeholder="587">
                    </div>
                    
                    <div class="form-group" style="margin-bottom:14px">
                        <label class="form-label">SMTP Username</label>
                        <input type="text" name="smtp_user" class="form-control" value="<?= e($s('smtp_user')) ?>" placeholder="user@domain.com">
                    </div>
                    
                    <div class="form-group" style="margin-bottom:14px">
                        <label class="form-label">SMTP Password</label>
                        <input type="password" name="smtp_pass" class="form-control" placeholder="•••••••• (leave blank to keep current)">
                    </div>
                    
                    <div class="form-group" style="margin-bottom:14px">
                        <label class="form-label">SMTP Secure</label>
                        <select name="smtp_secure" class="form-control">
                            <option value="tls" <?= $s('smtp_secure')=='tls'?'selected':'' ?>>TLS (Recommended)</option>
                            <option value="ssl" <?= $s('smtp_secure')=='ssl'?'selected':'' ?>>SSL</option>
                            <option value="none" <?= $s('smtp_secure')=='none'?'selected':'' ?>>None</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom:14px">
                        <label class="form-label">Sender Email Address</label>
                        <input type="email" name="mail_from_email" class="form-control" value="<?= e($s('mail_from_email')) ?>" placeholder="info@dakari.com">
                    </div>
                    
                    <div class="form-group" style="margin-bottom:20px">
                        <label class="form-label">Sender Display Name</label>
                        <input type="text" name="mail_from_name" class="form-control" value="<?= e($s('mail_from_name')) ?>" placeholder="Dakari Store">
                    </div>
                    
                    <div style="border-top: 1px solid var(--border); padding-top: 18px;">
                        <label class="form-label">Test Configuration</label>
                        <div style="display:flex; gap:10px; margin-bottom:10px">
                            <input type="email" name="test_email_to" class="form-control" placeholder="receiver@example.com" value="<?= e($s('site_email')) ?>" style="flex:1">
                            <button type="submit" name="action" value="test_email" class="btn btn-outline" style="width:auto; border-color:var(--accent); color:var(--accent); font-size:0.85rem; padding: 0 15px">Test Mail</button>
                        </div>
                        <span class="form-hint">Saves settings and sends a test email to verify.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div style="margin-top:24px;text-align:right">
        <button type="submit" class="btn btn-gold btn-lg">Save All Settings</button>
    </div>
</form>

<script>
function syncColor(picker, textId) {
    document.getElementById(textId).value = picker.value;
    updateColorPreviews();
}
function syncText(input, pickerId) {
    if (/^#[0-9a-fA-F]{6}$/i.test(input.value)) {
        document.getElementById(pickerId).value = input.value;
        updateColorPreviews();
    }
}
function updateColorPreviews() {
    const p = document.getElementById('cp-primary').value;
    const s = document.getElementById('cp-secondary').value;
    document.getElementById('prev-primary').style.background   = p;
    document.getElementById('prev-secondary').style.background = s;
    document.getElementById('prev-bar').style.background       = p;
    document.getElementById('prev-btn').style.background       = s;
    document.documentElement.style.setProperty('--green', p);
    document.documentElement.style.setProperty('--gold',  s);
}
</script>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
