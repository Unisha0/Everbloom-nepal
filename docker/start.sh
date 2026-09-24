#!/bin/bash
# Runs as the container's CMD. On first boot it installs WordPress,
# activates WooCommerce/theme/plugin, and imports the catalog; on every
# later boot (redeploys, restarts) it's a no-op past the seeding steps and
# just starts Apache, so it's safe to run unconditionally.
set -euo pipefail

WP="wp --path=/var/www/html --allow-root"

# The base wordpress:php8.3-apache image's own docker-entrypoint.sh only
# seeds /var/www/html and generates wp-config.php when CMD's argv[0] matches
# apache2*/php-fpm — ours doesn't, so that logic never runs and we have to
# do both steps ourselves.
if [ ! -e /var/www/html/index.php ]; then
  echo "everbloom: seeding WordPress core files..."
  cp -a /usr/src/wordpress/. /var/www/html/
fi

if [ ! -s /var/www/html/wp-config.php ]; then
  echo "everbloom: generating wp-config.php..."
  $WP config create \
    --dbname="${WORDPRESS_DB_NAME:?WORDPRESS_DB_NAME env var is required}" \
    --dbuser="${WORDPRESS_DB_USER:?WORDPRESS_DB_USER env var is required}" \
    --dbpass="${WORDPRESS_DB_PASSWORD:?WORDPRESS_DB_PASSWORD env var is required}" \
    --dbhost="${WORDPRESS_DB_HOST:?WORDPRESS_DB_HOST env var is required}" \
    --skip-check
fi

echo "everbloom: waiting for database..."
attempt=0
# wp db check shells out to mariadb-check, which on this image defaults to
# verifying TLS certs and fails against Railway's self-signed one. Check
# with a direct mysqli connection instead (matches how wp-cli/WordPress
# itself talk to the DB — plain, no SSL by default) to sidestep that.
DB_CHECK_PHP='mysqli_report(MYSQLI_REPORT_OFF); $l = @mysqli_connect(getenv("WORDPRESS_DB_HOST"), getenv("WORDPRESS_DB_USER"), getenv("WORDPRESS_DB_PASSWORD")); if (!$l) { fwrite(STDERR, mysqli_connect_error() . PHP_EOL); exit(1); } exit(0);'
until wp eval --skip-wordpress --allow-root "$DB_CHECK_PHP" 2>/tmp/db-check.err; do
  attempt=$((attempt + 1))
  if [ "$attempt" -eq 5 ] || [ $((attempt % 15)) -eq 0 ]; then
    echo "everbloom: still waiting (attempt $attempt), last error:"
    cat /tmp/db-check.err
  fi
  sleep 2
done
echo "everbloom: database is up"

if ! $WP core is-installed >/dev/null 2>&1; then
  echo "everbloom: installing WordPress..."
  $WP core install \
    --url="${WP_HOME:?WP_HOME env var is required}" \
    --title="Everbloom Nepal" \
    --admin_user="${WP_ADMIN_USER:-admin}" \
    --admin_password="${WP_ADMIN_PASSWORD:?WP_ADMIN_PASSWORD env var is required}" \
    --admin_email="${WP_ADMIN_EMAIL:?WP_ADMIN_EMAIL env var is required}" \
    --skip-email

  echo "everbloom: activating plugins and theme..."
  $WP plugin activate woocommerce
  $WP plugin activate everbloom-nepal
  $WP theme activate everbloom

  echo "everbloom: configuring store..."
  $WP option update woocommerce_coming_soon no
  $WP eval 'everbloom_restrict_to_nepal(); everbloom_ensure_shipping_zone(); everbloom_create_wishlist_page();'

  echo "everbloom: importing catalog..."
  $WP eval-file /var/www/html/migration/import.php

  echo "everbloom: setup complete"
else
  echo "everbloom: already installed, skipping setup"
fi

exec apache2-foreground
