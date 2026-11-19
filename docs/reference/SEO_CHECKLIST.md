# SEO and Google Ads pre-launch checklist

## Built in (verify, do not rebuild)
- [ ] Every page is server-rendered HTML with one H1, a unique title and meta description, and a canonical URL (covered by `SeoAndPagesTest`).
- [ ] Open Graph and Twitter cards; default share image set in Admin > Settings (replace `images/og-default.webp`, a placeholder).
- [ ] JSON-LD: TravelAgency (all pages), BreadcrumbList, FAQPage (home and packages with FAQs), TouristTrip (packages). Test at search.google.com/test/rich-results.
- [ ] `/sitemap.xml` (auto-generated, excludes noindex pages), `/robots.txt` blocks `/admin` and `/thank-you`.
- [ ] Thank You page is `noindex` and fires the conversion event only after a real submission.
- [ ] Images: WebP, width/height attributes, lazy loading below the fold (including the hidden gallery pop-up), hero poster preloaded. Static images over 60 KB have an `-800.webp` copy that `<x-picture>` offers as a phone-size `srcset`; the logo has 400 px and 600 px copies.
- [ ] Hero video is not downloaded with the page: the poster is the LCP image and `initBgVideo()` (`app.js`) loads the MP4 after page load on 768 px+ screens only (not on phones, Data Saver or reduced motion). The feature video is `preload="none"` with a poster.
- [ ] Fonts self-hosted (Playfair Display, Montserrat) with `font-display: swap`.

## Before launch
- [ ] Replace **every** `[CONFIRM: ...]` and placeholder (CONTENT_TO_CONFIRM.md).
- [ ] Replace placeholder images with licensed photos; update IMAGE_CREDITS.md.
- [ ] Un-tick *Hide from search engines* on real packages (samples are noindex) and delete sample packages.
- [ ] Set the real domain as `APP_URL`; HTTPS forced; `www`/non-www redirect decided.
- [ ] Unique SEO title and meta description for Home, Packages, About, Contact, each package and destination (titles under about 60 characters, descriptions about 155).
- [ ] Phone number and email set (click-to-call works on mobile).
- [ ] Real reCAPTCHA keys registered for the domain. Submit all five form types once.
- [ ] GTM container set; Ads conversion attached to the `enquiry_thank_you` event; test in Tag Assistant (see FORMS_AND_TRACKING.md).
- [ ] Legal pages reviewed; consent wording approved; cookie/consent banner decision made for the target market.
- [ ] Google Search Console: verify the domain, submit the sitemap. Google Business Profile if applicable.
- [ ] Message match: each Ads ad group points to a page whose H1 and hero repeat the ad's promise (use package or destination pages, or add `?utm_*` links to the home page).
- [ ] Run Lighthouse (mobile and desktop) on Home, a package and Contact: aim for 90+ in every category. Last run 19 Nov 2026 (Home and a package: all above 90, see "Re-run after the mobile carousels" below); Contact still to run.
- [ ] Test at 360, 768 and 1280 px: no horizontal scroll; keyboard-only walk-through; WCAG AA contrast (brand blue `#437DA5` is decorative; text/buttons use `#2F6285`).
- [ ] Check 404 page, `robots.txt`, `sitemap.xml` on production.

## Ads landing page principles used
One clear call to action (quote), click-to-call in the top bar and mobile action bar, trust badges above the fold, minimal navigation clutter, fast load, mobile-first quiz.

## Lighthouse results (mobile, Lighthouse 12, 17 Nov 2026)
Run against the local build with gzip and long-cache headers in front of `php artisan serve` (what `public/.htaccess` does on Apache/Hostinger). Re-run on the live URL after deploy: real numbers depend on the host.

| Page | Performance | Accessibility | Best practices | SEO |
|---|---|---|---|---|
| Home, before | 61 | 97 | 100 | 92 |
| Home, after | 94 | 100 | 100 | 100 |
| Package (`sample-tulip-season-self-drive`), before | 77 | 100 | 100 | 61 |
| Package, after | 93-95 | 100 | 100 | 100 (see note) |

Note: sample packages are `noindex` on purpose, which makes Lighthouse fail "Page is blocked from indexing" (SEO 69). The SEO 100 above was measured with `noindex` switched off for one run. Real packages must have *Hide from search engines* unticked, so they will score 100.

What was fixed:
- **LCP 13.7 s to 2.5 s (home).** The 2.2 MB hero MP4 started downloading immediately and starved the poster. It now loads after page load on larger screens only (`components/video.blade.php`, `initBgVideo()` in `app.js`).
- **About 1.7 MB of hidden images removed from every home load.** The photo-viewer pop-up rendered six full-size 2000 px gallery images without `loading="lazy"`.
- **Oversized images.** Logo was 138 KB for a 126 px display (now 10 KB on phones, 17 KB on desktop); traveller, intro, feature and CTA images get 800 px `srcset` copies (about 80 percent smaller on phones).
- **Accessibility.** Slider dots were 8 px tap targets; each is now a 24 px target around the visible dot (`components/slider.blade.php`).
- **SEO "links have descriptive text".** "More info" and "Read more" links now carry a visually hidden suffix with the package or article title.

Keep it above 90 when editing:
- New large photo: save a `name.webp` plus a `name-800.webp` beside it (800 px wide, quality about 78) and use `<x-picture>`. Never add a bare `<img>` without `loading="lazy"` below the fold.
- Do not add a new above-the-fold video, carousel or third-party script; test on throttled mobile first.
- Round slider/icon buttons must stay at least 24 x 24 px.
- Link text such as "More info" needs the sr-only suffix pattern used in `partials/package-card.blade.php`.
- Re-run: `npx lighthouse <url> --form-factor=mobile` (Chrome needed). Measure against a gzip-enabled server: `php artisan serve` alone does not compress and understates scores.

### After the design refresh (photo hero, transparent header, packages carousel)
Home 93-94 (6 featured packages), About 97, Destinations 99, package page 96 for performance; accessibility 100 and best practices 100 everywhere; SEO 100 (a `noindex` sample package scores lower on SEO by design). Costs to watch: the hero photo is the LCP image (`hero-home-800.webp` is about 60 KB on phones), the carousel adds Alpine start-up work (kept low by `content-visibility` on lower sections), and every new featured package adds cards. Re-run Lighthouse after adding many featured packages.

### Re-run after the mobile carousels and banner changes (Lighthouse 12, 19 Nov 2026)
Local build behind a gzip + long-cache proxy, home page and package `sample-tulip-season-self-drive`, mobile and desktop presets. Nothing was below 90, so no code fixes were needed.

| Page | Size | Performance | Accessibility | Best practices | SEO |
|---|---|---|---|---|---|
| Home | Mobile | 94 (LCP 2.8 s, TBT 130 ms, CLS 0) | 100 | 100 | 100 |
| Home | Desktop | 98 (LCP 1.0 s) | 100 | 100 | 100 |
| Package | Mobile | 95 (LCP 2.7 s, CLS 0) | 100 | 100 | 69 / 100 |
| Package | Desktop | 100 (LCP 0.7 s) | 100 | 100 | 69 / 100 |

The SEO 69 on the package page is only "Page is blocked from indexing": the sample package is `noindex` on purpose. With *Hide from search engines* switched off for one run it scored 100 (then restored). Real packages must leave that box unticked.
