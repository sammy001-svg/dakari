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
            <div class="card" style="margin-bottom:20px">
                <div class="card-header"><span class="card-title">Commerce</span></div>
                <div class="card-body">
                    <div class="form-group" style="margin-bottom:14px"><label class="form-label">Currency Symbol</label><input type="text" name="currency_symbol" class="form-control" value="<?= e($s('currency_symbol','KSh')) ?>"></div>
                    <div class="form-group" style="margin-bottom:14px"><label class="form-label">Currency Code</label><input type="text" name="currency_code" class="form-control" value="<?= e($s('currency_code','KES')) ?>"></div>
                    <div class="form-group" style="margin-bottom:14px"><label class="form-label">Shipping Cost (0 = free for qualifying orders)</label><input type="number" name="shipping_cost" class="form-control" value="<?= e($s('shipping_cost','250')) ?>" step="0.01"></div>
                    <div class="form-group"><label class="form-label">Tax Rate (%)</label><input type="number" name="tax_rate" class="form-control" value="<?= e($s('tax_rate','0')) ?>" step="0.01"></div>
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

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
