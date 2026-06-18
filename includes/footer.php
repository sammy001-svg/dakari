<?php
$site_name  = setting('site_name', 'Dakari');
$categories = get_categories();
?>
<footer class="footer">
    <div class="footer__top">
        <div class="container footer__grid">

            <!-- Brand column -->
            <div class="footer__col footer__col--brand">
                <div class="footer__logo">
                    <div class="footer__logo-img-wrap">
                        <div class="footer__logo-crop">
                            <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="<?= e($site_name) ?>" class="footer__logo-img">
                        </div>
                    </div>
                </div>
                <p class="footer__about-text"><?= e(setting('footer_about', 'Premium quality products for the modern lifestyle. Curated with care, delivered with excellence.')) ?></p>
                <div class="footer__social">
                    <a href="<?= e(setting('social_instagram','#')) ?>" aria-label="Instagram" target="_blank" rel="noopener">
                        <svg width="17" height="17" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    <a href="<?= e(setting('social_facebook','#')) ?>" aria-label="Facebook" target="_blank" rel="noopener">
                        <svg width="17" height="17" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="<?= e(setting('social_twitter','#')) ?>" aria-label="Twitter / X" target="_blank" rel="noopener">
                        <svg width="17" height="17" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.259 5.631zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="<?= e(setting('social_tiktok','#')) ?>" aria-label="TikTok" target="_blank" rel="noopener">
                        <svg width="17" height="17" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.18 8.18 0 0 0 4.78 1.52V6.76a4.85 4.85 0 0 1-1.01-.07z"/></svg>
                    </a>
                </div>
            </div>

            <!-- Shop column -->
            <div class="footer__col">
                <h4 class="footer__heading">Shop</h4>
                <ul class="footer__links">
                    <li><a href="<?= BASE_URL ?>/shop.php">All Products</a></li>
                    <li><a href="<?= BASE_URL ?>/shop.php?filter=new">New Arrivals</a></li>
                    <li><a href="<?= BASE_URL ?>/shop.php?filter=featured">Featured</a></li>
                    <li><a href="<?= BASE_URL ?>/shop.php?filter=sale">On Sale</a></li>
                    <?php foreach (array_slice($categories, 0, 4) as $cat): ?>
                    <li><a href="<?= BASE_URL ?>/shop.php?category=<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Company column -->
            <div class="footer__col">
                <h4 class="footer__heading">Company</h4>
                <ul class="footer__links">
                    <li><a href="<?= BASE_URL ?>/about.php">About Us</a></li>
                    <li><a href="<?= BASE_URL ?>/influencers.php">Brand Ambassadors</a></li>
                    <li><a href="<?= BASE_URL ?>/contact.php">Contact Us</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Press &amp; Media</a></li>
                    <li><a href="#">Sustainability</a></li>
                </ul>
            </div>

            <!-- Customer care column -->
            <div class="footer__col">
                <h4 class="footer__heading">Customer Care</h4>
                <ul class="footer__links">
                    <li><a href="<?= BASE_URL ?>/client/dashboard.php">My Account</a></li>
                    <li><a href="<?= BASE_URL ?>/client/orders.php">Track My Order</a></li>
                    <li><a href="<?= BASE_URL ?>/cart.php">Shopping Cart</a></li>
                    <li><a href="#">Returns &amp; Exchanges</a></li>
                    <li><a href="#">Shipping Policy</a></li>
                    <li><a href="#">FAQ</a></li>
                </ul>
            </div>

            <!-- Contact column -->
            <div class="footer__col">
                <h4 class="footer__heading">Get in Touch</h4>
                <ul class="footer__contact-list">
                    <li>
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span><?= e(setting('site_address', 'Nairobi, Kenya')) ?></span>
                    </li>
                    <li>
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.39 2 2 0 0 1 3.6 1.21h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.77a16 16 0 0 0 6.29 6.29l.97-.97a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7a2 2 0 0 1 1.72 2.03z"/></svg>
                        <a href="tel:<?= e(setting('site_phone')) ?>"><?= e(setting('site_phone', '+254 700 000 000')) ?></a>
                    </li>
                    <li>
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <a href="mailto:<?= e(setting('site_email')) ?>"><?= e(setting('site_email', 'hello@dakari.com')) ?></a>
                    </li>
                    <li>
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span>Mon – Sat: 8am – 6pm EAT</span>
                    </li>
                </ul>

                <!-- Payment badges -->
                <div class="footer__payments">
                    <p class="footer__payments-label">We Accept</p>
                    <div class="footer__payment-icons">
                        <span class="payment-badge">VISA</span>
                        <span class="payment-badge">Mastercard</span>
                        <span class="payment-badge">M-Pesa</span>
                        <span class="payment-badge">PayPal</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="footer__bottom">
        <div class="container footer__bottom__inner">
            <p>&copy; <?= date('Y') ?> <?= e($site_name) ?>. All rights reserved.</p>
            <div class="footer__legal">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Cookie Policy</a>
            </div>
        </div>
    </div>
</footer>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<?php if (!empty($extra_js)) echo $extra_js; ?>
</body>
</html>
