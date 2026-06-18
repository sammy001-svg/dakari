<?php
require_once __DIR__ . '/includes/admin_init.php';
$admin_page_title = 'Orders';
$admin_active     = 'orders';

$status = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');
$page   = max(1,(int)($_GET['page']??1));
$limit  = 20; $offset = ($page-1)*$limit;

$where = []; $params = []; $types = '';
if ($status) { $where[] = 'status = ?'; $params[] = $status; $types .= 's'; }
if ($search) { $where[] = '(order_number LIKE ? OR ship_name LIKE ? OR ship_email LIKE ?)'; $params = array_merge($params, ["%$search%","%$search%","%$search%"]); $types .= 'sss'; }
$w = $where ? 'WHERE '.implode(' AND ',$where) : '';

$total  = fetchOne("SELECT COUNT(*) as n FROM orders $w", $types, ...$params)['n'] ?? 0;
$orders = fetchAll("SELECT * FROM orders $w ORDER BY created_at DESC LIMIT ? OFFSET ?", $types.'ii', ...[...$params,$limit,$offset]);
$statuses = ['pending','processing','shipped','delivered','cancelled','refunded'];

include __DIR__ . '/includes/admin_header.php';
?>
<div class="page-header">
    <div><div class="page-title">Orders</div><div class="page-subtitle"><?= $total ?> orders</div></div>
</div>
<div class="card">
    <div class="card-header">
        <form method="get" action="orders.php" style="display:flex;gap:10px;flex-wrap:wrap">
            <div class="search-box">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search orders…">
            </div>
            <select name="status" class="filter-select" id="statusFilter">
                <option value="">All Statuses</option>
                <?php foreach ($statuses as $s): ?>
                <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-green btn-sm">Filter</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Order #</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($orders as $o):
                $items_count = fetchOne('SELECT SUM(quantity) as n FROM order_items WHERE order_id=?','i',$o['id'])['n']??0;
            ?>
            <tr>
                <td><strong><?= e($o['order_number']) ?></strong></td>
                <td>
                    <p style="font-size:.88rem;font-weight:600"><?= e($o['ship_name']) ?></p>
                    <p style="font-size:.75rem;color:var(--text-muted)"><?= e($o['ship_email']) ?></p>
                </td>
                <td><?= $items_count ?></td>
                <td><strong><?= money((float)$o['total']) ?></strong></td>
                <td><span class="status-badge status-<?= e($o['status']) ?>"><?= ucfirst(e($o['status'])) ?></span></td>
                <td style="font-size:.82rem;color:var(--text-muted)"><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
                <td><a href="order-detail.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline">View</a></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($orders)): ?><tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">No orders found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
