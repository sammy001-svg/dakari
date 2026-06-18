<?php
require_once __DIR__ . '/includes/admin_init.php';
$admin_page_title = 'Dashboard';
$admin_active     = 'dashboard';

// ── Primary stats ──────────────────────────────────────────────
$total_revenue  = (float)(fetchOne("SELECT COALESCE(SUM(total),0) n FROM orders WHERE status NOT IN ('cancelled','refunded')")['n'] ?? 0);
$revenue_today  = (float)(fetchOne("SELECT COALESCE(SUM(total),0) n FROM orders WHERE DATE(created_at)=CURDATE() AND status NOT IN ('cancelled','refunded')")['n'] ?? 0);
$revenue_month  = (float)(fetchOne("SELECT COALESCE(SUM(total),0) n FROM orders WHERE YEAR(created_at)=YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW()) AND status NOT IN ('cancelled','refunded')")['n'] ?? 0);
$revenue_lmonth = (float)(fetchOne("SELECT COALESCE(SUM(total),0) n FROM orders WHERE YEAR(created_at)=YEAR(DATE_SUB(NOW(),INTERVAL 1 MONTH)) AND MONTH(created_at)=MONTH(DATE_SUB(NOW(),INTERVAL 1 MONTH)) AND status NOT IN ('cancelled','refunded')")['n'] ?? 0);

$total_orders   = (int)(fetchOne("SELECT COUNT(*) n FROM orders")['n'] ?? 0);
$orders_today   = (int)(fetchOne("SELECT COUNT(*) n FROM orders WHERE DATE(created_at)=CURDATE()")['n'] ?? 0);
$orders_month   = (int)(fetchOne("SELECT COUNT(*) n FROM orders WHERE YEAR(created_at)=YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW())")['n'] ?? 0);

$total_customers   = (int)(fetchOne("SELECT COUNT(*) n FROM users WHERE role='client'")['n'] ?? 0);
$customers_month   = (int)(fetchOne("SELECT COUNT(*) n FROM users WHERE role='client' AND YEAR(created_at)=YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW())")['n'] ?? 0);

$total_products = (int)(fetchOne("SELECT COUNT(*) n FROM products WHERE is_active=1")['n'] ?? 0);
$avg_order      = $total_orders > 0 ? $total_revenue / $total_orders : 0;

// ── Alert stats ────────────────────────────────────────────────
$pending_orders  = (int)(fetchOne("SELECT COUNT(*) n FROM orders WHERE status='pending'")['n'] ?? 0);
$low_stock_count = (int)(fetchOne("SELECT COUNT(*) n FROM products WHERE stock <= low_stock_threshold AND is_active=1")['n'] ?? 0);
$unread_msgs     = (int)(fetchOne("SELECT COUNT(*) n FROM contact_messages WHERE status='new'")['n'] ?? 0);
$pending_reviews = (int)(fetchOne("SELECT COUNT(*) n FROM product_reviews WHERE is_approved=0")['n'] ?? 0);

// ── Month-over-month % change ──────────────────────────────────
$rev_change = $revenue_lmonth > 0 ? round(($revenue_month - $revenue_lmonth) / $revenue_lmonth * 100, 1) : null;

// ── Recent orders (10) ────────────────────────────────────────
$recent_orders = fetchAll('SELECT * FROM orders ORDER BY created_at DESC LIMIT 10');

// ── Top products by revenue (5) ───────────────────────────────
$top_products = fetchAll(
    "SELECT p.id, p.name, p.thumbnail, p.slug,
            SUM(oi.quantity) AS qty_sold,
            SUM(oi.subtotal) AS revenue
     FROM order_items oi
     JOIN products p ON p.id = oi.product_id
     GROUP BY p.id
     ORDER BY revenue DESC
     LIMIT 5"
);

// ── Low stock (6) ─────────────────────────────────────────────
$low_stock = fetchAll('SELECT * FROM products WHERE stock <= low_stock_threshold AND is_active=1 ORDER BY stock ASC LIMIT 6');

// ── Revenue chart — last 30 days ──────────────────────────────
$chart_rows = fetchAll(
    "SELECT DATE(created_at) AS day, COALESCE(SUM(total),0) AS revenue
     FROM orders
     WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
       AND status NOT IN ('cancelled','refunded')
     GROUP BY DATE(created_at)
     ORDER BY day ASC"
);
// Fill every day in the range
$chart_data = [];
for ($i = 29; $i >= 0; $i--) {
    $chart_data[date('Y-m-d', strtotime("-{$i} days"))] = 0;
}
foreach ($chart_rows as $r) {
    $chart_data[$r['day']] = (float)$r['revenue'];
}
$chart_labels = array_map(fn($d) => date('M j', strtotime($d)), array_keys($chart_data));
$chart_values = array_values($chart_data);

include __DIR__ . '/includes/admin_header.php';
?>

<!-- ── Quick Actions ──────────────────────────────────────────── -->
<div class="dash-quick-actions">
    <a href="add-product.php" class="dash-qa">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Product
    </a>
    <a href="coupons.php" class="dash-qa">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
        New Coupon
    </a>
    <a href="orders.php?status=pending" class="dash-qa <?= $pending_orders > 0 ? 'dash-qa--alert' : '' ?>">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg>
        Pending Orders<?php if ($pending_orders > 0): ?> <span class="qa-badge"><?= $pending_orders ?></span><?php endif; ?>
    </a>
    <a href="messages.php" class="dash-qa <?= $unread_msgs > 0 ? 'dash-qa--alert' : '' ?>">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        Messages<?php if ($unread_msgs > 0): ?> <span class="qa-badge"><?= $unread_msgs ?></span><?php endif; ?>
    </a>
    <a href="reviews.php" class="dash-qa <?= $pending_reviews > 0 ? 'dash-qa--alert' : '' ?>">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Reviews<?php if ($pending_reviews > 0): ?> <span class="qa-badge"><?= $pending_reviews ?></span><?php endif; ?>
    </a>
    <a href="inventory.php" class="dash-qa <?= $low_stock_count > 0 ? 'dash-qa--alert' : '' ?>">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
        Stock<?php if ($low_stock_count > 0): ?> <span class="qa-badge"><?= $low_stock_count ?></span><?php endif; ?>
    </a>
</div>

<!-- ── Primary Stats ──────────────────────────────────────────── -->
<div class="stats-row" style="margin-bottom:20px">
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--gold">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div>
            <span class="stat-card__value"><?= money($total_revenue) ?></span>
            <span class="stat-card__label">Total Revenue</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--green">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
        </div>
        <div>
            <span class="stat-card__value"><?= money($revenue_month) ?></span>
            <span class="stat-card__label">This Month</span>
            <?php if ($rev_change !== null): ?>
            <span class="stat-card__change <?= $rev_change >= 0 ? 'change--up' : 'change--down' ?>">
                <?= $rev_change >= 0 ? '▲' : '▼' ?> <?= abs($rev_change) ?>% vs last month
            </span>
            <?php endif; ?>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--light">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg>
        </div>
        <div>
            <span class="stat-card__value"><?= $total_orders ?></span>
            <span class="stat-card__label">Total Orders</span>
            <span class="stat-card__change" style="color:var(--text-muted)"><?= $orders_today ?> today · <?= $orders_month ?> this month</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--green">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div>
            <span class="stat-card__value"><?= $total_customers ?></span>
            <span class="stat-card__label">Customers</span>
            <span class="stat-card__change" style="color:var(--text-muted)">+<?= $customers_month ?> this month</span>
        </div>
    </div>
</div>

<!-- ── Secondary alert stats ─────────────────────────────────── -->
<div class="dash-alert-row">
    <div class="dash-alert-card <?= $pending_orders > 0 ? 'dash-alert-card--warn' : '' ?>">
        <div class="dash-alert-card__val"><?= $pending_orders ?></div>
        <div class="dash-alert-card__label">Pending Orders</div>
        <a href="orders.php?status=pending" class="dash-alert-card__link">View →</a>
    </div>
    <div class="dash-alert-card <?= $low_stock_count > 0 ? 'dash-alert-card--warn' : '' ?>">
        <div class="dash-alert-card__val"><?= $low_stock_count ?></div>
        <div class="dash-alert-card__label">Low / Out of Stock</div>
        <a href="inventory.php" class="dash-alert-card__link">Manage →</a>
    </div>
    <div class="dash-alert-card <?= $unread_msgs > 0 ? 'dash-alert-card--warn' : '' ?>">
        <div class="dash-alert-card__val"><?= $unread_msgs ?></div>
        <div class="dash-alert-card__label">Unread Messages</div>
        <a href="messages.php" class="dash-alert-card__link">Inbox →</a>
    </div>
    <div class="dash-alert-card <?= $pending_reviews > 0 ? 'dash-alert-card--warn' : '' ?>">
        <div class="dash-alert-card__val"><?= $pending_reviews ?></div>
        <div class="dash-alert-card__label">Reviews to Approve</div>
        <a href="reviews.php" class="dash-alert-card__link">Review →</a>
    </div>
    <div class="dash-alert-card">
        <div class="dash-alert-card__val"><?= money($avg_order) ?></div>
        <div class="dash-alert-card__label">Avg. Order Value</div>
        <a href="orders.php" class="dash-alert-card__link">Orders →</a>
    </div>
    <div class="dash-alert-card">
        <div class="dash-alert-card__val"><?= $total_products ?></div>
        <div class="dash-alert-card__label">Active Products</div>
        <a href="products.php" class="dash-alert-card__link">Products →</a>
    </div>
</div>

<!-- ── Revenue Chart ──────────────────────────────────────────── -->
<div class="card" style="margin-bottom:24px">
    <div class="card-header">
        <span class="card-title">Revenue — Last 30 Days</span>
        <span style="font-size:.8rem;color:var(--text-muted)"><?= date('M j', strtotime('-29 days')) ?> – <?= date('M j, Y') ?></span>
    </div>
    <div class="card-body" style="padding:20px 24px 16px">
        <canvas id="revenueChart" height="90"></canvas>
    </div>
</div>

<!-- ── Orders + Right column ─────────────────────────────────── -->
<div style="display:grid;grid-template-columns:1fr 340px;gap:24px">

    <!-- Recent orders -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent Orders</span>
            <a href="orders.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($recent_orders as $o): ?>
                <tr>
                    <td><strong><?= e($o['order_number']) ?></strong></td>
                    <td style="font-size:.84rem"><?= e($o['ship_name'] ?: ($o['guest_email'] ?? '—')) ?></td>
                    <td><?= money((float)$o['total']) ?></td>
                    <td><span class="status-badge status-<?= e($o['status']) ?>"><?= ucfirst(e($o['status'])) ?></span></td>
                    <td style="color:var(--text-muted);font-size:.8rem"><?= date('M j', strtotime($o['created_at'])) ?></td>
                    <td><a href="order-detail.php?id=<?= $o['id'] ?>" class="btn btn-icon btn-sm" title="View">→</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recent_orders)): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:36px">No orders yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right column -->
    <div>
        <!-- Top products -->
        <div class="card" style="margin-bottom:20px">
            <div class="card-header">
                <span class="card-title">Top Products</span>
                <a href="products.php" class="btn btn-outline btn-sm">All</a>
            </div>
            <div>
                <?php if (!empty($top_products)): ?>
                <?php foreach ($top_products as $i => $p): ?>
                <div class="dash-top-row">
                    <span class="dash-top-rank"><?= $i + 1 ?></span>
                    <img src="<?= product_thumb($p) ?>" class="dash-top-img" alt="">
                    <div class="dash-top-info">
                        <p class="dash-top-name"><?= e($p['name']) ?></p>
                        <span class="dash-top-meta"><?= (int)$p['qty_sold'] ?> sold</span>
                    </div>
                    <strong class="dash-top-rev"><?= money((float)$p['revenue']) ?></strong>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p style="text-align:center;padding:24px;color:var(--text-muted);font-size:.85rem">No sales data yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Low stock -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Low Stock</span>
                <a href="inventory.php" class="btn btn-outline btn-sm">Manage</a>
            </div>
            <div>
                <?php if (!empty($low_stock)): ?>
                <?php foreach ($low_stock as $p): ?>
                <div class="dash-stock-row">
                    <img src="<?= product_thumb($p) ?>" class="dash-top-img" alt="">
                    <div style="flex:1;min-width:0">
                        <p class="dash-top-name"><?= e($p['name']) ?></p>
                        <span style="font-size:.75rem;font-weight:700;color:<?= $p['stock'] == 0 ? '#c0392b' : '#e67e22' ?>"><?= $p['stock'] == 0 ? 'Out of Stock' : $p['stock'] . ' left' ?></span>
                    </div>
                    <a href="edit-product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p style="text-align:center;padding:24px;color:var(--text-muted);font-size:.85rem">All products well stocked.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const labels = <?= json_encode($chart_labels) ?>;
    const values = <?= json_encode($chart_values) ?>;
    const green  = '#1B4332';
    const gold   = '#C9A84C';
    const ctx    = document.getElementById('revenueChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Revenue',
                data: values,
                backgroundColor: gold + '55',
                borderColor: gold,
                borderWidth: 1.5,
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' KSh ' + parseFloat(ctx.raw).toLocaleString('en-KE', {minimumFractionDigits:2})
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, color: '#888',
                        maxTicksLimit: 10,
                        maxRotation: 0
                    }
                },
                y: {
                    grid: { color: '#f0f0f0' },
                    ticks: {
                        font: { size: 11 }, color: '#888',
                        callback: v => 'KSh ' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v)
                    }
                }
            }
        }
    });
})();
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
