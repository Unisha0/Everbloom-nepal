# Everbloom Nepal 🌸

*Flowers that make every moment bloom.*

A modern, elegant e-commerce website for a Nepal-based online flower shop, built on **WordPress + WooCommerce**. Fresh flowers, handcrafted bouquets, and gift hampers — priced in NPR, delivered across Nepal.

This was converted from an earlier Django prototype. Only WordPress-native code is tracked in this repo — WordPress core, WooCommerce and other third-party plugins are installed via WP-CLI, not committed.

## Features

- Full product catalog (WooCommerce products/categories) — ratings & reviews, search, category & price filtering, sorting
- Cart & AJAX add-to-cart, wishlist (login-gated)
- Checkout with Nepal provinces, districts, delivery date, and COD / eSewa / Khalti / IME Pay / Bank Transfer
- Nepal-only flat delivery fee that becomes free above a threshold (configurable shipping method)
- Custom order statuses (Confirmed, Preparing Bouquet, Out for Delivery) and order numbers (`EBxx0000`)
- Order confirmation, order history, and order status tracking (WooCommerce My Account)
- Accounts: signup, login (by username or email — WordPress native), profile with saved address
- WordPress/WooCommerce admin for managing catalog, orders, newsletter subscribers and contact messages

## Stack

WordPress · WooCommerce · custom theme (`theme/everbloom`) · custom plugin (`plugin/everbloom-nepal`) · Tailwind CSS (CDN) · Font Awesome · Google Fonts — no frontend build step required.

## Repo layout

```
theme/everbloom/          Custom WooCommerce theme (tracked in git — this is the real source)
plugin/everbloom-nepal/   Custom plugin: Nepal checkout fields, delivery pricing,
                           eSewa/Khalti/IME Pay gateways, order statuses/numbers,
                           wishlist, newsletter, contact form
migration/                One-off scripts that migrated the legacy Django/SQLite
                           catalog (categories, products, reviews) into WooCommerce
wordpress/                WordPress core + WooCommerce + uploads (gitignored,
                           installed locally via WP-CLI — see below)
```

## Local setup

Requires PHP 8+, MySQL/MariaDB, and [WP-CLI](https://wp-cli.org/).

```bash
# 1. Database
mysql -u root -e "CREATE DATABASE everbloom_wp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER 'everbloom'@'localhost' IDENTIFIED BY 'everbloom_local_dev';
  GRANT ALL PRIVILEGES ON everbloom_wp.* TO 'everbloom'@'localhost';"

# 2. WordPress core (memory_limit bumped for the tarball extraction)
mkdir wordpress
php -d memory_limit=1024M "$(which wp)" core download --path=wordpress
./wpcli.sh config create --path=wordpress --dbname=everbloom_wp --dbuser=everbloom --dbpass=everbloom_local_dev --dbhost=localhost
./wpcli.sh core install --path=wordpress --url="http://localhost:8888" --title="Everbloom Nepal" \
  --admin_user=admin --admin_password="<choose one>" --admin_email="you@example.com"

# 3. WooCommerce + this repo's theme/plugin
./wpcli.sh plugin install woocommerce --activate --path=wordpress
ln -s "$(pwd)/theme/everbloom" wordpress/wp-content/themes/everbloom
ln -s "$(pwd)/plugin/everbloom-nepal" wordpress/wp-content/plugins/everbloom-nepal
./wpcli.sh theme activate everbloom --path=wordpress
./wpcli.sh plugin activate everbloom-nepal --path=wordpress

# 4. Store config (Nepal country/currency, shipping zone, gateways, wishlist page)
./wpcli.sh option update woocommerce_coming_soon no --path=wordpress   # disable the WC 11 "coming soon" gate
./wpcli.sh eval 'everbloom_restrict_to_nepal(); everbloom_ensure_shipping_zone(); everbloom_create_wishlist_page();' --path=wordpress

# 5. Import the catalog (categories, products, reviews)
python3 migration/export.py                                  # only needed once, already run
./wpcli.sh eval-file migration/import.php --path=wordpress

# 6. Run it
php -S localhost:8888 -t wordpress
```

`wpcli.sh` is a thin wrapper around `wp` that raises PHP's memory limit and silences PHP 8.5 deprecation
noise from WP-CLI's own bundled libraries — use it in place of `wp` for every command above.

Visit `http://localhost:8888/`. WP Admin at `/wp-admin/`.

## Notes for going to production

- Point the theme/plugin symlinks (or copy them) into a real host's `wp-content/themes` and
  `wp-content/plugins`, install WordPress + WooCommerce there the normal way, and re-run the
  migration script (or export/import via WooCommerce's CSV product importer) against that site.
- The eSewa/Khalti/IME Pay payment methods are manual/offline gateways (order placed immediately,
  payment instructions shown after) — same as the original Django app, which never integrated real
  payment APIs either. Wire up real merchant credentials before accepting live payments.
- Category/product URLs are WooCommerce's defaults (`/product-category/slug/`, `/product/slug/`)
  rather than the old Django routes — set up redirects if preserving old URLs for SEO matters.
