<?php
require_once __DIR__ . '/includes/admin_init.php';
$id      = (int)($_GET['id'] ?? 0);
$product = $id ? fetchOne('SELECT * FROM products WHERE id = ?', 'i', $id) : null;
if (!$product) { flash('error', 'Product not found.'); header('Location: products.php'); exit; }

$admin_page_title = 'Edit Product';
$admin_active     = 'products';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $d = [
        'name'       => trim($_POST['name'] ?? ''),
        'slug'       => trim($_POST['slug'] ?? '') ?: slugify(trim($_POST['name'] ?? '')),
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
    if ($d['price'] <= 0) $errors[] = 'Price must be > 0.';

    $thumbnail = $product['thumbnail'];
    if (!empty($_FILES['thumbnail']['name'])) {
        $new = upload_image($_FILES['thumbnail'], ROOT_PATH . '/uploads/products');
        if ($new) { $thumbnail = $new; } else { $errors[] = 'Thumbnail upload failed.'; }
    }

    if (empty($errors)) {
        $old_stock = (int)$product['stock'];
        query(
            'UPDATE products SET name=?,slug=?,description=?,short_desc=?,price=?,sale_price=?,sku=?,stock=?,
             category_id=?,thumbnail=?,is_featured=?,is_new=? WHERE id=?',
            'sssssdsiisiii',
            $d['name'],$d['slug'],$d['description'],$d['short_desc'],$d['price'],
            $d['sale_price'],$d['sku'],$d['stock'],$d['category_id'],$thumbnail,$d['is_featured'],$d['is_new'],$id
        );
        // Log stock change if it changed
        if ($d['stock'] !== $old_stock) {
            $change = $d['stock'] - $old_stock;
            $type   = $change > 0 ? 'restock' : 'adjustment';
            query(
                'INSERT INTO stock_logs (product_id, admin_id, type, quantity_change, quantity_before, quantity_after, note)
                 VALUES (?,?,?,?,?,?,?)',
                'iisiiii',
                $id, $_SESSION['user_id'], $type, $change, $old_stock, $d['stock'], 'Updated via product edit form'
            );
        }
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['name'] as $i => $name) {
                if (!$name) continue;
                $file = ['name'=>$name,'type'=>$_FILES['images']['type'][$i],'tmp_name'=>$_FILES['images']['tmp_name'][$i],'size'=>$_FILES['images']['size'][$i]];
                $img = upload_image($file, ROOT_PATH . '/uploads/products');
                if ($img) query('INSERT INTO product_images (product_id,image_path,sort_order) VALUES (?,?,?)', 'isi', $id, $img, $i);
            }
        }
        flash('success', 'Product updated.');
        header('Location: products.php'); exit;
    }
    // Merge post data back
    $product = array_merge($product, $d);
}

$categories = get_categories();
$extra_images = get_product_images($id);
include __DIR__ . '/includes/admin_header.php';
?>
<div class="page-header">
    <div><div class="page-title">Edit Product</div><div class="page-subtitle"><?= e($product['name']) ?></div></div>
    <a href="products.php" class="btn btn-outline">← Back</a>
</div>
<?php foreach ($errors as $e): ?><div class="alert alert-error"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>

<form method="post" action="edit-product.php?id=<?= $id ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start">
        <div>
            <div class="card" style="margin-bottom:20px">
                <div class="card-header"><span class="card-title">Product Information</span></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Name <span class="req">*</span></label>
                            <input type="text" name="name" id="product_name" class="form-control" value="<?= e($product['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="product_slug" class="form-control" value="<?= e($product['slug']) ?>">
                        </div>
                    </div>
                    <div class="form-row full"><div class="form-group">
                        <label class="form-label">Short Description</label>
                        <input type="text" name="short_desc" class="form-control" value="<?= e($product['short_desc'] ?? '') ?>">
                    </div></div>
                    <div class="form-row full"><div class="form-group">
                        <label class="form-label">Full Description</label>
                        <textarea name="description" class="form-control" style="min-height:160px"><?= e($product['description'] ?? '') ?></textarea>
                    </div></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><span class="card-title">Images</span></div>
                <div class="card-body">
                    <?php if (!empty($product['thumbnail'])): ?>
                    <div style="margin-bottom:16px">
                        <p class="form-label" style="margin-bottom:8px">Current Thumbnail</p>
                        <img src="<?= product_thumb($product) ?>" style="width:100px;height:100px;object-fit:cover;border-radius:4px;border:1px solid var(--border)">
                    </div>
                    <?php endif; ?>
                    <div class="form-group" style="margin-bottom:20px">
                        <label class="form-label">Replace Thumbnail</label>
                        <label class="img-upload-box">
                            <div class="icon"><svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
                            <p>Click to upload new thumbnail</p>
                            <input type="file" name="thumbnail" accept="image/*">
                        </label>
                        <div class="img-preview-grid"></div>
                    </div>
                    <?php if (!empty($extra_images)): ?>
                    <div style="margin-bottom:16px">
                        <p class="form-label" style="margin-bottom:8px">Gallery Images</p>
                        <div style="display:flex;gap:10px;flex-wrap:wrap">
                        <?php foreach ($extra_images as $img): ?>
                        <div class="img-preview">
                            <img src="<?= BASE_URL ?>/uploads/products/<?= e($img['image_path']) ?>">
                            <a href="delete-image.php?id=<?= $img['id'] ?>&product=<?= $id ?>" class="img-preview__remove" onclick="return confirm('Remove image?')">&times;</a>
                        </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label class="form-label">Add More Images</label>
                        <label class="img-upload-box">
                            <div class="icon"><svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
                            <p>Upload additional images</p>
                            <p style="font-size:.75rem;color:#aaa">Hold Ctrl / Cmd to select multiple files · JPG, PNG, WEBP · Max 5MB each</p>
                            <input type="file" name="images[]" accept="image/*" multiple>
                        </label>
                        <div class="img-preview-grid"></div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="card" style="margin-bottom:20px">
                <div class="card-header"><span class="card-title">Pricing & Stock</span></div>
                <div class="card-body">
                    <div class="form-group" style="margin-bottom:16px">
                        <label class="form-label">Regular Price <span class="req">*</span></label>
                        <input type="number" name="price" class="form-control" value="<?= e($product['price']) ?>" step="0.01" min="0" required>
                    </div>
                    <div class="form-group" style="margin-bottom:16px">
                        <label class="form-label">Sale Price</label>
                        <input type="number" name="sale_price" class="form-control" value="<?= e($product['sale_price'] ?? '') ?>" step="0.01" min="0">
                    </div>
                    <div class="form-group" style="margin-bottom:16px">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-control" value="<?= e($product['sku'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-control" value="<?= e($product['stock']) ?>" min="0">
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
                            <option value="<?= $c['id'] ?>" <?= $product['category_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:12px">
                        <label class="form-check"><input type="checkbox" name="is_featured" <?= $product['is_featured'] ? 'checked' : '' ?>><label>Featured</label></label>
                        <label class="form-check"><input type="checkbox" name="is_new" <?= $product['is_new'] ? 'checked' : '' ?>><label>New Arrival</label></label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-gold" style="width:100%;padding:14px">Update Product</button>
        </div>
    </div>
</form>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
