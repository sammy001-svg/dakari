<?php
require_once __DIR__ . '/includes/admin_init.php';
$admin_page_title = 'Site Settings';
$admin_active     = 'settings';

if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $allowed_keys = ['site_name','site_tagline','site_email','site_phone','site_address',
                     'currency_symbol','currency_code','shipping_cost','tax_rate',
                     'footer_about','social_instagram','social_facebook','social_twitter','social_tiktok',
                     'maintenance_mode','auto_approve_reviews'];
    foreach ($allowed_keys as $key) {
        if (isset($_POST[$key])) {
            $val = trim($_POST[$key]);
            query('INSERT INTO settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?','sss',$key,$val,$val);
        }
    }
    flash('success','Settings saved.');
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
                    <div class="form-group" style="margin-bottom:14px"><label class="form-label">Address</label><input type="text" name="site_address" class="form-control" value="<?= e($s('site_address')) ?>"></div>
                    <div class="form-group"><label class="form-label">Footer About Text</label><textarea name="footer_about" class="form-control"><?= e($s('footer_about')) ?></textarea></div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><span class="card-title">Social Media</span></div>
                <div class="card-body">
                    <?php foreach (['instagram'=>'Instagram','facebook'=>'Facebook','twitter'=>'Twitter / X','tiktok'=>'TikTok'] as $key=>$label): ?>
                    <div class="form-group" style="margin-bottom:14px"><label class="form-label"><?= $label ?></label><input type="text" name="social_<?= $key ?>" class="form-control" value="<?= e($s('social_'.$key)) ?>" placeholder="URL or #"></div>
                    <?php endforeach; ?>
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
            <div class="card">
                <div class="card-header"><span class="card-title">Maintenance</span></div>
                <div class="card-body">
                    <label class="form-check"><input type="checkbox" name="maintenance_mode" value="1" <?= $s('maintenance_mode')=='1'?'checked':'' ?>><label>Enable Maintenance Mode</label></label>
                    <p class="form-hint" style="margin-top:8px">When enabled, only admins can view the store front.</p>
                </div>
            </div>
        </div>
    </div>
    <div style="margin-top:24px;text-align:right">
        <button type="submit" class="btn btn-gold btn-lg">Save All Settings</button>
    </div>
</form>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
