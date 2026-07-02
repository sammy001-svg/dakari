<?php
require_once __DIR__ . '/includes/init.php';
$page_title  = 'About Us';
$active_page = 'about';

// Icon path map (must match admin/about.php)
$about_icons = [
    'shield'  => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    'star'    => 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z',
    'users'   => 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75',
    'check'   => 'M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
    'bulb'    => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 1 1 7.072 0l-.548.547A3.374 3.374 0 0 0 14 18.469V19a2 2 0 1 1-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
    'globe'   => 'M3.055 11H5a2 2 0 0 1 2 2v1a2 2 0 0 0 2 2 2 2 0 0 1 2 2v2.945M8 3.935V5.5A2.5 2.5 0 0 0 10.5 8h.5a2 2 0 0 1 2 2 2 2 0 0 0 4 0 2 2 0 0 1 2-2h1.064M15 20.488V18a2 2 0 0 1 2-2h3.064M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
    'heart'   => 'M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z',
    'award'   => 'M5 3h14a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2zm7 9v9m-4 0h8',
    'target'  => 'M22 12h-4m-8 0H2m10-8v4m0 8v4M12 6a6 6 0 1 0 0 12A6 6 0 0 0 12 6z',
    'leaf'    => 'M17 8C8 10 5.9 16.17 3.82 19.95M5 19a7.96 7.96 0 0 0 8-8c0-5.56-5.07-10-5.07-10S3 6.31 3 11c0 3.12 1.12 6 3.07 8',
];

// Helper
$ab = fn($k, $d='') => setting($k) ?: $d;

// JSON-stored repeaters with hardcoded defaults
$values = json_decode(setting('about_values', ''), true) ?: [
    ['icon'=>'shield','title'=>'Integrity',      'text'=>'We operate with complete transparency and honesty in every interaction — with customers, partners, and each other.'],
    ['icon'=>'star',  'title'=>'Excellence',     'text'=>'We never settle for "good enough". Every product, every interaction must be exceptional and exceed expectations.'],
    ['icon'=>'users', 'title'=>'Customer First', 'text'=>'Our customers are the centre of every decision we make. Their satisfaction is our primary measure of success.'],
    ['icon'=>'check', 'title'=>'Authenticity',   'text'=>'We stock only genuine, verified products from trusted suppliers. No counterfeits, no compromises — ever.'],
    ['icon'=>'bulb',  'title'=>'Innovation',     'text'=>'We continuously improve our platform, processes, and product selection to deliver a better experience.'],
    ['icon'=>'globe', 'title'=>'Sustainability', 'text'=>'We are committed to responsible sourcing, minimal environmental footprint, and giving back to our community.'],
];
$milestones = json_decode(setting('about_milestones', ''), true) ?: [
    ['year'=>'2020','quarter'=>'Q1','title'=>'Company Founded',        'text'=>'Dakari was incorporated in Nairobi with a vision to bring premium products to East Africa.'],
    ['year'=>'2020','quarter'=>'Q4','title'=>'First 500 Customers',    'text'=>'We hit our first major milestone — 500 satisfied customers and a 4.8-star average rating.'],
    ['year'=>'2021','quarter'=>'Q2','title'=>'Influencer Programme',   'text'=>'Launched our brand ambassador programme, partnering with top Kenyan content creators.'],
    ['year'=>'2022','quarter'=>'Q1','title'=>'Online Platform Launch', 'text'=>'Rolled out our full e-commerce platform with cart, wishlist, and client portal.'],
    ['year'=>'2022','quarter'=>'Q3','title'=>'5,000+ Customers',       'text'=>'Surpassed 5,000 customers and expanded our product catalogue to 300+ SKUs.'],
    ['year'=>'2023','quarter'=>'Q2','title'=>'Regional Expansion',     'text'=>'Extended delivery coverage to Uganda, Tanzania, and Rwanda.'],
    ['year'=>'2024','quarter'=>'Q1','title'=>'10,000+ Orders',         'text'=>'Celebrated over 10,000 fulfilled orders with a 99% customer satisfaction rate.'],
    ['year'=>'2025','quarter'=>'Q3','title'=>'Platform Redesign',      'text'=>'Launched our new corporate platform with advanced inventory, coupons, and reviews.'],
];

include __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<div class="page-hero page-hero--tall" style="background:var(--green)">
    <div class="container">
        <p class="section-eyebrow" style="color:var(--gold)"><?= e($ab('about_hero_eyebrow','Our Story')) ?></p>
        <h1 style="color:var(--white);font-size:clamp(2rem,5vw,3.2rem);margin-bottom:16px">
            <?= nl2br(e($ab('about_hero_headline','Built on Quality. Driven by Excellence.'))) ?>
        </h1>
        <p style="color:rgba(255,255,255,.75);max-width:560px;font-size:1.05rem;line-height:1.8">
            <?= e($ab('about_hero_sub','Dakari was founded with a simple belief: every person deserves access to premium quality products without compromise.')) ?>
        </p>
    </div>
</div>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb__list">
            <li><a href="<?= BASE_URL ?>/">Home</a></li>
            <li>About Us</li>
        </ul>
    </div>
</div>

<!-- ── Our Story ── -->
<section class="section">
    <div class="container">
        <div class="about-story">
            <div class="about-story__text">
                <p class="section-eyebrow">Who We Are</p>
                <h2 class="section-title">The <span class="gold-accent">Dakari</span> Story</h2>
                <?php foreach (['about_story_p1','about_story_p2','about_story_p3'] as $pk):
                    $def = [
                        'about_story_p1' => 'Founded in 2020 in Nairobi, Kenya, Dakari began as a passion project between a group of entrepreneurs who believed the East African market deserved access to curated, premium-quality products.',
                        'about_story_p2' => 'What started as a small boutique has grown into one of the region\'s most trusted e-commerce brands, serving over 12,000 satisfied customers across Kenya and beyond. Our name — Dakari — is Swahili-inspired, evoking pride, joy, and excellence.',
                        'about_story_p3' => 'Today, our team of passionate professionals works tirelessly to source, curate, and deliver products that meet the highest standards of quality, authenticity, and value.',
                    ][$pk];
                    $txt = $ab($pk, $def);
                    if ($txt): ?>
                <p style="margin-top:16px"><?= e($txt) ?></p>
                <?php endif; endforeach; ?>
            </div>
            <div class="about-story__visual">
                <div class="about-story__card">
                    <?php for ($i = 1; $i <= 4; $i++):
                        $dn = ['2020','12K+','500+','50+'];
                        $dl = ['Year Founded','Customers','Products','Partners'];
                    ?>
                    <?php if ($i > 1): ?><div class="about-story__card-divider"></div><?php endif; ?>
                    <div class="about-story__card-stat">
                        <span class="about-stat-num"><?= e($ab("about_stat{$i}_num", $dn[$i-1])) ?></span>
                        <span class="about-stat-label"><?= e($ab("about_stat{$i}_label", $dl[$i-1])) ?></span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Mission & Vision ── -->
<section class="section section--alt">
    <div class="container">
        <div class="section__header section__header--center">
            <p class="section-eyebrow">Purpose</p>
            <h2 class="section-title">Mission &amp; <span class="gold-accent">Vision</span></h2>
        </div>
        <div class="mv-grid">
            <div class="mv-card mv-card--mission">
                <div class="mv-card__icon">
                    <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <h3>Our Mission</h3>
                <p><?= e($ab('about_mission','To democratise access to premium products in East Africa by delivering an exceptional shopping experience built on trust, authenticity, and world-class customer service.')) ?></p>
            </div>
            <div class="mv-card mv-card--vision">
                <div class="mv-card__icon">
                    <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </div>
                <h3>Our Vision</h3>
                <p><?= e($ab('about_vision','To be the most trusted and beloved premium retail brand in Africa — a name synonymous with quality, integrity, and the celebration of African excellence on the world stage.')) ?></p>
            </div>
        </div>
    </div>
</section>

<!-- ── Core Values ── -->
<?php if (!empty($values)): ?>
<section class="section">
    <div class="container">
        <div class="section__header section__header--center">
            <p class="section-eyebrow">What We Stand For</p>
            <h2 class="section-title">Our Core <span class="gold-accent">Values</span></h2>
        </div>
        <div class="values-grid">
            <?php foreach ($values as $v):
                $path = $about_icons[$v['icon'] ?? 'star'] ?? reset($about_icons); ?>
            <div class="value-card">
                <div class="value-card__icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="<?= e($path) ?>"/></svg>
                </div>
                <h4 class="value-card__title"><?= e($v['title'] ?? '') ?></h4>
                <p class="value-card__text"><?= e($v['text'] ?? '') ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── Milestones Timeline ── -->
<?php if (!empty($milestones)): ?>
<section class="section section--alt">
    <div class="container">
        <div class="section__header section__header--center">
            <p class="section-eyebrow">Our Journey</p>
            <h2 class="section-title">Key <span class="gold-accent">Milestones</span></h2>
        </div>
        <div class="timeline">
            <div class="timeline__line"></div>
            <?php foreach ($milestones as $i => $m): ?>
            <div class="timeline-item <?= $i % 2 === 0 ? 'timeline-item--left' : 'timeline-item--right' ?>">
                <div class="timeline-item__dot"></div>
                <div class="timeline-item__card">
                    <span class="timeline-item__date"><?= e($m['year'] ?? '') ?> &middot; <?= e($m['quarter'] ?? '') ?></span>
                    <h4 class="timeline-item__title"><?= e($m['title'] ?? '') ?></h4>
                    <p class="timeline-item__text"><?= e($m['text'] ?? '') ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── CTA ── -->
<section class="about-cta">
    <div class="container">
        <div class="about-cta__inner">
            <div>
                <h2 style="color:var(--white);font-size:clamp(1.6rem,3vw,2.2rem)">
                    <?= e($ab('about_cta_headline','Ready to Experience Dakari?')) ?>
                </h2>
                <p style="color:rgba(255,255,255,.75);margin-top:10px">
                    <?= e($ab('about_cta_sub','Join thousands of satisfied customers who trust us for premium quality.')) ?>
                </p>
            </div>
            <div style="display:flex;gap:14px;flex-wrap:wrap">
                <a href="shop.php" class="btn btn-gold btn-lg">Shop Now</a>
                <a href="contact.php" class="btn btn-outline-white btn-lg">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
