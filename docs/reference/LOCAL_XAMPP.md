# Running locally on XAMPP (Windows)

Verified on: XAMPP with PHP 8.4.27 (`C:\xampp`), MariaDB 10.11, Node 26, Composer 2.11.

## 1. Requirements

| Need | Notes |
|---|---|
| PHP 8.2+ | Laravel 12 needs 8.2 or newer. XAMPP here ships 8.4. |
| php.ini extensions | `mbstring, openssl, pdo_mysql, fileinfo, curl, gd (with WebP), intl, zip`. Enable in `C:\xampp\php\php.ini` by removing the `;` before `extension=...`, then restart Apache. Check with `C:\xampp\php\php.exe -m`. |
| Composer 2.x | Old 2.4 prints errors on PHP 8.4. Run `composer self-update`, or download a fresh `composer.phar` from getcomposer.org. |
| Node 20+ and npm | Only for building CSS/JS. |
| MySQL/MariaDB | Start it in the XAMPP Control Panel. |

Add PHP to your PATH for the session: `$env:Path = 'C:\xampp\php;' + $env:Path`

## 2. Database

In phpMyAdmin (http://localhost/phpmyadmin) or the MySQL console:

```sql
CREATE DATABASE gonetherlandtours CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

XAMPP defaults (`root`, empty password, `127.0.0.1:3306`) already match `.env.example`. For a dedicated user instead:

```sql
CREATE USER 'gnt'@'localhost' IDENTIFIED BY 'choose-a-password';
GRANT ALL ON gonetherlandtours.* TO 'gnt'@'localhost';
```

then set `DB_USERNAME` / `DB_PASSWORD` in `.env`.

## 3. Install and run (the exact sequence)

```powershell
cd "C:\Users\Tonix\Pictures\Work DG Ventures\DG Website Creation\gonetherlandtours"
copy .env.example .env            # then set ADMIN_SEED_EMAIL and ADMIN_SEED_PASSWORD
composer install
npm install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build                     # or: npm run dev  (live reload while editing)
```

`migrate --seed` creates the tables, sample content, the quiz and the first admin from `ADMIN_SEED_EMAIL` / `ADMIN_SEED_PASSWORD`.

## 4. Viewing the site

### a) `php artisan serve` (simplest)

```powershell
php artisan serve        # http://127.0.0.1:8000
```

If it fails on Windows, use the fallback: `php -S 127.0.0.1:8000 -t public vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php`

Set `APP_URL=http://127.0.0.1:8000` in `.env` while using this option.

### b) Apache virtual host: `http://gonetherlandtours.test`

1. `C:\Windows\System32\drivers\etc\hosts` (edit as Administrator), add:
   ```
   127.0.0.1  gonetherlandtours.test
   ```
2. `C:\xampp\apache\conf\extra\httpd-vhosts.conf`, add:
   ```apache
   <VirtualHost *:80>
       ServerName gonetherlandtours.test
       DocumentRoot "C:/Users/Tonix/Pictures/Work DG Ventures/DG Website Creation/gonetherlandtours/public"
       <Directory "C:/Users/Tonix/Pictures/Work DG Ventures/DG Website Creation/gonetherlandtours/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
3. Make sure `C:\xampp\apache\conf\httpd.conf` contains `Include conf/extra/httpd-vhosts.conf` and `LoadModule rewrite_module modules/mod_rewrite.so` (not commented out).
4. Restart Apache. Set `APP_URL=http://gonetherlandtours.test`.

## 5. Local mail, reCAPTCHA, queue

- **Mail**: `MAIL_MAILER=log` writes emails to `storage/logs/laravel.log`. For a real inbox UI install Mailpit and set `MAIL_MAILER=smtp`, `MAIL_HOST=127.0.0.1`, `MAIL_PORT=1025`. Emails only go to the addresses in **Admin > Settings > Staff notification emails**.
- **reCAPTCHA**: `.env.example` contains Google's published *test* keys, so forms always pass locally. Never use them in production.
- **Queue**: `QUEUE_CONNECTION=sync` runs emails and the GHL sync immediately inside the request. See DEPLOYMENT.md for production.
- **GHL**: leave `GHL_API_TOKEN` and `GHL_LOCATION_ID` blank locally unless you want to test against a sandbox sub-account.

## 6. Tests

```powershell
php artisan test
```
Uses in-memory SQLite (needs the `pdo_sqlite` extension, enabled by default in XAMPP). Tests are blocked from making any real HTTP calls.

## 7. Troubleshooting

| Problem | Fix |
|---|---|
| Port 80 in use (Apache won't start) | Skype/IIS/World Wide Web Publishing Service. Stop it, or change Apache's `Listen` port and add the port to the URL. |
| `could not find driver` / missing extension | Enable the extension in php.ini (section 1), restart Apache, and make sure the CLI uses the same php.ini: `php --ini`. |
| `The stream or file ... laravel.log could not be opened` | Make `storage` and `bootstrap/cache` writable (right-click > Properties > Security) or run the terminal as your normal user. |
| 419 Page Expired | Stale CSRF token: refresh the page. Also check `APP_URL` matches the URL you browse and `SESSION_DOMAIN=null`. |
| Images 404 under `/storage/...` | Run `php artisan storage:link` (re-run if you moved the folder). |
| Styles missing | Run `npm run build` (or keep `npm run dev` running). |
| `SQLSTATE[HY000] [2002]` | MySQL is not running. Start it in the XAMPP Control Panel. |
| Blank page after changing `.env` | `php artisan config:clear` |
