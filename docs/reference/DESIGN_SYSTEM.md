# Design system

Everything visual is defined once in `resources/css/app.css` inside the `@theme` block (Tailwind v4 tokens). To re-skin for another brand, change that block (and the font imports at the top), the logo files `public/images/logo.webp` / `logo-2x.webp` (rendered by `resources/views/partials/logo.blade.php`; the supplied logo is navy and orange on white, so it sits on a white plate in the dark footer), and the images.

## Tokens (`@theme`)

| Token | Value | Use |
|---|---|---|
| `--color-brand` | `#437DA5` | Decorative blue (tints, progress). Contrast on white is 4.5:1 so it is NOT used for small text |
| `--color-brand-700` | `#2F6285` | Blue for text, links, secondary buttons (AA on white) |
| `--color-brand-900` | `#1F4660` | Hover, dark blue areas |
| `--color-accent` / `-600` | `#F2B743` / `#D99A1F` | Primary call-to-action, quiz border, highlights (dark text on it) |
| `--color-rust` | `#A54225` | Eyebrow labels, links on light backgrounds, errors accent |
| `--color-brown` | `#5A2D1A` | Dark sections (page headers, testimonials) |
| `--color-ink` | `#0C0D11` | Body text, top bar, footer |
| `--color-sand` / `-200` | `#F6F1EA` / `#E9DFD2` | Soft backgrounds and borders |
| `--font-display` | Playfair Display Variable | Headings (high-contrast serif, echoes the "onesalt" reference) |
| `--font-sans` | Montserrat Variable | Body, buttons, wide-caps labels (echoes the "ORÍMA" reference) |
| `--container-site`, `--radius-card`, `--shadow-card` | | Layout |

## Brand palette (from the supplied mood board)

The live palette is the five-colour set on the right of the mood board (Copenhagen waterfront photo), sampled from the image: steel blue `#437DA5`, gold `#F2B743`, rust `#A54225`, brown `#5A2D1A`, near-black `#0C0D11`. Everything else is a shade or tint of these plus the warm neutral sand (`#F6F1EA`). **Rule: use only the tokens above (Tailwind classes such as `bg-accent`), never other Tailwind colours (`emerald`, `red`, `amber`...) or raw hex values on public pages.** `tests/Feature/PaletteTest.php` fails if a public view or the CSS uses an off-palette colour. The standalone `errors/500` and `errors/503` pages cannot use the build, so they hard-code the same hex values.

The mood board also holds a second, four-colour Copenhagen set that is **not** applied: gold `#F6C463`, off-white `#F0F1EB`, navy `#012B53`, coral `#D24F45`. To switch to it, change only the `@theme` block (for example `--color-accent: #F6C463`, `--color-brand-*` from the navy, `--color-rust: #D24F45`, `--color-sand: #F0F1EB`), then check text contrast (WCAG AA) and run `npm run build`.

## Header, hero and cards
- **Transparent header:** on pages that start with a photo hero the header floats over it (white nav, logo on a white plate) and turns solid white with a shadow after 24px of scrolling or when a menu opens. CSS lives at the end of `app.css`, unlayered.
- **Hero:** a responsive photo (1600w + 800w WebP) with dark gradients for legibility; headline `lg:text-6xl`.
- **Package card:** image on top, chips for nights / tour type / destination, price, then "Get a Quote" + "More info".
- **Mega menus (Tours, About Us dropdown):** soft sand backdrop, white rounded cards with a subtle ring that lift 2px on hover, photo overlays, count/icon chips and small arrow buttons that turn gold on hover. The About dropdown shows the intro panel beside the cards from 1280px up and as a slim banner above a full-width card row below that. Keep new effects this quiet: no heavy shadows, gradients or motion.
- **Carousel:** native scroll-snap row with arrow buttons on desktop.

Use tokens as Tailwind classes: `bg-brand-700`, `text-rust`, `bg-accent`, `font-display`, `shadow-card`.

Fonts are self-hosted via Fontsource (`@fontsource-variable/*`), loaded with `font-display: swap`. To change fonts: `npm i @fontsource-variable/<font>`, swap the `@import` lines and the two `--font-*` tokens.

## Component classes (`@layer components`)
`.btn` + `.btn-primary | .btn-secondary | .btn-outline | .btn-block`, `.field`, `.field-label`, `.field-error`, `.label-caps` (wide uppercase label), `.container-site`, `.prose-site` (rich text), `.nojs-only` (no-JS fallbacks).

## Blade components (`resources/views/components/`)

| Component | Purpose |
|---|---|
| `<x-button :href variant block>` | Link or button styled as `.btn` |
| `<x-section-heading eyebrow title subtitle :center light>` | Section heading block |
| `<x-icon name>` | Inline SVG icon set (add a `@case` to add one; names are used by the quiz editor) |
| `<x-picture src|media alt width height priority>` | Responsive image with width/height, lazy loading, srcset for uploaded media |
| `<x-faq-item question>` | Native `<details>` accordion (works without JS) |
| `<x-modal title>` | Accessible dialog (focus trap, Esc, controlled by `$store.modal`) |
| `<x-form-field name label idp>` | Labelled input with inline error |
| `<x-enquiry-form form idp :package extra quiz-answers>` | THE enquiry form (consent, reCAPTCHA, honeypot) |
| `<x-quiz :steps>` | Hero quiz card |

Partials: `partials/topbar`, `header`, `footer`, `logo`, `package-card`. Layout: `layouts/app.blade.php`.

## Accessibility and motion
Visible focus ring (gold), skip link, labelled controls, `aria-invalid` and `role="alert"` errors, `prefers-reduced-motion` respected in `app.css`. Keep text contrast at AA: do not put small text in `--color-brand` or `--color-accent` on white.

## Re-skin checklist
1. Edit tokens and fonts in `app.css`.
2. Replace `public/images/logo.webp` + `logo-2x.webp` (or upload a logo in Admin > Settings).
3. Replace images in `public/images/` (same filenames) and update IMAGE_CREDITS.md.
4. Update `config/gnt.php`, the seeders (copy, FAQs, quiz) and `docs`.
5. `npm run build`, run tests, run Lighthouse.

## Layout inspired by goscottishtours.com

Same fonts (Playfair Display, Montserrat) and palette; only the layout changed.

- **Header:** transparent over the hero, solid navy (`brand-900`) once scrolled or on pages without a hero; white nav links; logo on a white plate. Menu: Home, Self Drive, Chauffeur Driven, Coach Tours (each a **tour type dropdown** that lists that tour type's packages automatically), About Us (image cards, includes Meet the Team). Menu item type "Tour type dropdown": set the link to `/tours/<slug>`.
- **Hero:** centered (label with a line and dot, headline, intro, the quiz in a framed card, trust row). The old two-column hero is still available via the section's Layout setting.
- **Home order:** hero, feature strip (4 round-icon points on navy), intro (photo + ticked list), travel-style cards (navy cards: photo, tag, summary, 3 bullets, More Info, Get a Quote, Ask Us a Question), offer/reassurance band, packages in one row per tour type, then the rest.
- **Tour type card text:** Admin > Tour types > "Small tag" and "bullet points" (one per line, first three shown).
- **Headings:** small label with a short line and a dot; italic emphasised words carry a thin rust underline.
- New section types: **Feature strip**, **Offer / reassurance band**. "Featured packages" has a Layout choice (one row per tour type, or tabs).
- New page **Meet the Team** (`/meet-the-team`, normal custom page).
- Migrations `2026_11_21_000001` (card fields) and `2026_11_21_000002` (home order, menu, Meet the Team page) only change a home page and menu that are still the seeded ones.
