#!/usr/bin/env bash
# Copies admin/developer uploads (storage/app/public, which git ignores) to the server over SSH.
# Run from your PC after adding or changing images:   npm run sync:media     (or: bash scripts/sync-media.sh)
# Options:  --dry-run  list what would be sent, send nothing    --target=live|staging  (default: live)
# Settings live in .deploy.env (git-ignored; copy .deploy.env.example). Needs ssh + tar (both ship with Git for Windows).
set -euo pipefail
cd "$(dirname "$0")/.."

TARGET=live; DRY=0
for a in "$@"; do case "$a" in --dry-run) DRY=1;; --target=*) TARGET="${a#--target=}";; esac; done

[ -f .deploy.env ] || { echo "Missing .deploy.env - copy .deploy.env.example and fill it in."; exit 1; }
# shellcheck disable=SC1091
source .deploy.env
T=$(echo "$TARGET" | tr '[:lower:]' '[:upper:]')
HOST_VAR="${T}_HOST"; PORT_VAR="${T}_PORT"; USER_VAR="${T}_USER"; PATH_VAR="${T}_PATH"; KEY_VAR="${T}_KEY"
HOST="${!HOST_VAR:-}"; PORT="${!PORT_VAR:-22}"; USR="${!USER_VAR:-}"; APP="${!PATH_VAR:-}"; KEY="${!KEY_VAR:-}"
[ -n "$HOST" ] && [ -n "$USR" ] && [ -n "$APP" ] || { echo "Set ${T}_HOST, ${T}_USER and ${T}_PATH in .deploy.env"; exit 1; }

SSH=(ssh -p "$PORT" -o StrictHostKeyChecking=accept-new)
[ -n "$KEY" ] && SSH+=(-i "${KEY/#\~/$HOME}")

SRC=storage/app/public
COUNT=$(find "$SRC" -type f ! -name .gitignore | wc -l | tr -d ' ')
echo "Uploads to send to $TARGET ($USR@$HOST:$APP/$SRC): $COUNT files"
if [ "$DRY" = 1 ]; then find "$SRC" -type f ! -name .gitignore | sed "s#^$SRC/##"; exit 0; fi

# tar over ssh: no rsync needed; existing files are overwritten, nothing on the server is deleted.
tar -C "$SRC" --exclude=.gitignore -cf - . | "${SSH[@]}" "$USR@$HOST" "mkdir -p '$APP/$SRC' && tar -C '$APP/$SRC' -xf - && find '$APP/$SRC' -type f -exec chmod 644 {} + && find '$APP/$SRC' -type d -exec chmod 755 {} +"
echo "Done. $COUNT files are on the server."
