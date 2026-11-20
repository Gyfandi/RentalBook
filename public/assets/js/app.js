'use strict';

// Tandai bahwa JS aktif (CSS memakai ini agar tanpa JS menu tetap terlihat)
document.documentElement.classList.add('js');

const $ = (selector, root = document) => root.querySelector(selector);
const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

function debounce(fn, delay = 150) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

/* ---------- 1. Menu mobile ---------- */
function initNavToggle() {
    const toggle = $('.nav-toggle');
    const nav = $('#site-nav');
    if (!toggle || !nav) return;

    const setOpen = (open) => {
        nav.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
    };

    toggle.addEventListener('click', () => {
        setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setOpen(false);
    });
}

/* ---------- 2. Notifikasi (toast) ---------- */
function getToastRegion() {
    let region = $('#toast-region');
    if (!region) {
        region = document.createElement('div');
        region.id = 'toast-region';
        region.className = 'toast-region';
        region.setAttribute('aria-live', 'polite');
        document.body.append(region);
    }
    return region;
}

function showToast(message, type = 'success', duration = 5000) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    const text = document.createElement('span');
    text.textContent = message;

    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'toast-close';
    close.setAttribute('aria-label', 'Tutup notifikasi');
    close.textContent = '\u00d7';

    const dismiss = () => {
        toast.classList.add('is-leaving');
        setTimeout(() => toast.remove(), 200);
    };
    close.addEventListener('click', dismiss);

    toast.append(text, close);
    getToastRegion().append(toast);
    setTimeout(dismiss, duration);
}

// Pesan sukses dari server (flash) ditampilkan sebagai toast
function initFlashToasts() {
    $$('main .notice, main [role="status"]').forEach((element) => {
        const message = element.textContent.trim();
        if (message === '') return;
        element.hidden = true;
        showToast(message, 'success');
    });
}

/* ---------- 3. Modal konfirmasi ---------- */
function confirmDialog(message, { danger = false } = {}) {
    return new Promise((resolve) => {
        const dialog = document.createElement('dialog');
        dialog.className = 'confirm-dialog';
        dialog.setAttribute('aria-labelledby', 'confirm-title');

        const title = document.createElement('h2');
        title.id = 'confirm-title';
        title.textContent = 'Konfirmasi';

        const body = document.createElement('p');
        body.textContent = message;

        const actions = document.createElement('div');
        actions.className = 'dialog-actions';

        const cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.className = 'btn-outline';
        cancel.textContent = 'Batal';
        cancel.addEventListener('click', () => dialog.close('cancel'));

        const ok = document.createElement('button');
        ok.type = 'button';
        ok.className = danger ? 'js-confirm' : '';
        ok.textContent = 'Ya, lanjutkan';
        ok.addEventListener('click', () => dialog.close('ok'));

        actions.append(cancel, ok);
        dialog.append(title, body, actions);

        // klik di area gelap (backdrop) = batal
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) dialog.close('cancel');
        });
        dialog.addEventListener('close', () => {
            const confirmed = dialog.returnValue === 'ok';
            dialog.remove();
            resolve(confirmed);
        });

        document.body.append(dialog);
        dialog.showModal();
        cancel.focus();
    });
}

// Semua elemen dengan atribut data-confirm="..." meminta konfirmasi dulu
function initConfirmButtons() {
    document.addEventListener('click', async (event) => {
        const trigger = event.target.closest('[data-confirm]');
        if (!trigger) return;

        event.preventDefault();
        const confirmed = await confirmDialog(trigger.dataset.confirm, {
            danger: trigger.classList.contains('js-confirm'),
        });
        if (!confirmed) return;

        if (trigger.form) {
            trigger.form.requestSubmit(trigger);
        } else if (trigger.href) {
            window.location.href = trigger.href;
        }
    });
}

/* ---------- 4. Cari & filter katalog secara langsung ---------- */
function initCatalogFilter() {
    const form = $('#catalog-filter');
    const grid = $('.catalog-grid');
    if (!form || !grid) return;

    const search = form.elements.q;
    const type = form.elements.type;
    const availableOnly = form.elements.available;

    const cards = $$('.media-card', grid).map((element) => {
        const paragraphs = $$('p', element);
        const status = paragraphs.find((p) => /^(Tersedia|Sedang dipinjam)$/.test(p.textContent.trim()));
        const available = status ? status.textContent.trim() === 'Tersedia' : true;

        if (status) {
            status.classList.add('badge', available ? 'badge-ok' : 'badge-busy');
        }

        // Rapikan tombol admin (Edit + Hapus) dalam satu baris
        const actions = $$(':scope > a, :scope > form', element);
        if (actions.length > 0) {
            const wrapper = document.createElement('div');
            wrapper.className = 'card-actions';
            actions[0].before(wrapper);
            wrapper.append(...actions);
        }

        const title = $('h2', element)?.textContent ?? '';
        const author = (paragraphs[0]?.textContent ?? '').split('\u00b7')[0];

        return {
            element,
            available,
            type: element.dataset.type ?? '',
            haystack: `${title} ${author}`.toLowerCase(),
        };
    });

    const summary = document.createElement('p');
    summary.className = 'catalog-summary';
    summary.setAttribute('aria-live', 'polite');
    grid.before(summary);

    const apply = () => {
        const keyword = search.value.trim().toLowerCase();
        let visible = 0;

        cards.forEach((card) => {
            const match = (keyword === '' || card.haystack.includes(keyword))
                && (type.value === '' || card.type === type.value)
                && (!availableOnly.checked || card.available);
            card.element.hidden = !match;
            if (match) visible += 1;
        });

        summary.textContent = visible === 0
            ? 'Tidak ada media yang cocok dengan pencarianmu.'
            : `Menampilkan ${visible} dari ${cards.length} media`;
    };

    search.addEventListener('input', debounce(apply));
    type.addEventListener('change', apply);
    availableOnly.addEventListener('change', apply);
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        apply();
    });

    apply();
}

/* ---------- 5. Validasi form di sisi klien ---------- */
let fieldCounter = 0;

function messageFor(field) {
    const { validity } = field;
    const value = field.value;

    if (field.required && value.trim() === '') return 'Kolom ini wajib diisi.';
    if (validity.typeMismatch) {
        return field.type === 'email' ? 'Format email tidak valid.' : 'Format isian tidak valid.';
    }
    if (field.minLength > 0 && value.length > 0 && value.length < field.minLength) {
        return `Minimal ${field.minLength} karakter.`;
    }
    if (validity.tooLong) return `Maksimal ${field.maxLength} karakter.`;
    if (validity.badInput) return 'Isi dengan angka yang valid.';
    if (validity.rangeUnderflow) return `Nilai minimal ${field.min}.`;
    if (validity.rangeOverflow) return `Nilai maksimal ${field.max}.`;
    if (validity.stepMismatch) return `Gunakan kelipatan ${field.step}.`;
    return '';
}

function getErrorElement(field) {
    if (!field.id) field.id = `field-${++fieldCounter}`;
    const errorId = `${field.id}-error`;
    let error = document.getElementById(errorId);
    if (!error) {
        error = document.createElement('small');
        error.id = errorId;
        error.className = 'field-error';
        error.hidden = true;
        (field.closest('label') ?? field).insertAdjacentElement('afterend', error);
    }
    return error;
}

function validateField(field) {
    const error = getErrorElement(field);
    // field yang disembunyikan (mis. Volume saat jenis E-Book) tidak divalidasi
    const message = field.closest('[hidden]') ? '' : messageFor(field);

    error.textContent = message;
    error.hidden = message === '';
    field.setAttribute('aria-invalid', message === '' ? 'false' : 'true');
    if (message === '') {
        field.removeAttribute('aria-describedby');
    } else {
        field.setAttribute('aria-describedby', error.id);
    }
    return message === '';
}

function initFormValidation() {
    $$('form').forEach((form) => {
        if (form.id === 'catalog-filter') return;

        const fields = $$('input:not([type="hidden"]):not([type="checkbox"]), select, textarea', form);
        if (fields.length === 0) return;

        form.noValidate = true; // pesan error ditampilkan oleh JS, bukan bubble bawaan browser

        fields.forEach((field) => {
            field.addEventListener('blur', () => validateField(field));
            field.addEventListener('input', () => {
                if (field.getAttribute('aria-invalid') === 'true') validateField(field);
            });
        });

        form.addEventListener('submit', (event) => {
            const invalid = fields.filter((field) => !validateField(field));
            if (invalid.length > 0) {
                event.preventDefault();
                invalid[0].focus();
            }
        });
    });
}

/* ---------- 6. Form media: tampilkan kolom sesuai jenis ---------- */
function initMediaForm() {
    const form = $('form[action*="catalog_form"]');
    const type = form && form.elements.type;
    if (!type) return;

    const formatField = form.elements.format?.closest('label');
    const volumeField = form.elements.volume?.closest('label');

    const sync = () => {
        if (formatField) formatField.hidden = type.value !== 'ebook';
        if (volumeField) volumeField.hidden = type.value !== 'journal';
    };
    type.addEventListener('change', sync);
    sync();
}

/* ---------- Inisialisasi ---------- */
initNavToggle();
initFlashToasts();
initConfirmButtons();
initCatalogFilter();
initFormValidation();
initMediaForm();