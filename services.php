<?php
require_once __DIR__ . '/includes/init.php';
$page_title  = 'Our Services';
$active_page = 'services';
$services    = get_services();
include __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<div class="page-hero page-hero--tall">
    <div class="container">
        <p class="section-eyebrow" style="color:var(--gold)">What We Offer</p>
        <h1>Our <span class="gold-accent">Services</span></h1>
        <p>From personal shopping to corporate gifting, we go beyond products to deliver a complete premium experience.</p>
    </div>
</div>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb__list">
            <li><a href="<?= BASE_URL ?>/">Home</a></li>
            <li>Our Services</li>
        </ul>
    </div>
</div>

<!-- Services Grid -->
<section class="section">
    <div class="container">
        <?php if (empty($services)): ?>
        <div class="empty-state">
            <p>Our services are being updated. Please check back soon.</p>
        </div>
        <?php else: ?>
        <div class="services-grid">
            <?php foreach ($services as $svc):
                $features = service_features($svc);
                $img      = service_img($svc);
            ?>
            <div class="service-card" id="<?= e($svc['slug']) ?>">
                <div class="service-card__icon-wrap">
                    <?php echo service_icon_svg($svc['icon'] ?? 'star', 32); ?>
                </div>
                <?php if ($img): ?>
                <div class="service-card__img">
                    <img src="<?= e($img) ?>" alt="<?= e($svc['title']) ?>">
                </div>
                <?php endif; ?>
                <div class="service-card__body">
                    <h2 class="service-card__title"><?= e($svc['title']) ?></h2>
                    <?php if ($svc['tagline']): ?>
                    <p class="service-card__tagline"><?= e($svc['tagline']) ?></p>
                    <?php endif; ?>
                    <?php if ($svc['description']): ?>
                    <p class="service-card__desc"><?= e($svc['description']) ?></p>
                    <?php endif; ?>
                    <?php if ($features): ?>
                    <ul class="service-card__features">
                        <?php foreach ($features as $f): ?>
                        <li>
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            <?= e($f) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                    <div class="service-card__footer">
                        <?php if ($svc['price_label']): ?>
                        <span class="service-card__price"><?= e($svc['price_label']) ?></span>
                        <?php endif; ?>
                        <a href="<?= e($svc['cta_url'] ?: BASE_URL . '/contact.php') ?>"
                           class="btn btn-green btn-sm">
                            <?= e($svc['cta_text'] ?: 'Learn More') ?>
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA -->
<section class="section" style="background:var(--green);padding:72px 0">
    <div class="container" style="text-align:center;max-width:640px;margin:0 auto">
        <p class="section-eyebrow" style="color:var(--gold)">Get Started</p>
        <h2 style="color:var(--white);font-size:clamp(1.6rem,3vw,2.2rem);margin-bottom:16px">
            Ready to Experience the <span style="color:var(--gold)">Dakari Difference?</span>
        </h2>
        <p style="color:rgba(255,255,255,.75);margin-bottom:36px;line-height:1.8">
            Our team is standing by to help you find the perfect service for your needs. Get in touch today.
        </p>
        <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
            <a href="<?= BASE_URL ?>/contact.php" class="btn btn-gold btn-lg">Contact Us</a>
            <a href="<?= BASE_URL ?>/shop.php"    class="btn btn-outline-white btn-lg">Shop Now</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
