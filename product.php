<?php
require_once __DIR__ . '/includes/init.php';

$slug    = trim($_GET['slug'] ?? '');
$product = $slug ? get_product_by_slug($slug) : null;
if (!$product) { header('HTTP/1.1 404 Not Found'); include __DIR__ . '/includes/header.php'; echo '<div class="container section"><div class="empty-state"><h3>Product not found</h3><a href="shop.php" class="btn btn-green">Back to Shop</a></div></div>'; include __DIR__ . '/includes/footer.php'; exit; }

// Increment views
query('UPDATE products SET views = views + 1 WHERE id = ?', 'i', $product['id']);

$images      = get_product_images($product['id']);
$category    = $product['category_id'] ? fetchOne('SELECT * FROM categories WHERE id = ?', 'i', $product['category_id']) : null;
$related     = fetchAll('SELECT * FROM products WHERE category_id = ? AND id != ? AND is_active = 1 LIMIT 4', 'ii', $product['category_id'] ?? 0, $product['id']);
$page_title  = $product['name'];
$active_page = 'shop';

// Reviews
$reviews        = get_product_reviews($product['id']);
$review_summary = get_review_summary($product['id']);
$user           = current_user();
$can_review     = can_review($product['id']);
$review_errors  = [];
$review_success = false;

// Handle add-to-cart POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!verify_csrf()) { flash('error', 'Invalid request.'); }
    else {
        $qty        = max(1, (int)($_POST['quantity'] ?? 1));
        $session_id = session_id();
        $user_id    = $_SESSION['user_id'] ?? null;
        if ($user_id) {
            $existing = fetchOne('SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?', 'ii', $user_id, $product['id']);
            if ($existing) {
                query('UPDATE cart SET quantity = quantity + ? WHERE id = ?', 'ii', $qty, $existing['id']);
            } else {
                query('INSERT INTO cart (user_id, product_id, quantity) VALUES (?,?,?)', 'iii', $user_id, $product['id'], $qty);
            }
        } else {
            $existing = fetchOne('SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ? AND user_id IS NULL', 'si', $session_id, $product['id']);
            if ($existing) {
                query('UPDATE cart SET quantity = quantity + ? WHERE id = ?', 'ii', $qty, $existing['id']);
            } else {
                query('INSERT INTO cart (session_id, product_id, quantity) VALUES (?,?,?)', 'sii', $session_id, $product['id'], $qty);
            }
        }
        flash('success', 'Added to cart!');
        header('Location: product.php?slug=' . urlencode($product['slug']));
        exit;
    }
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!verify_csrf()) {
        $review_errors[] = 'Invalid request.';
    } else {
        $rating      = max(1, min(5, (int)($_POST['rating']      ?? 5)));
        $title       = trim($_POST['review_title'] ?? '');
        $body        = trim($_POST['review_body']  ?? '');
        $guest_name  = trim($_POST['guest_name']   ?? '');
        $guest_email = trim($_POST['guest_email']  ?? '');

        if (empty($body)) $review_errors[] = 'Please write your review.';
        if (!$user && empty($guest_name)) $review_errors[] = 'Your name is required.';
        if (!$user && !filter_var($guest_email, FILTER_VALIDATE_EMAIL)) $review_errors[] = 'Valid email is required.';
        if (!$can_review) $review_errors[] = 'You have already reviewed this product.';

        if (empty($review_errors)) {
            $auto_approve = (int)(setting('auto_approve_reviews', '0'));
            query(
                'INSERT INTO product_reviews
                 (product_id, user_id, guest_name, guest_email, rating, title, body, is_approved)
                 VALUES (?,?,?,?,?,?,?,?)',
                'iissiisi',
                $product['id'],
                $user['id'] ?? null,
                $user ? null : $guest_name,
                $user ? null : $guest_email,
                $rating, $title, $body, $auto_approve
            );
            if ($auto_approve) update_product_rating($product['id']);
            $review_success = true;
            $can_review     = false;
            $reviews        = get_product_reviews($product['id']);
            $review_summary = get_review_summary($product['id']);
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb__list">
            <li><a href="<?= BASE_URL ?>/">Home</a></li>
            <li><a href="shop.php">Shop</a></li>
            <?php if ($category): ?><li><a href="shop.php?category=<?= e($category['slug']) ?>"><?= e($category['name']) ?></a></li><?php endif; ?>
            <li><?= e($product['name']) ?></li>
        </ul>
    </div>
</div>

<div class="container">
    <div class="product-detail">
        <!-- Gallery -->
        <div class="product-gallery">
            <div class="product-gallery__main">
                <img id="mainProductImg" src="<?= product_thumb($product) ?>" alt="<?= e($product['name']) ?>">
            </div>
            <?php if (!empty($images)): ?>
            <div class="product-gallery__thumbs">
                <div class="product-gallery__thumb active" data-src="<?= product_thumb($product) ?>">
                    <img src="<?= product_thumb($product) ?>" alt="Main">
                </div>
                <?php foreach ($images as $img): ?>
                <div class="product-gallery__thumb" data-src="<?= BASE_URL ?>/uploads/products/<?= e($img['image_path']) ?>">
                    <img src="<?= BASE_URL ?>/uploads/products/<?= e($img['image_path']) ?>" alt="">
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Info -->
        <div class="product-info">
            <?php if ($category): ?>
            <p class="product-info__category"><?= e($category['name']) ?></p>
            <?php endif; ?>
            <h1 class="product-info__name"><?= e($product['name']) ?></h1>

            <!-- Inline star summary -->
            <?php if ($review_summary['total'] > 0): ?>
            <div class="product-info__rating">
                <?= render_stars($review_summary['avg']) ?>
                <a href="#reviews" class="product-info__rating-count"><?= $review_summary['avg'] ?> (<?= $review_summary['total'] ?> review<?= $review_summary['total'] !== 1 ? 's' : '' ?>)</a>
            </div>
            <?php endif; ?>

            <div class="product-info__price">
                <?php if (is_on_sale($product)): ?>
                    <span class="price-current price-sale"><?= money((float)$product['sale_price']) ?></span>
                    <span class="price-original"><?= money((float)$product['price']) ?></span>
                    <span class="badge badge-sale">Save <?= money((float)$product['price'] - (float)$product['sale_price']) ?></span>
                <?php else: ?>
                    <span class="price-current"><?= money((float)$product['price']) ?></span>
                <?php endif; ?>
            </div>

            <?php if (!empty($product['short_desc'])): ?>
            <p class="product-info__desc"><?= e($product['short_desc']) ?></p>
            <?php endif; ?>

            <div class="product-info__meta">
                <?php if (!empty($product['sku'])): ?>
                <span><strong>SKU:</strong> <?= e($product['sku']) ?></span>
                <?php endif; ?>
                <span><strong>Availability:</strong>
                    <?php if ($product['stock'] > 0): ?>
                    <span style="color:var(--green-light);font-weight:600">In Stock (<?= $product['stock'] ?> left)</span>
                    <?php else: ?>
                    <span style="color:#c0392b;font-weight:600">Out of Stock</span>
                    <?php endif; ?>
                </span>
            </div>

            <?php if ($product['stock'] > 0): ?>
            <form method="post" action="product.php?slug=<?= e($product['slug']) ?>">
                <?= csrf_field() ?>
                <div class="qty-selector" style="margin-bottom:20px">
                    <button type="button" class="qty-btn" data-action="dec">−</button>
                    <input type="number" id="qtyInput" name="quantity" class="qty-input" value="1" min="1" max="<?= $product['stock'] ?>">
                    <button type="button" class="qty-btn" data-action="inc">+</button>
                </div>
                <div class="product-info__actions">
                    <button type="submit" name="add_to_cart" class="btn btn-green btn-lg">Add to Cart</button>
                    <button type="button" class="btn btn-outline-gold btn-wishlist" data-id="<?= $product['id'] ?>" aria-label="Wishlist">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        Wishlist
                    </button>
                </div>
            </form>
            <?php else: ?>
            <p style="color:#c0392b;font-weight:600;margin-bottom:20px">Currently out of stock. Check back soon.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabs: Description / Shipping / Reviews -->
    <div class="tabs-wrapper" id="reviews" style="margin-top:48px;margin-bottom:56px">
        <div class="tabs">
            <button class="tab-btn active" data-tab="tab-desc">Description</button>
            <button class="tab-btn" data-tab="tab-shipping">Shipping &amp; Returns</button>
            <button class="tab-btn" data-tab="tab-reviews">
                Reviews
                <?php if ($review_summary['total'] > 0): ?>
                <span class="review-tab-badge"><?= $review_summary['total'] ?></span>
                <?php endif; ?>
            </button>
        </div>

        <div id="tab-desc" class="tab-pane active" style="line-height:1.8;color:var(--text-muted);font-size:.95rem">
            <?= nl2br(e($product['description'] ?? 'No description available.')) ?>
        </div>

        <div id="tab-shipping" class="tab-pane" style="line-height:1.8;color:var(--text-muted);font-size:.95rem">
            <p><strong>Free shipping</strong> on orders over <?= money(5000) ?>. Standard delivery in 3–5 business days within Nairobi. Upcountry 5–7 business days.</p>
            <p style="margin-top:12px"><strong>Returns:</strong> We accept returns within 14 days of delivery. Items must be unused and in original packaging. Contact us at <?= e(setting('site_email')) ?> to initiate a return.</p>
        </div>

        <!-- ── Reviews tab ─────────────────────────────────────────────────── -->
        <div id="tab-reviews" class="tab-pane">
            <div class="reviews-layout">

                <!-- Left: summary + form -->
                <div class="reviews-left">

                    <!-- Rating summary -->
                    <?php if ($review_summary['total'] > 0): ?>
                    <div class="review-summary">
                        <div class="review-summary__score">
                            <span class="review-summary__big"><?= $review_summary['avg'] ?></span>
                            <div>
                                <?= render_stars($review_summary['avg'], '20') ?>
                                <p style="font-size:.82rem;color:var(--text-muted);margin-top:4px"><?= $review_summary['total'] ?> review<?= $review_summary['total'] !== 1 ? 's' : '' ?></p>
                            </div>
                        </div>
                        <div class="review-summary__bars">
                            <?php for ($s = 5; $s >= 1; $s--):
                                $pct = $review_summary['total'] > 0 ? round($review_summary['dist'][$s] / $review_summary['total'] * 100) : 0;
                            ?>
                            <div class="review-bar">
                                <span class="review-bar__label"><?= $s ?></span>
                                <svg class="review-bar__star" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                <div class="review-bar__track"><div class="review-bar__fill" style="width:<?= $pct ?>%"></div></div>
                                <span class="review-bar__count"><?= $review_summary['dist'][$s] ?></span>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Write a review -->
                    <?php if ($review_success): ?>
                    <div class="alert alert-success" style="margin-top:24px">
                        Thank you! Your review has been submitted<?= setting('auto_approve_reviews','0') ? '' : ' and is awaiting approval' ?>.
                    </div>
                    <?php elseif ($can_review): ?>
                    <div class="review-form-box">
                        <h4 class="review-form-box__title">Write a Review</h4>

                        <?php foreach ($review_errors as $err): ?>
                        <div class="alert alert-error" style="margin-bottom:12px"><?= e($err) ?></div>
                        <?php endforeach; ?>

                        <form method="post" action="product.php?slug=<?= e($product['slug']) ?>#reviews">
                            <?= csrf_field() ?>

                            <!-- Star picker -->
                            <div class="form-group" style="margin-bottom:16px">
                                <label class="form-label">Your Rating <span class="required">*</span></label>
                                <div class="star-picker" id="starPicker">
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                    <button type="button" class="star-pick-btn" data-val="<?= $s ?>" aria-label="<?= $s ?> star<?= $s > 1 ? 's' : '' ?>">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    </button>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="rating" id="ratingInput" value="5">
                            </div>

                            <?php if (!$user): ?>
                            <div class="form-grid" style="margin-bottom:12px">
                                <div class="form-group">
                                    <label class="form-label">Your Name <span class="required">*</span></label>
                                    <input type="text" name="guest_name" class="form-control" value="<?= e($_POST['guest_name'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email <span class="required">*</span></label>
                                    <input type="email" name="guest_email" class="form-control" value="<?= e($_POST['guest_email'] ?? '') ?>" required>
                                </div>
                            </div>
                            <?php else: ?>
                            <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:12px">
                                Reviewing as <strong><?= e($user['first_name'] . ' ' . $user['last_name']) ?></strong>
                            </p>
                            <?php endif; ?>

                            <div class="form-group" style="margin-bottom:12px">
                                <label class="form-label">Review Title</label>
                                <input type="text" name="review_title" class="form-control" placeholder="Sum it up in one line" value="<?= e($_POST['review_title'] ?? '') ?>" maxlength="150">
                            </div>
                            <div class="form-group" style="margin-bottom:16px">
                                <label class="form-label">Your Review <span class="required">*</span></label>
                                <textarea name="review_body" class="form-control" rows="4" placeholder="What did you like or dislike? How was the quality?" required><?= e($_POST['review_body'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" name="submit_review" class="btn btn-green">Submit Review</button>
                        </form>
                    </div>
                    <?php elseif ($user): ?>
                    <p style="font-size:.88rem;color:var(--text-muted);margin-top:16px;padding:12px 16px;background:var(--off-white);border-radius:var(--radius)">
                        You have already reviewed this product.
                    </p>
                    <?php else: ?>
                    <p style="font-size:.88rem;color:var(--text-muted);margin-top:16px;padding:12px 16px;background:var(--off-white);border-radius:var(--radius)">
                        <a href="login.php?redirect=product.php?slug=<?= urlencode($product['slug']) ?>" style="color:var(--green);font-weight:600">Log in</a> to write a review, or continue as a guest below.
                    </p>
                    <?php endif; ?>
                </div>

                <!-- Right: reviews list -->
                <div class="reviews-right">
                    <?php if (empty($reviews)): ?>
                    <div style="padding:32px 0;text-align:center;color:var(--text-muted)">
                        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:12px;opacity:.4"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <p>No reviews yet. Be the first to share your experience!</p>
                    </div>
                    <?php else: ?>
                    <div class="review-list">
                        <?php foreach ($reviews as $rev): ?>
                        <div class="review-card">
                            <div class="review-card__header">
                                <div class="review-card__avatar"><?= strtoupper(substr($rev['first_name'] ?? $rev['guest_name'] ?? 'A', 0, 1)) ?></div>
                                <div class="review-card__meta">
                                    <p class="review-card__author"><?= review_display_name($rev) ?></p>
                                    <div class="review-card__stars"><?= render_stars((float)$rev['rating'], '14') ?></div>
                                </div>
                                <span class="review-card__date"><?= date('M j, Y', strtotime($rev['created_at'])) ?></span>
                            </div>
                            <?php if (!empty($rev['title'])): ?>
                            <p class="review-card__title"><?= e($rev['title']) ?></p>
                            <?php endif; ?>
                            <p class="review-card__body"><?= nl2br(e($rev['body'])) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div><!-- /tab-reviews -->
    </div>

    <!-- Related products -->
    <?php if (!empty($related)): ?>
    <section style="padding-bottom:60px">
        <div class="section__header section__header--center">
            <h2 class="section-title">You May Also <span class="gold-accent">Like</span></h2>
        </div>
        <div class="product-grid">
            <?php foreach ($related as $rel): ?>
            <div class="product-card">
                <div class="product-card__image">
                    <a href="product.php?slug=<?= e($rel['slug']) ?>">
                        <img src="<?= product_thumb($rel) ?>" alt="<?= e($rel['name']) ?>" loading="lazy">
                    </a>
                    <?php if ($rel['is_new']): ?><div class="product-card__badges"><span class="badge badge-new">New</span></div><?php endif; ?>
                </div>
                <div class="product-card__body">
                    <p class="product-card__name"><a href="product.php?slug=<?= e($rel['slug']) ?>"><?= e($rel['name']) ?></a></p>
                    <?php if ((float)$rel['avg_rating'] > 0): ?>
                    <div class="product-card__rating">
                        <?= render_stars((float)$rel['avg_rating'], '13') ?>
                        <span class="product-card__rating-count">(<?= $rel['review_count'] ?>)</span>
                    </div>
                    <?php endif; ?>
                    <div class="product-card__price">
                        <?php if (is_on_sale($rel)): ?>
                            <span class="price-sale"><?= money((float)$rel['sale_price']) ?></span>
                            <span class="price-original"><?= money((float)$rel['price']) ?></span>
                        <?php else: ?>
                            <span class="price-current"><?= money((float)$rel['price']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-card__footer">
                        <a href="product.php?slug=<?= e($rel['slug']) ?>" class="btn btn-outline-green btn-sm">View</a>
                        <button class="btn btn-green btn-sm btn-add-cart" data-id="<?= $rel['id'] ?>">Add to Cart</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<script>
window.__csrf = "<?= e(csrf_token()) ?>";

// Star picker interaction
(function () {
    const picker = document.getElementById('starPicker');
    const input  = document.getElementById('ratingInput');
    if (!picker) return;

    const btns = picker.querySelectorAll('.star-pick-btn');
    let selected = 5;

    function paint(val, hover) {
        btns.forEach(b => {
            const v   = +b.dataset.val;
            const svg = b.querySelector('svg');
            if (v <= val) {
                svg.style.fill   = 'var(--gold)';
                svg.style.stroke = 'var(--gold)';
            } else {
                svg.style.fill   = hover ? 'none' : (v <= selected ? 'var(--gold)' : 'none');
                svg.style.stroke = 'var(--gold)';
            }
        });
    }

    // Init at 5
    paint(5, false);

    btns.forEach(b => {
        b.addEventListener('mouseenter', () => paint(+b.dataset.val, true));
        b.addEventListener('mouseleave', () => paint(selected, false));
        b.addEventListener('click', () => {
            selected = +b.dataset.val;
            input.value = selected;
            paint(selected, false);
        });
    });
})();

// Auto-open reviews tab if URL hash is #reviews
if (window.location.hash === '#reviews') {
    const btn = document.querySelector('[data-tab="tab-reviews"]');
    if (btn) btn.click();
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
