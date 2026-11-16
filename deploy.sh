#!/usr/bin/env bash
# Run ON THE SERVER from the project folder (or via the GitHub Action). Updates the checked-out branch and refreshes Laravel.
# public/build is committed (the server has no Node), so run `npm run build` locally and commit it before pushing.
set -euo pipefail
cd "$(dirname "$0")"

PHP="${PHP_BIN:-/opt/alt/php84/usr/bin/php}"   # Hostinger CLI PHP 8.4 (the default `php` is older)

git pull --ff-only
"$PHP" "$(command -v composer)" install --no-dev --optimize-autoloader --no-interaction
"$PHP" artisan migrate --force
"$PHP" artisan optimize:clear
"$PHP" artisan config:cache
"$PHP" artisan view:cache
# route:cache is intentionally skipped: routes/web.php uses closures, which cannot be cached.
echo "Deployed $(git rev-parse --short HEAD) on $(git branch --show-current)"
