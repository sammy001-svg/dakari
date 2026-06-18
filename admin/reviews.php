<?php
require_once __DIR__ . '/../admin/includes/admin_init.php';

$admin_page_title = 'Reviews';
$admin_active     = 'reviews';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action    = $_POST['action']    ?? '';
    $review_id = (int)($_POST['review_id'] ?? 0);

    if ($action === 'approve' && $review_id) {
        $rev = fetchOne('SELECT product_id FROM product_reviews WHERE id = ?', 'i', $review_id);
        query('UPDATE product_reviews SET is_approved = 1 WHERE id = ?', 'i', $review_id);
        if ($rev) update_product_rating((int)$rev['product_id']);
        flash('success', 'Review approved.');
    }
    if ($action === 'unapprove' && $review_id) {
        $rev = fetchOne('SELECT product_id FROM product_reviews WHERE id = ?', 'i', $review_id);
        query('UPDATE product_reviews SET is_approved = 0 WHERE id = ?', 'i', $review_id);
        if ($rev) update_product_rating((int)$rev['product_id']);
        flash('success', 'Review unapproved.');
    }
    if ($action === 'delete' && $review_id) {
        $rev = fetchOne('SELECT product_id FROM product_reviews WHERE id = ?', 'i', $review_id);
        query('DELETE FROM product_reviews WHERE id = ?', 'i', $review_id);
        if ($rev) update_product_rating((int)$rev['product_id']);
        flash('success', 'Review deleted.');
    }
    if ($action === 'bulk_approve') {
        $ids = array_map('intval', $_POST['ids'] ?? []);
        if ($ids) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $types = str_repeat('i', count($ids));
            $affected_products = fetchAll("SELECT DISTINCT product_id FROM product_reviews WHERE id IN ($placeholders)", $types, ...$ids);
            query("UPDATE product_reviews SET is_approved = 1 WHERE id IN ($placeholders)", $types, ...$ids);
            foreach ($affected_products as $ap) update_product_rating((int)$ap['product_id']);
            flash('success', count($ids) . ' reviews approved.');
        }
    }
    if ($action === 'bulk_delete') {
        $ids = array_map('intval', $_POST['ids'] ?? []);
        if ($ids) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $types = str_repeat('i', count($ids));
            $affected_products = fetchAll("SELECT DISTINCT product_id FROM product_reviews WHERE id IN ($placeholders)", $types, ...$ids);
            query("DELETE FROM product_reviews WHERE id IN ($placeholders)", $types, ...$ids);
            foreach ($affected_products as $ap) update_product_rating((int)$ap['product_id']);
            flash('success', count($ids) . ' reviews deleted.');
        }
    }
    header('Location: reviews.php?' . http_build_query(array_filter(['status' => $_GET['status'] ?? '', 'product' => $_GET['product'] ?? '', 'q' => $_GET['q'] ?? '']))); exit;
}

// Filters
$filter_status  = $_GET['status']  ?? '';
$filter_product = (int)($_GET['product'] ?? 0);
$search         = trim($_GET['q'] ?? '');
$page_num       = max(1, (int)($_GET['page'] ?? 1));
$per_page       = 20;

$where  = ['1=1'];
$params = [];
$types  = '';

if ($filter_status === 'pending') { $where[] = 'r.is_approved = 0'; }
elseif ($filter_status === 'approved') { $where[] = 'r.is_approved = 1'; }

if ($filter_product) { $where[] = 'r.product_id = ?'; $params[] = $filter_product; $types .= 'i'; }

if ($search !== '') {
    $where[] = '(r.title LIKE ? OR r.body LIKE ? OR r.guest_name LIKE ? OR u.first_name LIKE ?)';
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
    $types .= 'ssss';
}

$where_sql = implode(' AND ', $where);

$total_count = (int)(fetchOne(
    "SELECT COUNT(*) AS cnt FROM product_reviews r LEFT JOIN users u ON u.id = r.user_id WHERE $where_sql",
    $types ?: '', ...$params
)['cnt'] ?? 0);

$offset  = ($page_num - 1) * $per_page;
$reviews = [];
if ($total_count > 0) {
    $all_params = array_merge($params, [$per_page, $offset]);
    $all_types  = ($types ?: '') . 'ii';
    $reviews = fetchAll(
        "SELECT r.*, p.name AS product_name, p.slug AS product_slug,
                u.first_name, u.last_name
         FROM product_reviews r
         LEFT JOIN products p ON p.id = r.product_id
         LEFT JOIN users u    ON u.id = r.user_id
         WHERE $where_sql
         ORDER BY r.is_approved ASC, r.created_at DESC
         LIMIT ? OFFSET ?",
        $all_types, ...$all_params
    );
}

$pagination  = paginate($total_count, $per_page, $page_num);
$pending_cnt = (int)(fetchOne('SELECT COUNT(*) AS c FROM product_reviews WHERE is_approved = 0')['c'] ?? 0);

// Products for filter dropdown
$all_products = fetchAll('SELECT id, name FROM products WHERE is_active = 1 ORDER BY name');

require_once __DIR__ . '/includes/admin_header.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px">
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <a href="reviews.php" class="btn btn-sm <?= $filter_status === '' ? 'btn-green' : 'btn-outline-green' ?>">All</a>
        <a href="reviews.php?status=pending" class="btn btn-sm <?= $filter_status === 'pending' ? 'btn-green' : 'btn-outline-green' ?>" style="display:flex;align-items:center;gap:6px">
            Pending
            <?php if ($pending_cnt > 0): ?>
            <span style="background:var(--gold);color:var(--dark);font-size:.68rem;font-weight:700;min-width:18px;height:18px;border-radius:9px;padding:0 4px;display:inline-flex;align-items:center;justify-content:center"><?= $pending_cnt ?></span>
            <?php endif; ?>
        </a>
        <a href="reviews.php?status=approved" class="btn btn-sm <?= $filter_status === 'approved' ? 'btn-green' : 'btn-outline-green' ?>">Approved</a>
    </div>
    <form method="get" action="reviews.php" style="display:flex;gap:8px;flex-wrap:wrap">
        <?php if ($filter_status): ?><input type="hidden" name="status" value="<?= e($filter_status) ?>"><?php endif; ?>
        <select name="product" class="form-control" style="min-width:180px;font-size:.85rem;height:36px">
            <option value="">All Products</option>
            <?php foreach ($all_products as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $filter_product === (int)$p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="q" class="form-control" placeholder="Search reviews…" value="<?= e($search) ?>" style="width:200px;height:36px;font-size:.85rem">
        <button type="submit" class="btn btn-outline-green btn-sm">Filter</button>
    </form>
</div>

<?php if (empty($reviews)): ?>
<div class="empty-state" style="padding:60px 0">
    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="opacity:.3"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
    <p style="margin-top:12px;color:var(--text-muted)">No reviews found.</p>
</div>

<?php else: ?>
<form method="post" action="reviews.php" id="bulkForm">
    <?= csrf_field() ?>
    <input type="hidden" name="action" id="bulkAction" value="">

    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;font-size:.85rem;color:var(--text-muted)">
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
            <input type="checkbox" id="selectAll"> Select all on page
        </label>
        <button type="button" class="btn btn-sm btn-outline-green" onclick="bulkDo('bulk_approve')">Approve selected</button>
        <button type="button" class="btn btn-sm" style="background:none;border:1px solid #c0392b;color:#c0392b;padding:5px 12px;border-radius:var(--radius);cursor:pointer" onclick="bulkDo('bulk_delete')">Delete selected</button>
        <span style="margin-left:auto"><?= $total_count ?> review<?= $total_count !== 1 ? 's' : '' ?></span>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:36px"><input type="checkbox" id="selectAllHead"></th>
                    <th>Reviewer</th>
                    <th>Product</th>
                    <th style="width:100px">Rating</th>
                    <th>Review</th>
                    <th style="width:90px">Date</th>
                    <th style="width:90px">Status</th>
                    <th style="width:120px">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $rev):
                    $author = !empty($rev['first_name'])
                        ? e($rev['first_name'] . ' ' . $rev['last_name'])
                        : e($rev['guest_name'] ?? 'Guest');
                ?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?= $rev['id'] ?>" class="row-check"></td>
                    <td>
                        <p style="font-weight:600;font-size:.88rem"><?= $author ?></p>
                        <?php if (!empty($rev['guest_email'])): ?>
                        <p style="font-size:.75rem;color:var(--text-muted)"><?= e($rev['guest_email']) ?></p>
                        <?php elseif (empty($rev['first_name'])): ?>
                        <p style="font-size:.75rem;color:var(--text-muted)">Guest</p>
                        <?php else: ?>
                        <p style="font-size:.75rem;color:var(--text-muted)">Registered user</p>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= BASE_URL ?>/product.php?slug=<?= e($rev['product_slug']) ?>" target="_blank" style="font-size:.85rem;color:var(--green);text-decoration:none;font-weight:600"><?= e($rev['product_name']) ?></a>
                    </td>
                    <td>
                        <div style="display:flex;gap:2px;color:var(--gold)">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <svg width="13" height="13" viewBox="0 0 24 24" <?= $i <= $rev['rating'] ? 'fill="currentColor"' : 'fill="none" stroke="currentColor" stroke-width="1.5"' ?>><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            <?php endfor; ?>
                        </div>
                    </td>
                    <td style="max-width:300px">
                        <?php if (!empty($rev['title'])): ?><p style="font-weight:600;font-size:.85rem;margin-bottom:3px"><?= e($rev['title']) ?></p><?php endif; ?>
                        <p style="font-size:.83rem;color:var(--text-muted);overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical"><?= e($rev['body']) ?></p>
                    </td>
                    <td style="font-size:.78rem;color:var(--text-muted)"><?= date('M j, Y', strtotime($rev['created_at'])) ?></td>
                    <td>
                        <?php if ($rev['is_approved']): ?>
                        <span class="status-badge status-active">Approved</span>
                        <?php else: ?>
                        <span class="status-badge status-pending">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <?php if (!$rev['is_approved']): ?>
                            <form method="post" style="display:inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-green" style="padding:4px 10px;font-size:.75rem">Approve</button>
                            </form>
                            <?php else: ?>
                            <form method="post" style="display:inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="unapprove">
                                <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-green" style="padding:4px 10px;font-size:.75rem">Unpublish</button>
                            </form>
                            <?php endif; ?>
                            <form method="post" style="display:inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                                <button type="submit" class="btn-delete-icon" data-confirm="Delete this review?" style="background:none;border:none;cursor:pointer;color:#c0392b;padding:4px" title="Delete">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['pages'] > 1): ?>
    <nav class="pagination" style="margin-top:24px">
        <?php if ($page_num > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page_num - 1])) ?>" class="page-link">&lsaquo;</a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>" class="page-link <?= $p === $page_num ? 'active' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>
        <?php if ($page_num < $pagination['pages']): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page_num + 1])) ?>" class="page-link">&rsaquo;</a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>

</form>
<?php endif; ?>

<script>
// Select all checkboxes
document.getElementById('selectAll')?.addEventListener('change', function () {
    document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked);
});
document.getElementById('selectAllHead')?.addEventListener('change', function () {
    document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked);
    document.getElementById('selectAll').checked = this.checked;
});

function bulkDo(action) {
    const checked = [...document.querySelectorAll('.row-check:checked')];
    if (!checked.length) { alert('Select at least one review.'); return; }
    if (action === 'bulk_delete' && !confirm('Delete ' + checked.length + ' review(s)?')) return;
    document.getElementById('bulkAction').value = action;
    document.getElementById('bulkForm').submit();
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
