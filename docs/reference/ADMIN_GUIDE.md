# Admin guide (for non-developers)

Log in at **/admin** with the email and password from your setup. The menu is on the left (the menu icon on a phone). Click **View site** to see changes. Changes are live as soon as you press Save.

## Enquiries
Every form submission lands here. Use search, **Status**, **Source** and date filters. Open an enquiry to see their details, quiz answers, the **consent evidence** (what they agreed to, when, from which IP) and tracking info. Set the status (New, Contacted, Quoted, Won, Lost) and add internal notes. **Export CSV** downloads the filtered list. The **GoHighLevel** box shows whether the lead reached the CRM; press **Retry sync** if it failed. Do not delete enquiries you may need as evidence.

## Packages
**New package**: title, tour type, destination, nights, "from" price (leave blank to show `[CONFIRM: price]`), summary, description, highlights/included/not included (one per line), day-by-day itinerary, FAQs, gallery images and SEO. Notes:
- **Published** makes it visible; drafts show a 404 to visitors. **Featured** puts it on the home page.
- **Gallery**: upload JPG/PNG/WebP. **Alt text is required** (describe the photo). The first image is the cover. Change the **Order** number to reorder, tick **Remove** to delete.
- Untick **Hide from search engines** only when the content is final (sample packages are hidden by default).
- On the list: arrows reorder, **Duplicate** copies a package as a draft.

## Tour types and Destinations
The **Destinations** page (`/destinations`, linked in the header) lists every active destination as a photo card; its heading and intro are in Settings. In a package's **Day-by-day itinerary** you can pick, for each day, one of that package's own gallery photos to show beside the day (upload the photos in *Gallery images* first, save, then choose them).

Tour types (Self Drive, Chauffeur Driven, Coach Tours) appear on the home page, the menu and package filters, and each active one has its own landing page (`/tours/slug`) with an editable heading, content, SEO title and meta description. Each active destination gets its own page (`/destinations/name`). You cannot delete one that still has packages.

## Quiz
The hero quiz is built from these steps. Each step has a question, a type (**Tiles** advance when clicked; **Dropdown**, **Calendar date picker** and **Text box** have a Next button; a step with key `travel_date` is saved as the preferred travel date). The pop-up form reads its dropdown options (travellers, travel style, nights, budget) from the steps with those keys, so editing them here updates both forms and options (label, saved value, icon). Use the arrows to reorder, **Active/Off** to switch a step off, **Edit** to change options. The last "contact details" step is automatic. Avoid changing a step's **key** after leads exist; new steps need `php artisan ghl:setup` to create their GHL field.

## Pages
Every page is built from **sections**. Open **Admin > Pages**, choose a page, and you see its sections in the order visitors see them:

- **Edit** opens a form for that section: headings, text, buttons, photos (upload and describe each photo), and lists such as cards, steps, badges or gallery photos (use **+ Add**, the arrows and **Remove**). In headings, wrap a word in asterisks (`*word*`) to show it in the italic accent colour. A button link left blank opens the quote pop-up.
- **Arrows** reorder sections, **Hide / Show** takes a section off the page without deleting it, **Delete** removes it.
- **Add a section** picks a type (hero, text, photo band, cards, steps, testimonials, FAQ, gallery, team, contact form...). Each type has a short description.
- **Add page** (top of the Pages list) creates a new page at `/page-name`. It starts as a **draft**: build its sections, click the green **Publish this page** button at the top of the page editor (it turns into a green "LIVE" box). Draft pages are not public and not in the sitemap. Built-in pages (Home, About, Contact, FAQ, Testimonials, Terms, Privacy) keep their addresses and cannot be deleted.
- Each page has an SEO title, meta description and a *hide from search engines* switch. Terms and Privacy have a **version**: raise it when you change the legal text (it is stored with each enquiry).
- Packages, destinations, tour types, FAQs, testimonials and blog posts are managed in their own menu items; sections such as the FAQ list and testimonials show them automatically.

Section styles worth knowing: **Numbered steps** can be a connected timeline or simple columns; **Testimonials** can be dark cards, light cards or a one-at-a-time *spotlight* with a picker; **Hero** sections show a photo (upload yours in the section; the video plays over it on desktop if one has been uploaded to the server).

The **Selling points with proof** section shows each selling point with a real example ("In practice") and a link. Replace the `[CONFIRM: ...]` examples with facts you can prove. The **Why choose us strip** section shows your four selling points; their wording is set once in **Settings > Why choose us**, and the same strip appears automatically on package, tour type and destination pages. Each **tour type** has a *Travel style note* (for example "Private: just your own party") shown on its pages and on its packages.

## Navigation (administrators only)
**Admin > Navigation** controls the header menu and the footer links. Add a *Normal link* (a page path like `/about`, a full `https://` address or `#section`), the automatic *Tours menu* (built from Tour types), or a *Dropdown of cards*: create the dropdown, then add cards by choosing it as the parent (each card has a title, short description, link and image). The arrows reorder items within their menu. The wording around the menus (top bar, buttons, mobile menu, footer headings) is in **Settings**.

## FAQs and Testimonials
FAQs on the home page also feed Google's FAQ rich results. Only publish **real** reviews you have permission to use, and untick *placeholder* for them.

## Media
Upload images (resized and converted to WebP automatically). Alt text is required.

## Settings
Besides the brand details below, Settings holds the **wording used around the site** (top bar, header and menu buttons, mobile menu and bar, quote pop-up, quiz title, thank-you page, footer headings): edit the text, or clear a box to hide that element. Brand name, **legal company name** (used in the consent wording), tagline, phone (click-to-call), email, address, social links, **staff notification emails**, the discount shown in the modal headline, **Google Tag Manager ID**, reCAPTCHA keys, default SEO, logo, favicon and social image. GoHighLevel has its own page (below).

## GoHighLevel (administrators only)
Connect the CRM: paste the **Location ID** and **Private Integration token**, tick **Send enquiries to GoHighLevel**, **Save**, then **Test connection** (read-only) and **Create missing custom fields** (safe to repeat). The token is stored encrypted and only its last 4 characters are shown. Untick the box to pause the sync without losing credentials. New quiz steps need **Create missing custom fields** again.

## Admin users
Add or remove people who can log in. You cannot delete yourself or the last admin. Leave the password blank when editing to keep it. Use long unique passwords.

## Tour type pages (Self Drive, Chauffeur Driven, Coach Tours)

The hero (heading, summary, "how you travel" note) of `/tours/<name>` is edited in **Admin > Tour types**. Everything under the hero is built from sections, edited in **Admin > Pages > "<Tour type> page"** exactly like the home page: value strip, intro with photo and ticked list, the packages of that tour type (a carousel), why-us strip, offer band, reviews, three steps and a closing call to action. You can reorder, hide, add or remove sections there. If you delete a tour type's page, the older fixed layout is used until it is recreated (deploy runs `migrate`, or use the seeder). Text marked `[CONFIRM: ...]` needs your real facts (support hours, coach group size).
