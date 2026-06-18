<?php
require_once __DIR__ . '/includes/client_init.php';
$page_title    = 'My Wishlist';
$client_active = 'wishlist';
$extra_css     = '<link rel="stylesheet" href="' . BASE_URL . '/assets/css/client.css">';
$user          = current_user();

if (isset($_GET['remove'])) {
    query('DELETE FROM wishlist WHERE user_id=? AND product_id=?','ii',$user['id'],(int)$_GET['remove']);
    flash('success','Removed from wishlist.'); header('Location: wishlist.php'); exit;
}

$items = fetchAll('SELECT w.*,p.name,p.slug,p.price,p.sale_price,p.thumbnail FROM wishlist w JOIN products p ON p.id=w.product_id WHERE w.user_id=? ORDER BY w.added_at DESC','i',$user['id']);
include __DIR__ . '/includes/client_header.php';
?>
<h1 class="client-page-title">My Wishlist</h1>

<?php if (!empty($items)): ?>
<div class="product-grid">
    <?php foreach ($items as $item): ?>
    <div class="product-card">
        <div class="product-card__image">
            <a href="<?= BASE_URL ?>/product.php?slug=<?= e($item['slug']) ?>">
                <img src="<?= product_thumb($item) ?>" alt="<?= e($item['name']) ?>" loading="lazy">
            </a>
        </div>
        <div class="product-card__body">
            <p class="product-card__name"><a href="<?= BASE_URL ?>/product.php?slug=<?= e($item['slug']) ?>"><?= e($item['name']) ?></a></p>
            <div class="product-card__price">
                <?php if (is_on_sale($item)): ?>
                    <span class="price-sale"><?= money((float)$item['sale_price']) ?></span>
                    <span class="price-original"><?= money((float)$item['price']) ?></span>
                <?php else: ?>
                    <span class="price-current"><?= money((float)$item['price']) ?></span>
                <?php endif; ?>
            </div>
            <div class="product-card__footer">
                <button class="btn btn-green btn-sm btn-add-cart" data-id="<?= $item['product_id'] ?>">Add to Cart</button>
                <a href="wishlist.php?remove=<?= $item['product_id'] ?>" class="btn btn-outline-green btn-sm">Remove</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state" style="padding:60px 0">
    <svg width="56" height="56" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
    <h3>Your wishlist is empty</h3>
    <p>Save products you love while browsing the shop.</p>
    <a href="<?= BASE_URL ?>/shop.php" class="btn btn-green">Browse Products</a>
</div>
<?php endif; ?>

<script>window.__csrf = "<?= e(csrf_token()) ?>";</script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<?php include __DIR__ . '/includes/client_footer.php'; ?>
