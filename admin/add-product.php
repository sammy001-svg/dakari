<?php
require_once __DIR__ . '/includes/admin_init.php';
$admin_page_title = 'Add Product';
$admin_active     = 'products';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $d = [
        'name'       => trim($_POST['name'] ?? ''),
        'slug'       => trim($_POST['slug'] ?? ''),
        'description'=> trim($_POST['description'] ?? ''),
        'short_desc' => trim($_POST['short_desc'] ?? ''),
        'price'      => (float)($_POST['price'] ?? 0),
        'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
        'sku'        => trim($_POST['sku'] ?? '') ?: null,
        'stock'      => (int)($_POST['stock'] ?? 0),
        'category_id'=> (int)($_POST['category_id'] ?? 0) ?: null,
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'is_new'      => isset($_POST['is_new']) ? 1 : 0,
    ];
    if (!$d['name']) $errors[] = 'Name is required.';
    if (!$d['slug']) $d['slug'] = slugify($d['name']);
    if ($d['price'] <= 0) $errors[] = 'Price must be greater than 0.';

    // Check slug uniqueness
    $slug_exists = fetchOne('SELECT id FROM products WHERE slug = ?', 's', $d['slug']);
    if ($slug_exists) { $d['slug'] .= '-' . substr(md5(time()), 0, 5); }

    if (empty($errors)) {
        // Handle thumbnail
        $thumbnail = null;
        if (!empty($_FILES['thumbnail']['name'])) {
            $thumbnail = upload_image($_FILES['thumbnail'], ROOT_PATH . '/uploads/products');
            if (!$thumbnail) $errors[] = 'Failed to upload thumbnail. Use JPG/PNG/WEBP under 5MB.';
        }
    }
    if (empty($errors)) {
        query(
            'INSERT INTO products (name,slug,description,short_desc,price,sale_price,sku,stock,category_id,thumbnail,is_featured,is_new)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
            'sssssdsiisii',
            $d['name'],$d['slug'],$d['description'],$d['short_desc'],$d['price'],
            $d['sale_price'],$d['sku'],$d['stock'],$d['category_id'],$thumbnail,$d['is_featured'],$d['is_new']
        );
        $pid = lastInsertId();
        // Log initial stock
        if ($d['stock'] > 0) {
            query(
                'INSERT INTO stock_logs (product_id, admin_id, type, quantity_change, quantity_before, quantity_after, note)
                 VALUES (?,?,?,?,?,?,?)',
                'iisiiii',
                $pid, $_SESSION['user_id'], 'restock', $d['stock'], 0, $d['stock'], 'Initial stock on product creation'
            );
        }
        // Additional images
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['name'] as $i => $name) {
                if (!$name) continue;
                $file = ['name'=>$name,'type'=>$_FILES['images']['type'][$i],'tmp_name'=>$_FILES['images']['tmp_name'][$i],'size'=>$_FILES['images']['size'][$i]];
                $img = upload_image($file, ROOT_PATH . '/uploads/products');
                if ($img) query('INSERT INTO product_images (product_id,image_path,sort_order) VALUES (?,?,?)', 'isi', $pid, $img, $i);
            }
        }
        flash('success', 'Product "' . $d['name'] . '" added successfully.');
        header('Location: products.php'); exit;
    }
}

$categories = get_categories();
include __DIR__ . '/includes/admin_header.php';
?>
<div class="page-header">
    <div><div class="page-title">Add Product</div></div>
    <a href="products.php" class="btn btn-outline">← Back</a>
</div>
<?php foreach ($errors as $e): ?><div class="alert alert-error"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>

<form method="post" action="add-product.php" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start">
        <div>
            <div class="card" style="margin-bottom:20px">
                <div class="card-header"><span class="card-title">Product Information</span></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Product Name <span class="req">*</span></label>
                            <input type="text" name="name" id="product_name" class="form-control" value="<?= e($_POST['name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Slug <span class="req">*</span></label>
                            <input type="text" name="slug" id="product_slug" class="form-control" value="<?= e($_POST['slug'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-row full">
                        <div class="form-group">
                            <label class="form-label">Short Description</label>
                            <input type="text" name="short_desc" class="form-control" value="<?= e($_POST['short_desc'] ?? '') ?>" placeholder="One-line description shown on product cards">
                        </div>
                    </div>
                    <div class="form-row full">
                        <div class="form-group">
                            <label class="form-label">Full Description</label>
                            <textarea name="description" class="form-control" style="min-height:160px"><?= e($_POST['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><span class="card-title">Images</span></div>
                <div class="card-body">
                    <div class="form-group" style="margin-bottom:20px">
                        <label class="form-label">Thumbnail (main image)</label>
                        <label class="img-upload-box">
                            <div class="icon"><svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
                            <p>Click to upload thumbnail</p>
                            <p style="font-size:.75rem;color:#aaa">JPG, PNG, WEBP · Max 5MB</p>
                            <input type="file" name="thumbnail" accept="image/*">
                        </label>
                        <div class="img-preview-grid"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Additional Images</label>
                        <label class="img-upload-box">
                            <div class="icon"><svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
                            <p>Click to upload additional images</p>
                            <input type="file" name="images[]" accept="image/*" multiple>
                        </label>
                        <div class="img-preview-grid"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right column -->
        <div>
            <div class="card" style="margin-bottom:20px">
                <div class="card-header"><span class="card-title">Pricing & Stock</span></div>
                <div class="card-body">
                    <div class="form-group" style="margin-bottom:16px">
                        <label class="form-label">Regular Price <span class="req">*</span></label>
                        <input type="number" name="price" class="form-control" value="<?= e($_POST['price'] ?? '') ?>" step="0.01" min="0" required>
                    </div>
                    <div class="form-group" style="margin-bottom:16px">
                        <label class="form-label">Sale Price (optional)</label>
                        <input type="number" name="sale_price" class="form-control" value="<?= e($_POST['sale_price'] ?? '') ?>" step="0.01" min="0">
                    </div>
                    <div class="form-group" style="margin-bottom:16px">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-control" value="<?= e($_POST['sku'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" name="stock" class="form-control" value="<?= e($_POST['stock'] ?? '0') ?>" min="0">
                    </div>
                </div>
            </div>

            <div class="card" style="margin-bottom:20px">
                <div class="card-header"><span class="card-title">Organisation</span></div>
                <div class="card-body">
                    <div class="form-group" style="margin-bottom:16px">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control">
                            <option value="">— No Category —</option>
                            <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($_POST['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:12px">
                        <label class="form-check">
                            <input type="checkbox" name="is_featured" <?= isset($_POST['is_featured']) ? 'checked' : '' ?>>
                            <label>Mark as Featured</label>
                        </label>
                        <label class="form-check">
                            <input type="checkbox" name="is_new" <?= isset($_POST['is_new']) ? 'checked' : '' ?>>
                            <label>Mark as New Arrival</label>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-gold" style="width:100%;padding:14px">Save Product</button>
        </div>
    </div>
</form>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
