/* Dakari — Main JavaScript */
(function () {
    'use strict';

    // ── Hamburger / mobile nav ──────────────────────────────────
    const hamburger = document.getElementById('hamburger');
    const nav       = document.getElementById('nav');
    if (hamburger && nav) {
        hamburger.addEventListener('click', () => {
            nav.classList.toggle('open');
            hamburger.classList.toggle('open');
        });
    }

    // ── Search bar toggle ───────────────────────────────────────
    const searchToggle = document.getElementById('searchToggle');
    const searchBar    = document.getElementById('searchBar');
    if (searchToggle && searchBar) {
        searchToggle.addEventListener('click', () => {
            searchBar.classList.toggle('open');
            if (searchBar.classList.contains('open')) {
                searchBar.querySelector('input')?.focus();
            }
        });
    }

    // ── Hero Carousel ───────────────────────────────────────────
    const carousel = document.querySelector('.carousel');
    if (carousel) {
        const track  = carousel.querySelector('.carousel__track');
        const slides = carousel.querySelectorAll('.carousel__slide');
        const dots   = carousel.querySelectorAll('.carousel__dot');
        const prevBtn = carousel.querySelector('.carousel__btn--prev');
        const nextBtn = carousel.querySelector('.carousel__btn--next');
        let current  = 0;
        let timer;

        function goTo(n) {
            current = (n + slides.length) % slides.length;
            track.style.transform = `translateX(-${current * 100}%)`;
            dots.forEach((d, i) => d.classList.toggle('active', i === current));
        }
        function next() { goTo(current + 1); }
        function resetTimer() { clearInterval(timer); timer = setInterval(next, 5500); }

        if (slides.length > 1) {
            goTo(0);
            resetTimer();
            nextBtn?.addEventListener('click', () => { next(); resetTimer(); });
            prevBtn?.addEventListener('click', () => { goTo(current - 1); resetTimer(); });
            dots.forEach((d, i) => d.addEventListener('click', () => { goTo(i); resetTimer(); }));
        }

        // Touch/swipe
        let startX = 0;
        carousel.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
        carousel.addEventListener('touchend', e => {
            const diff = startX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 50) { diff > 0 ? next() : goTo(current - 1); resetTimer(); }
        });
    }

    // ── Quantity selector ───────────────────────────────────────
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('qty-btn')) {
            const input = e.target.closest('.qty-selector')?.querySelector('.qty-input');
            if (!input) return;
            let val = parseInt(input.value, 10) || 1;
            if (e.target.dataset.action === 'inc') val = Math.min(val + 1, parseInt(input.max, 10) || 999);
            if (e.target.dataset.action === 'dec') val = Math.max(val - 1, 1);
            input.value = val;
        }
    });

    // ── Product gallery thumbnails ──────────────────────────────
    const mainImg  = document.getElementById('mainProductImg');
    const thumbs   = document.querySelectorAll('.product-gallery__thumb');
    if (mainImg && thumbs.length) {
        thumbs.forEach(thumb => {
            thumb.addEventListener('click', () => {
                mainImg.src = thumb.dataset.src;
                thumbs.forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
            });
        });
    }

    // ── Tabs ────────────────────────────────────────────────────
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.tab;
            btn.closest('.tabs-wrapper')?.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.closest('.tabs-wrapper')?.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(target)?.classList.add('active');
        });
    });

    // ── Sticky header shrink ────────────────────────────────────
    const header = document.getElementById('header');
    if (header) {
        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 60);
        }, { passive: true });
    }

    // ── Wishlist AJAX ───────────────────────────────────────────
    document.querySelectorAll('.btn-wishlist').forEach(btn => {
        btn.addEventListener('click', async function () {
            const productId = this.dataset.id;
            try {
                const res  = await fetch('/api/wishlist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `product_id=${productId}&csrf_token=${encodeURIComponent(window.__csrf || '')}`
                });
                const data = await res.json();
                if (data.success) {
                    this.classList.toggle('active', data.action === 'added');
                    showToast(data.action === 'added' ? 'Added to wishlist' : 'Removed from wishlist');
                }
            } catch (_) {}
        });
    });

    // ── Cart AJAX add ───────────────────────────────────────────
    document.querySelectorAll('.btn-add-cart').forEach(btn => {
        btn.addEventListener('click', async function () {
            const productId = this.dataset.id;
            const qty = document.getElementById('qtyInput')?.value || 1;
            const original = this.innerHTML;
            this.innerHTML = 'Adding…';
            try {
                const res  = await fetch('/api/cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=add&product_id=${productId}&quantity=${qty}&csrf_token=${encodeURIComponent(window.__csrf || '')}`
                });
                const data = await res.json();
                if (data.success) {
                    const badge = document.querySelector('.cart-badge');
                    if (badge) badge.textContent = data.cart_count;
                    else if (data.cart_count > 0) {
                        const cartIcon = document.querySelector('.header__cart');
                        if (cartIcon) {
                            const b = document.createElement('span');
                            b.className = 'cart-badge'; b.textContent = data.cart_count;
                            cartIcon.appendChild(b);
                        }
                    }
                    showToast('Added to cart!');
                }
            } catch (_) {}
            setTimeout(() => this.innerHTML = original, 400);
        });
    });

    // ── Toast notification ──────────────────────────────────────
    function showToast(msg) {
        const t = document.createElement('div');
        t.className = 'toast'; t.textContent = msg;
        Object.assign(t.style, {
            position: 'fixed', bottom: '24px', right: '24px', zIndex: 9999,
            background: '#1B4332', color: '#C9A84C', padding: '12px 22px',
            borderRadius: '4px', fontWeight: '600', fontSize: '.9rem',
            boxShadow: '0 4px 20px rgba(0,0,0,.18)', transition: 'opacity .3s'
        });
        document.body.appendChild(t);
        setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 2500);
    }

    // ── Auto-dismiss alerts ─────────────────────────────────────
    document.querySelectorAll('.alert').forEach(a => {
        setTimeout(() => { a.style.opacity = '0'; setTimeout(() => a.remove(), 300); }, 4000);
    });

})();
