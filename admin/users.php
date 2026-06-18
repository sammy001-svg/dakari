<?php
require_once __DIR__ . '/includes/admin_init.php';
$admin_page_title = 'Users';
$admin_active     = 'users';

// Toggle active
if (isset($_GET['toggle'])) {
    $u = fetchOne('SELECT id,is_active FROM users WHERE id=? AND role="client"','i',(int)$_GET['toggle']);
    if ($u) query('UPDATE users SET is_active=? WHERE id=?','ii',($u['is_active']?0:1),$u['id']);
    header('Location: users.php'); exit;
}

$search = trim($_GET['q']??'');
$page   = max(1,(int)($_GET['page']??1));
$limit  = 25; $offset = ($page-1)*$limit;
$where  = ["role='client'"]; $params=[]; $types='';
if ($search) { $where[]="(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)"; $params=["%$search%","%$search%","%$search%"]; $types='sss'; }
$w = implode(' AND ',$where);
$total = fetchOne("SELECT COUNT(*) as n FROM users WHERE $w",$types,...$params)['n']??0;
$users = fetchAll("SELECT u.*,(SELECT COUNT(*) FROM orders o WHERE o.user_id=u.id) as order_count FROM users u WHERE $w ORDER BY created_at DESC LIMIT ? OFFSET ?",$types.'ii',...[...$params,$limit,$offset]);

include __DIR__ . '/includes/admin_header.php';
?>
<div class="page-header">
    <div><div class="page-title">Customers</div><div class="page-subtitle"><?= $total ?> registered customers</div></div>
</div>
<div class="card">
    <div class="card-header">
        <form method="get" style="display:flex;gap:10px">
            <div class="search-box">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search customers…">
            </div>
            <button type="submit" class="btn btn-green btn-sm">Search</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Status</th><th>Joined</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><strong><?= e($u['first_name'].' '.$u['last_name']) ?></strong></td>
                <td><?= e($u['email']) ?></td>
                <td style="color:var(--text-muted);font-size:.85rem"><?= e($u['phone']??'—') ?></td>
                <td><?= $u['order_count'] ?></td>
                <td><a href="users.php?toggle=<?= $u['id'] ?>"><span class="status-badge <?= $u['is_active']?'status-active':'status-inactive' ?>"><?= $u['is_active']?'Active':'Disabled' ?></span></a></td>
                <td style="font-size:.82rem;color:var(--text-muted)"><?= date('M j, Y',strtotime($u['created_at'])) ?></td>
                <td><a href="users.php?toggle=<?= $u['id'] ?>" class="btn btn-sm btn-outline"><?= $u['is_active']?'Disable':'Enable' ?></a></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?><tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">No users found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
