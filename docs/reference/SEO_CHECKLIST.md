# SEO and Google Ads pre-launch checklist

## Built in (verify, do not rebuild)
- [ ] Every page is server-rendered HTML with one H1, a unique title and meta description, and a canonical URL (covered by `SeoAndPagesTest`).
- [ ] Open Graph and Twitter cards; default share image set in Admin > Settings (replace `images/og-default.webp`, a placeholder).
- [ ] JSON-LD: TravelAgency (all pages), BreadcrumbList, FAQPage (home and packages with FAQs), TouristTrip (packages). Test at search.google.com/test/rich-results.
- [ ] `/sitemap.xml` (auto-generated, excludes noindex pages), `/robots.txt` blocks `/admin` and `/thank-you`.
- [ ] Thank You page is `noindex` and fires the conversion event only after a real submission.
- [ ] Images: WebP, width/height attributes, lazy loading below the fold, hero preloaded.
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
- [ ] Run Lighthouse (mobile) on Home, a package and Contact: aim for 90+ in every category.
- [ ] Test at 360, 768 and 1280 px: no horizontal scroll; keyboard-only walk-through; WCAG AA contrast (brand blue `#437DA5` is decorative; text/buttons use `#2F6285`).
- [ ] Check 404 page, `robots.txt`, `sitemap.xml` on production.

## Ads landing page principles used
One clear call to action (quote), click-to-call in the top bar and mobile action bar, trust badges above the fold, minimal navigation clutter, fast load, mobile-first quiz.
