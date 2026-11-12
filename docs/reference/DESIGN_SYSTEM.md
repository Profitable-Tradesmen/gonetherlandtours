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
