<?php
require_once __DIR__ . '/includes/init.php';
$page_title  = 'Contact Us';
$active_page = 'contact';

$errors  = [];
$success = false;
$name = $email = $phone = $category = $subject = $message_val = '';

/* ── Handle form submission ─────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please refresh and try again.';
    } else {
        $name        = trim($_POST['name']     ?? '');
        $email       = trim($_POST['email']    ?? '');
        $phone       = trim($_POST['phone']    ?? '');
        $category    = trim($_POST['category'] ?? '');
        $subject     = trim($_POST['subject']  ?? '');
        $message_val = trim($_POST['message']  ?? '');

        if (mb_strlen($name) < 2)                          $errors[] = 'Please enter your full name.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))    $errors[] = 'Please enter a valid email address.';
        if (mb_strlen($message_val) < 10)                  $errors[] = 'Message must be at least 10 characters.';

        $last_sent = $_SESSION['contact_last_sent'] ?? 0;
        if (time() - $last_sent < 60)                      $errors[] = 'Please wait a moment before sending another message.';

        if (empty($errors)) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            query(
                'INSERT INTO contact_messages (name,email,phone,category,subject,message,ip_address)
                 VALUES (?,?,?,?,?,?,?)',
                'sssssss',
                $name, $email, $phone, $category, $subject, $message_val, $ip
            );
            $_SESSION['contact_last_sent'] = time();
            $success = true;
        }
    }
}

$csrf = generate_csrf();
include __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<div class="page-hero page-hero--tall">
    <div class="container">
        <p class="section-eyebrow" style="color:var(--gold)">We're Here to Help</p>
        <h1>Contact <span class="gold-accent">Dakari</span></h1>
        <p>Have a question, need support, or just want to say hello? Our team is ready to assist you.</p>
    </div>
</div>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb__list">
            <li><a href="<?= BASE_URL ?>/">Home</a></li>
            <li>Contact Us</li>
        </ul>
    </div>
</div>

<!-- ── Contact Layout ─────────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="contact-layout">

            <!-- Left: Form -->
            <div class="contact-form-wrap">
                <h2 class="contact-form-title">Send Us a Message</h2>
                <p class="contact-form-sub">Fill in the form below and we'll get back to you within 24 hours.</p>

                <?php if ($success): ?>
                <div class="contact-success">
                    <svg width="44" height="44" fill="none" stroke="var(--green)" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <h3>Message Sent!</h3>
                    <p>Thank you, <?= e($name) ?>. We've received your message and will respond to <strong><?= e($email) ?></strong> within 24 hours.</p>
                    <a href="<?= BASE_URL ?>/contact.php" class="btn btn-green btn-sm" style="margin-top:16px">Send Another</a>
                </div>
                <?php else: ?>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-error" style="margin-bottom:20px;border-radius:var(--radius)">
                    <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="" class="contact-form" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="contact_submit" value="1">

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Full Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control <?= in_array('Please enter your full name.', $errors) ? 'error' : '' ?>"
                                   value="<?= e($name) ?>" placeholder="Jane Doe" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address <span class="required">*</span></label>
                            <input type="email" name="email" class="form-control <?= in_array('Please enter a valid email address.', $errors) ? 'error' : '' ?>"
                                   value="<?= e($email) ?>" placeholder="jane@example.com" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control"
                                   value="<?= e($phone) ?>" placeholder="+254 700 000 000">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-control">
                                <option value="">— Select a topic —</option>
                                <?php foreach (['Order Support','Product Inquiry','Returns & Refunds','Shipping','Account Help','Partnership / Wholesale','Other'] as $cat): ?>
                                <option value="<?= e($cat) ?>" <?= ($category === $cat) ? 'selected' : '' ?>><?= e($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group full">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control"
                                   value="<?= e($subject) ?>" placeholder="Brief description of your enquiry">
                        </div>
                        <div class="form-group full">
                            <label class="form-label">Message <span class="required">*</span></label>
                            <textarea name="message" rows="6" class="form-control <?= in_array('Message must be at least 10 characters.', $errors) ? 'error' : '' ?>"
                                      placeholder="Tell us how we can help…" required><?= e($message_val) ?></textarea>
                        </div>
                    </div>

                    <div style="margin-top:24px">
                        <button type="submit" class="btn btn-green btn-lg">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            Send Message
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>

            <!-- Right: Info -->
            <div class="contact-info">

                <div class="contact-info-card">
                    <h3 class="contact-info-card__title">Get in Touch</h3>
                    <ul class="contact-detail-list">
                        <li>
                            <div class="contact-detail-icon">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                            <div>
                                <strong>Visit Us</strong>
                                <?php if (setting('site_address')): ?>
                                <span><?= e(setting('site_address')) ?></span>
                                <?php endif; ?>
                                <?php $loc_parts = array_filter([setting('location_city'), setting('location_country')]); ?>
                                <?php if ($loc_parts): ?>
                                <span style="color:var(--text-muted);font-size:.85rem"><?= e(implode(', ', $loc_parts)) ?></span>
                                <?php elseif (!setting('site_address')): ?>
                                <span>Nairobi, Kenya</span>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li>
                            <div class="contact-detail-icon">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.39 2 2 0 0 1 3.6 1.21h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.77a16 16 0 0 0 6.29 6.29l.97-.97a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7a2 2 0 0 1 1.72 2.03z"/></svg>
                            </div>
                            <div>
                                <strong>Call Us</strong>
                                <a href="tel:<?= e(setting('site_phone')) ?>"><?= e(setting('site_phone', '+254 700 000 000')) ?></a>
                            </div>
                        </li>
                        <li>
                            <div class="contact-detail-icon">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </div>
                            <div>
                                <strong>Email Us</strong>
                                <a href="mailto:<?= e(setting('site_email')) ?>"><?= e(setting('site_email', 'hello@dakari.com')) ?></a>
                            </div>
                        </li>
                        <li>
                            <div class="contact-detail-icon">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                            <div>
                                <strong>Business Hours</strong>
                                <?php if (setting('hours_weekday')): ?><span><?= e(setting('hours_weekday')) ?></span><?php endif; ?>
                                <?php if (setting('hours_saturday')): ?><span><?= e(setting('hours_saturday')) ?></span><?php endif; ?>
                                <?php if (setting('hours_sunday')): ?><span><?= e(setting('hours_sunday')) ?></span><?php endif; ?>
                                <?php if (!setting('hours_weekday') && !setting('hours_saturday') && !setting('hours_sunday')): ?>
                                <span>Mon – Fri: 8am – 6pm EAT</span>
                                <span>Sat: 9am – 4pm EAT</span>
                                <span>Sun: Closed</span>
                                <?php endif; ?>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="contact-info-card" style="margin-top:20px">
                    <h3 class="contact-info-card__title">Follow Us</h3>
                    <div class="contact-social">
                        <a href="<?= e(setting('social_instagram','#')) ?>" target="_blank" rel="noopener" class="contact-social-link">
                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            Instagram
                        </a>
                        <a href="<?= e(setting('social_facebook','#')) ?>" target="_blank" rel="noopener" class="contact-social-link">
                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            Facebook
                        </a>
                        <a href="<?= e(setting('social_tiktok','#')) ?>" target="_blank" rel="noopener" class="contact-social-link">
                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.18 8.18 0 0 0 4.78 1.52V6.76a4.85 4.85 0 0 1-1.01-.07z"/></svg>
                            TikTok
                        </a>
                    </div>
                </div>

                <div class="contact-response-badge">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <div>
                        <strong>Average Response Time</strong>
                        <span>Within 4 business hours</span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- ── Map ──────────────────────────────────────────────────────── -->
<?php $map_url = setting('location_map_url'); ?>
<div class="contact-map">
    <?php if ($map_url): ?>
    <iframe
        src="<?= e($map_url) ?>"
        width="100%" height="100%" style="border:0;display:block"
        allowfullscreen="" loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
        title="<?= e(setting('site_name','Dakari')) ?> location map">
    </iframe>
    <?php else: ?>
    <div class="contact-map__inner">
        <svg width="48" height="48" fill="none" stroke="var(--gold)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        <?php $city = setting('location_city','Nairobi'); $country = setting('location_country','Kenya'); ?>
        <p><?= e($city . ', ' . $country) ?></p>
        <span>Add a Google Maps URL in Admin → Settings → Location to show the map here.</span>
    </div>
    <?php endif; ?>
</div>

<!-- ── FAQ Teaser ──────────────────────────────────────────────── -->
<section class="section section--alt">
    <div class="container" style="text-align:center;max-width:680px;margin-left:auto;margin-right:auto">
        <p class="section-eyebrow">Quick Answers</p>
        <h2 class="section-title">Frequently Asked <span class="gold-accent">Questions</span></h2>
        <p style="color:var(--text-muted);margin-bottom:32px">Before reaching out, check our FAQ — you might find the answer instantly.</p>
        <a href="<?= BASE_URL ?>/faq.php" class="btn btn-green btn-lg">View All FAQs</a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
