#!/bin/bash
# Runs as the container's CMD, after the base wordpress:php8.3-apache image's
# own entrypoint has already generated wp-config.php from WORDPRESS_DB_* env
# vars and seeded /var/www/html from /usr/src/wordpress. On first boot it
# installs WordPress, activates WooCommerce/theme/plugin, and imports the
# catalog; on every later boot (redeploys, restarts) it's a no-op and just
# starts Apache, so it's safe to run unconditionally.
set -euo pipefail

WP="wp --path=/var/www/html --allow-root"

echo "everbloom: waiting for database..."
attempt=0
until $WP db check 2>/tmp/db-check.err; do
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
