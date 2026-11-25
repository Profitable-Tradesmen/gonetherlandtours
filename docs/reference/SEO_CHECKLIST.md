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
- [ ] Run Lighthouse (mobile and desktop) on Home, a package and Contact: aim for 90+ in every category. Latest run 24 Nov 2026 on the local build (live domain blocks Lighthouse with a 403, see the last "Re-run" section): Home and a package, mobile and desktop, all 92 or higher; staging run of 20 Nov 2026 was 98 or higher; Contact still to run.
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
- The Tour Types mega menu shows one featured trip card (the first featured package); the three-slide carousel it had before was removed to save two images and some script work.
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

### Re-run after the settings-lookup and caching fixes (staging, Lighthouse 12, 20 Nov 2026)
The home page used to run 248 database queries per visit (228 were one-by-one cache-table reads for site wording); it now runs 22 (`Setting::all_cached()` and `CodeSnippet::render()` keep their data for the rest of the request). Static SVG, JPEG and icon files are cached for 6 months. Home mobile was run 8 times: 98, 97, 88, 98, 99, 98, 98, 98. The 88 was a host hiccup (the HTML took 4.3 s to arrive, giving Speed Index 6.3 s and one layout shift of 0.138); it did not repeat, so re-run three times before trusting any single low number. Package mobile 98, home desktop 100, package desktop 100; accessibility, best practices and SEO 100 everywhere. Time to first byte measured with curl: about 0.57-0.59 s on staging against 0.75-0.85 s on the Go Belgium site.

### Full-site scan (staging, Lighthouse 12, 20 Nov 2026, all 29 sitemap pages, mobile and desktop)
Every page in the sitemap was run once on mobile and once on desktop, one after another. 25 of 29 pages scored 96 or higher on mobile and 97 or higher on desktop for performance; accessibility, best practices and SEO were 98 to 100 everywhere except one page.

| Page group | Mobile performance | Desktop performance |
|---|---|---|
| Home | 99 | 100 |
| About, Meet the team, FAQ, Testimonials, legal pages | 97-100 | 99-100 |
| Contact | 99 | 97 (Best Practices 78, see below) |
| Blog and its 3 articles | 96-99 | 98-100 |
| Destinations overview and 3 destination pages | **88**, 97, **89**, 96 | 99 |
| Tour type pages (Self Drive, Chauffeur, Coach) | **87**, 94, 91 | 99-100 |
| Packages overview and 9 package pages | 98-99 | 99-100 |

Found and fixed after the scan (not yet re-measured on the server):
- **Contact, desktop Best Practices 78:** the reCAPTCHA script loaded as soon as the form scrolled into view and set a third-party cookie (`_GRECAPTCHA`). It now loads only when someone touches the form (`enquiryForm` in `app.js`). Pages whose form is above the fold on desktop were affected; the Contact page was the only one.
- **Slow mobile LCP (3.8-3.9 s) on `/destinations`, Tulips & Countryside and Self Drive:**
  - `dest-tulips` was a tall portrait photo (467 KB, 307 KB for the 800 px copy) used as a wide banner; it is now a 3:2 landscape crop (149 KB and 84 KB).
  - The first destination card was lazy-loaded although it is the largest thing on screen on a phone; it now loads eagerly.
  - Tour type heroes (full-width on a phone) jumped from the 720 px to the 960 px file (340 KB for Self Drive); the three tour-type photos now also come in an 800 px copy (about 200 KB), which `components/picture.blade.php` offers.
- **`/packages` heading order:** package card titles are `h2` on the list page (they follow the `h1`), `h3` elsewhere (`headingLevel` in `partials/package-card.blade.php`).
- **Country picker label:** the accessible name said "United States" while the button shows "US"; it now says "Country code: US (+1)".

Watch list:
- Time to first byte is 0.55-0.75 s on every page; it is the largest part of every LCP.
- The tour type pages' LCP is the tour photo at full width; if they stay near 90, give those heroes their own landscape photo.
- Re-run the four pages below 90 (and Contact on desktop) after deploying these fixes.

### Re-scan after the speed fixes, and the tour type hero (staging, Lighthouse 12, 20 Nov 2026)
Contact desktop is back to 100 across the board (no third-party cookie before the form is touched). `/destinations` mobile went 88 to 95-97, Tulips & Countryside 89 to 96-97, Coach Tours 91 to 94-95 and Self Drive 87 to 92-93 (the weakest left, 2-3 points above 90). `/packages` accessibility is 100 (heading order fixed). No audit failed in any of the 26 runs.

Next change, not yet measured: the tour type pages now use a purpose-made landscape hero (`<name>-hero.webp`, 960x720, and `<name>-hero-800.webp` for phones) cut from the tall card photo, so a phone downloads about 90 KB (Self Drive), 49 KB (Chauffeur) and 62 KB (Coach) instead of 200, 76 and 171 KB. `tour-type-show.blade.php` picks the `-hero` copy automatically when it exists beside the `-960` file; a photo uploaded later without one falls back to the tall photo. The photo alt texts were also corrected to describe what is in the pictures.

### Re-run on the local build after the layout, banner and photo changes (Lighthouse 12, 23 Nov 2026)
**Not measured on the live domain.** `https://gonetherlandtours.com/` returns 403 to Lighthouse and PageSpeed requests (Hostinger's bot protection blocks automated Chrome; ordinary visitors and `curl` get 200). Google PageSpeed Insights also refused (shared API quota used up). So this run is the local build behind a gzip + long-cache proxy (a stand-in for Hostinger's Apache settings: plain `php artisan serve` sends no compression or caching and scored home mobile 70 and package mobile 79, which says nothing about the site). Home page (`/`) and package `tulip-route-self-drive`, mobile and desktop presets. **Nothing is below 90, so no score-driven fixes were needed.**

| Page | Size | Performance | Accessibility | Best practices | SEO | LCP | Page size |
|---|---|---|---|---|---|---|---|
| Home | Mobile | 91 (TBT 140 ms, CLS 0) | 100 | 100 | 100 | 2.6 s | 479 KiB |
| Home | Desktop | 99 | 100 | 100 | 100 | 0.9 s | 824 KiB |
| Package | Mobile | 92 (TBT 150 ms, CLS 0) | 100 | 100 | 100 | 2.9 s | 524 KiB |
| Package | Desktop | 100 | 100 | 100 | 100 | 0.8 s | 664 KiB |

To get real live numbers: run Lighthouse from Chrome DevTools in your own browser on the live URL, or ask Hostinger support to allow Lighthouse / PageSpeed on the domain. (Do not try to disguise the test browser to get past the block.)

What was fixed in this pass:
- **The three tour type cards, the Tour Types menu and the tour pages were using old admin uploads** (an aerial car, a Mercedes front, a minibus; 168 KB, 144 KB and 68 KB at 720 px) instead of the generated photos. Migration `tour_type_card_photos` now points them at `public/images/tour-<slug>-card.webp` (640x941, 54-82 KB) with a wide `tour-<slug>-hero.webp` (+ `-hero-800.webp`) for the page banner (`pages/tour-type-show.blade.php` also accepts a `-card.webp` name). Home mobile page weight dropped from 581 to 479 KiB.

Watch list:
- Home mobile has only about 1 point of margin (91): TBT 140-230 ms from the bundled `app.js` (about 57 percent unused on the home page) and a 2,347-element DOM (mega menus, quiz, partner slider). Do not add scripts, sliders or above-the-fold media without re-testing.
- The card photos were generated at 1024x637, so the tall 640 px crops are upscaled slightly; a larger export would look sharper without costing more bytes at this size.
- Lighthouse still lists about 100 KiB of "image delivery" savings (mostly extra compression on package covers and the home hero at -800); fine for now.
- Home mobile on the live host will differ (TTFB, the host's own gzip and cache rules); re-run from DevTools after deploying.

### Re-run after the new home page, real photos and simpler package cards (Lighthouse 12, 24 Nov 2026)
**Not measured on the live domain**: `https://gonetherlandtours.com/` still returns 403 to Lighthouse (Hostinger's bot protection). Local build behind a gzip + long-cache proxy (stand-in for Hostinger's Apache settings), home page (`/`, the new design) and package `tulip-route-self-drive`, mobile and desktop presets. **Nothing is below 90, so no fixes were needed.**

| Page | Size | Performance | Accessibility | Best practices | SEO | LCP | Page size |
|---|---|---|---|---|---|---|---|
| Home | Mobile | 97 (TBT 40 ms, CLS 0) | 100 | 100 | 100 | 2.5 s | 870 KiB |
| Home | Desktop | 99 | 100 | 100 | 100 | 0.9 s | 994 KiB |
| Package | Mobile | 92 (TBT 90 ms, CLS 0) | 100 | 100 | 100 | 2.9 s | 524 KiB |
| Package | Desktop | 100 | 100 | 100 | 100 | 0.8 s | 658 KiB |

Notes:
- Home mobile went from 91 (previous run) to 97: the reviews section is hidden (sample reviews) and the package cards on the home page are simpler, so less markup and script work. Page size rose from 479 to 870 KiB because the real stock photos replaced the lighter generated ones; they are lazy-loaded below the hero, so LCP is unchanged.
- Package mobile has about 2 points of margin (92). Its LCP is the cover photo; keep covers as 480/960/1600 copies.
- Live numbers will differ (TTFB, the host's own gzip and cache rules). Run Lighthouse from Chrome DevTools on the live URL after deploying.

### Re-run after the landmark icons in the home package cards (Lighthouse 12, 24 Nov 2026)
Same method as above (local build behind the gzip + long-cache proxy; the live domain still returns 403 to Lighthouse). **Nothing is below 90, so no fixes were needed.**

| Page | Size | Performance | Accessibility | Best practices | SEO | LCP | Page size |
|---|---|---|---|---|---|---|---|
| Home | Mobile | 95-96 (two warm runs; TBT 130-140 ms, CLS 0) | 100 | 100 | 100 | 2.5 s | 871 KiB |
| Home | Desktop | 99 | 100 | 100 | 100 | 0.9 s | 994 KiB |
| Package | Mobile | 90 (TBT 140 ms, CLS 0) | 100 | 100 | 100 | 2.9 s | 524 KiB |
| Package | Desktop | 100 | 100 | 100 | 100 | 0.8 s | 658 KiB |

Notes:
- The inline SVG landmark icons (about 1 KB each, six cards) did not change the score. Home mobile stays at 95-96.
- A first run right after `php artisan view:clear` scored home mobile 86: the first request recompiled every Blade view (TTFB 6.7 s). That is a test artifact, not a page problem. Warm the page with one request before auditing.
- Package mobile is at the edge (90-92 between runs, LCP 2.9 s is the cover photo). If it dips below 90 on the live host, first try smaller 480/960 cover copies or a lower quality for the cover.

### Re-run after the new home layout, gallery and new hero photo (Lighthouse 12, 25 Nov 2026)
Same method as above (local build behind the gzip + long-cache proxy; the live domain still returns 403 to Lighthouse). Home mobile first scored **85-86**, so it was fixed (see below); everything else was already at 90 or more.

| Page | Size | Performance | Accessibility | Best practices | SEO | LCP | Page size |
|---|---|---|---|---|---|---|---|
| Home | Mobile | 90-93 after the fix (was 85-86; TBT 180 ms, CLS 0) | 100 | 100 | 100 | 3.1 s | about 870 KiB |
| Home | Desktop | 98 | 100 | 100 | 100 | 1.1 s | 1152 KiB |
| Package | Mobile | 92 (TBT 140 ms, CLS 0) | 100 | 100 | 100 | 3.1 s | 524 KiB |
| Package | Desktop | 100 | 100 | 100 | 100 | 0.8 s | 658 KiB |

What was wrong and what was changed:
- **Cause:** on phones the home hero (photo plus quiz) is taller than the screen. Chrome does not count an image that covers the whole viewport as the "largest" element, so the 36 px logo became the LCP element and Lighthouse's estimate for it was 3.7 s (LCP score 0.58).
- **Fix 1:** the hero photo in `sections/hero_video_quiz.blade.php` is now 1 px narrower on each side (`inset-x-px`, `w-[calc(100%-2px)]`; invisible) so Chrome counts it as the LCP image again.
- **Fix 2:** the new Amsterdam hero photo was heavier than the old one (108 KB at 800 px). `images/hero-home-800.webp` is now 73 KB (quality 48) and `images/hero-home.webp` 267 KB (quality 74). Estimated LCP went from 3.7 s to 3.1 s.

Notes:
- Home mobile now has little margin (90-93 between runs). If it dips below 90 on the live host, make `hero-home-800.webp` smaller still, or add a 480 px copy for small phones.
- The first request after `php artisan view:clear` is slow because every Blade view is recompiled; warm the page with one request before auditing.
- Keep the hero photo slightly narrower than the screen if the hero layout changes again; a photo exactly as large as the screen is ignored as the LCP image.
