<?php
require_once __DIR__ . '/includes/admin_init.php';
$admin_page_title = 'FAQs';
$admin_active     = 'faqs';
$csrf = generate_csrf();

/* ── Actions ── */
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {

    if ($action === 'add') {
        $cat  = trim($_POST['category'] ?? '');
        $q    = trim($_POST['question'] ?? '');
        $a    = trim($_POST['answer']   ?? '');
        $ord  = (int)($_POST['sort_order'] ?? 0);
        if ($cat && $q && $a) {
            query('INSERT INTO faqs (category,question,answer,sort_order) VALUES (?,?,?,?)',
                  'sssi', $cat, $q, $a, $ord);
            flash('success', 'FAQ added.');
        } else {
            flash('error', 'Category, question and answer are all required.');
        }
    }

    if ($action === 'edit') {
        $id  = (int)($_POST['id'] ?? 0);
        $cat = trim($_POST['category'] ?? '');
        $q   = trim($_POST['question'] ?? '');
        $a   = trim($_POST['answer']   ?? '');
        $ord = (int)($_POST['sort_order'] ?? 0);
        $act = (int)(isset($_POST['is_active']) ? 1 : 0);
        if ($id && $cat && $q && $a) {
            query('UPDATE faqs SET category=?,question=?,answer=?,sort_order=?,is_active=? WHERE id=?',
                  'sssiii', $cat, $q, $a, $ord, $act, $id);
            flash('success', 'FAQ updated.');
        } else {
            flash('error', 'Category, question and answer are all required.');
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            query('DELETE FROM faqs WHERE id=?', 'i', $id);
            flash('success', 'FAQ deleted.');
        }
    }

    header('Location: faqs.php'); exit;
}

// Collect all existing category names for the datalist
$all_faqs  = fetchAll('SELECT * FROM faqs ORDER BY category, sort_order, id');
$categories = array_unique(array_column($all_faqs, 'category'));

// Group by category
$grouped = [];
foreach ($all_faqs as $row) {
    $grouped[$row['category']][] = $row;
}

$edit_id = (int)($_GET['edit'] ?? 0);
$edit_faq = $edit_id ? fetchOne('SELECT * FROM faqs WHERE id=?', 'i', $edit_id) : null;

include __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">FAQs</h1>
        <p class="admin-page-sub"><?= count($all_faqs) ?> question<?= count($all_faqs) !== 1 ? 's' : '' ?> across <?= count($grouped) ?> categories</p>
    </div>
    <a href="#add-faq" class="btn btn-green" style="scroll-behavior:smooth">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add FAQ
    </a>
</div>

<?php if ($edit_faq): ?>
<!-- ── Edit form ── -->
<div class="admin-card" style="margin-bottom:24px;border:2px solid var(--green)">
    <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:20px;font-size:1rem;border-bottom:1px solid var(--border);padding-bottom:12px">
        Edit FAQ #<?= $edit_faq['id'] ?>
    </h3>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="action"     value="edit">
        <input type="hidden" name="id"         value="<?= $edit_faq['id'] ?>">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
            <div>
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px">Category <span style="color:var(--gold)">*</span></label>
                <input type="text" name="category" list="cat-list" class="form-control"
                       value="<?= e($edit_faq['category']) ?>" required style="width:100%">
            </div>
            <div>
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" min="0"
                       value="<?= (int)$edit_faq['sort_order'] ?>" style="width:100%">
            </div>
            <div style="grid-column:1/-1">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px">Question <span style="color:var(--gold)">*</span></label>
                <input type="text" name="question" class="form-control"
                       value="<?= e($edit_faq['question']) ?>" required style="width:100%">
            </div>
            <div style="grid-column:1/-1">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px">Answer <span style="color:var(--gold)">*</span></label>
                <textarea name="answer" class="form-control" rows="5" required
                          style="width:100%"><?= e($edit_faq['answer']) ?></textarea>
            </div>
            <div>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.88rem;margin-top:8px">
                    <input type="checkbox" name="is_active" value="1" <?= $edit_faq['is_active'] ? 'checked' : '' ?>
                           style="accent-color:#1B4332;width:16px;height:16px">
                    Visible on FAQ page
                </label>
            </div>
        </div>
        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-green">Save Changes</button>
            <a href="faqs.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>

<?php if (empty($grouped)): ?>
<div class="admin-card" style="text-align:center;padding:48px">
    <svg width="48" height="48" fill="none" stroke="#ccc" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:16px"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    <p style="color:var(--text-muted);margin-bottom:20px">No FAQs yet. Add your first one below.</p>
</div>
<?php else: ?>
<?php foreach ($grouped as $cat => $items): ?>
<div class="admin-card" style="margin-bottom:20px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid var(--border);padding-bottom:12px">
        <h3 style="font-family:var(--font-serif);color:var(--green);font-size:1rem;margin:0"><?= e($cat) ?></h3>
        <span style="font-size:.78rem;color:var(--text-muted)"><?= count($items) ?> item<?= count($items) !== 1 ? 's' : '' ?></span>
    </div>
    <div style="display:flex;flex-direction:column;gap:12px">
        <?php foreach ($items as $faq): ?>
        <div style="border:1px solid var(--border);border-radius:var(--radius);padding:14px 16px;<?= !$faq['is_active'] ? 'opacity:.5;' : '' ?>">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px">
                <div style="flex:1;min-width:0">
                    <p style="font-weight:600;font-size:.9rem;margin:0 0 4px;color:var(--text)"><?= e($faq['question']) ?></p>
                    <p style="font-size:.82rem;color:var(--text-muted);margin:0;line-height:1.5;white-space:pre-wrap"><?= e(mb_strimwidth($faq['answer'], 0, 160, '…')) ?></p>
                </div>
                <div style="display:flex;gap:6px;flex-shrink:0">
                    <?php if (!$faq['is_active']): ?>
                    <span style="font-size:.72rem;background:#f3f4f6;color:#999;padding:3px 8px;border-radius:3px;align-self:center">Hidden</span>
                    <?php endif; ?>
                    <a href="faqs.php?edit=<?= $faq['id'] ?>"
                       style="padding:5px 12px;border:1px solid #ddd;border-radius:4px;font-size:.8rem;color:var(--text);text-decoration:none;background:#fff">Edit</a>
                    <form method="POST" style="display:inline"
                          onsubmit="return confirm('Delete this FAQ?')">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id"     value="<?= $faq['id'] ?>">
                        <button type="submit"
                                style="padding:5px 12px;border:1px solid #fca5a5;border-radius:4px;font-size:.8rem;color:#dc2626;background:#fff;cursor:pointer">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<!-- ── Add new FAQ ── -->
<div class="admin-card" id="add-faq" style="border:1px dashed var(--border)">
    <h3 style="font-family:var(--font-serif);color:var(--green);margin-bottom:20px;font-size:1rem;border-bottom:1px solid var(--border);padding-bottom:12px">
        Add New FAQ
    </h3>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="action"     value="add">
        <datalist id="cat-list">
            <?php foreach ($categories as $c): ?>
            <option value="<?= e($c) ?>">
            <?php endforeach; ?>
        </datalist>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
            <div>
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px">Category <span style="color:var(--gold)">*</span></label>
                <input type="text" name="category" list="cat-list" class="form-control" required
                       style="width:100%" placeholder="e.g. Orders & Shipping">
                <span style="font-size:.75rem;color:#888;margin-top:4px;display:block">Type a new category or pick an existing one.</span>
            </div>
            <div>
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" min="0" value="0" style="width:100%">
            </div>
            <div style="grid-column:1/-1">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px">Question <span style="color:var(--gold)">*</span></label>
                <input type="text" name="question" class="form-control" required
                       style="width:100%" placeholder="What is your return policy?">
            </div>
            <div style="grid-column:1/-1">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px">Answer <span style="color:var(--gold)">*</span></label>
                <textarea name="answer" class="form-control" rows="5" required
                          style="width:100%" placeholder="Write the full answer here…"></textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-green">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add FAQ
        </button>
    </form>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
