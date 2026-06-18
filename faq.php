<?php
require_once __DIR__ . '/includes/init.php';
$page_title  = 'FAQ – Frequently Asked Questions';
$active_page = 'faq';

$faqs = [
    'Orders & Shipping' => [
        ['How long does delivery take?',
         'Standard delivery within Nairobi takes 1–3 business days. For upcountry Kenya and regional destinations (Uganda, Tanzania, Rwanda), allow 3–7 business days. We dispatch orders placed before 12 pm EAT the same day.'],
        ['How much does shipping cost?',
         'Shipping is free on orders over KES 3,000. For orders below that threshold, a flat shipping fee of KES 250 applies within Nairobi, and KES 500 for upcountry/regional.'],
        ['Can I track my order?',
         'Yes. Once your order is dispatched you will receive an SMS and email with your tracking number. You can also log in to your account and view live order status from the "My Orders" section.'],
        ['What happens if my order is delayed?',
         'If your order has not arrived within the expected window, please contact us via the Contact page or call +254 700 000 000. Our support team will investigate and provide an update within 4 hours.'],
        ['Do you offer same-day delivery?',
         'Same-day delivery is available within Nairobi CBD and select suburbs for orders placed before 10 am EAT. Select "Same-Day Delivery" at checkout to see if your area qualifies.'],
    ],
    'Returns & Refunds' => [
        ['What is your return policy?',
         'We offer a 14-day hassle-free return policy. If you are not 100% satisfied with your purchase, return the item in its original condition and packaging and we will issue a full refund or exchange.'],
        ['How do I initiate a return?',
         'Log in to your account, go to "My Orders", select the order and click "Request Return". Fill in the reason and submit. Our team will confirm within 24 hours with return instructions.'],
        ['When will I receive my refund?',
         'Once we receive and inspect the returned item, refunds are processed within 3–5 business days. M-Pesa refunds reflect within 24 hours; card refunds may take up to 7 business days depending on your bank.'],
        ['What items cannot be returned?',
         'For hygiene reasons, personal care products, undergarments, and opened consumables cannot be returned unless they arrive damaged or defective. All other items are eligible for return.'],
    ],
    'Products & Stock' => [
        ['Are all your products authentic?',
         'Absolutely. Dakari only stocks genuine products sourced directly from authorised distributors and brand partners. We do not stock grey-market or counterfeit goods — authenticity is non-negotiable.'],
        ['What does "Out of Stock" mean?',
         '"Out of Stock" means the item is temporarily unavailable. You can click "Notify Me" on the product page and we will email you the moment stock is replenished.'],
        ['Can I place a bulk or wholesale order?',
         'Yes. For orders of 10+ units of a single product, or wholesale enquiries, please fill out the Contact form and select "Partnership / Wholesale" as the category. Our team will send a custom quote within 24 hours.'],
        ['Do your products come with a warranty?',
         'Warranty terms vary by product and brand. Warranty information is listed on each product page. For warranty claims, contact the brand directly or reach out to our support team for assistance.'],
    ],
    'Account & Payments' => [
        ['How do I create an account?',
         'Click "Sign In" in the top navigation and select "Create Account". You will need a valid email address and a password. Account creation is free and takes under 60 seconds.'],
        ['What payment methods do you accept?',
         'We accept M-Pesa (Paybill and STK push), Visa, Mastercard, and PayPal. All transactions are secured with 256-bit SSL encryption. We do not store card details.'],
        ['Is my payment information secure?',
         'Yes. All payments are processed through PCI-DSS compliant payment gateways. We never store your full card number or CVV. M-Pesa transactions use Safaricom\'s secure STK push protocol.'],
        ['Can I checkout as a guest?',
         'Yes. You can complete a purchase without creating an account. However, creating an account allows you to track orders, manage returns, save your address, and earn loyalty points.'],
        ['How do I use a discount code?',
         'At checkout, enter your discount code in the "Promo Code" field and click "Apply". Valid codes will deduct the discount amount before payment. Codes are case-insensitive.'],
    ],
    'Support' => [
        ['How do I contact customer support?',
         'You can reach us via the Contact page form, by email at hello@dakari.com, or by phone at +254 700 000 000. Our support hours are Monday–Friday 8 am–6 pm EAT and Saturday 9 am–4 pm EAT.'],
        ['How long does it take to get a response?',
         'We aim to respond to all enquiries within 4 business hours during working hours. Messages received outside business hours are addressed first thing the next morning.'],
        ['I forgot my password. What do I do?',
         'On the login page, click "Forgot Password?", enter your registered email address, and we will send a secure reset link. The link is valid for 60 minutes.'],
    ],
];

include __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<div class="page-hero page-hero--tall">
    <div class="container">
        <p class="section-eyebrow" style="color:var(--gold)">Help Centre</p>
        <h1>Frequently Asked <span class="gold-accent">Questions</span></h1>
        <p>Find quick answers to the most common questions about shopping with Dakari.</p>
    </div>
</div>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb__list">
            <li><a href="<?= BASE_URL ?>/">Home</a></li>
            <li><a href="<?= BASE_URL ?>/contact.php">Support</a></li>
            <li>FAQ</li>
        </ul>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="faq-layout">

            <!-- Sidebar nav -->
            <aside class="faq-sidebar">
                <nav class="faq-nav">
                    <?php foreach (array_keys($faqs) as $cat): ?>
                    <a href="#<?= e(strtolower(str_replace([' ','&','/',],['_','and',''],trim($cat)))) ?>" class="faq-nav__link"><?= e($cat) ?></a>
                    <?php endforeach; ?>
                    <a href="<?= BASE_URL ?>/contact.php" class="faq-nav__cta">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        Still have questions?
                    </a>
                </nav>
            </aside>

            <!-- FAQ content -->
            <div class="faq-content">
                <?php foreach ($faqs as $cat => $items):
                    $anchor = strtolower(str_replace([' ','&','/',],['_','and',''],trim($cat)));
                ?>
                <div class="faq-group" id="<?= e($anchor) ?>">
                    <h2 class="faq-group__title"><?= e($cat) ?></h2>
                    <div class="faq-list">
                        <?php foreach ($items as $idx => $item): ?>
                        <div class="faq-item" id="faq-<?= $anchor ?>-<?= $idx ?>">
                            <button class="faq-question" aria-expanded="false" aria-controls="faq-answer-<?= $anchor ?>-<?= $idx ?>">
                                <span><?= e($item[0]) ?></span>
                                <svg class="faq-chevron" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <div class="faq-answer" id="faq-answer-<?= $anchor ?>-<?= $idx ?>" hidden>
                                <p><?= e($item[1]) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</section>

<!-- CTA -->
<section class="section section--alt">
    <div class="container" style="text-align:center;max-width:600px;margin-left:auto;margin-right:auto">
        <p class="section-eyebrow">Still Need Help?</p>
        <h2 class="section-title">We're <span class="gold-accent">Here</span> for You</h2>
        <p style="color:var(--text-muted);margin-bottom:32px">Couldn't find the answer you were looking for? Our support team is happy to help.</p>
        <a href="<?= BASE_URL ?>/contact.php" class="btn btn-green btn-lg">Contact Support</a>
    </div>
</section>

<script>
document.querySelectorAll('.faq-question').forEach(btn => {
    btn.addEventListener('click', () => {
        const item   = btn.closest('.faq-item');
        const answer = btn.nextElementSibling;
        const open   = btn.getAttribute('aria-expanded') === 'true';

        /* close all in same group */
        btn.closest('.faq-list').querySelectorAll('.faq-item').forEach(el => {
            el.querySelector('.faq-question').setAttribute('aria-expanded','false');
            el.querySelector('.faq-answer').hidden = true;
            el.classList.remove('faq-item--open');
        });

        if (!open) {
            btn.setAttribute('aria-expanded','true');
            answer.hidden = false;
            item.classList.add('faq-item--open');
        }
    });
});

/* Smooth scroll for sidebar links */
document.querySelectorAll('.faq-nav__link').forEach(a => {
    a.addEventListener('click', e => {
        e.preventDefault();
        const target = document.getElementById(a.getAttribute('href').slice(1));
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
