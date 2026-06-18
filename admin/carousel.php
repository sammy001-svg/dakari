<?php
require_once __DIR__ . '/includes/admin_init.php';
$admin_page_title = 'Carousel Slides';
$admin_active     = 'carousel';
$errors = [];

if (isset($_GET['delete'])) { query('DELETE FROM carousel_slides WHERE id=?','i',(int)$_GET['delete']); flash('success','Slide deleted.'); header('Location: carousel.php'); exit; }
if (isset($_GET['toggle'])) { $s=fetchOne('SELECT id,is_active FROM carousel_slides WHERE id=?','i',(int)$_GET['toggle']); if($s) query('UPDATE carousel_slides SET is_active=? WHERE id=?','ii',($s['is_active']?0:1),$s['id']); header('Location: carousel.php'); exit; }

if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $d = ['title'=>trim($_POST['title']??''),'subtitle'=>trim($_POST['subtitle']??''),'link_url'=>trim($_POST['link_url']??''),'link_text'=>trim($_POST['link_text']??'Shop Now'),'sort_order'=>(int)($_POST['sort_order']??0),'is_active'=>isset($_POST['is_active'])?1:0];
    $image = null;
    if (!empty($_FILES['image']['name'])) { $image = upload_image($_FILES['image'],ROOT_PATH.'/uploads/carousel'); if (!$image) $errors[] = 'Image upload failed.'; }
    if (empty($errors)) {
        $edit_id = (int)($_POST['edit_id']??0);
        if ($edit_id) {
            $old = fetchOne('SELECT image FROM carousel_slides WHERE id=?','i',$edit_id);
            $img = $image ?: ($old['image']??'');
            query('UPDATE carousel_slides SET title=?,subtitle=?,image=?,link_url=?,link_text=?,sort_order=?,is_active=? WHERE id=?','ssssssii',$d['title'],$d['subtitle'],$img,$d['link_url'],$d['link_text'],$d['sort_order'],$d['is_active'],$edit_id);
            flash('success','Slide updated.');
        } else {
            if (!$image && empty($errors)) $errors[] = 'Image is required.';
            if (empty($errors)) { query('INSERT INTO carousel_slides (title,subtitle,image,link_url,link_text,sort_order,is_active) VALUES (?,?,?,?,?,?,?)','sssssii',$d['title'],$d['subtitle'],$image,$d['link_url'],$d['link_text'],$d['sort_order'],$d['is_active']); flash('success','Slide added.'); }
        }
        if (empty($errors)) { header('Location: carousel.php'); exit; }
    }
}

$slides = fetchAll('SELECT * FROM carousel_slides ORDER BY sort_order');
$edit   = isset($_GET['edit']) ? fetchOne('SELECT * FROM carousel_slides WHERE id=?','i',(int)$_GET['edit']) : null;
include __DIR__ . '/includes/admin_header.php';
?>
<div class="page-header">
    <div><div class="page-title">Carousel Slides</div></div>
</div>
<?php foreach ($errors as $e): ?><div class="alert alert-error"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>

<div style="display:grid;grid-template-columns:1fr 400px;gap:24px;align-items:start">
    <div class="card">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Image</th><th>Title</th><th>Link</th><th>Order</th><th>Active</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($slides as $s): ?>
                <tr>
                    <td><img src="<?= carousel_img($s) ?>" style="width:80px;height:48px;object-fit:cover;border-radius:4px;border:1px solid var(--border)"></td>
                    <td><strong style="font-size:.88rem"><?= e($s['title']) ?></strong><br><span style="font-size:.75rem;color:var(--text-muted)"><?= e(mb_substr($s['subtitle'],0,50)) ?>…</span></td>
                    <td style="font-size:.8rem;color:var(--text-muted)"><?= e($s['link_url']) ?></td>
                    <td><?= $s['sort_order'] ?></td>
                    <td><a href="carousel.php?toggle=<?= $s['id'] ?>"><span class="status-badge <?= $s['is_active']?'status-active':'status-inactive' ?>"><?= $s['is_active']?'Active':'Hidden' ?></span></a></td>
                    <td>
                        <a href="carousel.php?edit=<?= $s['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                        <a href="carousel.php?delete=<?= $s['id'] ?>" class="btn btn-sm btn-danger" data-confirm="Delete this slide?">Del</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span class="card-title"><?= $edit ? 'Edit Slide' : 'Add Slide' ?></span></div>
        <div class="card-body">
            <form method="post" action="carousel.php" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <?php if ($edit): ?><input type="hidden" name="edit_id" value="<?= $edit['id'] ?>"><?php endif; ?>
                <div class="form-group" style="margin-bottom:14px"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="<?= e($edit['title']??'') ?>"></div>
                <div class="form-group" style="margin-bottom:14px"><label class="form-label">Subtitle</label><textarea name="subtitle" class="form-control" style="min-height:80px"><?= e($edit['subtitle']??'') ?></textarea></div>
                <div class="form-group" style="margin-bottom:14px"><label class="form-label">Link URL</label><input type="text" name="link_url" class="form-control" value="<?= e($edit['link_url']??'') ?>" placeholder="shop.php?category=new-arrivals"></div>
                <div class="form-group" style="margin-bottom:14px"><label class="form-label">Button Text</label><input type="text" name="link_text" class="form-control" value="<?= e($edit['link_text']??'Shop Now') ?>"></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= e($edit['sort_order']??0) ?>"></div>
                    <div class="form-group" style="justify-content:center;align-items:center">
                        <label class="form-check" style="margin-top:28px"><input type="checkbox" name="is_active" <?= empty($edit)||$edit['is_active']?'checked':'' ?>><label>Active</label></label>
                    </div>
                </div>
                <?php if ($edit && !empty($edit['image'])): ?>
                <div style="margin-bottom:12px"><p class="form-hint" style="margin-bottom:6px">Current image:</p><img src="<?= carousel_img($edit) ?>" style="width:100%;height:80px;object-fit:cover;border-radius:4px;border:1px solid var(--border)"></div>
                <?php endif; ?>
                <div class="form-group" style="margin-bottom:16px">
                    <label class="form-label">Slide Image <?= $edit?'(replace)':'<span class=req>*</span>' ?></label>
                    <label class="img-upload-box">
                        <div class="icon"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><polyline points="21 15 16 10 5 21"/></svg></div>
                        <p>Click to upload (recommended 1400×580)</p>
                        <input type="file" name="image" accept="image/*">
                    </label>
                    <div class="img-preview-grid"></div>
                </div>
                <button type="submit" class="btn btn-gold" style="width:100%"><?= $edit?'Update Slide':'Add Slide' ?></button>
                <?php if ($edit): ?><a href="carousel.php" class="btn btn-outline" style="width:100%;margin-top:8px">Cancel</a><?php endif; ?>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
