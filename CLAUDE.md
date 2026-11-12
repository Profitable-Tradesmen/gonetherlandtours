# CLAUDE.md: GoNetherlandTours

A fast, SEO-friendly Google Ads landing site for a GoTour brand (Netherlands; self drive, chauffeur, coach tours; US travellers; EUR prices). It captures enquiries reliably (quiz + forms), stores consent evidence and syncs leads to GoHighLevel. It is a **skeleton the team iterates on**. **Update this file whenever you change anything it describes.**

## Stack
Laravel 12 (PHP 8.2+; dev PHP 8.4), server-rendered Blade (no SPA), Tailwind v4 + Vite, Alpine.js (+ focus plugin), MySQL (utf8mb4; SQLite only for tests), custom admin (no Filament/WordPress). Fonts self-hosted: Playfair Display + Montserrat.

## Run it (XAMPP)
See `docs/reference/LOCAL_XAMPP.md`. Short: `.env` from `.env.example`, DB `gonetherlandtours`, `composer install`, `npm install`, `php artisan key:generate`, `php artisan migrate --seed`, `php artisan storage:link`, `npm run build` (or `npm run dev`), `php artisan serve`. Tests: `php artisan test`. Composer on this PC is old: use a fresh `composer.phar`.

## Folder map
- `routes/web.php` public routes; `routes/admin.php` all `/admin` routes; `routes/console.php` scheduler (queue worker every minute).
- `app/Http/Controllers/` public controllers; `Admin/` admin (a generic `ResourceController` drives tour types, destinations, FAQs, testimonials, admin users).
- `app/Services/` `EnquiryService` (save + evidence + queue), `RecaptchaService`, `ImageService` (GD, WebP, 480/960/1600), `Ghl/GhlService` + `Ghl/GhlFields`.
- `app/Jobs/SyncEnquiryToGhl.php`, `app/Mail/NewEnquiryMail.php`, `app/Console/Commands/Ghl*.php`.
- `app/Support/Seo.php` page SEO + JSON-LD; `app/helpers.php` (`setting()`, `consent_text()`, `clean_html()`, `ghl_enabled()`...).
- `config/gnt.php` consent template, countries, form names, tracking keys. `config/services.php` GHL + reCAPTCHA.
- `resources/views/`: `layouts/app`, `partials/`, `components/` (see DESIGN_SYSTEM.md), `pages/`, `admin/`, `emails/`.
- `resources/css/app.css` design tokens; `resources/js/app.js` quiz, enquiry form, reCAPTCHA, modal.
- `database/seeders/` all starter content. `tests/Feature/` feature tests. `docs/reference/` docs.

## Business rules that are not obvious from code
- **Consent text is exact** (`config/gnt.php` > `consent_template`); `[Company Name]` = Admin > Settings legal company name. Required, unticked by default, enforced in browser AND server. The text shown, terms version (Terms page `version`), timestamp, IP, user agent and page URL are stored per enquiry. Do not reword without legal review.
- **Every form uses `<x-enquiry-form>` and `POST /enquiry`**: reCAPTCHA v2 checkbox (explicit render; **fails closed**), honeypot `company_website`, CSRF, rate limit 6/min/IP, sanitising, E.164 phone. A new form = another use of the component, never a new endpoint.
- **No-JS**: hero shows `.nojs-only` plain form. Never nest `<noscript>` (the form already contains one).
- **Tracking**: `CaptureTracking` middleware stores first touch (30-day encrypted cookie) + latest touch (session); both saved on the enquiry and sent to GHL as `(First)` / `(Last)` fields. Thank You page fires the `enquiry_thank_you` dataLayer event only after a real submission (session flash). GTM loads only when its ID is set.
- **GHL is OFF** unless `GHL_API_TOKEN` and `GHL_LOCATION_ID` are set in `.env` (never in code/DB/git). Fields are matched by exact name; names live ONLY in `GhlFields`. Sync never blocks an enquiry; status + contact ID are stored on the enquiry; retry in admin. Existing contacts keep their first-touch values.
- Quiz steps/options are database driven (Admin > Quiz). Step `key` = answer name = GHL field `Quiz: <Key>`; avoid renaming keys once leads exist.
- Sample packages (`is_sample`) are `noindex`, have no price (`[CONFIRM: price]`). Never invent business facts: use `[CONFIRM: ...]` and list it in `docs/reference/CONTENT_TO_CONFIRM.md`.
- Legal pages are starter templates, not legal advice.
- Images: only free-for-commercial-use; credit in `IMAGE_CREDITS.md`. Currently all images are generated placeholders.
- Admin: separate `admin` guard + `admin_users`; login throttled 5/min; cannot delete self or last admin. Admin first user comes from `ADMIN_SEED_EMAIL` / `ADMIN_SEED_PASSWORD`.
- Package routes bind by **slug** (`/admin/packages/{slug}/edit`).

## Conventions
Blade + small Alpine; no inline business logic in views beyond display. Colours/fonts only via tokens in `app.css`. Rich text is sanitised with `clean_html()`. Settings via `setting('key')` (cached; `Setting::put` clears cache). New setting = add to `SettingsSeeder` and `SettingsController::GROUPS`.

## Gotchas
- PowerShell 5.1 `Set-Content -Encoding utf8` adds a BOM that breaks `.env`, JSON and PHP files: write files with the editor tools or `[IO.File]::WriteAllText` with `UTF8Encoding($false)`.
- `public/robots.txt` must not exist (the route generates it); likewise no `public/sitemap.xml`.
- `in:` validation rule splits on commas: use `Rule::in()` for values containing commas.
- Tests call `Http::preventStrayRequests()` (tests/TestCase.php) and blank GHL env in `phpunit.xml`: tests must never reach the live CRM.
- reCAPTCHA keys in `.env.example` are Google's TEST keys.
- Production queue on Hostinger shared hosting runs via cron `schedule:run` (see DEPLOYMENT.md).
