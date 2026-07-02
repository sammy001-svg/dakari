<?php
require_once __DIR__ . '/includes/admin_init.php';
$admin_page_title = 'Edit Service';
$admin_active     = 'services';

$id  = (int)($_GET['id'] ?? 0);
$svc = $id ? fetchOne('SELECT * FROM services WHERE id=?', 'i', $id) : null;
if (!$svc) { flash('error', 'Service not found.'); header('Location: services.php'); exit; }

$errors = [];
// Merge DB row over safe defaults — prevents NULL reaching e() in PHP 8
$d = array_merge([
    'title'       => '',
    'slug'        => '',
    'tagline'     => '',
    'description' => '',
    'icon'        => 'star',
    'image'       => '',
    'features'    => '',
    'price_label' => '',
    'cta_text'    => 'Learn More',
    'cta_url'     => '',
    'sort_order'  => 0,
    'is_featured' => 0,
    'status'      => 'active',
], $svc);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $d = [
        'title'       => trim($_POST['title']       ?? ''),
        'slug'        => trim($_POST['slug']         ?? ''),
        'tagline'     => trim($_POST['tagline']      ?? ''),
        'description' => trim($_POST['description']  ?? ''),
        'icon'        => trim($_POST['icon']         ?? 'star'),
        'features'    => trim($_POST['features']     ?? ''),
        'price_label' => trim($_POST['price_label']  ?? ''),
        'cta_text'    => trim($_POST['cta_text']     ?? 'Learn More'),
        'cta_url'     => trim($_POST['cta_url']      ?? ''),
        'sort_order'  => (int)($_POST['sort_order']  ?? 0),
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'status'      => (($_POST['status'] ?? '') === 'inactive') ? 'inactive' : 'active',
        'image'       => $svc['image'] ?? '',
    ];
    if (!$d['slug'] && $d['title']) $d['slug'] = slugify($d['title']);

    if (!$d['title'])  $errors[] = 'Title is required.';
    if (!$d['slug'])   $errors[] = 'Slug is required.';

    // Image upload
    if (!empty($_FILES['image']['name'])) {
        $ext   = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allow = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $allow)) {
            $errors[] = 'Image must be JPG, PNG, or WebP.';
        } else {
            $fname = uniqid('svc_') . '.' . $ext;
            $dest  = ROOT_PATH . '/uploads/services/' . $fname;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                if ($svc['image']) @unlink(ROOT_PATH . '/uploads/services/' . $svc['image']);
                $d['image'] = $fname;
            } else {
                $errors[] = 'Image upload failed.';
            }
        }
    }
    // Remove image
    if (isset($_POST['remove_image']) && $svc['image']) {
        @unlink(ROOT_PATH . '/uploads/services/' . $svc['image']);
        $d['image'] = '';
    }

    if (empty($errors)) {
        query(
            'UPDATE services SET title=?,slug=?,tagline=?,description=?,icon=?,image=?,
             features=?,price_label=?,cta_text=?,cta_url=?,is_featured=?,sort_order=?,status=?
             WHERE id=?',
            'ssssssssssiisi',
            $d['title'], $d['slug'], $d['tagline'], $d['description'],
            $d['icon'],  $d['image'], $d['features'], $d['price_label'],
            $d['cta_text'], $d['cta_url'], $d['is_featured'], $d['sort_order'],
            $d['status'], $id
        );
        flash('success', 'Service "' . $d['title'] . '" updated successfully.');
        header('Location: services.php'); exit;
    }
}

$icons = [
    'shopping_bag' => 'Shopping Bag',
    'briefcase'    => 'Briefcase',
    'gift'         => 'Gift',
    'truck'        => 'Delivery / Truck',
    'settings'     => 'Settings / Customise',
    'shield'       => 'Shield / Trust',
    'star'         => 'Star',
    'heart'        => 'Heart',
];
$csrf = generate_csrf();
include __DIR__ . '/includes/admin_header.php';
?>

<!-- ── Page header with Save button always visible ── -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Edit Service</h1>
        <p class="admin-page-sub">Editing: <strong><?= e($svc['title'] ?? '') ?></strong></p>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
        <a href="services.php" style="padding:9px 18px;border:1px solid #ccc;border-radius:4px;color:#555;text-decoration:none;font-size:.85rem">← Back</a>
        <button type="submit" form="edit-service-form"
                style="padding:10px 28px;background:#1B4332;color:#fff;border:none;border-radius:4px;font-size:.88rem;font-weight:600;cursor:pointer">
            ✓ Save Changes
        </button>
    </div>
</div>

<?php if ($errors): ?>
<div style="background:#fdf0ef;border-left:4px solid #c0392b;padding:12px 18px;border-radius:4px;margin-bottom:20px;color:#c0392b;font-size:.88rem">
    <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
</div>
<?php endif; ?>

<!-- ── Form ── -->
<form id="edit-service-form" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

    <!-- Service Details -->
    <div class="admin-card" style="margin-bottom:20px">
        <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:20px;font-size:1rem;border-bottom:1px solid var(--border);padding-bottom:12px">
            Service Details
        </h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div style="grid-column:1/-1">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px">Title <span style="color:var(--gold)">*</span></label>
                <input type="text" name="title" class="form-control" value="<?= e($d['title']) ?>" required
                       style="width:100%">
            </div>
            <div style="grid-column:1/-1">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px">Slug <span style="color:var(--gold)">*</span></label>
                <input type="text" name="slug" class="form-control" value="<?= e($d['slug']) ?>" required
                       style="width:100%">
                <span style="font-size:.75rem;color:#888;margin-top:4px;display:block">URL identifier, e.g. personal-shopping</span>
            </div>
            <div style="grid-column:1/-1">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px">Tagline</label>
                <input type="text" name="tagline" class="form-control" value="<?= e($d['tagline']) ?>"
                       style="width:100%" placeholder="Short punchy subtitle">
            </div>
            <div style="grid-column:1/-1">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px">Description</label>
                <textarea name="description" class="form-control" rows="5"
                          style="width:100%" placeholder="Full description shown on the services page"><?= e($d['description']) ?></textarea>
            </div>
        </div>
    </div>

    <!-- Features + CTA side by side -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
        <div class="admin-card">
            <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:16px;font-size:1rem;border-bottom:1px solid var(--border);padding-bottom:12px">
                Features List
            </h3>
            <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px">Features (one per line)</label>
            <textarea name="features" class="form-control" rows="8"
                      style="width:100%" placeholder="Free delivery on first order&#10;Dedicated account manager&#10;Priority support"><?= e($d['features']) ?></textarea>
            <span style="font-size:.75rem;color:#888;margin-top:4px;display:block">Each line becomes a bullet point on the service card.</span>
        </div>
        <div class="admin-card">
            <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:16px;font-size:1rem;border-bottom:1px solid var(--border);padding-bottom:12px">
                Publish &amp; Settings
            </h3>
            <div style="margin-bottom:14px">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px">Status</label>
                <select name="status" class="form-control" style="width:100%">
                    <option value="active"   <?= $d['status']==='active'   ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $d['status']==='inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div style="margin-bottom:14px">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.88rem">
                    <input type="checkbox" name="is_featured" value="1" <?= $d['is_featured'] ? 'checked' : '' ?>
                           style="accent-color:#1B4332;width:16px;height:16px">
                    Feature on homepage
                </label>
            </div>
            <div style="margin-bottom:14px">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="<?= (int)$d['sort_order'] ?>" min="0" style="width:100%">
            </div>
            <div style="margin-bottom:14px">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px">Icon</label>
                <select name="icon" class="form-control" style="width:100%">
                    <?php foreach ($icons as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($d['icon'] ?? 'star') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px">Price Label</label>
                <input type="text" name="price_label" class="form-control" value="<?= e($d['price_label']) ?>"
                       style="width:100%" placeholder="e.g. From KES 500">
            </div>
        </div>
    </div>

    <!-- CTA + Image -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
        <div class="admin-card">
            <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:16px;font-size:1rem;border-bottom:1px solid var(--border);padding-bottom:12px">
                Call to Action
            </h3>
            <div style="margin-bottom:14px">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px">Button Text</label>
                <input type="text" name="cta_text" class="form-control" value="<?= e($d['cta_text']) ?>"
                       style="width:100%" placeholder="Learn More">
            </div>
            <div>
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px">Button URL</label>
                <input type="text" name="cta_url" class="form-control" value="<?= e($d['cta_url']) ?>"
                       style="width:100%" placeholder="/contact.php">
            </div>
        </div>
        <div class="admin-card">
            <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:16px;font-size:1rem;border-bottom:1px solid var(--border);padding-bottom:12px">
                Service Image <span style="font-weight:400;font-size:.8rem;color:#888">(optional)</span>
            </h3>
            <?php if (!empty($d['image']) && file_exists(ROOT_PATH . '/uploads/services/' . $d['image'])): ?>
            <div style="margin-bottom:12px">
                <img src="<?= BASE_URL ?>/uploads/services/<?= e($d['image']) ?>"
                     style="max-height:90px;border-radius:4px;border:1px solid #ddd">
                <label style="display:flex;align-items:center;gap:8px;margin-top:8px;cursor:pointer;font-size:.82rem;color:#c0392b">
                    <input type="checkbox" name="remove_image" value="1">
                    Remove current image
                </label>
            </div>
            <?php endif; ?>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                   style="display:block;width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;font-size:.85rem">
            <span style="font-size:.75rem;color:#888;margin-top:6px;display:block">JPG, PNG or WebP · Max 5 MB</span>
        </div>
    </div>

    <!-- Bottom Save Bar -->
    <div style="background:#1B4332;border-radius:6px;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:32px">
        <span style="color:rgba(255,255,255,.8);font-size:.88rem">
            Ready to save your changes to <strong style="color:#C9A84C"><?= e($svc['title'] ?? '') ?></strong>?
        </span>
        <div style="display:flex;gap:10px">
            <a href="services.php"
               style="padding:10px 20px;background:transparent;border:1px solid rgba(255,255,255,.4);color:#fff;border-radius:4px;text-decoration:none;font-size:.85rem;font-weight:600">
                Cancel
            </a>
            <button type="submit"
                    style="padding:10px 32px;background:#C9A84C;color:#fff;border:none;border-radius:4px;font-size:.88rem;font-weight:700;cursor:pointer;letter-spacing:.02em">
                ✓ Save Changes
            </button>
        </div>
    </div>
</form>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
