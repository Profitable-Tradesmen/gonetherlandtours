import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';

Alpine.plugin(focus);
window.Alpine = Alpine;
window.dataLayer = window.dataLayer || [];

/* ---------------------------------------------------------------
 * reCAPTCHA v2 (checkbox), explicit rendering so several widgets
 * can live on one page (including inside the modal).
 * The Google script is only loaded once a visitor touches a form.
 * ------------------------------------------------------------- */
const rc = { loading: false, ready: false, queue: [] };

window.gntRecaptchaLoaded = () => {
    rc.ready = true;
    rc.queue.splice(0).forEach((fn) => fn());
};

function loadRecaptcha() {
    if (rc.loading) return;
    rc.loading = true;
    const s = document.createElement('script');
    s.src = 'https://www.google.com/recaptcha/api.js?onload=gntRecaptchaLoaded&render=explicit';
    s.async = true;
    s.defer = true;
    document.head.appendChild(s);
}

function renderCaptcha(el) {
    if (!el || el.dataset.widget) return;
    const run = () => {
        if (el.dataset.widget) return;
        el.dataset.widget = String(window.grecaptcha.render(el, { sitekey: el.dataset.sitekey }));
    };
    if (rc.ready) run();
    else {
        rc.queue.push(run);
        loadRecaptcha();
    }
}

function resetCaptcha(el) {
    if (el && el.dataset.widget && window.grecaptcha) window.grecaptcha.reset(Number(el.dataset.widget));
}

/* ---------------------------------------------------------------
 * Packages page: "Search by location" popup. Fetches an HTML fragment
 * (packages.search) as the visitor types; no page reload.
 * ------------------------------------------------------------- */
Alpine.data('locationSearch', (url) => ({
    open: false,
    q: '',
    html: '',
    loading: false,
    ctrl: null,

    show() {
        this.open = true;
        this.run();
        this.$nextTick(() => this.$refs.input && this.$refs.input.focus());
    },

    hide() {
        this.open = false;
    },

    async run() {
        if (this.ctrl) this.ctrl.abort(); // drop the previous in-flight request
        this.ctrl = new AbortController();
        this.loading = true;
        try {
            const res = await fetch(`${url}?q=${encodeURIComponent(this.q.trim())}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
                signal: this.ctrl.signal,
            });
            if (!res.ok) throw new Error(String(res.status));
            this.html = await res.text();
            this.loading = false;
        } catch (e) {
            if (e.name === 'AbortError') return;
            this.html = '<p class="py-6 text-center text-sm">Search is unavailable right now. Please try again.</p>';
            this.loading = false;
        }
    },
}));

/* ---------------------------------------------------------------
 * Enquiry form: same behaviour for every form on the site.
 * Posts via fetch; the server is the source of truth for validation.
 * Without JavaScript the form posts normally (see EnquiryController).
 * ------------------------------------------------------------- */
Alpine.data('enquiryForm', () => ({
    errors: {},
    message: '',
    sending: false,
    success: false,

    dial: '',

    init() {
        const sel = this.$el.querySelector('.cc-native option:checked');
        this.dial = ((sel && sel.textContent.match(/\+(\d+)/)) || [])[1] || '1';
        this.$el.addEventListener('dialchange', (e) => { this.dial = e.detail.dial; });
        // Render the reCAPTCHA widget (and load Google's script) only when someone touches this form.
        // Doing it when the form merely scrolls into view put Google's third-party cookie on pages whose form is
        // above the fold (the Contact page on desktop), which Lighthouse counts against Best Practices.
        const touch = () => this.captcha();
        this.$el.addEventListener('focusin', touch, { once: true });
        this.$el.addEventListener('pointerdown', touch, { once: true });
    },

    captcha() {
        renderCaptcha(this.$refs.captcha);
    },

    err(field) {
        return this.errors[field] ? this.errors[field][0] : '';
    },

    /** Phone field: letters and other symbols are removed as soon as they are typed, pasted or auto-filled. */
    phoneClean(input) {
        const allowed = /[^0-9+()\-. ]/g;
        if (!allowed.test(input.value)) return;
        // Keep the cursor where the visitor was typing.
        const before = input.value.slice(0, input.selectionStart).replace(allowed, '').length;
        input.value = input.value.replace(allowed, '');
        input.setSelectionRange(before, before);
    },

    phoneCheck(form) {
        let rules = {};
        try { rules = JSON.parse(form.dataset.phoneRules || '{}'); } catch (e) { /* use the default */ }
        const iso = (form.querySelector('[name=country_code]') || {}).value;
        const dial = (form.querySelector('.cc-native option:checked') || {}).textContent;
        const [min, max] = rules[iso] || [6, 12];
        const raw = ((form.querySelector('[name=phone]') || {}).value || '').replace(/\D/g, '');
        let n = raw.replace(/^0+/, '');
        const d = ((dial || '').match(/\+(\d+)/) || [])[1] || '';
        if ((n.length < min || n.length > max) && d && raw.startsWith(d)) n = raw.slice(d.length).replace(/^0+/, '');
        if (n.length >= min && n.length <= max) return '';
        const need = min === max ? min + ' digits' : min + '-' + max + ' digits';
        if (!n.length) return 'Please enter your phone number.';
        return (n.length < min ? 'This number is too short. ' : 'This number is too long. ') + 'Enter ' + need + ' without the country code.';
    },

    async submit(event) {
        const form = event.target;
        this.errors = {};
        this.message = '';

        // Phone: the number of digits has to fit the chosen country (too short or too long does not go through).
        const phoneMsg = this.phoneCheck(form);
        if (phoneMsg) {
            this.errors = { phone: [phoneMsg] };
            this.message = 'Please check the highlighted fields.';
            const p = form.querySelector('[name=phone]');
            if (p) p.focus();
            return;
        }
        if (!form.querySelector('[name=consent]').checked) {
            this.errors = { consent: ['Please tick the box to agree before sending your inquiry.'] };
            this.message = 'Please check the highlighted fields.';
            return;
        }
        this.sending = true;
        try {
            const res = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok) {
                this.success = true;
                window.dataLayer.push({
                    event: 'enquiry_submit',
                    form_name: form.dataset.formName || '',
                });
                setTimeout(() => (window.location.href = data.redirect || '/thank-you'), 700);
                return;
            }
            if (res.status === 422) {
                this.errors = data.errors || {};
                this.message = 'Please check the highlighted fields.';
            } else if (res.status === 429) {
                this.message = 'Too many attempts. Please wait a minute and try again.';
            } else if (res.status === 419) {
                this.message = 'Your session expired. Please refresh the page and try again.';
            } else {
                this.message = 'Something went wrong. Please try again or call us.';
            }
            resetCaptcha(this.$refs.captcha);
        } catch (e) {
            this.message = 'We could not reach the server. Check your connection and try again.';
        } finally {
            this.sending = false;
        }
    },
}));

/* ---------------------------------------------------------------
 * Hero quiz. Steps are rendered by Blade from the database; this
 * only tracks the current step and the answers.
 * ------------------------------------------------------------- */
Alpine.data('quiz', (keys) => ({
    keys,
    i: 0,
    answers: {},
    browsing: false,   // arrow keys moving through a closed dropdown do not count as a choice

    get total() {
        return this.keys.length + 1;
    },
    get onContact() {
        return this.i >= this.keys.length;
    },
    get percent() {
        return Math.round((this.i / this.total) * 100);
    },

    pick(key, value) {
        this.answers[key] = value;
        this.go(1);
    },
    next(key) {
        // only the step on screen can move on (a second event for the same choice, such as change then Enter, is ignored)
        if (this.keys[this.i] === key && this.answers[key]) this.go(1);
    },
    back() {
        if (this.i > 0) this.i--;
    },
    go(n) {
        this.i = Math.min(this.i + n, this.keys.length);
        window.dataLayer.push({ event: 'quiz_step', quiz_step: this.i, quiz_total: this.total });
    },
}));

/* ---------------------------------------------------------------
 * Country-code listbox with flags. The native <select> (x-model="iso")
 * stays the submitted value; this is the accessible custom UI on top.
 * Keys: arrows, Home/End, Enter/Space, Escape, Tab, type-ahead.
 * ------------------------------------------------------------- */
Alpine.data('countrySelect', (initial, list, flagBase) => ({
    iso: initial,
    list,
    open: false,
    active: -1,
    typed: '',
    typedAt: 0,

    get current() {
        return this.list.find((c) => c.iso === this.iso) || this.list[0];
    },
    flag(iso) {
        return flagBase + '/' + iso.toLowerCase() + '.svg';
    },
    toggle() {
        this.open ? this.close() : this.show();
    },
    show() {
        this.open = true;
        this.setActive(Math.max(0, this.list.findIndex((c) => c.iso === this.iso)));
        this.$nextTick(() => {
            this.$refs.list.focus();
            this.scroll();
        });
    },
    close(refocus = true) {
        this.open = false;
        if (refocus) this.$refs.btn.focus();
    },
    setActive(i) {
        this.active = (i + this.list.length) % this.list.length;
        this.scroll();
    },
    scroll() {
        const li = this.$refs.list.querySelectorAll('[role="option"]')[this.active];
        if (li) li.scrollIntoView({ block: 'nearest' });
    },
    choose(iso) {
        this.iso = iso;
        const form = this.$el.closest('form');
        if (form) form.dispatchEvent(new CustomEvent('dialchange', { detail: { dial: (this.list.find((c) => c.iso === iso) || {}).dial || '' } }));
        this.close();
    },
    key(e) {
        const k = e.key;
        if (k === 'ArrowDown') { e.preventDefault(); this.setActive(this.active + 1); }
        else if (k === 'ArrowUp') { e.preventDefault(); this.setActive(this.active - 1); }
        else if (k === 'Home') { e.preventDefault(); this.setActive(0); }
        else if (k === 'End') { e.preventDefault(); this.setActive(this.list.length - 1); }
        else if (k === 'Enter' || k === ' ') { e.preventDefault(); this.choose(this.list[this.active].iso); }
        else if (k === 'Escape') { e.preventDefault(); e.stopPropagation(); this.close(); }
        else if (k === 'Tab') { this.close(false); }
        else if (k.length === 1 && /\S/.test(k)) {
            // type-ahead on the country name
            const now = Date.now();
            this.typed = now - this.typedAt > 700 ? k.toLowerCase() : this.typed + k.toLowerCase();
            this.typedAt = now;
            const hit = this.list.findIndex((c, i) => i >= (this.typed.length > 1 ? this.active : this.active + 1) && c.name.toLowerCase().startsWith(this.typed));
            const any = hit >= 0 ? hit : this.list.findIndex((c) => c.name.toLowerCase().startsWith(this.typed));
            if (any >= 0) this.setActive(any);
        }
    },
}));

/* Header modal */
Alpine.store('modal', {
    open: false,
    pkg: null, // {id, name} when opened from a package card, so the inquiry is tied to that package
    show(pkg = null) {
        this.pkg = pkg;
        this.open = true;
        window.dataLayer.push({ event: 'modal_open' });
    },
    hide() {
        this.open = false;
    },
});

// Mobile slider (see components/slider.blade.php): counts visible slides and tracks the current one for the dots.
Alpine.data('slider', () => ({
    n: 0,
    i: 0,
    slides() {
        return [...this.$refs.track.children].filter((el) => el.style.display !== 'none');
    },
    init() {
        this.$nextTick(() => this.sync());
        // Slides can be filtered by the package tabs (x-show); recount when that changes.
        new MutationObserver(() => this.sync()).observe(this.$refs.track, { attributes: true, attributeFilter: ['style'], subtree: true, childList: true });
        window.addEventListener('resize', () => this.sync());
    },
    sync() {
        const t = this.$refs.track;
        const s = this.slides();
        this.n = s.length;
        if (!s.length) return;
        const pos = t.scrollLeft;
        let best = 0;
        let min = Infinity;
        s.forEach((el, k) => {
            const d = Math.abs(el.offsetLeft - 16 - pos);
            if (d < min) { min = d; best = k; }
        });
        this.i = pos + t.clientWidth >= t.scrollWidth - 4 ? s.length - 1 : best;
    },
    go(k) {
        const el = this.slides()[k];
        if (el) this.$refs.track.scrollTo({ left: el.offsetLeft - 16, behavior: 'smooth' });
    },
}));

/* -------------------------------------------------------------
 * Carousel (featured packages): the track is a native scroll-snap row, so touch swipe, trackpad and keyboard scroll
 * work with no JavaScript help. This only drives the arrow buttons and their enabled state.
 * ------------------------------------------------------------- */
Alpine.data('carousel', () => ({
    atStart: true,
    atEnd: false,
    count: 0,   // number of cards (for the dash indicators)
    idx: 0,     // index of the card at the left edge
    init() {
        // Measure after the first paint (not during Alpine start-up) so it does not force an early layout.
        requestAnimationFrame(() => requestAnimationFrame(() => this.sync()));
        window.addEventListener('resize', () => this.sync(), { passive: true });
    },
    sync() {
        const t = this.$refs.track;
        if (!t) return;
        this.atStart = t.scrollLeft <= 4;
        this.atEnd = t.scrollLeft + t.clientWidth >= t.scrollWidth - 4;
        const slides = [...t.querySelectorAll('[data-slide]')].filter((c) => c.offsetParent !== null);
        this.count = slides.length;
        if (slides.length) {
            // the card whose left edge is closest to the left edge of the track; the last dash when scrolled to the end
            let best = 0;
            slides.forEach((c, i) => { if (Math.abs(c.offsetLeft - t.offsetLeft - t.scrollLeft) < Math.abs(slides[best].offsetLeft - t.offsetLeft - t.scrollLeft)) best = i; });
            this.idx = this.atEnd && !this.atStart ? slides.length - 1 : best;
        }
    },
    go(i) {
        const t = this.$refs.track;
        const slides = [...t.querySelectorAll('[data-slide]')].filter((c) => c.offsetParent !== null);
        if (!slides[i]) return;
        t.scrollTo({ left: slides[i].offsetLeft - t.offsetLeft, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
    },
    reset() {
        if (this.$refs.track) this.$refs.track.scrollLeft = 0;
        this.sync();
    },
    step(dir) {
        const t = this.$refs.track;
        const card = [...t.querySelectorAll('[data-slide]')].find((c) => c.offsetParent !== null);
        const w = (card ? card.getBoundingClientRect().width : 320) + 20;
        t.scrollBy({ left: dir * w, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
    },
}));

/* -------------------------------------------------------------
 * Partner logos slider: shows 3 logos and slides by one logo continuously: a slow 1.8s move, a 0.3s pause, the next move; logos fade out as they leave and fade in as they arrive. The first logos are repeated at the end of the track;
 * when the slide reaches that copy the track snaps back to the start without animation, so the loop has no visible jump.
 * Pauses while hovered or focused and when the tab is hidden; does nothing for "reduce motion" (the CSS wraps the logos instead).
 * ------------------------------------------------------------- */
Alpine.data('partnerSlider', (n) => ({
    i: 0,
    anim: true,
    paused: false,
    per: 3,
    still: false,
    init() {
        this.still = n < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const setPer = () => { this.per = window.innerWidth >= 1024 ? 3 : window.innerWidth >= 640 ? 2 : 1; };
        setPer();
        window.addEventListener('resize', setPer);
        if (this.still) return;
        setInterval(() => {
            if (this.paused || document.hidden) return;
            this.anim = true;
            this.i++;
        }, 2100);   // 1.8s move + 0.3s pause
    },
    // a logo is shown while it is inside the visible window; leaving or entering it fades out or in while the row slides
    shown(k) {
        return this.still || (k >= this.i && k < this.i + this.per);
    },
    end() {
        if (this.i >= n) {
            this.anim = false;
            this.i = 0;
        }
    },
}));

/* -------------------------------------------------------------
 * Package gallery enlarge view (pages/package-show.blade.php): a photo opens large; arrows and the Left/Right keys move between photos,
 * Escape or a click outside closes it and focus goes back to the photo that was clicked. The sliding itself is CSS only (.gslider).
 * ------------------------------------------------------------- */
Alpine.data('gallerySlider', (items) => ({
    items,
    open: false,
    i: 0,
    opener: null,
    get cur() {
        return this.items[this.i] || { src: '', alt: '' };
    },
    show(k, el) {
        this.i = k;
        this.opener = el || null;
        this.open = true;
    },
    close() {
        this.open = false;
        this.$nextTick(() => this.opener && this.opener.focus && this.opener.focus());
    },
    next() {
        this.i = (this.i + 1) % this.items.length;
    },
    prev() {
        this.i = (this.i - 1 + this.items.length) % this.items.length;
    },
}));
/* -------------------------------------------------------------
 * Contact page message starters (sections/contact_details.blade.php): a tap adds a sentence to the "Special request" box
 * (on a new line if something is already written), puts the cursor there and scrolls it into view.
 * ------------------------------------------------------------- */
Alpine.data('contactPrompts', (id) => ({
    fill(text) {
        const el = document.getElementById(id);
        if (!el) return;
        const cur = el.value.trim();
        if (cur.includes(text)) { el.focus(); return; }
        el.value = cur ? cur + '\n' + text : text;
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.scrollIntoView({ block: 'center', behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
        el.focus({ preventScroll: true });
    },
}));
Alpine.start();

/* ---------------------------------------------------------------
 * Scroll reveal: adds .is-in to [data-reveal] elements as they enter the
 * viewport, then removes the attribute so hover transitions work again.
 * Skipped (content shown immediately) without IntersectionObserver or
 * when the visitor prefers reduced motion.
 * ------------------------------------------------------------- */
function initReveal() {
    const els = [...document.querySelectorAll('[data-reveal]')];
    if (!els.length) return;
    const clean = (el) => {
        el.removeAttribute('data-reveal');
        el.classList.remove('is-in');
        el.style.removeProperty('--d');
    };
    if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        els.forEach(clean);
        return;
    }
    const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (!e.isIntersecting) return;
            const el = e.target;
            io.unobserve(el);
            el.classList.add('is-in');
            const delay = parseInt(el.style.getPropertyValue('--d'), 10) || 0;
            setTimeout(() => clean(el), delay + 1000);
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
    els.forEach((el) => io.observe(el));
}
initReveal();

/* -------------------------------------------------------------
 * Hero background video: the poster paints first (it is the LCP image);
 * the 2 MB video is fetched only after the page has loaded, and never on
 * phones, with Data Saver on, or when the visitor prefers reduced motion.
 * ------------------------------------------------------------- */
function initBgVideo() {
    const videos = [...document.querySelectorAll('video[data-bg-video]')];
    if (!videos.length) return;
    const conn = navigator.connection || {};
    if (conn.saveData || window.matchMedia('(prefers-reduced-motion: reduce)').matches || !window.matchMedia('(min-width: 768px)').matches) return;
    const start = () => videos.forEach((v) => {
        v.querySelectorAll('source[data-src]').forEach((s) => { s.src = s.dataset.src; });
        v.load();
        v.play().catch(() => {});
    });
    const later = () => ('requestIdleCallback' in window ? requestIdleCallback(start, { timeout: 3000 }) : setTimeout(start, 1500));
    if (document.readyState === 'complete') later(); else window.addEventListener('load', later, { once: true });
}
initBgVideo();

/* Accordions (FAQ): <details name="x"> already opens one at a time in current browsers; this gives older ones the same behaviour. */
document.addEventListener(
    'toggle',
    (e) => {
        const d = e.target;
        if (!(d instanceof HTMLDetailsElement) || !d.open || !d.name) return;
        document.querySelectorAll(`details[name="` + CSS.escape(d.name) + `"][open]`).forEach((o) => { if (o !== d) o.open = false; });
    },
    true,
);
