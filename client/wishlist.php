<?php
require_once __DIR__ . '/includes/client_init.php';
$page_title    = 'My Wishlist';
$client_active = 'wishlist';
$extra_css     = '<link rel="stylesheet" href="' . BASE_URL . '/assets/css/client.css">';
$user          = current_user();

if (isset($_GET['remove'])) {
    query('DELETE FROM wishlist WHERE user_id=? AND product_id=?','ii',$user['id'],(int)$_GET['remove']);
    flash('success','Removed from wishlist.');
    header('Location: wishlist.php'); exit;
}

$items = fetchAll(
    'SELECT w.*, p.name, p.slug, p.price, p.sale_price, p.thumbnail, p.is_active
     FROM wishlist w
     JOIN products p ON p.id = w.product_id
     WHERE w.user_id = ?
     ORDER BY w.added_at DESC',
    'i', $user['id']
);
include __DIR__ . '/includes/client_header.php';
?>

<div class="client-page-header">
    <h1 class="client-page-title">My Wishlist</h1>
    <?php if (!empty($items)): ?>
    <span style="font-size:.85rem;color:var(--text-muted)"><?= count($items) ?> saved item<?= count($items) !== 1 ? 's' : '' ?></span>
    <?php endif; ?>
</div>

<?php if (!empty($items)): ?>
<div class="wish-grid">
    <?php foreach ($items as $item): ?>
    <div class="wish-card">
        <div class="wish-card__image">
            <a href="<?= BASE_URL ?>/product.php?slug=<?= e($item['slug']) ?>">
                <img src="<?= product_thumb($item) ?>" alt="<?= e($item['name']) ?>" loading="lazy">
            </a>
            <?php if (!$item['is_active']): ?>
            <div style="position:absolute;inset:0;background:rgba(255,255,255,.7);display:flex;align-items:center;justify-content:center">
                <span style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em">Unavailable</span>
            </div>
            <?php elseif (is_on_sale($item)): ?>
            <span style="position:absolute;top:8px;left:8px;background:var(--gold);color:var(--white);font-size:.68rem;font-weight:700;padding:3px 8px;border-radius:20px;text-transform:uppercase">Sale</span>
            <?php endif; ?>
        </div>
        <div class="wish-card__body">
            <p class="wish-card__name"><a href="<?= BASE_URL ?>/product.php?slug=<?= e($item['slug']) ?>"><?= e($item['name']) ?></a></p>
            <div class="wish-card__price">
                <?php if (is_on_sale($item)): ?>
                <?= money((float)$item['sale_price']) ?>
                <span class="was"><?= money((float)$item['price']) ?></span>
                <?php else: ?>
                <?= money((float)$item['price']) ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="wish-card__footer">
            <?php if ($item['is_active']): ?>
            <button class="btn btn-green btn-add-cart" data-id="<?= $item['product_id'] ?>">Add to Cart</button>
            <?php else: ?>
            <button class="btn btn-outline" disabled style="cursor:not-allowed;opacity:.5;flex:1">Unavailable</button>
            <?php endif; ?>
            <a href="wishlist.php?remove=<?= $item['product_id'] ?>" class="btn btn-outline" title="Remove" onclick="return confirm('Remove from wishlist?')">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state" style="padding:80px 0">
    <svg width="56" height="56" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
    <h3>Your wishlist is empty</h3>
    <p>Save products you love while browsing the shop and find them here.</p>
    <a href="<?= BASE_URL ?>/shop.php" class="btn btn-green btn-sm" style="margin-top:4px">Browse Products</a>
</div>
<?php endif; ?>

<script>window.__csrf = "<?= e(csrf_token()) ?>";</script>
<?php include __DIR__ . '/includes/client_footer.php'; ?>
