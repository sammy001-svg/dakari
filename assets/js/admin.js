/* Dakari — Admin JavaScript */
(function () {
    'use strict';

    // ── Image upload preview ────────────────────────────────────
    document.querySelectorAll('.img-upload-box').forEach(box => {
        const input   = box.querySelector('input[type=file]');
        const preview = box.nextElementSibling;
        box.addEventListener('click', () => input?.click());
        box.addEventListener('dragover', e => { e.preventDefault(); box.style.borderColor = '#C9A84C'; });
        box.addEventListener('dragleave', () => { box.style.borderColor = ''; });
        box.addEventListener('drop', e => {
            e.preventDefault(); box.style.borderColor = '';
            if (input && e.dataTransfer.files.length) {
                const dt = new DataTransfer();
                [...e.dataTransfer.files].forEach(f => dt.items.add(f));
                input.files = dt.files;
                input.dispatchEvent(new Event('change'));
            }
        });
        input?.addEventListener('change', () => {
            if (!preview || !preview.classList.contains('img-preview-grid')) return;
            preview.innerHTML = '';
            [...input.files].forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    const wrap = document.createElement('div');
                    wrap.className = 'img-preview';
                    wrap.innerHTML = `<img src="${e.target.result}">
                        <button class="img-preview__remove" type="button">&times;</button>`;
                    wrap.querySelector('.img-preview__remove').addEventListener('click', ev => {
                        ev.stopPropagation(); wrap.remove();
                    });
                    preview.appendChild(wrap);
                };
                reader.readAsDataURL(file);
            });
        });
    });

    // ── Confirm deletes ─────────────────────────────────────────
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) e.preventDefault();
        });
    });

    // ── Slug auto-generate ──────────────────────────────────────
    const nameInput = document.getElementById('product_name');
    const slugInput = document.getElementById('product_slug');
    if (nameInput && slugInput && !slugInput.value) {
        nameInput.addEventListener('input', () => {
            slugInput.value = nameInput.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').trim();
        });
    }

    // ── Data table search ───────────────────────────────────────
    const tableSearch = document.getElementById('tableSearch');
    if (tableSearch) {
        tableSearch.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.data-table tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    // ── Status filter ───────────────────────────────────────────
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function () {
            const val = this.value.toLowerCase();
            document.querySelectorAll('.data-table tbody tr').forEach(row => {
                const badge = row.querySelector('.status-badge');
                row.style.display = (!val || badge?.textContent.trim().toLowerCase() === val) ? '' : 'none';
            });
        });
    }

    // ── Toast helper ────────────────────────────────────────────
    window.adminToast = function (msg, type = 'success') {
        const t = document.createElement('div');
        t.className = 'alert alert-' + type;
        t.style.cssText = 'position:fixed;top:24px;right:24px;z-index:9999;min-width:260px;box-shadow:0 4px 20px rgba(0,0,0,.15)';
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 3000);
    };

})();
