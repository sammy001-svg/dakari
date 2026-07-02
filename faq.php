<?php
require_once __DIR__ . '/includes/init.php';
$page_title  = 'FAQ – Frequently Asked Questions';
$active_page = 'faq';

// Load FAQs from database, grouped by category
$rows = fetchAll('SELECT * FROM faqs WHERE is_active=1 ORDER BY category, sort_order, id');
$faqs = [];
foreach ($rows as $row) {
    $faqs[$row['category']][] = [$row['question'], $row['answer']];
}

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
