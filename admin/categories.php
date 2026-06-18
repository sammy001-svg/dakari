<?php
require_once __DIR__ . '/includes/admin_init.php';
$admin_page_title = 'Categories';
$admin_active     = 'categories';

if (isset($_GET['delete'])) { query('DELETE FROM categories WHERE id=?','i',(int)$_GET['delete']); flash('success','Category deleted.'); header('Location: categories.php'); exit; }

if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $d = ['name'=>trim($_POST['name']??''),'slug'=>trim($_POST['slug']??''),'description'=>trim($_POST['description']??''),'sort_order'=>(int)($_POST['sort_order']??0),'is_active'=>isset($_POST['is_active'])?1:0];
    if (!$d['name']) { flash('error','Name required.'); }
    else {
        if (!$d['slug']) $d['slug'] = slugify($d['name']);
        $edit_id = (int)($_POST['edit_id']??0);
        if ($edit_id) { query('UPDATE categories SET name=?,slug=?,description=?,sort_order=?,is_active=? WHERE id=?','sssiii',$d['name'],$d['slug'],$d['description'],$d['sort_order'],$d['is_active'],$edit_id); flash('success','Category updated.'); }
        else { query('INSERT INTO categories (name,slug,description,sort_order,is_active) VALUES (?,?,?,?,?)','sssii',$d['name'],$d['slug'],$d['description'],$d['sort_order'],$d['is_active']); flash('success','Category added.'); }
        header('Location: categories.php'); exit;
    }
}

$cats = fetchAll('SELECT c.*,(SELECT COUNT(*) FROM products p WHERE p.category_id=c.id AND p.is_active=1) as product_count FROM categories c ORDER BY sort_order,name');
$edit = isset($_GET['edit']) ? fetchOne('SELECT * FROM categories WHERE id=?','i',(int)$_GET['edit']) : null;
include __DIR__ . '/includes/admin_header.php';
?>
<div class="page-header"><div><div class="page-title">Categories</div></div></div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start">
    <div class="card">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Name</th><th>Slug</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($cats as $c): ?>
                <tr>
                    <td><strong><?= e($c['name']) ?></strong></td>
                    <td style="font-size:.8rem;color:var(--text-muted)"><?= e($c['slug']) ?></td>
                    <td><?= $c['product_count'] ?></td>
                    <td><span class="status-badge <?= $c['is_active']?'status-active':'status-inactive' ?>"><?= $c['is_active']?'Active':'Hidden' ?></span></td>
                    <td>
                        <a href="categories.php?edit=<?= $c['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                        <a href="categories.php?delete=<?= $c['id'] ?>" class="btn btn-sm btn-danger" data-confirm="Delete category '<?= e($c['name']) ?>'?">Del</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><span class="card-title"><?= $edit?'Edit Category':'Add Category' ?></span></div>
        <div class="card-body">
            <form method="post" action="categories.php">
                <?= csrf_field() ?>
                <?php if ($edit): ?><input type="hidden" name="edit_id" value="<?= $edit['id'] ?>"><?php endif; ?>
                <div class="form-group" style="margin-bottom:14px"><label class="form-label">Name <span class="req">*</span></label><input type="text" name="name" class="form-control" value="<?= e($edit['name']??'') ?>" required></div>
                <div class="form-group" style="margin-bottom:14px"><label class="form-label">Slug</label><input type="text" name="slug" class="form-control" value="<?= e($edit['slug']??'') ?>" placeholder="auto-generated"></div>
                <div class="form-group" style="margin-bottom:14px"><label class="form-label">Description</label><textarea name="description" class="form-control" style="min-height:80px"><?= e($edit['description']??'') ?></textarea></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= e($edit['sort_order']??0) ?>"></div>
                    <div class="form-group" style="justify-content:center"><label class="form-check" style="margin-top:28px"><input type="checkbox" name="is_active" <?= empty($edit)||$edit['is_active']?'checked':'' ?>><label>Active</label></label></div>
                </div>
                <button type="submit" class="btn btn-gold" style="width:100%"><?= $edit?'Update':'Add Category' ?></button>
                <?php if ($edit): ?><a href="categories.php" class="btn btn-outline" style="width:100%;margin-top:8px">Cancel</a><?php endif; ?>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
