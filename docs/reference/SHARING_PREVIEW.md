# Sharing a preview of the site

**GitHub cannot host this site.** GitHub Pages only serves static files; this is a Laravel app that needs PHP and MySQL. Pushing to GitHub stores the code. To let someone *see* it you need one of the two routes below.

| | A. Quick tunnel from your PC | B. Staging site on Hostinger |
|---|---|---|
| Best for | Showing a colleague/client right now, for an hour or a day | A stable link the team can use for days or weeks |
| Needs | Your PC on, XAMPP + `php artisan serve` running | A subdomain, a database, Git or SFTP |
| Effort | 5 minutes | 30 to 45 minutes first time, then a few minutes per update |
| URL | Random `https://...trycloudflare.com`, changes each time | `https://preview.yourdomain.com` |

Do the **safety checklist** (section 3) for either route.

---

## 1. Before you push to GitHub (once)

1. Make the repository **private** (GitHub > repo > Settings > General > Danger Zone > Change visibility). The seeders contain sample copy and the project docs describe your setup.
2. Confirm secrets are not committed: `.env` is git-ignored (check with `git check-ignore .env`; it should print `.env`). Never paste the GHL token, reCAPTCHA secret or admin password into any file that is committed.
3. Push:
   ```powershell
   git remote add origin https://github.com/YOUR-ORG/gonetherlandtours.git   # first time only
   git push -u origin main
   ```
4. Share the repository with teammates who need the code (repo > Settings > Collaborators). That gives them the code, not a running site.

---

## 2A. Quick tunnel from your PC (Cloudflare Tunnel)

Free, no account needed for a temporary link.

1. Install once: `winget install --id Cloudflare.cloudflared` (or download from developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/).
2. Start the site: `php artisan serve` (it listens on `http://127.0.0.1:8000`). MySQL must be running in XAMPP.
3. In a second terminal: `cloudflared tunnel --url http://127.0.0.1:8000`
   It prints a link like `https://random-words.trycloudflare.com`. Send that link.
4. Edit `.env` so the site generates correct links, then clear the config cache:
   ```
   APP_URL=https://random-words.trycloudflare.com
   SITE_NOINDEX=true
   ```
   ```powershell
   php artisan config:clear
   ```
   (Without the `APP_URL` change, links, canonical tags and the sitemap point at `127.0.0.1`.)
5. Stop sharing: press `Ctrl+C` in the tunnel terminal. The link dies immediately. Put `APP_URL` back to `http://127.0.0.1:8000` when you are done.

Notes:
- The link works only while your PC, `artisan serve` and the tunnel are all running.
- Anyone with the link can use the site. Add the protections in section 3 if it is sensitive, or only share it with people you trust.
- ngrok works the same way (`ngrok http 8000`) but needs a free account and token.
- Visitors can submit the forms: with the Google **test** reCAPTCHA keys they pass automatically, and enquiries are saved to **your local database**.

---

## 2B. Staging site on Hostinger (recommended for the team and clients)

Use a separate subdomain so it never touches the live site.

### Set up (first time)
1. hPanel > **Domains > Subdomains**: create `preview.yourdomain.com`. Note its folder.
2. hPanel > **Databases**: create a **new, separate** database and user for the preview (do not reuse the live one).
3. hPanel > **Advanced > PHP Configuration**: PHP 8.2 or newer, extensions `mbstring, openssl, pdo_mysql, fileinfo, curl, gd, intl, zip`.
4. Put the project on the server in a folder **outside** the web root, and make the subdomain's web root point at the project's `public` folder (see DEPLOYMENT.md section 2).
5. Get the code there, either way:
   - **Git (hPanel > Advanced > Git)**: add your private repo (use a GitHub *deploy key* or token with read-only access), branch `main`, target folder as above, then press **Deploy**. You can also tick auto-deploy via the webhook so each push updates the preview.
   - **SSH**: `git clone <repo> laravel` once, then `git pull` for each update.
6. Create the server `.env` (never commit it). Use these preview values:
   ```
   APP_NAME=GoNetherlandTours
   APP_ENV=staging
   APP_DEBUG=false
   APP_URL=https://preview.yourdomain.com
   SITE_NOINDEX=true

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_DATABASE=...        # the preview database
   DB_USERNAME=...
   DB_PASSWORD=...

   SESSION_DRIVER=database
   CACHE_STORE=database
   QUEUE_CONNECTION=sync           # no cron needed for a preview
   MAIL_MAILER=log                 # no real emails from a preview

   RECAPTCHA_SITE_KEY=6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI    # Google TEST keys
   RECAPTCHA_SECRET_KEY=6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe

   GHL_API_TOKEN=          # leave EMPTY so test enquiries never reach the CRM
   GHL_LOCATION_ID=

   ADMIN_SEED_EMAIL=you@yourdomain.com
   ADMIN_SEED_PASSWORD=a-long-unique-password
   ```
7. Run once over SSH in the project folder:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan key:generate --force
   php artisan migrate --force --seed
   php artisan storage:link
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```

### The CSS/JS problem (`public/build`)
`public/build` is git-ignored, so a Git pull does **not** include the compiled CSS/JS and the preview will look unstyled. Pick one:

- **Simplest:** build locally (`npm run build`) and upload the `public/build` folder to the server with hPanel File Manager or SFTP after each change that touches CSS/JS/Blade classes.
- **Automatic (optional, untested here):** a GitHub Action builds the assets and pushes them to a `deploy` branch that Hostinger pulls. Save as `.github/workflows/build-deploy.yml`:
  ```yaml
  name: Build deploy branch
  on: { push: { branches: [main] } }
  permissions: { contents: write }
  jobs:
    build:
      runs-on: ubuntu-latest
      steps:
        - uses: actions/checkout@v4
        - uses: actions/setup-node@v4
          with: { node-version: 22, cache: npm }
        - run: npm ci && npm run build
        - run: |
            git config user.name "build-bot" && git config user.email "bot@users.noreply.github.com"
            git checkout -B deploy
            git add -f public/build
            git commit -m "Build assets" || true
            git push -f origin deploy
  ```
  Then point Hostinger's Git deployment at the `deploy` branch.

### Updating the preview after more pushes
```bash
git pull                                   # or press Deploy in hPanel
composer install --no-dev -o               # only if composer.json changed
php artisan migrate --force                # only if there are new migrations
php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
```
Upload a fresh `public/build` if CSS/JS changed.

---

## 3. Safety checklist for any shared preview

- [ ] `SITE_NOINDEX=true` in the preview `.env`. This adds `noindex` to every page, an `X-Robots-Tag` header and a `Disallow: /` robots.txt. Verify: open `https://preview.../robots.txt`. **Remove it (set to `false`) on the live site.**
- [ ] **Password-protect the preview** if the content is not public. In hPanel use **Advanced > Password Protect Directories** on the preview folder, or add basic auth to `public/.htaccess` (create the password file with the hPanel tool). Share the username and password with reviewers separately.
- [ ] `GHL_API_TOKEN` and `GHL_LOCATION_ID` **empty** on the preview, so test enquiries do not create real contacts. (They show in Admin > Enquiries with GHL "off".)
- [ ] Mail is `log` (or `staff notification emails` blank) so no real emails go out.
- [ ] Google test reCAPTCHA keys (they show a "for testing purposes only" notice) or real keys registered for the preview domain.
- [ ] GTM ID **blank** in Admin > Settings so preview traffic and test leads do not hit your analytics or Google Ads.
- [ ] Do not give reviewers the admin login unless they need it. Make them their own admin account (Admin > Admin users) and delete it afterwards.
- [ ] Remember the content is still **sample/placeholder**: packages are labelled samples, images are placeholders and legal pages are unreviewed templates (see CONTENT_TO_CONFIRM.md). Tell reviewers so.
- [ ] When finished, delete the subdomain (or the tunnel) so the preview does not linger.

## 4. What to tell reviewers
- The link and (if used) the basic-auth username/password.
- "Test enquiries are fine; nothing is sent to the CRM or by email."
- How to give feedback (e.g. screenshots with the page URL and device).
- To check at phone width too, since most Ads traffic is mobile.
