# SEO and Google Ads pre-launch checklist

## Built in (verify, do not rebuild)
- [ ] Every page is server-rendered HTML with one H1, a unique title and meta description, and a canonical URL (covered by `SeoAndPagesTest`).
- [ ] Open Graph and Twitter cards; default share image set in Admin > Settings (replace `images/og-default.webp`, a placeholder).
- [ ] JSON-LD: TravelAgency (all pages), BreadcrumbList, FAQPage (home and packages with FAQs), TouristTrip (packages). Test at search.google.com/test/rich-results.
- [ ] `/sitemap.xml` (auto-generated, excludes noindex pages), `/robots.txt` blocks `/admin` and `/thank-you`.
- [ ] Thank You page is `noindex` and fires the conversion event only after a real submission.
- [ ] Images: WebP, width/height attributes, lazy loading below the fold (including the hidden gallery pop-up), hero poster preloaded. Static images over 60 KB have an `-800.webp` copy that `<x-picture>` offers as a phone-size `srcset`; the logo has 400 px and 600 px copies.
- [ ] The home hero is a photo with no video (the photo is the LCP image, `hero-home-800.webp` on phones). Background videos are still supported (`initBgVideo()` in `app.js` loads an MP4 after page load on 768 px+ screens only, not on phones, Data Saver or reduced motion), but none is in use; add one only after re-running Lighthouse. The feature video is `preload="none"` with a poster.
- [ ] Fonts self-hosted (Playfair Display, Montserrat) with `font-display: swap`.

## Before launch
- [ ] Replace placeholder wording (CONTENT_TO_CONFIRM.md). No `[CONFIRM: ...]` markers remain in the content as of 20 Nov 2026; the finished wording (team names, terms, privacy, group size, discount, prices) is sensible placeholder text to review. The legal pages are a starter template and need a lawyer.
- [ ] Photos are public-domain (CC0) pictures from Wikimedia Commons (no attribution needed, 20 Nov 2026); swap in your own photography when you have it. The tour-type photos and the nine package photo sets are uploads under `storage/app/public/media/2026`, so copy that folder when you deploy.
- [ ] The six empty sample packages were replaced by nine complete starter packages (three per tour type), published and indexable. Set the real prices and itineraries in Admin > Packages (the prices shown are placeholders).
- [ ] Set the real domain as `APP_URL`; HTTPS forced; `www`/non-www redirect decided.
- [ ] Unique SEO title and meta description for Home, Packages, About, Contact, each package and destination (titles under about 60 characters, descriptions about 155).
- [ ] Phone number and email set (click-to-call works on mobile).
- [ ] Real reCAPTCHA keys registered for the domain. Submit all five form types once.
- [ ] GTM container set; Ads conversion attached to the `enquiry_thank_you` event; test in Tag Assistant (see FORMS_AND_TRACKING.md).
- [ ] Legal pages reviewed; consent wording approved; cookie/consent banner decision made for the target market.
- [ ] Google Search Console: verify the domain, submit the sitemap. Google Business Profile if applicable.
- [ ] Message match: each Ads ad group points to a page whose H1 and hero repeat the ad's promise (use package or destination pages, or add `?utm_*` links to the home page).
- [ ] Run Lighthouse (mobile and desktop) on Home, a package and Contact: aim for 90+ in every category. Last run 20 Nov 2026 on the staging server (Home and a package, mobile and desktop: all 98 or higher, see the latest "Re-run" table below); Contact still to run.
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

### Re-run after the mega menus, partner slider, quiz card and Header & Footer snippets (Lighthouse 12, 19 Nov 2026)
Local build behind a gzip + long-cache proxy; home page and package `sample-tulip-season-self-drive`, mobile and desktop presets. Nothing was below 90, so no code fixes were needed.

| Page | Size | Performance | Accessibility | Best practices | SEO |
|---|---|---|---|---|---|
| Home | Mobile | 93 (LCP 2.8 s, TBT 140 ms, CLS 0) | 100 | 100 | 100 |
| Home | Desktop | 98 (LCP 1.0 s) | 100 | 100 | 100 |
| Package | Mobile | 95 (LCP 2.7 s, CLS 0) | 100 | 100 | 100 |
| Package | Desktop | 100 (LCP 0.7 s) | 100 | 100 | 100 |

The package SEO score was measured with the sample package's *Hide from search engines* switched off for the run (restored afterwards); with it on, "Page is blocked from indexing" gives 69 by design. Home mobile performance dropped from 94 to 93 since the previous run (mega menu markup and the logo slider); watch it if more sections are added above the fold. Custom code added in Admin > Header & Footer (analytics, chat widgets) is not part of these numbers and often costs 5-15 points: re-run Lighthouse after adding any.

### Re-run after the new logo, mega menus and Header & Footer (Lighthouse 12, 19 Nov 2026)
Local build behind a gzip + long-cache proxy; home page and package `sample-tulip-season-self-drive`, mobile and desktop presets. Nothing ended below 90.

| Page | Size | Performance | Accessibility | Best practices | SEO |
|---|---|---|---|---|---|
| Home | Mobile | 91 (LCP 2.9 s, TBT 200 ms, CLS 0) | 100 | 100 | 100 |
| Home | Desktop | 98 (LCP 1.1 s) | 100 | 100 | 100 |
| Package | Mobile | 95 (LCP 2.7 s, CLS 0) | 100 | 100 | 100 |
| Package | Desktop | 100 (LCP 0.6 s) | 100 | 100 | 100 |

What was fixed in this pass:
- **Accessibility 96-97 to 100 ("Links do not have a discernible name").** With the new two-version logo, the header logo link had no readable name while the dark-background version was showing (the colored image is `display:none` then, and the white-on-dark one is `alt=""`). The link now has `aria-label` = the brand name (`partials/header.blade.php`).
- **Mega menu thumbnails pulled the 960 px photos.** The small tour-type thumbnails in the Tour Types menu loaded the full 960 px upload (about 250 KB of wasted download on the home page, "Properly size images"). They now use the 480 px copy.

Watch list: home mobile performance has moved 94, 93, 91 over the last three runs (more markup above and below the fold: mega menus add about 600 DOM elements, plus the logo slider). It is still above 90 but has little margin. Before adding more sections to the home page, consider trimming the mega menus' DOM (render the panels only when first opened) or removing a lower section. One run showed 82 (Speed Index 12 s) when the machine was busy: three re-runs gave 91, 91, 91, so treat single outliers with suspicion and run three times. As before, custom code added in Admin > Header & Footer is not in these numbers.

### Re-run after the photo swap, new packages and featured-trip carousel (Lighthouse 12, 20 Nov 2026)
Local build behind a gzip + long-cache proxy, home page and package `tulip-route-self-drive` (a real, indexable package), mobile and desktop presets. Home mobile was run three times (95, 93, 94). Nothing is below 90.

| Page | Size | Performance | Accessibility | Best practices | SEO |
|---|---|---|---|---|---|
| Home | Mobile | 94 (LCP 2.6 s, TBT 150 ms, CLS 0) | 100 | 100 | 100 |
| Home | Desktop | 99 (LCP 0.9 s) | 100 | 100 | 100 |
| Package | Mobile | 92 (LCP 2.9 s, CLS 0) | 100 | 100 | 100 |
| Package | Desktop | 100 (LCP 0.8 s) | 100 | 100 | 100 |

What happened in this pass:
- **The first runs looked bad because of the test setup, not the site.** The proxy sent the page the address `127.0.0.1:8000`, so images, CSS and fonts bypassed gzip and caching, and the fonts were blocked by CORS (home mobile 86, best practices 96, "Enable text compression", console errors). After the proxy forwarded the original host header, the same code scored as in the table. When you re-run, make sure the asset URLs in the page point at the proxy, not at `php artisan serve`.
- **Uploaded photos now get a real `srcset`.** `components/picture.blade.php` offers the `-480` copy next to the `-960` copy for uploaded images, and the three tour-type card photos were re-compressed (about 20 percent smaller). Before this, phones downloaded the 960 px portrait photos for the home tour-type cards ("Properly size images", about 400 KiB).
- **The home hero is a photo only** (no video), so LCP stays near 2.6 s on throttled mobile.

Watch list:
- Tour-type photos now also come in a 720px size, and the tour-type card's `sizes` is `calc(100vw - 2rem)` on phones (it was `100vw`, which made a 412 px phone at 1.75x pick the 960 px file, up to 339 KB each). Image addresses carry `?v=<file modified time>` (`asset_v()` and `Media::url()`), so a browser or CDN never serves an old picture that reused its file name.
- Home DOM was about 2,770 elements (mega menus, quiz, partner slider); the country-code lists in the enquiry forms (37 flag images each, in 3 forms) now render only while open, which took the home page to about 2,440 elements, 84 `<img>` tags (was 180) and 325 KB of HTML (was 392 KB). (An offer band and a payments band were tried on the home page and removed again: the page is long enough.) Re-run Lighthouse on the live server before adding more sections.
- The Tour Types mega menu now holds a three-slide featured-trip carousel (Alpine; images are lazy-loaded inside the hidden panel and it only advances while the menu is open).
- Package mobile performance has about 2 points of margin. Keep package photos as 480, 960 and 1600 px copies, lazy-loaded.
- Minor notes that do not affect the scores: the logo SVG is sometimes flagged as offscreen, and the bundled JavaScript reports a tiny legacy-JavaScript saving.

### Re-run on the staging server (Lighthouse 12, 20 Nov 2026)
Staging site (Hostinger, behind the host's own gzip and caching), home page and package `tulip-route-self-drive`. Home mobile was run three times (98, 98, 99). Nothing is below 90, so nothing needed fixing.

| Page | Size | Performance | Accessibility | Best practices | SEO | LCP | Page size |
|---|---|---|---|---|---|---|---|
| Home | Mobile | 98-99 (TBT 40-70 ms, CLS 0) | 100 | 100 | 100 | 2.0-2.3 s | 649 KiB |
| Home | Desktop | 99 (CLS 0.022) | 100 | 100 | 100 | 0.6 s | 836 KiB |
| Package | Mobile | 98 (CLS 0) | 100 | 100 | 100 | 2.3 s | 353 KiB |
| Package | Desktop | 100 | 100 | 100 | 100 | 0.6 s | 508 KiB |

For comparison, on the same day a sister site (Go Belgium) scored 95 / 95 / 100 / 100 on mobile and 99 / 95 / 100 / 100 on desktop, and another (Go Germany) refused Lighthouse with a 403. Home has about 2,360 elements (Belgium about 1,650) and no failed requests.

Watch list:
- Lighthouse still lists about 177 KiB of oversized images on the home page: 100 KiB is a photo uploaded in the staging admin (`lihnvjfr0j7jnl-960.webp`, replace it with a smaller one, about 1200 px wide) and 77 KiB is the Self Drive card photo. It does not lower the score.
- The staging site says `index, follow`; switch it to noindex until the live domain is ready.
- Real phone number, real reviews (the cards still say "Sample review") and analytics IDs are still to be added; analytics scripts will cost points, so re-run after adding them.
