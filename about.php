<?php
require_once __DIR__ . '/includes/init.php';
$page_title  = 'About Us';
$active_page = 'about';
include __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<div class="page-hero page-hero--tall" style="background:var(--green)">
    <div class="container">
        <p class="section-eyebrow" style="color:var(--gold)">Our Story</p>
        <h1 style="color:var(--white);font-size:clamp(2rem,5vw,3.2rem);margin-bottom:16px">Built on Quality.<br>Driven by <span class="gold-accent">Excellence.</span></h1>
        <p style="color:rgba(255,255,255,.75);max-width:560px;font-size:1.05rem;line-height:1.8">
            Dakari was founded with a simple belief: every person deserves access to premium quality products without compromise.
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

<!-- ── Our Story ─────────────────────────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="about-story">
            <div class="about-story__text">
                <p class="section-eyebrow">Who We Are</p>
                <h2 class="section-title">The <span class="gold-accent">Dakari</span> Story</h2>
                <p>Founded in 2020 in Nairobi, Kenya, Dakari began as a passion project between a group of entrepreneurs who believed the East African market deserved access to curated, premium-quality products.</p>
                <p style="margin-top:16px">What started as a small boutique has grown into one of the region's most trusted e-commerce brands, serving over 12,000 satisfied customers across Kenya and beyond. Our name — Dakari — is Swahili-inspired, evoking pride, joy, and excellence.</p>
                <p style="margin-top:16px">Today, our team of passionate professionals works tirelessly to source, curate, and deliver products that meet the highest standards of quality, authenticity, and value.</p>
            </div>
            <div class="about-story__visual">
                <div class="about-story__card">
                    <div class="about-story__card-stat"><span class="about-stat-num">2020</span><span class="about-stat-label">Year Founded</span></div>
                    <div class="about-story__card-divider"></div>
                    <div class="about-story__card-stat"><span class="about-stat-num">12K+</span><span class="about-stat-label">Customers</span></div>
                    <div class="about-story__card-divider"></div>
                    <div class="about-story__card-stat"><span class="about-stat-num">500+</span><span class="about-stat-label">Products</span></div>
                    <div class="about-story__card-divider"></div>
                    <div class="about-story__card-stat"><span class="about-stat-num">50+</span><span class="about-stat-label">Partners</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Mission & Vision ──────────────────────────────────────────────────── -->
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
                <p>To democratise access to premium products in East Africa by delivering an exceptional shopping experience built on trust, authenticity, and world-class customer service.</p>
            </div>
            <div class="mv-card mv-card--vision">
                <div class="mv-card__icon">
                    <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </div>
                <h3>Our Vision</h3>
                <p>To be the most trusted and beloved premium retail brand in Africa — a name synonymous with quality, integrity, and the celebration of African excellence on the world stage.</p>
            </div>
        </div>
    </div>
</section>

<!-- ── Core Values ───────────────────────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="section__header section__header--center">
            <p class="section-eyebrow">What We Stand For</p>
            <h2 class="section-title">Our Core <span class="gold-accent">Values</span></h2>
        </div>
        <div class="values-grid">
            <?php
            $values = [
                ['Integrity',      'We operate with complete transparency and honesty in every interaction — with customers, partners, and each other.',
                 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                ['Excellence',     'We never settle for "good enough". Every product, every interaction must be exceptional and exceed expectations.',
                 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z'],
                ['Customer First', 'Our customers are the centre of every decision we make. Their satisfaction is our primary measure of success.',
                 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75'],
                ['Authenticity',   'We stock only genuine, verified products from trusted suppliers. No counterfeits, no compromises — ever.',
                 'M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
                ['Innovation',     'We continuously improve our platform, processes, and product selection to deliver a better experience.',
                 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 1 1 7.072 0l-.548.547A3.374 3.374 0 0 0 14 18.469V19a2 2 0 1 1-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'],
                ['Sustainability', 'We are committed to responsible sourcing, minimal environmental footprint, and giving back to our community.',
                 'M3.055 11H5a2 2 0 0 1 2 2v1a2 2 0 0 0 2 2 2 2 0 0 1 2 2v2.945M8 3.935V5.5A2.5 2.5 0 0 0 10.5 8h.5a2 2 0 0 1 2 2 2 2 0 0 0 4 0 2 2 0 0 1 2-2h1.064M15 20.488V18a2 2 0 0 1 2-2h3.064M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
            ];
            foreach ($values as $v): ?>
            <div class="value-card">
                <div class="value-card__icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="<?= $v[2] ?>"/></svg>
                </div>
                <h4 class="value-card__title"><?= $v[0] ?></h4>
                <p class="value-card__text"><?= $v[1] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── Milestones Timeline ────────────────────────────────────────────────── -->
<section class="section section--alt">
    <div class="container">
        <div class="section__header section__header--center">
            <p class="section-eyebrow">Our Journey</p>
            <h2 class="section-title">Key <span class="gold-accent">Milestones</span></h2>
        </div>
        <div class="timeline">
            <div class="timeline__line"></div>
            <?php
            $milestones = [
                ['2020', 'Q1', 'Company Founded',         'Dakari was incorporated in Nairobi with a vision to bring premium products to East Africa.'],
                ['2020', 'Q4', 'First 500 Customers',     'We hit our first major milestone — 500 satisfied customers and a 4.8-star average rating.'],
                ['2021', 'Q2', 'Influencer Programme',    'Launched our brand ambassador programme, partnering with top Kenyan content creators.'],
                ['2022', 'Q1', 'Online Platform Launch',  'Rolled out our full e-commerce platform with cart, wishlist, and client portal.'],
                ['2022', 'Q3', '5,000+ Customers',        'Surpassed 5,000 customers and expanded our product catalogue to 300+ SKUs.'],
                ['2023', 'Q2', 'Regional Expansion',      'Extended delivery coverage to Uganda, Tanzania, and Rwanda.'],
                ['2024', 'Q1', '10,000+ Orders',          'Celebrated over 10,000 fulfilled orders with a 99% customer satisfaction rate.'],
                ['2025', 'Q3', 'Platform Redesign',       'Launched our new corporate platform with advanced inventory, coupons, and reviews.'],
            ];
            foreach ($milestones as $i => $m): ?>
            <div class="timeline-item <?= $i % 2 === 0 ? 'timeline-item--left' : 'timeline-item--right' ?>">
                <div class="timeline-item__dot"></div>
                <div class="timeline-item__card">
                    <span class="timeline-item__date"><?= $m[0] ?> &middot; <?= $m[1] ?></span>
                    <h4 class="timeline-item__title"><?= $m[2] ?></h4>
                    <p class="timeline-item__text"><?= $m[3] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── CTA ───────────────────────────────────────────────────────────────── -->
<section class="about-cta">
    <div class="container">
        <div class="about-cta__inner">
            <div>
                <h2 style="color:var(--white);font-size:clamp(1.6rem,3vw,2.2rem)">Ready to Experience <span class="gold-accent">Dakari</span>?</h2>
                <p style="color:rgba(255,255,255,.75);margin-top:10px">Join thousands of satisfied customers who trust us for premium quality.</p>
            </div>
            <div style="display:flex;gap:14px;flex-wrap:wrap">
                <a href="shop.php" class="btn btn-gold btn-lg">Shop Now</a>
                <a href="contact.php" class="btn btn-outline-white btn-lg">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
