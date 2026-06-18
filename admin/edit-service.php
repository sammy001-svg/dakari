<?php
require_once __DIR__ . '/includes/admin_init.php';
$admin_page_title = 'Edit Service';
$admin_active     = 'services';

$id  = (int)($_GET['id'] ?? 0);
$svc = $id ? fetchOne('SELECT * FROM services WHERE id=?', 'i', $id) : null;
if (!$svc) { flash('error', 'Service not found.'); header('Location: services.php'); exit; }

$errors = [];
$d = $svc;

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
        'status'      => $_POST['status'] === 'inactive' ? 'inactive' : 'active',
        'image'       => $svc['image'],
    ];
    if (!$d['slug'] && $d['title']) $d['slug'] = slugify($d['title']);

    if (!$d['title'])  $errors[] = 'Title is required.';
    if (!$d['slug'])   $errors[] = 'Slug is required.';
    $slugCheck = fetchOne('SELECT id FROM services WHERE slug=? AND id!=?','si',$d['slug'],$id);
    if ($slugCheck)    $errors[] = 'Slug already used by another service.';

    /* Image upload */
    if (!empty($_FILES['image']['name'])) {
        $ext   = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allow = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $allow)) { $errors[] = 'Image must be JPG, PNG, or WebP.'; }
        else {
            $fname = uniqid('svc_') . '.' . $ext;
            $dest  = ROOT_PATH . '/uploads/services/' . $fname;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                if ($svc['image']) @unlink(ROOT_PATH . '/uploads/services/' . $svc['image']);
                $d['image'] = $fname;
            } else { $errors[] = 'Image upload failed.'; }
        }
    }
    /* Remove image */
    if (isset($_POST['remove_image']) && $svc['image']) {
        @unlink(ROOT_PATH . '/uploads/services/' . $svc['image']);
        $d['image'] = '';
    }

    if (empty($errors)) {
        query(
            'UPDATE services SET title=?,slug=?,tagline=?,description=?,icon=?,image=?,features=?,price_label=?,cta_text=?,cta_url=?,is_featured=?,sort_order=?,status=? WHERE id=?',
            'ssssssssssiiis',
            $d['title'],$d['slug'],$d['tagline'],$d['description'],$d['icon'],$d['image'],
            $d['features'],$d['price_label'],$d['cta_text'],$d['cta_url'],
            $d['is_featured'],$d['sort_order'],$d['status'],$id
        );
        flash('success', 'Service updated.');
        header('Location: services.php'); exit;
    }
}

$icons = ['shopping_bag'=>'Shopping Bag','briefcase'=>'Briefcase','gift'=>'Gift','truck'=>'Truck','settings'=>'Settings / Customise','shield'=>'Shield / Security','star'=>'Star','heart'=>'Heart'];
$csrf  = generate_csrf();
include __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Edit Service</h1>
        <p class="admin-page-sub">Editing: <strong><?= e($svc['title']) ?></strong></p>
    </div>
    <div style="display:flex;gap:10px">
        <a href="<?= BASE_URL ?>/services.php#<?= e($svc['slug']) ?>" target="_blank" class="btn btn-outline-green">View Live</a>
        <a href="services.php" class="btn btn-outline-green">← Back</a>
    </div>
</div>

<?php if ($errors): ?>
<div class="alert alert-error" style="margin-bottom:20px"><?= implode('<br>', array_map('e', $errors)) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start">

        <div>
            <div class="admin-card" style="margin-bottom:24px">
                <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:20px;font-size:1rem">Service Details</h3>
                <div class="form-grid" style="grid-template-columns:1fr 1fr">
                    <div class="form-group full">
                        <label class="form-label">Title <span class="required">*</span></label>
                        <input type="text" name="title" class="form-control" value="<?= e($d['title']) ?>" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Slug <span class="required">*</span></label>
                        <input type="text" name="slug" class="form-control" value="<?= e($d['slug']) ?>" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Tagline</label>
                        <input type="text" name="tagline" class="form-control" value="<?= e($d['tagline']) ?>">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="5"><?= e($d['description']) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="admin-card" style="margin-bottom:24px">
                <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:20px;font-size:1rem">Features List</h3>
                <div class="form-group">
                    <label class="form-label">Features (one per line)</label>
                    <textarea name="features" class="form-control" rows="7"><?= e($d['features']) ?></textarea>
                    <span class="form-hint">Each line becomes a checkmark bullet on the service card.</span>
                </div>
            </div>

            <div class="admin-card">
                <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:20px;font-size:1rem">Call to Action</h3>
                <div class="form-grid" style="grid-template-columns:1fr 1fr">
                    <div class="form-group">
                        <label class="form-label">Button Text</label>
                        <input type="text" name="cta_text" class="form-control" value="<?= e($d['cta_text']) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Button URL</label>
                        <input type="text" name="cta_url" class="form-control" value="<?= e($d['cta_url']) ?>">
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="admin-card" style="margin-bottom:20px">
                <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:20px;font-size:1rem">Publish</h3>
                <div class="form-group" style="margin-bottom:16px">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="active"   <?= $d['status']==='active'  ?'selected':'' ?>>Active</option>
                        <option value="inactive" <?= $d['status']==='inactive'?'selected':'' ?>>Inactive</option>
                    </select>
                </div>
                <label class="form-check" style="margin-bottom:16px">
                    <input type="checkbox" name="is_featured" value="1" <?= $d['is_featured'] ? 'checked':'' ?>>
                    <span style="font-size:.88rem">Feature on homepage</span>
                </label>
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= $d['sort_order'] ?>" min="0">
                </div>
                <button type="submit" class="btn btn-green btn-block" style="margin-top:18px">Update Service</button>
            </div>

            <div class="admin-card" style="margin-bottom:20px">
                <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:20px;font-size:1rem">Icon</h3>
                <select name="icon" class="form-control">
                    <?php foreach ($icons as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($d['icon'] ?? '') === $val ? 'selected':'' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
                <div style="margin-top:14px;padding:16px;background:var(--green);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;color:var(--gold)">
                    <?= service_icon_svg($d['icon'] ?? 'star', 32) ?>
                </div>
            </div>

            <div class="admin-card" style="margin-bottom:20px">
                <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:20px;font-size:1rem">Image</h3>
                <?php if (!empty($d['image']) && file_exists(ROOT_PATH.'/uploads/services/'.$d['image'])): ?>
                <div style="margin-bottom:12px">
                    <img src="<?= BASE_URL ?>/uploads/services/<?= e($d['image']) ?>" style="max-height:100px;border-radius:var(--radius);border:1px solid var(--border)">
                    <label class="form-check" style="margin-top:8px">
                        <input type="checkbox" name="remove_image" value="1">
                        <span style="font-size:.8rem;color:#c0392b">Remove image</span>
                    </label>
                </div>
                <?php endif; ?>
                <input type="file" name="image" class="form-control" accept="image/*">
                <span class="form-hint" style="margin-top:6px;display:block">Replace with a new JPG, PNG or WebP.</span>
            </div>

            <div class="admin-card">
                <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:20px;font-size:1rem">Pricing</h3>
                <div class="form-group">
                    <label class="form-label">Price Label</label>
                    <input type="text" name="price_label" class="form-control" value="<?= e($d['price_label']) ?>">
                </div>
            </div>
        </div>
    </div>
</form>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
