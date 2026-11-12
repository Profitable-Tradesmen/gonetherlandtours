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
Tour types (Self Drive, Chauffeur Driven, Coach Tours) appear on the home page, the menu and package filters. Each active destination gets its own page (`/destinations/name`). You cannot delete one that still has packages.

## Quiz
The hero quiz is built from these steps. Each step has a question, a type (**Tiles** advance when clicked; **Dropdown** has a Next button) and options (label, saved value, icon). Use the arrows to reorder, **Active/Off** to switch a step off, **Edit** to change options. The last "contact details" step is automatic. Avoid changing a step's **key** after leads exist; new steps need `php artisan ghl:setup` to create their GHL field.

## Pages
Edit the **Home** page text, **About**, **Contact**, **Terms and Conditions** and **Privacy Policy** (rich text editor). Each has an SEO title, meta description and a noindex switch. Terms/Privacy have a **Version**: raise it whenever you change the text (it is saved with each enquiry). Type `[Company Name]` in legal text to insert the company name. **The legal pages are starter templates, not legal advice. Get them reviewed.**

## FAQs and Testimonials
FAQs on the home page also feed Google's FAQ rich results. Only publish **real** reviews you have permission to use, and untick *placeholder* for them.

## Media
Upload images (resized and converted to WebP automatically). Alt text is required.

## Settings
Brand name, **legal company name** (used in the consent wording), tagline, phone (click-to-call), email, address, social links, **staff notification emails**, the discount shown in the modal headline, **Google Tag Manager ID**, reCAPTCHA keys, default SEO, logo, favicon and social image. GoHighLevel shows ON/OFF here; its token is set in the `.env` file by a developer.

## Admin users
Add or remove people who can log in. You cannot delete yourself or the last admin. Leave the password blank when editing to keep it. Use long unique passwords.
