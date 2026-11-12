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
 * Enquiry form: same behaviour for every form on the site.
 * Posts via fetch; the server is the source of truth for validation.
 * Without JavaScript the form posts normally (see EnquiryController).
 * ------------------------------------------------------------- */
Alpine.data('enquiryForm', () => ({
    errors: {},
    message: '',
    sending: false,
    success: false,

    init() {
        // Render the reCAPTCHA widget (and load Google's script) only when this form is actually
        // visible or near the viewport: hidden quiz step / closed modal forms cost nothing.
        this.$el.addEventListener('focusin', () => this.captcha(), { once: true });
        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver((entries) => {
                if (entries.some((e) => e.isIntersecting)) {
                    this.captcha();
                    io.disconnect();
                }
            }, { rootMargin: '300px' });
            io.observe(this.$el);
        }
    },

    captcha() {
        renderCaptcha(this.$refs.captcha);
    },

    err(field) {
        return this.errors[field] ? this.errors[field][0] : '';
    },

    async submit(event) {
        const form = event.target;
        this.errors = {};
        this.message = '';

        if (!form.querySelector('[name=consent]').checked) {
            this.errors = { consent: ['Please tick the box to agree before sending your enquiry.'] };
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
        if (this.answers[key]) this.go(1);
    },
    back() {
        if (this.i > 0) this.i--;
    },
    go(n) {
        this.i = Math.min(this.i + n, this.keys.length);
        window.dataLayer.push({ event: 'quiz_step', quiz_step: this.i, quiz_total: this.total });
    },
}));

/* Header modal */
Alpine.store('modal', {
    open: false,
    show() {
        this.open = true;
        window.dataLayer.push({ event: 'modal_open' });
    },
    hide() {
        this.open = false;
    },
});

Alpine.start();
