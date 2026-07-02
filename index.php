<?php
require_once __DIR__ . '/includes/init.php';

/* ── Newsletter subscription handler ── */
$nl_success = isset($_GET['subscribed']);
$nl_error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nl_submit'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $nl_error = 'Security check failed. Please refresh the page and try again.';
    } else {
        $nl_email = trim($_POST['email'] ?? '');
        $nl_name  = trim($_POST['name']  ?? '');
        if (!filter_var($nl_email, FILTER_VALIDATE_EMAIL)) {
            $nl_error = 'Please enter a valid email address.';
        } else {
            try {
                $existing = fetchOne('SELECT id FROM newsletter_subscribers WHERE email=?', 's', $nl_email);
                if (!$existing) {
                    query('INSERT INTO newsletter_subscribers (name,email,ip_address) VALUES (?,?,?)',
                          'sss', $nl_name, $nl_email, $_SERVER['REMOTE_ADDR'] ?? '');
                }
                header('Location: ' . BASE_URL . '/?subscribed=1#newsletter'); exit;
            } catch (\Throwable $e) {
                error_log('Newsletter subscribe error: ' . $e->getMessage());
                $nl_error = 'Something went wrong. Please try again later.';
            }
        }
    }
}

$page_title   = 'Home';
$active_page  = 'home';
$slides       = get_carousel_slides();
$featured     = get_featured_products(8);
$new_arrivals = get_new_products(4);
$home_services = get_featured_services(4);
$categories   = get_categories();
include __DIR__ . '/includes/header.php';
?>

<!-- ── Hero Carousel ──────────────────────────────────────────────────────── -->
<section class="carousel" aria-label="Featured promotions">
    <div class="carousel__track">
        <?php if (!empty($slides)): foreach ($slides as $i => $slide): ?>
        <div class="carousel__slide">
            <img src="<?= carousel_img($slide) ?>" alt="<?= e($slide['title']) ?>">
            <div class="carousel__overlay"></div>
            <div class="container">
                <div class="carousel__content">
                    <span class="carousel__eyebrow">New Collection <?= date('Y') ?></span>
                    <h1 class="carousel__title"><?= e($slide['title']) ?></h1>
                    <p class="carousel__subtitle"><?= e($slide['subtitle']) ?></p>
                    <div class="carousel__cta">
                        <a href="<?= e($slide['link_url'] ?? 'shop.php') ?>" class="btn btn-gold btn-lg">
                            <?= e($slide['link_text'] ?? 'Shop Now') ?>
                        </a>
                        <a href="about.php" class="btn btn-outline-white btn-lg">Our Story</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; else: ?>
        <div class="carousel__slide carousel__slide--default">
            <div class="carousel__overlay"></div>
            <div class="container">
                <div class="carousel__content">
                    <span class="carousel__eyebrow">Established 2020 · Nairobi, Kenya</span>
                    <h1 class="carousel__title">Premium Style,<br>Unmatched Quality</h1>
                    <p class="carousel__subtitle">Discover our exclusive collection of luxury products crafted for the discerning individual.</p>
                    <div class="carousel__cta">
                        <a href="shop.php" class="btn btn-gold btn-lg">Explore Collection</a>
                        <a href="about.php" class="btn btn-outline-white btn-lg">Our Story</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php if (count($slides) > 1): ?>
    <button class="carousel__btn carousel__btn--prev" aria-label="Previous">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
    <button class="carousel__btn carousel__btn--next" aria-label="Next">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    </button>
    <div class="carousel__nav">
        <?php foreach ($slides as $i => $_): ?>
        <button class="carousel__dot <?= $i === 0 ? 'active' : '' ?>" aria-label="Slide <?= $i+1 ?>"></button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<!-- ── Trust Strip ────────────────────────────────────────────────────────── -->
<div class="trust-strip">
    <div class="container">
        <div class="trust-strip__grid">
            <div class="trust-item">
                <div class="trust-item__icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </div>
                <div>
                    <strong>We Ship Worldwide</strong>
                    <span>Delivering to your doorstep</span>
                </div>
            </div>
            <div class="trust-item">
                <div class="trust-item__icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div>
                    <strong>100% Authentic</strong>
                    <span>Genuine products only</span>
                </div>
            </div>
            <div class="trust-item">
                <div class="trust-item__icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3z"/></svg>
                </div>
                <div>
                    <strong>Secure Payment</strong>
                    <span>Protected transactions</span>
                </div>
            </div>
            <div class="trust-item">
                <div class="trust-item__icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <div>
                    <strong>Easy Returns</strong>
                    <span>14-day return policy</span>
                </div>
            </div>
            <div class="trust-item">
                <div class="trust-item__icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.39 2 2 0 0 1 3.6 1.21h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.77a16 16 0 0 0 6.29 6.29l.97-.97a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7a2 2 0 0 1 1.72 2.03z"/></svg>
                </div>
                <div>
                    <strong>24 / 7 Support</strong>
                    <span>Always here for you</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Shop by Category ───────────────────────────────────────────────────── -->
<?php if (!empty($categories)): ?>
<section class="section">
    <div class="container">
        <div class="section__header section__header--between">
            <div>
                <p class="section-eyebrow">Browse by Category</p>
                <h2 class="section-title">Our <span class="gold-accent">Collections</span></h2>
            </div>
            <a href="shop.php" class="btn btn-outline-green">View All</a>
        </div>
        <div class="category-grid">
            <?php foreach (array_slice($categories, 0, 6) as $cat): ?>
            <a href="shop.php?category=<?= e($cat['slug']) ?>" class="category-card">
                <div class="category-card__image">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                </div>
                <div class="category-card__body">
                    <h3 class="category-card__name"><?= e($cat['name']) ?></h3>
                    <span class="category-card__link">
                        Shop now
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── Featured Products ──────────────────────────────────────────────────── -->
<?php if (!empty($featured)): ?>
<section class="section section--alt">
    <div class="container">
        <div class="section__header section__header--between">
            <div>
                <p class="section-eyebrow">Hand-Picked</p>
                <h2 class="section-title">Featured <span class="gold-accent">Products</span></h2>
            </div>
            <a href="shop.php?filter=featured" class="btn btn-outline-green">View All</a>
        </div>
        <div class="product-grid">
            <?php foreach ($featured as $product): ?>
            <div class="product-card">
                <div class="product-card__image">
                    <a href="product.php?slug=<?= e($product['slug']) ?>">
                        <img src="<?= product_thumb($product) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
                    </a>
                    <div class="product-card__badges">
                        <?php if ($product['is_new']): ?><span class="badge badge-new">New</span><?php endif; ?>
                        <?php if (is_on_sale($product)): ?><span class="badge badge-sale">Sale</span><?php endif; ?>
                    </div>
                    <button class="product-card__wishlist btn-wishlist" data-id="<?= $product['id'] ?>" aria-label="Add to wishlist">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </button>
                </div>
                <div class="product-card__body">
                    <p class="product-card__name"><a href="product.php?slug=<?= e($product['slug']) ?>"><?= e($product['name']) ?></a></p>
                    <?php if ((float)($product['avg_rating'] ?? 0) > 0): ?>
                    <div class="product-card__rating">
                        <?= render_stars((float)$product['avg_rating'], '13') ?>
                        <span class="product-card__rating-count">(<?= $product['review_count'] ?>)</span>
                    </div>
                    <?php endif; ?>
                    <div class="product-card__price">
                        <?php if (is_on_sale($product)): ?>
                            <span class="price-sale"><?= money((float)$product['sale_price']) ?></span>
                            <span class="price-original"><?= money((float)$product['price']) ?></span>
                        <?php else: ?>
                            <span class="price-current"><?= money((float)$product['price']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-card__footer">
                        <a href="product.php?slug=<?= e($product['slug']) ?>" class="btn btn-outline-green btn-sm">View</a>
                        <button class="btn btn-green btn-sm btn-add-cart" data-id="<?= $product['id'] ?>">Add to Cart</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── Stats Banner ───────────────────────────────────────────────────────── -->
<div class="stats-banner">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-value">500+</span>
                <span class="stat-label">Premium Products</span>
            </div>
            <div class="stat-item">
                <span class="stat-value">12K+</span>
                <span class="stat-label">Happy Customers</span>
            </div>
            <div class="stat-item">
                <span class="stat-value">50+</span>
                <span class="stat-label">Brand Partners</span>
            </div>
            <div class="stat-item">
                <span class="stat-value">4</span>
                <span class="stat-label">Years in Business</span>
            </div>
        </div>
    </div>
</div>

<!-- ── New Arrivals ───────────────────────────────────────────────────────── -->
<?php if (!empty($new_arrivals)): ?>
<section class="section">
    <div class="container">
        <div class="section__header section__header--between">
            <div>
                <p class="section-eyebrow">Just In</p>
                <h2 class="section-title">New <span class="gold-accent">Arrivals</span></h2>
            </div>
            <a href="shop.php?filter=new" class="btn btn-outline-green">View All</a>
        </div>
        <div class="product-grid product-grid--4">
            <?php foreach ($new_arrivals as $product): ?>
            <div class="product-card">
                <div class="product-card__image">
                    <a href="product.php?slug=<?= e($product['slug']) ?>">
                        <img src="<?= product_thumb($product) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
                    </a>
                    <div class="product-card__badges"><span class="badge badge-new">New</span></div>
                    <button class="product-card__wishlist btn-wishlist" data-id="<?= $product['id'] ?>" aria-label="Wishlist">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </button>
                </div>
                <div class="product-card__body">
                    <p class="product-card__name"><a href="product.php?slug=<?= e($product['slug']) ?>"><?= e($product['name']) ?></a></p>
                    <?php if ((float)($product['avg_rating'] ?? 0) > 0): ?>
                    <div class="product-card__rating">
                        <?= render_stars((float)$product['avg_rating'], '13') ?>
                        <span class="product-card__rating-count">(<?= $product['review_count'] ?>)</span>
                    </div>
                    <?php endif; ?>
                    <div class="product-card__price">
                        <?php if (is_on_sale($product)): ?>
                            <span class="price-sale"><?= money((float)$product['sale_price']) ?></span>
                            <span class="price-original"><?= money((float)$product['price']) ?></span>
                        <?php else: ?>
                            <span class="price-current"><?= money((float)$product['price']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-card__footer">
                        <a href="product.php?slug=<?= e($product['slug']) ?>" class="btn btn-outline-green btn-sm">View</a>
                        <button class="btn btn-green btn-sm btn-add-cart" data-id="<?= $product['id'] ?>">Add to Cart</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── Why Choose Dakari ──────────────────────────────────────────────────── -->
<section class="why-section">
    <div class="container">
        <div class="why-section__inner">
            <div class="why-section__text">
                <p class="section-eyebrow" style="color:var(--gold-light)">Why Dakari</p>
                <h2 class="section-title" style="color:var(--white)">A Brand Built on<br><span class="gold-accent">Excellence</span></h2>
                <p style="color:rgba(255,255,255,.75);line-height:1.8;margin-bottom:32px">
                    Since our founding, Dakari has stood at the intersection of quality and style. Every product in our collection is carefully sourced, quality-tested, and curated to reflect the premium standards our customers expect.
                </p>
                <div class="why-pillars">
                    <?php
                    $pillars = [
                        ['Quality Assurance',  'Every product passes our rigorous quality inspection before listing.'],
                        ['Ethical Sourcing',   'We partner only with responsible, certified suppliers.'],
                        ['Customer First',     'Our team is available 24/7 to ensure your satisfaction.'],
                        ['Secure Shopping',    'End-to-end encryption and secure payment processing.'],
                    ];
                    foreach ($pillars as $p): ?>
                    <div class="why-pillar">
                        <div class="why-pillar__dot"></div>
                        <div>
                            <strong><?= $p[0] ?></strong>
                            <span><?= $p[1] ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <a href="about.php" class="btn btn-gold btn-lg" style="margin-top:32px">Learn Our Story</a>
            </div>
            <div class="why-section__visual">
                <div class="why-card">
                    <div class="why-card__badge">Est. 2020</div>
                    <div class="why-card__stat"><span>12,000+</span><small>Customers Served</small></div>
                    <div class="why-card__stat"><span>4.9 ★</span><small>Average Rating</small></div>
                    <div class="why-card__stat"><span>99%</span><small>Satisfaction Rate</small></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Our Services ──────────────────────────────────────────────────────── -->
<?php if (!empty($home_services)): ?>
<section class="section section--alt">
    <div class="container">
        <div class="section__header section__header--center">
            <p class="section-eyebrow">What We Offer</p>
            <h2 class="section-title">Our <span class="gold-accent">Services</span></h2>
            <p class="section-subtitle">Beyond great products — a complete premium experience from consultation to delivery</p>
        </div>
        <div class="home-services-grid">
            <?php foreach ($home_services as $svc): ?>
            <div class="home-service-card">
                <div class="home-service-card__icon">
                    <?= service_icon_svg($svc['icon'] ?? 'star', 30) ?>
                </div>
                <h3 class="home-service-card__title"><?= e($svc['title']) ?></h3>
                <p class="home-service-card__tagline"><?= e($svc['tagline']) ?></p>
                <?php $features = array_slice(service_features($svc), 0, 3); ?>
                <?php if ($features): ?>
                <ul class="home-service-card__features">
                    <?php foreach ($features as $f): ?>
                    <li><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg><?= e($f) ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/services.php#<?= e($svc['slug']) ?>" class="home-service-card__link">
                    <?= e($svc['cta_text'] ?: 'Learn More') ?>
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:44px">
            <a href="<?= BASE_URL ?>/services.php" class="btn btn-outline-green">View All Services</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── Newsletter ─────────────────────────────────────────────────────────── -->
<section class="newsletter-section" id="newsletter">
    <div class="container">
        <div class="newsletter-inner">
            <div class="newsletter-text">
                <p class="section-eyebrow" style="color:var(--gold)">Stay Connected</p>
                <h2>Join the Dakari <span class="gold-accent">Community</span></h2>
                <p>Get exclusive access to new arrivals, special offers, and insider style guides — delivered straight to your inbox.</p>
            </div>

            <?php if ($nl_success): ?>
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;gap:12px;padding:24px 0">
                <div style="width:56px;height:56px;border-radius:50%;background:rgba(201,168,76,.15);display:flex;align-items:center;justify-content:center">
                    <svg width="28" height="28" fill="none" stroke="#C9A84C" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <h3 style="color:#C9A84C;font-family:var(--font-serif);font-size:1.3rem;margin:0">You're subscribed!</h3>
                <p style="color:#f0e6c8;margin:0;font-size:.95rem">
                    Welcome to the Dakari community. Watch your inbox for exclusive offers and new arrivals.
                </p>
            </div>
            <?php else: ?>
            <form class="newsletter-form" action="<?= BASE_URL ?>/#newsletter" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="nl_submit" value="1">
                <?php if ($nl_error): ?>
                <p style="color:#fca5a5;font-size:.85rem;margin-bottom:10px;text-align:center"><?= e($nl_error) ?></p>
                <?php endif; ?>
                <div class="newsletter-form__row">
                    <input type="text"  name="name"  class="newsletter-input" placeholder="Your name"
                           value="<?= e($_POST['name'] ?? '') ?>">
                    <input type="email" name="email" class="newsletter-input" placeholder="Email address" required
                           value="<?= e($_POST['email'] ?? '') ?>">
                    <button type="submit" class="btn btn-gold">Subscribe</button>
                </div>
                <p class="newsletter-disclaimer">By subscribing you agree to our privacy policy. Unsubscribe anytime.</p>
            </form>
            <?php endif; ?>

        </div>
    </div>
</section>

<script>window.__csrf = "<?= e(csrf_token()) ?>";</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
