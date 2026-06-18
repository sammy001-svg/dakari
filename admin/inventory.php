<?php
require_once __DIR__ . '/includes/admin_init.php';

$admin_page_title = 'Inventory';
$admin_active     = 'inventory';

$admin_user = current_user();

// ── Handle POST actions ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? '';

    // Single stock adjustment
    if ($action === 'adjust') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $mode       = $_POST['mode'] ?? 'set';   // set | add | subtract
        $qty        = (int)($_POST['quantity']   ?? 0);
        $type       = $_POST['type']             ?? 'adjustment';
        $note       = trim($_POST['note']        ?? '');

        if ($product_id && $qty >= 0) {
            $current = (int)(fetchOne('SELECT stock FROM products WHERE id = ?', 'i', $product_id)['stock'] ?? 0);
            $new_stock = match($mode) {
                'add'      => $current + $qty,
                'subtract' => max(0, $current - $qty),
                default    => $qty,
            };
            adjust_stock($product_id, $new_stock, $type, $note, $admin_user['id']);
            flash('success', 'Stock updated.');
        }
        header('Location: inventory.php' . ($_GET ? '?' . http_build_query($_GET) : '')); exit;
    }

    // Bulk stock update (from table form)
    if ($action === 'bulk_update') {
        $stocks = $_POST['stocks'] ?? [];
        $note   = trim($_POST['bulk_note'] ?? 'Bulk update');
        $count  = 0;
        foreach ($stocks as $pid => $qty) {
            $pid = (int)$pid;
            $qty = max(0, (int)$qty);
            if (!$pid) continue;
            $current = (int)(fetchOne('SELECT stock FROM products WHERE id = ?', 'i', $pid)['stock'] ?? 0);
            if ($qty === $current) continue;
            adjust_stock($pid, $qty, 'adjustment', $note, $admin_user['id']);
            $count++;
        }
        flash('success', "$count product(s) updated.");
        header('Location: inventory.php'); exit;
    }

    // Low-stock threshold setting
    if ($action === 'save_threshold') {
        $threshold = max(0, (int)($_POST['low_stock_threshold'] ?? 5));
        query('INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?',
              'sss', 'low_stock_threshold', $threshold, $threshold);
        flash('success', 'Threshold saved.');
        header('Location: inventory.php'); exit;
    }
}

// ── Filters ───────────────────────────────────────────────────────────────────
$filter_stock  = $_GET['stock']    ?? '';   // low | out | ok
$filter_cat    = (int)($_GET['cat'] ?? 0);
$search        = trim($_GET['q']   ?? '');
$page_num      = max(1, (int)($_GET['page'] ?? 1));
$per_page      = 25;
$threshold     = (int)setting('low_stock_threshold', '5');

$where  = ['p.is_active = 1'];
$params = [];
$types  = '';

if ($filter_stock === 'out') { $where[] = 'p.stock = 0'; }
elseif ($filter_stock === 'low') { $where[] = 'p.stock > 0 AND p.stock <= p.low_stock_threshold'; }
elseif ($filter_stock === 'ok')  { $where[] = 'p.stock > p.low_stock_threshold'; }

if ($filter_cat) { $where[] = 'p.category_id = ?'; $params[] = $filter_cat; $types .= 'i'; }
if ($search !== '') {
    $where[] = '(p.name LIKE ? OR p.sku LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $types .= 'ss';
}

$where_sql = implode(' AND ', $where);

$total_count = (int)(fetchOne(
    "SELECT COUNT(*) AS cnt FROM products p WHERE $where_sql",
    $types ?: '', ...$params
)['cnt'] ?? 0);

$offset   = ($page_num - 1) * $per_page;
$products = [];
if ($total_count > 0) {
    $all_params = array_merge($params, [$per_page, $offset]);
    $all_types  = $types . 'ii';
    $products = fetchAll(
        "SELECT p.*, c.name AS category_name
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE $where_sql
         ORDER BY p.stock ASC, p.name ASC
         LIMIT ? OFFSET ?",
        $all_types, ...$all_params
    );
}

$pagination = paginate($total_count, $per_page, $page_num);

// Stats
$stats = fetchOne(
    'SELECT
        COUNT(*) AS total,
        SUM(stock = 0) AS out_of_stock,
        SUM(stock > 0 AND stock <= low_stock_threshold) AS low_stock,
        SUM(stock > low_stock_threshold) AS in_stock
     FROM products WHERE is_active = 1'
);
$low_stock_list = get_low_stock_products();
$categories     = fetchAll('SELECT id, name FROM categories ORDER BY name');

// Selected product for history panel
$history_pid     = (int)($_GET['history'] ?? 0);
$history_product = $history_pid ? fetchOne('SELECT id, name, stock FROM products WHERE id = ?', 'i', $history_pid) : null;
$history_log     = $history_product ? get_stock_log($history_pid, 30) : [];

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- Stats row -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
    <?php
    $stat_cards = [
        ['label'=>'Total Products', 'value'=>$stats['total'],        'color'=>'var(--green)',  'icon'=>'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10'],
        ['label'=>'In Stock',       'value'=>$stats['in_stock'],     'color'=>'var(--green)',  'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
        ['label'=>'Low Stock',      'value'=>$stats['low_stock'],    'color'=>'var(--gold)',   'icon'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
        ['label'=>'Out of Stock',   'value'=>$stats['out_of_stock'], 'color'=>'#c0392b',      'icon'=>'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
    ];
    foreach ($stat_cards as $card): ?>
    <div class="stat-card">
        <div class="stat-card__icon" style="background:<?= $card['color'] ?>22;color:<?= $card['color'] ?>">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="<?= $card['icon'] ?>"/></svg>
        </div>
        <div>
            <p class="stat-card__value"><?= $card['value'] ?></p>
            <p class="stat-card__label"><?= $card['label'] ?></p>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start">

<!-- Left: main inventory table -->
<div>

    <!-- Filters -->
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:16px">
        <div style="display:flex;gap:6px">
            <a href="inventory.php" class="btn btn-sm <?= $filter_stock==='' ? 'btn-green' : 'btn-outline-green' ?>">All</a>
            <a href="inventory.php?stock=ok" class="btn btn-sm <?= $filter_stock==='ok' ? 'btn-green' : 'btn-outline-green' ?>">In Stock</a>
            <a href="inventory.php?stock=low" class="btn btn-sm <?= $filter_stock==='low' ? 'btn-gold' : 'btn-outline-green' ?>"
               style="<?= $filter_stock==='low' ? 'background:var(--gold);border-color:var(--gold);color:var(--dark)' : '' ?>">
                Low Stock <?php if ($stats['low_stock'] > 0): ?><span style="background:var(--gold);color:var(--dark);font-size:.65rem;font-weight:700;padding:1px 5px;border-radius:8px;margin-left:4px"><?= $stats['low_stock'] ?></span><?php endif; ?>
            </a>
            <a href="inventory.php?stock=out" class="btn btn-sm <?= $filter_stock==='out' ? 'btn-green' : 'btn-outline-green' ?>"
               style="<?= $filter_stock==='out' ? 'background:#c0392b;border-color:#c0392b' : '' ?>">
                Out of Stock <?php if ($stats['out_of_stock'] > 0): ?><span style="background:#c0392b;color:#fff;font-size:.65rem;font-weight:700;padding:1px 5px;border-radius:8px;margin-left:4px"><?= $stats['out_of_stock'] ?></span><?php endif; ?>
            </a>
        </div>
        <form method="get" style="display:flex;gap:8px;margin-left:auto">
            <?php if ($filter_stock): ?><input type="hidden" name="stock" value="<?= e($filter_stock) ?>"><?php endif; ?>
            <select name="cat" class="form-control" style="height:34px;font-size:.82rem">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $filter_cat===$cat['id'] ? 'selected':'' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="q" class="form-control" placeholder="Search…" value="<?= e($search) ?>" style="width:160px;height:34px;font-size:.82rem">
            <button class="btn btn-outline-green btn-sm">Go</button>
        </form>
    </div>

    <?php if (empty($products)): ?>
    <div class="empty-state" style="padding:48px 0"><p>No products found.</p></div>
    <?php else: ?>

    <form method="post" action="inventory.php">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="bulk_update">

        <div class="table-wrapper">
            <table class="data-table" id="inventoryTable">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th style="width:80px">Threshold</th>
                        <th style="width:110px">Current Stock</th>
                        <th style="width:120px">New Stock</th>
                        <th style="width:80px">Status</th>
                        <th style="width:80px">Log</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p):
                        $status = stock_status((int)$p['stock'], (int)$p['low_stock_threshold']);
                    ?>
                    <tr>
                        <td>
                            <p style="font-weight:600;font-size:.88rem"><?= e($p['name']) ?></p>
                            <p style="font-size:.75rem;color:var(--text-muted)"><?= e($p['category_name'] ?? '—') ?></p>
                        </td>
                        <td style="font-size:.82rem;color:var(--text-muted);font-family:monospace"><?= e($p['sku'] ?? '—') ?></td>
                        <td style="font-size:.85rem;text-align:center"><?= (int)$p['low_stock_threshold'] ?></td>
                        <td>
                            <span style="font-weight:700;font-size:1rem;
                                color:<?= $status==='out' ? '#c0392b' : ($status==='low' ? '#b7791f' : 'var(--green)') ?>">
                                <?= (int)$p['stock'] ?>
                            </span>
                        </td>
                        <td>
                            <input type="number" name="stocks[<?= $p['id'] ?>]"
                                   class="form-control" style="width:90px;height:32px;font-size:.85rem;text-align:center"
                                   value="<?= (int)$p['stock'] ?>" min="0" max="99999">
                        </td>
                        <td>
                            <?php if ($status === 'out'): ?>
                            <span class="status-badge" style="background:#fde8e8;color:#c0392b">Out</span>
                            <?php elseif ($status === 'low'): ?>
                            <span class="status-badge" style="background:#fef3c7;color:#b7791f">Low</span>
                            <?php else: ?>
                            <span class="status-badge status-active">OK</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="inventory.php?history=<?= $p['id'] ?>" class="btn btn-sm btn-outline-green" style="padding:3px 9px;font-size:.75rem" title="View stock history">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3h18M3 9h18M3 15h18M3 21h18"/></svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="display:flex;align-items:center;gap:12px;margin-top:14px;flex-wrap:wrap">
            <div class="form-group" style="margin:0;display:flex;align-items:center;gap:8px">
                <label style="font-size:.82rem;color:var(--text-muted);white-space:nowrap">Update note:</label>
                <input type="text" name="bulk_note" class="form-control" placeholder="e.g. Restock from supplier" style="width:240px;height:34px;font-size:.82rem" value="Bulk stock update">
            </div>
            <button type="submit" class="btn btn-green">Save All Changes</button>
            <span style="font-size:.78rem;color:var(--text-muted)"><?= $total_count ?> product<?= $total_count!==1?'s':'' ?></span>
        </div>
    </form>

    <!-- Pagination -->
    <?php if ($pagination['pages'] > 1): ?>
    <nav class="pagination" style="margin-top:20px">
        <?php if ($page_num > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page_num-1])) ?>" class="page-link">&lsaquo;</a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>" class="page-link <?= $p===$page_num?'active':'' ?>"><?= $p ?></a>
        <?php endfor; ?>
        <?php if ($page_num < $pagination['pages']): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page_num+1])) ?>" class="page-link">&rsaquo;</a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>

    <?php endif; ?>
</div>

<!-- Right sidebar: quick adjust + threshold + history -->
<div>

    <!-- Quick stock adjustment -->
    <div class="card" style="margin-bottom:20px">
        <div class="card-header"><span class="card-title">Quick Adjustment</span></div>
        <div class="card-body">
            <form method="post" action="inventory.php">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="adjust">
                <div class="form-group" style="margin-bottom:12px">
                    <label class="form-label" style="font-size:.82rem">Product</label>
                    <select name="product_id" class="form-control" style="font-size:.82rem" required>
                        <option value="">— select —</option>
                        <?php foreach (fetchAll('SELECT id, name, stock FROM products WHERE is_active=1 ORDER BY name') as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $history_pid===$p['id']?'selected':'' ?>>
                            <?= e($p['name']) ?> (<?= $p['stock'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:12px">
                    <label class="form-label" style="font-size:.82rem">Mode</label>
                    <select name="mode" class="form-control" style="font-size:.82rem">
                        <option value="set">Set to exact amount</option>
                        <option value="add">Add to current stock</option>
                        <option value="subtract">Subtract from stock</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:12px">
                    <label class="form-label" style="font-size:.82rem">Quantity</label>
                    <input type="number" name="quantity" class="form-control" min="0" value="0" required style="font-size:.82rem">
                </div>
                <div class="form-group" style="margin-bottom:12px">
                    <label class="form-label" style="font-size:.82rem">Type</label>
                    <select name="type" class="form-control" style="font-size:.82rem">
                        <option value="restock">Restock</option>
                        <option value="adjustment" selected>Adjustment</option>
                        <option value="return">Return</option>
                        <option value="damage">Damage / Loss</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:14px">
                    <label class="form-label" style="font-size:.82rem">Note (optional)</label>
                    <input type="text" name="note" class="form-control" placeholder="e.g. Received from supplier" style="font-size:.82rem">
                </div>
                <button type="submit" class="btn btn-green btn-block">Apply Adjustment</button>
            </form>
        </div>
    </div>

    <!-- Alert threshold setting -->
    <div class="card" style="margin-bottom:20px">
        <div class="card-header"><span class="card-title">Low Stock Alert</span></div>
        <div class="card-body">
            <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:10px">
                Default threshold for new products. Each product also has its own threshold column.
            </p>
            <form method="post" action="inventory.php" style="display:flex;gap:8px;align-items:center">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save_threshold">
                <input type="number" name="low_stock_threshold" class="form-control" value="<?= (int)setting('low_stock_threshold','5') ?>"
                       min="0" max="999" style="width:80px;height:34px;font-size:.85rem;text-align:center">
                <button type="submit" class="btn btn-outline-green btn-sm">Save</button>
            </form>
        </div>
    </div>

    <!-- Stock history panel -->
    <?php if ($history_product): ?>
    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
            <span class="card-title">Stock History</span>
            <a href="inventory.php" style="font-size:.75rem;color:var(--text-muted)">✕ Close</a>
        </div>
        <div class="card-body" style="padding:0">
            <div style="padding:12px 16px;background:var(--off-white);border-bottom:1px solid var(--border)">
                <p style="font-weight:700;font-size:.9rem"><?= e($history_product['name']) ?></p>
                <p style="font-size:.8rem;color:var(--text-muted)">Current stock: <strong><?= $history_product['stock'] ?></strong></p>
            </div>
            <?php if (empty($history_log)): ?>
            <p style="padding:20px 16px;font-size:.82rem;color:var(--text-muted)">No history recorded yet.</p>
            <?php else: ?>
            <div style="max-height:360px;overflow-y:auto">
                <?php foreach ($history_log as $log):
                    $is_positive = $log['quantity_change'] >= 0;
                ?>
                <div style="padding:10px 16px;border-bottom:1px solid var(--border);font-size:.8rem">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:3px">
                        <span style="font-weight:600;text-transform:capitalize;color:var(--dark)"><?= e($log['type']) ?></span>
                        <span style="font-weight:700;color:<?= $is_positive ? 'var(--green)' : '#c0392b' ?>">
                            <?= $is_positive ? '+' : '' ?><?= $log['quantity_change'] ?>
                        </span>
                    </div>
                    <div style="display:flex;justify-content:space-between;color:var(--text-muted)">
                        <span><?= $log['quantity_before'] ?> → <?= $log['quantity_after'] ?></span>
                        <span><?= date('M j, H:i', strtotime($log['created_at'])) ?></span>
                    </div>
                    <?php if (!empty($log['note'])): ?>
                    <p style="color:var(--text-muted);margin-top:3px;font-style:italic"><?= e($log['note']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($log['first_name'])): ?>
                    <p style="color:var(--text-light);margin-top:2px">By <?= e($log['first_name'] . ' ' . $log['last_name']) ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
