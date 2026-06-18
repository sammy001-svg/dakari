<?php
require_once __DIR__ . '/includes/admin_init.php';
$admin_page_title = 'Services';
$admin_active     = 'services';

/* ── Actions ───────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $svc = fetchOne('SELECT image FROM services WHERE id=?', 'i', $id);
        if ($svc && $svc['image']) {
            @unlink(ROOT_PATH . '/uploads/services/' . $svc['image']);
        }
        query('DELETE FROM services WHERE id=?', 'i', $id);
        flash('success', 'Service deleted.');

    } elseif ($action === 'toggle_status') {
        $id  = (int)$_POST['id'];
        $cur = fetchOne('SELECT status FROM services WHERE id=?', 'i', $id)['status'] ?? 'active';
        $new = $cur === 'active' ? 'inactive' : 'active';
        query("UPDATE services SET status=? WHERE id=?", 'si', $new, $id);
        flash('success', 'Status updated.');

    } elseif ($action === 'toggle_featured') {
        $id  = (int)$_POST['id'];
        $cur = (int)(fetchOne('SELECT is_featured FROM services WHERE id=?', 'i', $id)['is_featured'] ?? 0);
        query('UPDATE services SET is_featured=? WHERE id=?', 'ii', $cur ? 0 : 1, $id);
        flash('success', 'Featured status updated.');

    } elseif ($action === 'reorder') {
        $ids = array_map('intval', (array)($_POST['order'] ?? []));
        foreach ($ids as $pos => $id) {
            query('UPDATE services SET sort_order=? WHERE id=?', 'ii', $pos + 1, $id);
        }
        flash('success', 'Order saved.');
    }
    header('Location: services.php'); exit;
}

/* ── Data ──────────────────────────────────────────────────────── */
$services = get_services(false);
$counts   = [
    'total'    => count($services),
    'active'   => count(array_filter($services, fn($s) => $s['status'] === 'active')),
    'inactive' => count(array_filter($services, fn($s) => $s['status'] === 'inactive')),
    'featured' => count(array_filter($services, fn($s) => $s['is_featured'])),
];
$csrf = generate_csrf();
include __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Our Services</h1>
        <p class="admin-page-sub">Manage the services Dakari offers to customers</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/add-service.php" class="btn btn-green">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Service
    </a>
</div>

<!-- Stat cards -->
<div class="stats-row" style="grid-template-columns:repeat(4,1fr)">
    <?php foreach ([
        ['Total Services',    $counts['total'],    'stat-card__icon--green'],
        ['Active',            $counts['active'],   'stat-card__icon--green'],
        ['Inactive',          $counts['inactive'], 'stat-card__icon--light'],
        ['Featured on Home',  $counts['featured'], 'stat-card__icon--gold'],
    ] as [$label,$val,$cls]): ?>
    <div class="stat-card">
        <div class="stat-card__icon <?= $cls ?>">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
        </div>
        <div>
            <span class="stat-card__value"><?= $val ?></span>
            <span class="stat-card__label"><?= $label ?></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="admin-card">
    <?php if (empty($services)): ?>
    <div class="empty-state" style="padding:48px 0">
        <h3>No services yet</h3>
        <p>Add your first service to display it on the website.</p>
        <a href="<?= BASE_URL ?>/admin/add-service.php" class="btn btn-green" style="margin-top:16px">Add Service</a>
    </div>
    <?php else: ?>
    <div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width:40px">#</th>
                <th>Service</th>
                <th>Tagline</th>
                <th>Price Label</th>
                <th>Featured</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($services as $svc): ?>
        <tr>
            <td style="color:var(--text-muted);font-size:.8rem"><?= $svc['sort_order'] ?: $svc['id'] ?></td>
            <td>
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="width:40px;height:40px;background:var(--green);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;color:var(--gold);flex-shrink:0">
                        <?= service_icon_svg($svc['icon'] ?? 'star', 18) ?>
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:.88rem"><?= e($svc['title']) ?></div>
                        <div style="font-size:.74rem;color:var(--text-muted)"><?= e($svc['slug']) ?></div>
                    </div>
                </div>
            </td>
            <td style="font-size:.84rem;color:var(--text-muted);max-width:220px"><?= e(mb_substr($svc['tagline'] ?? '', 0, 60)) ?><?= strlen($svc['tagline'] ?? '') > 60 ? '…' : '' ?></td>
            <td style="font-size:.82rem;color:var(--text-muted)"><?= $svc['price_label'] ? e($svc['price_label']) : '—' ?></td>
            <td>
                <form method="POST" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="action" value="toggle_featured">
                    <input type="hidden" name="id" value="<?= $svc['id'] ?>">
                    <button type="submit" class="badge <?= $svc['is_featured'] ? 'badge-new' : '' ?>" style="cursor:pointer;border:none;background:<?= $svc['is_featured'] ? 'var(--green)' : 'var(--light)' ?>;color:<?= $svc['is_featured'] ? 'var(--white)' : 'var(--text-muted)' ?>;padding:4px 10px;border-radius:20px;font-size:.72rem;font-weight:700">
                        <?= $svc['is_featured'] ? 'Featured' : 'Not Featured' ?>
                    </button>
                </form>
            </td>
            <td>
                <form method="POST" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="id" value="<?= $svc['id'] ?>">
                    <button type="submit" class="status-badge <?= $svc['status'] === 'active' ? 'status-processing' : 'status-cancelled' ?>" style="cursor:pointer;border:none">
                        <?= ucfirst($svc['status']) ?>
                    </button>
                </form>
            </td>
            <td>
                <div style="display:flex;gap:6px">
                    <a href="<?= BASE_URL ?>/admin/edit-service.php?id=<?= $svc['id'] ?>" class="btn btn-sm btn-outline-green">Edit</a>
                    <a href="<?= BASE_URL ?>/services.php#<?= e($svc['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-green">View</a>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete this service?')">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $svc['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Del</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
