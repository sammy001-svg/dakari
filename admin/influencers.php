<?php
require_once __DIR__ . '/includes/admin_init.php';
$admin_page_title = 'Influencers';
$admin_active     = 'influencers';
$errors = [];

// Delete
if (isset($_GET['delete'])) {
    query('DELETE FROM influencers WHERE id=?','i',(int)$_GET['delete']);
    flash('success','Influencer removed.'); header('Location: influencers.php'); exit;
}
// Toggle featured
if (isset($_GET['toggle'])) {
    $inf = fetchOne('SELECT id,is_featured FROM influencers WHERE id=?','i',(int)$_GET['toggle']);
    if ($inf) { query('UPDATE influencers SET is_featured=? WHERE id=?','ii',($inf['is_featured']?0:1),$inf['id']); }
    header('Location: influencers.php'); exit;
}

// Save
if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $d = [
        'name'          => trim($_POST['name']??''),
        'title'         => trim($_POST['title']??''),
        'bio'           => trim($_POST['bio']??''),
        'instagram_url' => trim($_POST['instagram_url']??''),
        'tiktok_url'    => trim($_POST['tiktok_url']??''),
        'youtube_url'   => trim($_POST['youtube_url']??''),
        'twitter_url'   => trim($_POST['twitter_url']??''),
        'followers_count'=> trim($_POST['followers_count']??''),
        'is_featured'   => isset($_POST['is_featured'])?1:0,
        'sort_order'    => (int)($_POST['sort_order']??0),
    ];
    if (!$d['name']) $errors[] = 'Name is required.';
    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $image = upload_image($_FILES['image'], ROOT_PATH.'/uploads/influencers');
        if (!$image) $errors[] = 'Image upload failed.';
    }
    if (empty($errors)) {
        $edit_id = (int)($_POST['edit_id']??0);
        if ($edit_id) {
            $old = fetchOne('SELECT image FROM influencers WHERE id=?','i',$edit_id);
            $img_val = $image ?: ($old['image']??null);
            query('UPDATE influencers SET name=?,title=?,bio=?,image=?,instagram_url=?,tiktok_url=?,youtube_url=?,twitter_url=?,followers_count=?,is_featured=?,sort_order=? WHERE id=?',
                  'ssssssssiii',  $d['name'],$d['title'],$d['bio'],$img_val,$d['instagram_url'],$d['tiktok_url'],$d['youtube_url'],$d['twitter_url'],$d['followers_count'],$d['is_featured'],$d['sort_order'],$edit_id);
            flash('success','Influencer updated.');
        } else {
            query('INSERT INTO influencers (name,title,bio,image,instagram_url,tiktok_url,youtube_url,twitter_url,followers_count,is_featured,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?)',
                  'sssssssssii', $d['name'],$d['title'],$d['bio'],$image,$d['instagram_url'],$d['tiktok_url'],$d['youtube_url'],$d['twitter_url'],$d['followers_count'],$d['is_featured'],$d['sort_order']);
            flash('success','Influencer added.');
        }
        header('Location: influencers.php'); exit;
    }
}

$all   = fetchAll('SELECT * FROM influencers ORDER BY sort_order,name');
$edit  = isset($_GET['edit']) ? fetchOne('SELECT * FROM influencers WHERE id=?','i',(int)$_GET['edit']) : null;
include __DIR__ . '/includes/admin_header.php';
?>
<div class="page-header">
    <div><div class="page-title">Influencers</div></div>
    <button onclick="document.getElementById('addForm').scrollIntoView({behavior:'smooth'})" class="btn btn-gold">+ Add Influencer</button>
</div>
<?php foreach ($errors as $e): ?><div class="alert alert-error"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>

<div class="card" style="margin-bottom:24px">
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Influencer</th><th>Title</th><th>Followers</th><th>Featured</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($all as $inf): ?>
            <tr>
                <td style="display:flex;align-items:center;gap:12px">
                    <img src="<?= influencer_img($inf) ?>" style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid var(--border)">
                    <strong><?= e($inf['name']) ?></strong>
                </td>
                <td style="color:var(--text-muted);font-size:.85rem"><?= e($inf['title']) ?></td>
                <td><?= e($inf['followers_count']) ?></td>
                <td>
                    <a href="influencers.php?toggle=<?= $inf['id'] ?>">
                        <span class="status-badge <?= $inf['is_featured']?'status-active':'status-inactive' ?>"><?= $inf['is_featured']?'Featured':'Hidden' ?></span>
                    </a>
                </td>
                <td>
                    <a href="influencers.php?edit=<?= $inf['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                    <a href="influencers.php?delete=<?= $inf['id'] ?>" class="btn btn-sm btn-danger" data-confirm="Delete this influencer?">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card" id="addForm">
    <div class="card-header"><span class="card-title"><?= $edit ? 'Edit Influencer' : 'Add Influencer' ?></span></div>
    <div class="card-body">
        <form method="post" action="influencers.php" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <?php if ($edit): ?><input type="hidden" name="edit_id" value="<?= $edit['id'] ?>"><?php endif; ?>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Name <span class="req">*</span></label><input type="text" name="name" class="form-control" value="<?= e($edit['name']??'') ?>" required></div>
                <div class="form-group"><label class="form-label">Title / Niche</label><input type="text" name="title" class="form-control" value="<?= e($edit['title']??'') ?>" placeholder="Fashion & Lifestyle"></div>
            </div>
            <div class="form-row full"><div class="form-group"><label class="form-label">Bio</label><textarea name="bio" class="form-control"><?= e($edit['bio']??'') ?></textarea></div></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Followers Count</label><input type="text" name="followers_count" class="form-control" value="<?= e($edit['followers_count']??'') ?>" placeholder="245K"></div>
                <div class="form-group"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= e($edit['sort_order']??0) ?>"></div>
            </div>
            <div class="form-row three">
                <div class="form-group"><label class="form-label">Instagram URL</label><input type="url" name="instagram_url" class="form-control" value="<?= e($edit['instagram_url']??'') ?>"></div>
                <div class="form-group"><label class="form-label">TikTok URL</label><input type="url" name="tiktok_url" class="form-control" value="<?= e($edit['tiktok_url']??'') ?>"></div>
                <div class="form-group"><label class="form-label">YouTube URL</label><input type="url" name="youtube_url" class="form-control" value="<?= e($edit['youtube_url']??'') ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Profile Image</label>
                    <label class="img-upload-box">
                        <div class="icon"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
                        <p>Click to upload</p>
                        <input type="file" name="image" accept="image/*">
                    </label>
                    <div class="img-preview-grid"></div>
                </div>
                <div class="form-group" style="justify-content:center">
                    <label class="form-check" style="margin-top:32px"><input type="checkbox" name="is_featured" <?= !empty($edit['is_featured'])?'checked':'' ?>><label>Show as Featured</label></label>
                </div>
            </div>
            <button type="submit" class="btn btn-gold"><?= $edit ? 'Update Influencer' : 'Add Influencer' ?></button>
            <?php if ($edit): ?><a href="influencers.php" class="btn btn-outline" style="margin-left:10px">Cancel</a><?php endif; ?>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
