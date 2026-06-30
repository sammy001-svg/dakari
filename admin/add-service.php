<?php
require_once __DIR__ . '/includes/admin_init.php';
$admin_page_title = 'Add Service';
$admin_active     = 'services';

$errors = [];
$d = ['title'=>'','slug'=>'','tagline'=>'','description'=>'','icon'=>'star','features'=>'','price_label'=>'','cta_text'=>'Learn More','cta_url'=>'','sort_order'=>0,'is_featured'=>0,'status'=>'active'];

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
    ];
    if (!$d['slug'] && $d['title']) $d['slug'] = slugify($d['title']);

    if (!$d['title'])                              $errors[] = 'Title is required.';
    if (!$d['slug'])                               $errors[] = 'Slug is required.';
    if (fetchOne('SELECT id FROM services WHERE slug=?','s',$d['slug'])) $errors[] = 'Slug already exists.';

    /* Image upload */
    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $ext  = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allow = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $allow)) { $errors[] = 'Image must be JPG, PNG, or WebP.'; }
        else {
            $fname = uniqid('svc_') . '.' . $ext;
            $dest  = ROOT_PATH . '/uploads/services/' . $fname;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) $image = $fname;
            else $errors[] = 'Image upload failed.';
        }
    }

    if (empty($errors)) {
        query(
            'INSERT INTO services (title,slug,tagline,description,icon,image,features,price_label,cta_text,cta_url,is_featured,sort_order,status)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
            'ssssssssssiis',
            $d['title'],$d['slug'],$d['tagline'],$d['description'],$d['icon'],$image,
            $d['features'],$d['price_label'],$d['cta_text'],$d['cta_url'],
            $d['is_featured'],$d['sort_order'],$d['status']
        );
        flash('success', 'Service "' . $d['title'] . '" created successfully.');
        header('Location: services.php'); exit;
    }
}

$icons = ['shopping_bag'=>'Shopping Bag','briefcase'=>'Briefcase','gift'=>'Gift','truck'=>'Truck','settings'=>'Settings / Customise','shield'=>'Shield / Security','star'=>'Star','heart'=>'Heart'];
$csrf  = generate_csrf();
include __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Add Service</h1>
        <p class="admin-page-sub">Create a new service for the public services page</p>
    </div>
    <a href="services.php" class="btn btn-outline-green">← Back to Services</a>
</div>

<?php if ($errors): ?>
<div class="alert alert-error" style="margin-bottom:20px"><?= implode('<br>', array_map('e', $errors)) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start">

        <!-- Main fields -->
        <div>
            <div class="admin-card" style="margin-bottom:24px">
                <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:20px;font-size:1rem">Service Details</h3>
                <div class="form-grid" style="grid-template-columns:1fr 1fr">
                    <div class="form-group full">
                        <label class="form-label">Title <span class="required">*</span></label>
                        <input type="text" name="title" class="form-control" value="<?= e($d['title']) ?>" required
                               oninput="document.getElementById('slug').value=this.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'')">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Slug <span class="required">*</span></label>
                        <input type="text" name="slug" id="slug" class="form-control" value="<?= e($d['slug']) ?>" required>
                        <span class="form-hint">URL-friendly identifier, e.g. personal-shopping</span>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Tagline</label>
                        <input type="text" name="tagline" class="form-control" value="<?= e($d['tagline']) ?>" placeholder="Short, punchy subtitle">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Detailed description shown on the services page…"><?= e($d['description']) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="admin-card" style="margin-bottom:24px">
                <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:20px;font-size:1rem">Features List</h3>
                <div class="form-group">
                    <label class="form-label">Features (one per line)</label>
                    <textarea name="features" class="form-control" rows="7" placeholder="Free delivery on first order&#10;Dedicated account manager&#10;Priority customer support"><?= e($d['features']) ?></textarea>
                    <span class="form-hint">Each line becomes a bullet point with a checkmark on the service card.</span>
                </div>
            </div>

            <div class="admin-card">
                <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:20px;font-size:1rem">Call to Action</h3>
                <div class="form-grid" style="grid-template-columns:1fr 1fr">
                    <div class="form-group">
                        <label class="form-label">Button Text</label>
                        <input type="text" name="cta_text" class="form-control" value="<?= e($d['cta_text']) ?>" placeholder="Learn More">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Button URL</label>
                        <input type="text" name="cta_url" class="form-control" value="<?= e($d['cta_url']) ?>" placeholder="/contact.php (leave blank for contact page)">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar options -->
        <div>
            <div class="admin-card" style="margin-bottom:20px">
                <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:20px;font-size:1rem">Publish</h3>
                <div class="form-group" style="margin-bottom:16px">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="active"   <?= $d['status']==='active'   ? 'selected':'' ?>>Active</option>
                        <option value="inactive" <?= $d['status']==='inactive' ? 'selected':'' ?>>Inactive</option>
                    </select>
                </div>
                <label class="form-check" style="margin-bottom:16px">
                    <input type="checkbox" name="is_featured" value="1" <?= $d['is_featured'] ? 'checked' : '' ?>>
                    <span style="font-size:.88rem">Feature on homepage</span>
                </label>
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= $d['sort_order'] ?>" min="0">
                </div>
                <button type="submit" class="btn btn-green btn-block" style="margin-top:18px">Save Service</button>
            </div>

            <div class="admin-card" style="margin-bottom:20px">
                <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:20px;font-size:1rem">Icon</h3>
                <select name="icon" class="form-control">
                    <?php foreach ($icons as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $d['icon'] === $val ? 'selected':'' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
                <div style="margin-top:14px;padding:16px;background:var(--green);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;color:var(--gold)">
                    <?= service_icon_svg($d['icon'], 32) ?>
                </div>
            </div>

            <div class="admin-card" style="margin-bottom:20px">
                <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:20px;font-size:1rem">Image</h3>
                <input type="file" name="image" class="form-control" accept="image/*">
                <span class="form-hint" style="margin-top:6px;display:block">Optional. JPG, PNG or WebP.</span>
            </div>

            <div class="admin-card">
                <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:20px;font-size:1rem">Pricing</h3>
                <div class="form-group">
                    <label class="form-label">Price Label</label>
                    <input type="text" name="price_label" class="form-control" value="<?= e($d['price_label']) ?>" placeholder="From KES 500 · Free with purchase">
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom save bar -->
    <div style="margin-top:24px;padding:20px 24px;background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:space-between;gap:16px">
        <span style="font-size:.85rem;color:var(--text-muted)">Fill in the details above, then save.</span>
        <div style="display:flex;gap:10px">
            <a href="services.php" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-green" style="padding:10px 32px">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Save Service
            </button>
        </div>
    </div>
</form>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
