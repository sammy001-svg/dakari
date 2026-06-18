<?php
require_once __DIR__ . '/includes/admin_init.php';
$admin_page_title = 'Coupons';
$admin_active     = 'coupons';
$errors = [];

// ── Delete ────────────────────────────────────────────────────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    query('DELETE FROM coupons WHERE id = ?', 'i', (int)$_GET['delete']);
    flash('success', 'Coupon deleted.');
    header('Location: coupons.php'); exit;
}

// ── Toggle active ─────────────────────────────────────────────────────────────
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $c = fetchOne('SELECT id, is_active FROM coupons WHERE id = ?', 'i', (int)$_GET['toggle']);
    if ($c) query('UPDATE coupons SET is_active = ? WHERE id = ?', 'ii', ($c['is_active'] ? 0 : 1), $c['id']);
    header('Location: coupons.php'); exit;
}

// ── Save (create / update) ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $d = [
        'code'           => strtoupper(trim($_POST['code'] ?? '')),
        'description'    => trim($_POST['description'] ?? ''),
        'type'           => $_POST['type'] ?? 'fixed',
        'value'          => (float)($_POST['value'] ?? 0),
        'min_order'      => (float)($_POST['min_order'] ?? 0),
        'max_uses'       => !empty($_POST['max_uses']) ? (int)$_POST['max_uses'] : null,
        'per_user_limit' => max(1, (int)($_POST['per_user_limit'] ?? 1)),
        'starts_at'      => !empty($_POST['starts_at'])  ? $_POST['starts_at']  : null,
        'expires_at'     => !empty($_POST['expires_at']) ? $_POST['expires_at'] : null,
        'is_active'      => isset($_POST['is_active']) ? 1 : 0,
    ];

    if (!$d['code'])                                           $errors[] = 'Coupon code is required.';
    if (!preg_match('/^[A-Z0-9_\-]{2,50}$/', $d['code']))    $errors[] = 'Code must be 2–50 uppercase letters/numbers/dashes.';
    if (!in_array($d['type'], ['percentage','fixed','free_shipping'])) $errors[] = 'Invalid coupon type.';
    if ($d['type'] !== 'free_shipping' && $d['value'] <= 0)   $errors[] = 'Value must be greater than 0.';
    if ($d['type'] === 'percentage' && $d['value'] > 100)     $errors[] = 'Percentage cannot exceed 100.';

    $edit_id = (int)($_POST['edit_id'] ?? 0);

    // Check code uniqueness (skip own record when editing)
    $exists = fetchOne(
        'SELECT id FROM coupons WHERE code = ? AND id != ?',
        'si', $d['code'], $edit_id
    );
    if ($exists) $errors[] = 'That coupon code already exists.';

    if (empty($errors)) {
        if ($edit_id) {
            query(
                'UPDATE coupons SET code=?,description=?,type=?,value=?,min_order=?,max_uses=?,
                 per_user_limit=?,starts_at=?,expires_at=?,is_active=? WHERE id=?',
                'sssddiissi',
                $d['code'],$d['description'],$d['type'],$d['value'],$d['min_order'],
                $d['max_uses'],$d['per_user_limit'],$d['starts_at'],$d['expires_at'],$d['is_active'],$edit_id
            );
            flash('success', 'Coupon updated.');
        } else {
            query(
                'INSERT INTO coupons (code,description,type,value,min_order,max_uses,per_user_limit,starts_at,expires_at,is_active)
                 VALUES (?,?,?,?,?,?,?,?,?,?)',
                'sssddiissi',
                $d['code'],$d['description'],$d['type'],$d['value'],$d['min_order'],
                $d['max_uses'],$d['per_user_limit'],$d['starts_at'],$d['expires_at'],$d['is_active']
            );
            flash('success', 'Coupon created.');
        }
        header('Location: coupons.php'); exit;
    }
}

// ── Load data ─────────────────────────────────────────────────────────────────
$coupons = fetchAll('SELECT * FROM coupons ORDER BY created_at DESC');
$edit    = isset($_GET['edit']) ? fetchOne('SELECT * FROM coupons WHERE id = ?', 'i', (int)$_GET['edit']) : null;

include __DIR__ . '/includes/admin_header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Coupons &amp; Promo Codes</div>
        <div class="page-subtitle"><?= count($coupons) ?> coupon<?= count($coupons) !== 1 ? 's' : '' ?> total</div>
    </div>
    <button onclick="document.getElementById('couponForm').scrollIntoView({behavior:'smooth'})" class="btn btn-gold">+ Create Coupon</button>
</div>

<?php foreach ($errors as $err): ?>
<div class="alert alert-error"><?= e($err) ?></div>
<?php endforeach; ?>

<!-- ── Coupons table ──────────────────────────────────────────────────────── -->
<div class="card" style="margin-bottom:28px">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Min Order</th>
                    <th>Uses</th>
                    <th>Expires</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($coupons as $c):
                $is_expired  = !empty($c['expires_at']) && $c['expires_at'] < date('Y-m-d H:i:s');
                $uses_remain = is_null($c['max_uses']) ? '∞' : ($c['max_uses'] - $c['uses_count']) . ' left';
            ?>
            <tr>
                <td>
                    <strong style="font-size:.95rem;letter-spacing:.08em;color:var(--green)"><?= e($c['code']) ?></strong>
                    <?php if (!empty($c['description'])): ?>
                    <p style="font-size:.75rem;color:var(--text-muted);margin-top:2px"><?= e($c['description']) ?></p>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    $type_labels = ['percentage'=>'Percentage','fixed'=>'Fixed Amount','free_shipping'=>'Free Shipping'];
                    $type_colors = ['percentage'=>'status-processing','fixed'=>'status-shipped','free_shipping'=>'status-active'];
                    ?>
                    <span class="status-badge <?= $type_colors[$c['type']] ?? '' ?>"><?= $type_labels[$c['type']] ?? $c['type'] ?></span>
                </td>
                <td>
                    <?php if ($c['type'] === 'percentage'): ?>
                        <strong><?= (int)$c['value'] ?>%</strong> off
                    <?php elseif ($c['type'] === 'fixed'): ?>
                        <strong><?= money((float)$c['value']) ?></strong> off
                    <?php else: ?>
                        <span style="color:var(--green);font-weight:600">Free Shipping</span>
                    <?php endif; ?>
                </td>
                <td style="font-size:.85rem;color:var(--text-muted)">
                    <?= $c['min_order'] > 0 ? money((float)$c['min_order']) : '<span style="color:var(--text-light)">None</span>' ?>
                </td>
                <td>
                    <span style="font-size:.88rem">
                        <?= $c['uses_count'] ?> used
                        <?php if (!is_null($c['max_uses'])): ?>
                        / <?= $c['max_uses'] ?>
                        <?php endif; ?>
                    </span>
                    <span style="display:block;font-size:.72rem;color:<?= is_null($c['max_uses']) ? 'var(--text-light)' : 'var(--gold)' ?>"><?= $uses_remain ?></span>
                </td>
                <td style="font-size:.82rem">
                    <?php if ($is_expired): ?>
                    <span style="color:#c0392b;font-weight:600">Expired</span><br>
                    <small style="color:var(--text-muted)"><?= date('M j, Y', strtotime($c['expires_at'])) ?></small>
                    <?php elseif (!empty($c['expires_at'])): ?>
                    <?= date('M j, Y', strtotime($c['expires_at'])) ?>
                    <?php else: ?>
                    <span style="color:var(--text-light)">No expiry</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="coupons.php?toggle=<?= $c['id'] ?>">
                        <span class="status-badge <?= ($c['is_active'] && !$is_expired) ? 'status-active' : 'status-inactive' ?>">
                            <?= ($c['is_active'] && !$is_expired) ? 'Active' : 'Inactive' ?>
                        </span>
                    </a>
                </td>
                <td>
                    <div class="actions">
                        <a href="coupons.php?edit=<?= $c['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                        <a href="coupons.php?delete=<?= $c['id'] ?>" class="btn btn-sm btn-danger"
                           data-confirm="Delete coupon '<?= e($c['code']) ?>'? This cannot be undone.">Delete</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($coupons)): ?>
            <tr><td colspan="8" style="text-align:center;padding:48px;color:var(--text-muted)">No coupons yet. Create your first one below.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Create / Edit form ─────────────────────────────────────────────────── -->
<div class="card" id="couponForm">
    <div class="card-header">
        <span class="card-title"><?= $edit ? 'Edit Coupon: ' . e($edit['code']) : 'Create New Coupon' ?></span>
        <?php if ($edit): ?>
        <a href="coupons.php" class="btn btn-outline btn-sm">Cancel Edit</a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <form method="post" action="coupons.php">
            <?= csrf_field() ?>
            <?php if ($edit): ?><input type="hidden" name="edit_id" value="<?= $edit['id'] ?>"><?php endif; ?>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:20px">
                <!-- Code -->
                <div class="form-group">
                    <label class="form-label">Coupon Code <span class="req">*</span></label>
                    <input type="text" name="code" class="form-control"
                           value="<?= e($edit['code'] ?? strtoupper($_POST['code'] ?? '')) ?>"
                           placeholder="e.g. SUMMER20" style="text-transform:uppercase;letter-spacing:.06em;font-weight:600"
                           <?= $edit ? '' : 'required' ?>>
                    <span class="form-hint">Uppercase letters, numbers, dashes only</span>
                </div>

                <!-- Type -->
                <div class="form-group">
                    <label class="form-label">Discount Type <span class="req">*</span></label>
                    <select name="type" class="form-control" id="couponTypeSelect">
                        <?php foreach (['percentage'=>'Percentage (%)','fixed'=>'Fixed Amount','free_shipping'=>'Free Shipping'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($edit['type'] ?? $_POST['type'] ?? 'fixed') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Value -->
                <div class="form-group" id="valueField">
                    <label class="form-label">Value <span class="req" id="valueReq">*</span></label>
                    <input type="number" name="value" class="form-control"
                           value="<?= e($edit['value'] ?? $_POST['value'] ?? '') ?>"
                           step="0.01" min="0" placeholder="e.g. 10 or 500" id="couponValueInput">
                    <span class="form-hint" id="valueHint">Enter percentage (0–100) or fixed amount</span>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:20px">
                <div class="form-group">
                    <label class="form-label">Minimum Order Amount</label>
                    <input type="number" name="min_order" class="form-control"
                           value="<?= e($edit['min_order'] ?? $_POST['min_order'] ?? '0') ?>"
                           step="0.01" min="0" placeholder="0 = no minimum">
                    <span class="form-hint">Leave 0 for no minimum</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Max Total Uses</label>
                    <input type="number" name="max_uses" class="form-control"
                           value="<?= e($edit['max_uses'] ?? $_POST['max_uses'] ?? '') ?>"
                           min="1" placeholder="Leave blank for unlimited">
                </div>
                <div class="form-group">
                    <label class="form-label">Per-User Limit</label>
                    <input type="number" name="per_user_limit" class="form-control"
                           value="<?= e($edit['per_user_limit'] ?? $_POST['per_user_limit'] ?? '1') ?>"
                           min="1" placeholder="1">
                    <span class="form-hint">How many times one customer can use it</span>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:20px">
                <div class="form-group">
                    <label class="form-label">Description (optional)</label>
                    <input type="text" name="description" class="form-control"
                           value="<?= e($edit['description'] ?? $_POST['description'] ?? '') ?>"
                           placeholder="Short note for admin reference">
                </div>
                <div class="form-group">
                    <label class="form-label">Start Date (optional)</label>
                    <input type="datetime-local" name="starts_at" class="form-control"
                           value="<?= e(!empty($edit['starts_at']) ? date('Y-m-d\TH:i', strtotime($edit['starts_at'])) : ($_POST['starts_at'] ?? '')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Expiry Date (optional)</label>
                    <input type="datetime-local" name="expires_at" class="form-control"
                           value="<?= e(!empty($edit['expires_at']) ? date('Y-m-d\TH:i', strtotime($edit['expires_at'])) : ($_POST['expires_at'] ?? '')) ?>">
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:32px;margin-bottom:24px">
                <label class="form-check">
                    <input type="checkbox" name="is_active" value="1"
                           <?= (!isset($edit) || $edit['is_active']) ? 'checked' : '' ?>>
                    <label>Coupon is active</label>
                </label>
                <?php if ($edit): ?>
                <span style="font-size:.82rem;color:var(--text-muted)">
                    Used <strong><?= $edit['uses_count'] ?></strong> time<?= $edit['uses_count'] !== 1 ? 's' : '' ?> so far.
                </span>
                <?php endif; ?>
            </div>

            <div style="display:flex;gap:12px">
                <button type="submit" class="btn btn-gold">
                    <?= $edit ? 'Update Coupon' : 'Create Coupon' ?>
                </button>
                <?php if ($edit): ?>
                <a href="coupons.php" class="btn btn-outline">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
// Dynamic form: hide value field when type = free_shipping
(function () {
    const typeSelect   = document.getElementById('couponTypeSelect');
    const valueField   = document.getElementById('valueField');
    const valueInput   = document.getElementById('couponValueInput');
    const valueHint    = document.getElementById('valueHint');
    const valueReq     = document.getElementById('valueReq');

    function updateForm() {
        const t = typeSelect.value;
        if (t === 'free_shipping') {
            valueField.style.opacity = '.4';
            valueInput.disabled = true;
            valueReq.style.display = 'none';
            valueHint.textContent = 'Not required for free shipping';
        } else {
            valueField.style.opacity = '1';
            valueInput.disabled = false;
            valueReq.style.display = 'inline';
            valueHint.textContent = t === 'percentage'
                ? 'Enter a number between 1 and 100'
                : 'Enter the fixed discount amount';
        }
    }

    typeSelect.addEventListener('change', updateForm);
    updateForm(); // run on load

    // Auto-uppercase coupon code input
    const codeInput = document.querySelector('input[name="code"]');
    if (codeInput) {
        codeInput.addEventListener('input', () => { codeInput.value = codeInput.value.toUpperCase(); });
    }
})();
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
