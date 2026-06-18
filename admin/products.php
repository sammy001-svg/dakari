<?php
require_once __DIR__ . '/includes/admin_init.php';
$admin_page_title = 'Products';
$admin_active     = 'products';

// Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    query('UPDATE products SET is_active = 0 WHERE id = ?', 'i', (int)$_GET['delete']);
    flash('success', 'Product deactivated.');
    header('Location: products.php'); exit;
}

$search = trim($_GET['q'] ?? '');
$cat_id = (int)($_GET['cat'] ?? 0);
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 20; $offset = ($page - 1) * $limit;

$where = ['p.is_active = 1'];
$params = []; $types = '';
if ($search) { $where[] = 'p.name LIKE ?'; $params[] = "%$search%"; $types .= 's'; }
if ($cat_id) { $where[] = 'p.category_id = ?'; $params[] = $cat_id; $types .= 'i'; }
$w = implode(' AND ', $where);

$total    = fetchOne("SELECT COUNT(*) as n FROM products p WHERE $w", $types, ...$params)['n'] ?? 0;
$products = fetchAll("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE $w ORDER BY p.created_at DESC LIMIT ? OFFSET ?", $types.'ii', ...[...$params,$limit,$offset]);
$all_cats = get_categories();

include __DIR__ . '/includes/admin_header.php';
?>
<div class="page-header">
    <div><div class="page-title">Products</div><div class="page-subtitle"><?= $total ?> products</div></div>
    <a href="add-product.php" class="btn btn-gold">+ Add Product</a>
</div>

<div class="card">
    <div class="card-header">
        <form method="get" action="products.php" style="display:flex;gap:10px;flex-wrap:wrap">
            <div class="search-box">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search products…" id="tableSearch">
            </div>
            <select name="cat" class="filter-select">
                <option value="">All Categories</option>
                <?php foreach ($all_cats as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $cat_id == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-green btn-sm">Filter</button>
            <?php if ($search || $cat_id): ?><a href="products.php" class="btn btn-outline btn-sm">Clear</a><?php endif; ?>
        </form>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:12px">
                        <img src="<?= product_thumb($p) ?>" class="product-thumb-sm" alt="">
                        <div>
                            <p style="font-weight:600;font-size:.88rem"><?= e($p['name']) ?></p>
                            <p style="font-size:.75rem;color:var(--text-muted)"><?= e($p['sku'] ?? '') ?></p>
                        </div>
                    </div>
                </td>
                <td><?= e($p['cat_name'] ?? '—') ?></td>
                <td>
                    <?php if (is_on_sale($p)): ?>
                    <span style="color:var(--gold);font-weight:700"><?= money((float)$p['sale_price']) ?></span>
                    <span style="text-decoration:line-through;color:var(--text-muted);font-size:.8rem"><?= money((float)$p['price']) ?></span>
                    <?php else: ?>
                    <?= money((float)$p['price']) ?>
                    <?php endif; ?>
                </td>
                <td><span style="color:<?= $p['stock']==0?'#c0392b':($p['stock']<=5?'#e67e22':'var(--green)') ?>;font-weight:600"><?= $p['stock'] ?></span></td>
                <td>
                    <?php if ($p['is_featured']): ?><span class="status-badge status-active" style="margin-right:4px">Featured</span><?php endif; ?>
                    <?php if ($p['is_new']): ?><span class="status-badge" style="background:#e8f0fe;color:#1a56db">New</span><?php endif; ?>
                </td>
                <td>
                    <div class="actions">
                        <a href="edit-product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline" title="Edit">Edit</a>
                        <a href="<?= BASE_URL ?>/product.php?slug=<?= e($p['slug']) ?>" target="_blank" class="btn btn-sm btn-outline" title="View">View</a>
                        <a href="products.php?delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger" data-confirm="Delete this product?" title="Delete">Del</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
            <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted)">No products found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (ceil($total/$limit) > 1): ?>
    <div class="card-footer" style="display:flex;gap:8px">
        <?php for ($p=1; $p<=ceil($total/$limit); $p++): ?>
        <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>" class="btn btn-sm <?= $p==$page?'btn-green':'btn-outline' ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
