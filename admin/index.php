<?php
require_once __DIR__ . '/includes/admin_init.php';
$admin_page_title = 'Dashboard';
$admin_active     = 'dashboard';

// Stats
$total_orders   = fetchOne('SELECT COUNT(*) as n FROM orders')['n'] ?? 0;
$total_products = fetchOne('SELECT COUNT(*) as n FROM products WHERE is_active=1')['n'] ?? 0;
$total_users    = fetchOne('SELECT COUNT(*) as n FROM users WHERE role="client"')['n'] ?? 0;
$total_revenue  = fetchOne('SELECT SUM(total) as n FROM orders WHERE status IN ("delivered","processing","shipped")')['n'] ?? 0;

$recent_orders = fetchAll('SELECT o.*, CONCAT(o.ship_name) as customer FROM orders o ORDER BY o.created_at DESC LIMIT 8');
$low_stock     = fetchAll('SELECT * FROM products WHERE stock <= 5 AND is_active=1 ORDER BY stock ASC LIMIT 8');

include __DIR__ . '/includes/admin_header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Dashboard</div>
        <div class="page-subtitle">Welcome back, <?= e($_SESSION['user_name']) ?>. Here's what's happening today.</div>
    </div>
    <a href="products.php?action=add" class="btn btn-gold">+ Add Product</a>
</div>

<!-- Stats -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--green">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg>
        </div>
        <div>
            <span class="stat-card__value"><?= $total_orders ?></span>
            <span class="stat-card__label">Total Orders</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--gold">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div>
            <span class="stat-card__value"><?= money((float)$total_revenue) ?></span>
            <span class="stat-card__label">Total Revenue</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--light">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/></svg>
        </div>
        <div>
            <span class="stat-card__value"><?= $total_products ?></span>
            <span class="stat-card__label">Active Products</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--green">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div>
            <span class="stat-card__value"><?= $total_users ?></span>
            <span class="stat-card__label">Customers</span>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 360px;gap:24px">
    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent Orders</span>
            <a href="orders.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($recent_orders as $order): ?>
                <tr>
                    <td><strong><?= e($order['order_number']) ?></strong></td>
                    <td><?= e($order['customer'] ?: $order['guest_email']) ?></td>
                    <td><?= money((float)$order['total']) ?></td>
                    <td><span class="status-badge status-<?= e($order['status']) ?>"><?= ucfirst(e($order['status'])) ?></span></td>
                    <td style="color:var(--text-muted);font-size:.82rem"><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                    <td><a href="order-detail.php?id=<?= $order['id'] ?>" class="btn btn-icon btn-sm" title="View">&#8594;</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recent_orders)): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:32px">No orders yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Low stock -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Low Stock Alert</span>
            <a href="products.php" class="btn btn-outline btn-sm">All Products</a>
        </div>
        <div class="card-body" style="padding:0">
            <?php foreach ($low_stock as $p): ?>
            <div style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--border)">
                <img src="<?= product_thumb($p) ?>" class="product-thumb-sm" alt="">
                <div style="flex:1;min-width:0">
                    <p style="font-size:.85rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($p['name']) ?></p>
                    <span style="font-size:.75rem;color:<?= $p['stock'] == 0 ? '#c0392b' : '#e67e22' ?>;font-weight:600"><?= $p['stock'] == 0 ? 'Out of Stock' : $p['stock'] . ' left' ?></span>
                </div>
                <a href="edit-product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
            </div>
            <?php endforeach; ?>
            <?php if (empty($low_stock)): ?>
            <p style="text-align:center;padding:28px;color:var(--text-muted);font-size:.88rem">All products well stocked.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
