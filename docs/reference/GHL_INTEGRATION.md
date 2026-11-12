# GoHighLevel (GHL) integration

Every enquiry is saved first, then a queued job upserts the contact in GHL (API v2, `services.leadconnectorhq.com`, Private Integration Token). It never blocks or fails the enquiry.

**The integration is OFF whenever `GHL_API_TOKEN` or `GHL_LOCATION_ID` is empty.** The token lives only in `.env` (never in code, git or the admin).

## Set up (one time)

1. In GHL open the sub-account: **Settings > Private Integrations > Create new integration**. Name it e.g. "GoNetherlandTours website".
2. Tick these scopes:
   - `contacts.readonly`
   - `contacts.write`
   - `locations/customFields.readonly`
   - `locations/customFields.write`
   - `locations.readonly` (used only by `ghl:test` to check the location)
3. Copy the token (shown once). The **Location ID** is under **Settings > Business Profile** (or in the sub-account URL).
4. Put them in `.env`:
   ```
   GHL_API_TOKEN=pit-xxxxxxxx
   GHL_LOCATION_ID=xxxxxxxx
   ```
5. `php artisan config:clear`, then:
   ```
   php artisan ghl:test     # checks token + location, upserts then deletes a "GHL TEST" contact
   php artisan ghl:setup    # creates every custom field below if missing (safe to re-run)
   ```

## What is sent

Contact: first name, last name, email (the upsert key), phone in E.164, source, and tags: brand name, `Source: <utm_source or direct>`, `Package: <name>` (if any), and the form name.

Custom fields (names are matched **exactly**; defined in one place, `app/Services/Ghl/GhlFields.php`):

| Group | Fields |
|---|---|
| Enquiry | Preferred Travel Date, Second Quote Preference, Special Request, Package, Form Name |
| Quiz | Quiz: Region, Quiz: Season, Quiz: Travel Style, Quiz: Group Size, Quiz: Nights, Quiz: Flights Booked, Quiz: Budget, Quiz: Likelihood (one per quiz step key), plus **Quiz Answers (Full)** (all answers as text) |
| Consent | Consent Given, Consent Timestamp, Consent Text Version |
| Tracking, latest touch | UTM Source / Medium / Campaign / Term / Content (Last), GCLID (Last), GBRAID (Last), WBRAID (Last), Referrer (Last), Landing Page URL (Last) |
| Tracking, first touch | the same ten, suffixed `(First)` |

Tracking is sent from **every** form (hero quiz, header modal, package quote, package question, contact page, which also covers tour-type enquiries) together with the **Form Name**.

**First vs latest touch:** first-touch values come from a 30-day cookie set on the visitor's first tracked visit. When a returning contact submits again, `(Last)` fields are updated and existing `(First)` values in GHL are **not** overwritten.

Quiz steps you add in the admin get a field named `Quiz: <Step Key>`; run `php artisan ghl:setup` again to create it.

## Behaviour

- Retries with backoff (1, 5, 15, 60 minutes; 5 tries) on the queue. With `QUEUE_CONNECTION=sync` it tries once.
- Status and the GHL contact ID are stored on the enquiry (**Admin > Enquiries > open an enquiry > GoHighLevel**) with a **Retry sync** button.
- Field IDs are cached for an hour. Logs contain error messages only, never the token.
- Tests blank the GHL env values in `phpunit.xml` and block all real HTTP calls.

## Notes for staff

- You may move the custom fields into folders in GHL (the API cannot create folders).
- **Do not rename fields.** If you must, rename it in GHL **and** in `GhlFields.php` together, then clear the cache (`php artisan cache:clear`).
