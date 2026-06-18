<?php
require_once __DIR__ . '/includes/admin_init.php';
$page_title = 'Contact Messages';

/* ── Actions ───────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['message_id'] ?? 0);

    if ($action === 'mark_read' && $id) {
        query("UPDATE contact_messages SET status='read' WHERE id=?", 'i', $id);
        flash('success', 'Message marked as read.');

    } elseif ($action === 'mark_replied' && $id) {
        $note = trim($_POST['admin_note'] ?? '');
        query("UPDATE contact_messages SET status='replied', admin_note=? WHERE id=?", 'si', $note ?: null, $id);
        flash('success', 'Message marked as replied.');

    } elseif ($action === 'delete' && $id) {
        query("DELETE FROM contact_messages WHERE id=?", 'i', $id);
        flash('success', 'Message deleted.');

    } elseif ($action === 'bulk_delete') {
        $ids = array_map('intval', (array)($_POST['selected'] ?? []));
        if ($ids) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $types = str_repeat('i', count($ids));
            query("DELETE FROM contact_messages WHERE id IN ($placeholders)", $types, ...$ids);
            flash('success', count($ids) . ' message(s) deleted.');
        }
    }
    header('Location: messages.php' . (isset($_GET['view']) ? '?view=' . (int)$_GET['view'] : ''));
    exit;
}

/* ── Filters ────────────────────────────────────────────────── */
$status_filter   = $_GET['status']   ?? '';
$category_filter = $_GET['category'] ?? '';
$search          = trim($_GET['search'] ?? '');

$where = ['1=1'];
$params = [];
$types  = '';

if ($status_filter && in_array($status_filter, ['new','read','replied'])) {
    $where[] = 'm.status = ?'; $params[] = $status_filter; $types .= 's';
}
if ($category_filter) {
    $where[] = 'm.category = ?'; $params[] = $category_filter; $types .= 's';
}
if ($search) {
    $like = "%$search%";
    $where[] = '(m.name LIKE ? OR m.email LIKE ? OR m.subject LIKE ? OR m.message LIKE ?)';
    $params = array_merge($params, [$like,$like,$like,$like]); $types .= 'ssss';
}

$sql  = 'SELECT m.* FROM contact_messages m WHERE ' . implode(' AND ', $where) . ' ORDER BY m.created_at DESC';
$messages = $params ? fetchAll($sql, $types, ...$params) : fetchAll($sql);

/* View single */
$view_msg = null;
if (isset($_GET['view'])) {
    $view_msg = fetchOne('SELECT * FROM contact_messages WHERE id=?', 'i', (int)$_GET['view']);
    if ($view_msg && $view_msg['status'] === 'new') {
        query("UPDATE contact_messages SET status='read' WHERE id=?", 'i', $view_msg['id']);
        $view_msg['status'] = 'read';
    }
}

/* Counts */
$counts = [
    'all'     => fetchOne('SELECT COUNT(*) n FROM contact_messages')['n'],
    'new'     => fetchOne("SELECT COUNT(*) n FROM contact_messages WHERE status='new'")['n'],
    'read'    => fetchOne("SELECT COUNT(*) n FROM contact_messages WHERE status='read'")['n'],
    'replied' => fetchOne("SELECT COUNT(*) n FROM contact_messages WHERE status='replied'")['n'],
];

$categories = fetchAll('SELECT DISTINCT category FROM contact_messages WHERE category IS NOT NULL AND category != "" ORDER BY category');
$csrf = generate_csrf();

include __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Contact Messages</h1>
        <p class="admin-page-sub">Customer enquiries submitted via the contact form</p>
    </div>
</div>

<!-- Stat tabs -->
<div class="admin-stat-tabs">
    <?php foreach ([
        ['all',     'All Messages',  $counts['all'],     ''],
        ['new',     'New',           $counts['new'],     'tab--new'],
        ['read',    'Read',          $counts['read'],    ''],
        ['replied', 'Replied',       $counts['replied'], 'tab--replied'],
    ] as [$val,$label,$cnt,$cls]): ?>
    <a href="?status=<?= $val === 'all' ? '' : $val ?>" class="admin-stat-tab <?= $cls ?> <?= ($status_filter === $val || ($val === 'all' && !$status_filter)) ? 'active' : '' ?>">
        <?= e($label) ?> <span class="admin-stat-tab__count"><?= $cnt ?></span>
    </a>
    <?php endforeach; ?>
</div>

<?php if ($view_msg): ?>
<!-- ── Single Message View ─────────────────────────────────── -->
<div class="admin-card" style="max-width:760px">
    <div class="msg-view-header">
        <div>
            <h2 class="msg-view-name"><?= e($view_msg['name']) ?></h2>
            <span class="msg-view-meta">
                <a href="mailto:<?= e($view_msg['email']) ?>"><?= e($view_msg['email']) ?></a>
                <?php if ($view_msg['phone']): ?> &middot; <?= e($view_msg['phone']) ?><?php endif; ?>
                &middot; <?= date('d M Y, H:i', strtotime($view_msg['created_at'])) ?>
            </span>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            <?php $sc = ['new'=>'badge-new','read'=>'badge-info','replied'=>'badge-success'];?>
            <span class="badge <?= $sc[$view_msg['status']] ?? '' ?>"><?= ucfirst($view_msg['status']) ?></span>
            <a href="messages.php<?= $status_filter ? '?status='.$status_filter : '' ?>" class="btn btn-sm btn-outline-green">← Back</a>
        </div>
    </div>

    <?php if ($view_msg['category'] || $view_msg['subject']): ?>
    <div class="msg-view-meta-row">
        <?php if ($view_msg['category']): ?><span><strong>Category:</strong> <?= e($view_msg['category']) ?></span><?php endif; ?>
        <?php if ($view_msg['subject']): ?><span><strong>Subject:</strong> <?= e($view_msg['subject']) ?></span><?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="msg-view-body"><?= nl2br(e($view_msg['message'])) ?></div>

    <?php if ($view_msg['admin_note']): ?>
    <div class="msg-admin-note"><strong>Admin note:</strong> <?= nl2br(e($view_msg['admin_note'])) ?></div>
    <?php endif; ?>

    <!-- Actions -->
    <div class="msg-view-actions">
        <a href="mailto:<?= e($view_msg['email']) ?>?subject=Re: <?= rawurlencode($view_msg['subject'] ?: 'Your Dakari enquiry') ?>" class="btn btn-green">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Reply via Email
        </a>

        <?php if ($view_msg['status'] !== 'replied'): ?>
        <form method="POST" style="display:flex;flex-direction:column;gap:8px;flex:1">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="action" value="mark_replied">
            <input type="hidden" name="message_id" value="<?= $view_msg['id'] ?>">
            <textarea name="admin_note" class="form-control" rows="2" placeholder="Optional internal note…"><?= e($view_msg['admin_note'] ?? '') ?></textarea>
            <button type="submit" class="btn btn-outline-green">Mark as Replied</button>
        </form>
        <?php endif; ?>

        <form method="POST" onsubmit="return confirm('Delete this message?')">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="message_id" value="<?= $view_msg['id'] ?>">
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    </div>
</div>

<?php else: ?>
<!-- ── Message List ────────────────────────────────────────── -->
<div class="admin-card">
    <!-- Filters -->
    <form method="GET" class="admin-filter-bar">
        <?php if ($status_filter): ?><input type="hidden" name="status" value="<?= e($status_filter) ?>"><?php endif; ?>
        <input type="text" name="search" class="form-control" placeholder="Search name, email, subject…" value="<?= e($search) ?>" style="max-width:280px">
        <?php if ($categories): ?>
        <select name="category" class="form-control" style="max-width:220px">
            <option value="">All Categories</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?= e($c['category']) ?>" <?= $category_filter === $c['category'] ? 'selected' : '' ?>><?= e($c['category']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
        <button type="submit" class="btn btn-green btn-sm">Filter</button>
        <?php if ($search || $category_filter): ?>
        <a href="messages.php<?= $status_filter ? '?status='.$status_filter : '' ?>" class="btn btn-sm btn-outline-green">Clear</a>
        <?php endif; ?>
    </form>

    <?php if (empty($messages)): ?>
    <div class="empty-state" style="padding:48px 0">
        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        <h3>No messages found</h3>
        <p>No contact messages match the current filter.</p>
    </div>
    <?php else: ?>

    <form method="POST" id="bulk-form">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="action" value="bulk_delete">

        <div class="admin-table-toolbar">
            <label style="display:flex;align-items:center;gap:8px;font-size:.84rem">
                <input type="checkbox" id="select-all"> Select all
            </label>
            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete selected messages?')" id="bulk-delete-btn" disabled>Delete Selected</button>
        </div>

        <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width:36px"></th>
                    <th>Sender</th>
                    <th>Category</th>
                    <th>Subject / Message</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($messages as $msg): ?>
            <tr class="<?= $msg['status'] === 'new' ? 'row--new' : '' ?>">
                <td><input type="checkbox" name="selected[]" value="<?= $msg['id'] ?>" class="row-check"></td>
                <td>
                    <div style="font-weight:<?= $msg['status']==='new' ? '700' : '500' ?>;font-size:.88rem"><?= e($msg['name']) ?></div>
                    <div style="font-size:.75rem;color:var(--text-muted)"><?= e($msg['email']) ?></div>
                    <?php if ($msg['phone']): ?><div style="font-size:.73rem;color:var(--text-light)"><?= e($msg['phone']) ?></div><?php endif; ?>
                </td>
                <td style="font-size:.8rem;color:var(--text-muted)"><?= $msg['category'] ? e($msg['category']) : '—' ?></td>
                <td style="max-width:280px">
                    <?php if ($msg['subject']): ?><div style="font-size:.84rem;font-weight:600;margin-bottom:2px"><?= e($msg['subject']) ?></div><?php endif; ?>
                    <div style="font-size:.78rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:260px"><?= e(mb_substr($msg['message'],0,100)) ?>…</div>
                </td>
                <td>
                    <?php
                    $badges = ['new'=>'badge-new','read'=>'badge-info','replied'=>'badge-success'];
                    echo '<span class="badge '.($badges[$msg['status']]??'').'">'.ucfirst($msg['status']).'</span>';
                    ?>
                </td>
                <td style="font-size:.78rem;color:var(--text-muted);white-space:nowrap"><?= date('d M Y', strtotime($msg['created_at'])) ?><br><?= date('H:i', strtotime($msg['created_at'])) ?></td>
                <td>
                    <div style="display:flex;gap:6px">
                        <a href="messages.php?view=<?= $msg['id'] ?>" class="btn btn-sm btn-outline-green">View</a>
                        <?php if ($msg['status'] === 'new'): ?>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                            <input type="hidden" name="action" value="mark_read">
                            <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-green">Mark Read</button>
                        </form>
                        <?php endif; ?>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this message?')">
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Del</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
const selectAll = document.getElementById('select-all');
const bulkBtn   = document.getElementById('bulk-delete-btn');
const checkboxes = () => document.querySelectorAll('.row-check');
const updateBulk = () => {
    const checked = [...checkboxes()].filter(c => c.checked).length;
    if (bulkBtn) bulkBtn.disabled = checked === 0;
};
if (selectAll) {
    selectAll.addEventListener('change', () => {
        checkboxes().forEach(c => c.checked = selectAll.checked);
        updateBulk();
    });
}
document.addEventListener('change', e => { if (e.target.classList.contains('row-check')) updateBulk(); });
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
