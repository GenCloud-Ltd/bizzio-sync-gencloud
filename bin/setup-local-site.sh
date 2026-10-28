#!/usr/bin/env bash
# Prepare a fresh LocalWP site as the QA/test site for the free plugin.
#
#   DB_PASSWORD=... bin/setup-local-site.sh <site-public-dir> <site-url>
#   e.g. DB_PASSWORD=... bin/setup-local-site.sh "D:/LocalWP-bizzio-free/app/public" http://bizzio-free.local
#
# Points the site at the shared external MariaDB (own table prefix), installs the latest
# WordPress + WooCommerce + Plugin Check, removes everything else, links the plugin from
# this repo via a junction and writes wp-cli.local.yml so `wp` / `composer plugin-check`
# from the repo root target this site.
set -euo pipefail

SITE="${1:?site public dir required (…/app/public)}"
URL="${2:?site url required (http://xxx.local)}"
DB_HOST="${DB_HOST:-192.168.1.93:3306}"
DB_NAME="${DB_NAME:-local}"
DB_USER="${DB_USER:-localwp}"
DB_PASSWORD="${DB_PASSWORD:?DB_PASSWORD env var required}"
DB_PREFIX="${DB_PREFIX:-bzfree_}"
SLUG="bizzio-sync-for-woocommerce"

REPO="$(cd "$(dirname "$0")/.." && pwd)"
wp() { command wp --path="$SITE" "$@"; }

echo "==> wp-config: external DB ${DB_HOST}/${DB_NAME}, prefix ${DB_PREFIX}"
wp config set DB_HOST "$DB_HOST"
wp config set DB_NAME "$DB_NAME"
wp config set DB_USER "$DB_USER"
wp config set DB_PASSWORD "$DB_PASSWORD" --quiet
wp config set table_prefix "$DB_PREFIX" --type=variable
wp config set WP_DEBUG true --raw
wp config set WP_DEBUG_LOG true --raw
wp config set WP_DEBUG_DISPLAY false --raw
wp config set SCRIPT_DEBUG true --raw
wp config set WP_ENVIRONMENT_TYPE local

if ! wp core is-installed 2>/dev/null; then
	echo "==> Installing WordPress into prefix ${DB_PREFIX}"
	wp core install --url="$URL" --title="Bizzio Free QA" \
		--admin_user=admin --admin_password=admin --admin_email=admin@example.test --skip-email
fi

echo "==> Updating core to latest"
wp core update
wp core update-db
wp language core install bg_BG || true

echo "==> Plugins: WooCommerce + Plugin Check only"
wp plugin delete akismet hello 2>/dev/null || true
wp plugin install woocommerce plugin-check --activate
wp plugin update woocommerce plugin-check
wp option update woocommerce_onboarding_profile '{"skipped":true}' --format=json
wp option update woocommerce_coming_soon no
wp option update woocommerce_currency BGN

echo "==> Themes: keep only the latest default theme"
DEFAULT_THEME="$(wp theme list --field=name --status=active)"
for theme in $(wp theme list --field=name --status=inactive); do
	wp theme delete "$theme"
done
wp theme update --all
echo "    active theme: ${DEFAULT_THEME}"

echo "==> mu-plugin: block WP.org updates of the junctioned plugin (would overwrite the repo)"
MU_DIR="$(wp eval 'echo WPMU_PLUGIN_DIR;')"
mkdir -p "$MU_DIR"
cp "$REPO/bin/mu-plugins/bizzio-qa-no-self-update.php" "$MU_DIR/"

echo "==> Linking ${SLUG} from repo (junction)"
PLUGINS_DIR="$(wp eval 'echo WP_PLUGIN_DIR;')"
TARGET="${PLUGINS_DIR}/${SLUG}"
if [ ! -e "$TARGET" ]; then
	powershell -NoProfile -Command "New-Item -ItemType Junction -Path '$(cygpath -w "$TARGET")' -Target '$(cygpath -w "$REPO/$SLUG")' | Out-Null"
fi
wp plugin activate "$SLUG"

echo "==> Writing ${REPO}/wp-cli.local.yml"
printf 'path: %s\n' "$SITE" > "$REPO/wp-cli.local.yml"

wp core version --extra
wp plugin list
