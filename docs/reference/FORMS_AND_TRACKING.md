# Forms and tracking

## One form, everywhere

Every form renders `resources/views/components/enquiry-form.blade.php` and posts to `POST /enquiry` (`EnquiryController@store`, validated by `EnquiryRequest`, saved by `EnquiryService`). Form names (`config/gnt.php`): `hero-quiz`, `header-modal`, `package-quote`, `package-question`, `contact`.

Fields: first name*, last name*, email*, country (ISO, stored as `+dial`), phone* (stored as E.164), preferred travel date, second-quote preference, special request; optional season / group size / nights; package ID and name on package forms; quiz answers as JSON.

## Rules enforced on every form

1. **reCAPTCHA v2 checkbox**, explicit rendering (several widgets per page, also inside the modal). The Google script loads only when a form is near the viewport. Verified server-side by `RecaptchaService`: **fails closed** (no token, bad token, Google unreachable = rejected). A `<noscript>` iframe fallback exists for no-JS visitors.
2. **Consent checkbox**: required, unticked by default, enforced in the browser and by the server (`accepted`). The exact wording is in `config/gnt.php` (`consent_template`); `[Company Name]` comes from **Admin > Settings > Legal company name**. Do not reword without legal review.
3. **Evidence stored per enquiry**: consent flag, the exact consent text shown, terms version (from the Terms page), timestamp, IP, user agent, page URL.
4. CSRF, honeypot (`company_website`), rate limit (6 per minute per IP, `enquiry` limiter), tag-stripping/sanitising, inline errors, success state, redirect to `/thank-you`.
5. Accessible labels, `aria-invalid`, `role="alert"` errors, visible focus.

No JavaScript: the hero shows a plain contact form (the `.nojs-only` block) and every form posts normally.

## Attribution capture

`CaptureTracking` middleware (all public GET pages):

- **First touch**: encrypted cookie `gnt_first_touch` (30 days) with utm_source, utm_medium, utm_campaign, utm_term, utm_content, gclid, gbraid, wbraid, referrer, landing page. Never overwritten.
- **Latest touch**: session value, updated whenever a visit carries tracking parameters.
Both are saved on the enquiry (`tracking_first`, `tracking_last`) and sent to GHL.

## Google Tag Manager and Google Ads

1. Enter the container ID (e.g. `GTM-ABC1234`) in **Admin > Settings > Google Tag Manager ID**. Nothing loads until it is set.
2. `dataLayer` events pushed by the site:

| Event | When | Useful fields |
|---|---|---|
| `quiz_step` | each quiz step | `quiz_step`, `quiz_total` |
| `modal_open` | header modal opened | |
| `enquiry_submit` | successful form submit (browser) | `form_name` |
| `enquiry_thank_you` | Thank You page, once per real submission | `form_name`, `enquiry_id` |

3. In GTM: create a **Custom Event trigger** `enquiry_thank_you`. Attach the **Google Ads Conversion Tracking** tag (your conversion ID and label) and a **Conversion Linker** tag to it. Use `enquiry_thank_you` (not `enquiry_submit`) for the Ads conversion, because it fires only after the server accepted the enquiry. The Thank You page is `noindex` and blocked in robots.txt.
4. Preview in GTM Tag Assistant before launch.

## Emails

New enquiries are emailed (queued) to **Admin > Settings > Staff notification emails**. Locally the `log` mailer writes them to `storage/logs/laravel.log`.
