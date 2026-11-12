# Deployment (Hostinger)

Written for **Hostinger shared / Business hosting (hPanel)** because the plan type was not confirmed; a VPS appendix is at the end. Check PHP version (8.2+) and extensions in hPanel > Advanced > PHP Configuration (`mbstring, openssl, pdo_mysql, fileinfo, curl, gd, intl, zip`). Hostinger plans change: verify SSH and cron availability on your plan.

## 1. Build assets locally

Shared hosting usually has no Node. Build on your PC and upload the result:

```powershell
npm ci
npm run build          # creates public/build
```
`public/build` is git-ignored, so upload it (SFTP/File Manager), or commit it to a deploy branch.

## 2. Folder layout

Laravel's web root must be the `public` folder, and the rest of the app must NOT be web-accessible.

Preferred: put the project in `~/domains/yourdomain.com/laravel` and make `public_html` point at `laravel/public`:
```bash
cd ~/domains/yourdomain.com
rm -rf public_html            # only if it is the empty default
ln -s laravel/public public_html
```
If you cannot symlink, upload the contents of `public/` into `public_html`, move everything else to a folder **above** it, and edit `public_html/index.php` so the two `require` paths (`vendor/autoload.php`, `bootstrap/app.php`) and `maintenance.php` point to that folder.

## 3. Production `.env`

Create `.env` on the server (never commit it):
```
APP_NAME=GoNetherlandTours
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=...            # from hPanel > Databases
DB_USERNAME=...
DB_PASSWORD=...
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
MAIL_MAILER=smtp           # hPanel mailbox SMTP settings
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS="enquiries@yourdomain.com"
RECAPTCHA_SITE_KEY=...      # real v2 checkbox keys, NOT the test keys
RECAPTCHA_SECRET_KEY=...
GHL_API_TOKEN=
GHL_LOCATION_ID=
ADMIN_SEED_EMAIL=you@yourdomain.com
ADMIN_SEED_PASSWORD=a-long-unique-password   # remove from .env after first seed
```
Register the real domain for the reCAPTCHA keys at google.com/recaptcha/admin.

## 4. First deploy (SSH)

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate --force        # first time only
php artisan migrate --force --seed      # first time only (seed creates the admin and sample content)
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```
Later deploys: upload code + `public/build`, `composer install --no-dev -o`, `php artisan migrate --force`, then re-run the three `:cache` commands (or `php artisan optimize`). **Never** run `migrate:fresh` in production.

If `storage:link` is blocked, create the symlink in File Manager or ask Hostinger support. Folders `storage` and `bootstrap/cache` must be writable (755/775).

## 5. Queue worker and scheduler (emails + GoHighLevel sync)

Shared hosting cannot keep a worker running. The app schedules a short-lived worker every minute (`routes/console.php`), so a single cron entry is enough. hPanel > Advanced > Cron Jobs, every minute:
```
* * * * * cd /home/USER/domains/yourdomain.com/laravel && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```
(use the PHP binary that matches your selected version, e.g. `/opt/alt/php83/usr/bin/php`).

## 6. After go-live

1. Log in at `/admin`, change the password, fill **Settings** (company name, phone, email, GTM ID, notification emails, logo).
2. Replace sample data and every `[CONFIRM: ...]` (see CONTENT_TO_CONFIRM.md); get the legal pages reviewed.
3. Add GHL token and location ID to `.env`, `php artisan config:cache`, `php artisan ghl:test`, `php artisan ghl:setup`.
4. Submit a test enquiry on every form; check the email, admin inbox and GHL contact.
5. Submit `https://yourdomain.com/sitemap.xml` in Google Search Console. Work through SEO_CHECKLIST.md.
6. Force HTTPS in hPanel (SSL) so security headers and cookies behave.

## Appendix: VPS (Ubuntu + Nginx)

Same steps, but run a real worker under Supervisor instead of the cron-driven one:
```
[program:gnt-worker]
command=php /var/www/gonetherlandtours/artisan queue:work --sleep=3 --tries=5 --max-time=3600
autostart=true
autorestart=true
user=www-data
```
Point Nginx `root` at `.../public`, `try_files $uri $uri/ /index.php?$query_string;`, and keep the single cron `* * * * * php artisan schedule:run`.
