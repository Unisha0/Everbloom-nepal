# Everbloom Nepal 🌸

*Flowers that make every moment bloom.*

A modern, elegant e-commerce website for a Nepal-based online flower shop, built on **WordPress + WooCommerce**. Fresh flowers, handcrafted bouquets, and gift hampers — priced in NPR and delivered across Nepal.

## Live site

**Production:** https://everbloom-nepal-production.up.railway.app/

- [My Account](https://everbloom-nepal-production.up.railway.app/my-account/)
- [WordPress Admin](https://everbloom-nepal-production.up.railway.app/wp-admin/)

> The production site is hosted on Railway using its free `*.up.railway.app` domain. The Railway dashboard URL is private administration infrastructure and is not the public site URL.

## Release status

**Current release: v1.0.0 — Railway production deployment**

- WordPress and WooCommerce deployed with Docker
- MySQL database connected through Railway private networking
- Custom Everbloom theme and plugin enabled
- Nepal-focused checkout, shipping, order statuses, and payment instructions configured
- Persistent uploads volume configured at `/var/www/html/wp-content/uploads`

See the full release notes in [`RELEASE.md`](RELEASE.md) and deployment details in [`DEPLOYMENT.md`](DEPLOYMENT.md).

## Features

- Full product catalog with WooCommerce products/categories, ratings, reviews, search, filtering, and sorting
- Cart and AJAX add-to-cart
- Login-gated wishlist
- Checkout with Nepal provinces, districts, delivery date, and COD / eSewa / Khalti / IME Pay / Bank Transfer instructions
- Nepal-only flat delivery fee with free delivery above a configurable threshold
- Custom order statuses: Confirmed, Preparing Bouquet, and Out for Delivery
- Custom order numbers such as `EBxx0000`
- Order confirmation, order history, and order status tracking
- Account signup, login by username or email, profile, and saved address
- WordPress/WooCommerce administration for products, orders, newsletter subscribers, and contact messages

## Stack

WordPress · WooCommerce · custom theme (`theme/everbloom`) · custom plugin (`plugin/everbloom-nepal`) · Tailwind CSS CDN · Font Awesome · Google Fonts.

There is no frontend build step.

## Repository layout

```text
theme/everbloom/          Custom WooCommerce theme
plugin/everbloom-nepal/   Nepal checkout, delivery, gateways, orders, wishlist, newsletter, and contact features
migration/                One-off catalog migration scripts
wordpress/                Local WordPress core, WooCommerce, and uploads (gitignored)
docker/                  Railway/Docker startup script
Dockerfile               Production WordPress image
railway.json             Railway deployment configuration
```

## Local setup

Requires PHP 8+, MySQL/MariaDB, and [WP-CLI](https://wp-cli.org/).

```bash
mysql -u root -e "CREATE DATABASE everbloom_wp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER 'everbloom'@'localhost' IDENTIFIED BY 'everbloom_local_dev';
  GRANT ALL PRIVILEGES ON everbloom_wp.* TO 'everbloom'@'localhost';"

mkdir wordpress
php -d memory_limit=1024M "$(which wp)" core download --path=wordpress
./wpcli.sh config create --path=wordpress --dbname=everbloom_wp --dbuser=everbloom --dbpass=everbloom_local_dev --dbhost=localhost
./wpcli.sh core install --path=wordpress --url="http://localhost:8888" --title="Everbloom Nepal" \
  --admin_user=admin --admin_password="<choose one>" --admin_email="you@example.com"

./wpcli.sh plugin install woocommerce --activate --path=wordpress
ln -s "$(pwd)/theme/everbloom" wordpress/wp-content/themes/everbloom
ln -s "$(pwd)/plugin/everbloom-nepal" wordpress/wp-content/plugins/everbloom-nepal
./wpcli.sh theme activate everbloom --path=wordpress
./wpcli.sh plugin activate everbloom-nepal --path=wordpress

./wpcli.sh option update woocommerce_coming_soon no --path=wordpress
./wpcli.sh eval 'everbloom_restrict_to_nepal(); everbloom_ensure_shipping_zone(); everbloom_create_wishlist_page();' --path=wordpress
./wpcli.sh eval-file migration/import.php --path=wordpress
php -S localhost:8888 -t wordpress
```

Local site: http://localhost:8888/  
Local admin: http://localhost:8888/wp-admin/

## Deploying on Railway

The production deployment uses the repository `Dockerfile` and `railway.json`.

Required web-service variables:

```text
WORDPRESS_DB_HOST=${{MySQL.MYSQLHOST}}:${{MySQL.MYSQLPORT}}
WORDPRESS_DB_USER=${{MySQL.MYSQLUSER}}
WORDPRESS_DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
WORDPRESS_DB_NAME=${{MySQL.MYSQLDATABASE}}
WP_HOME=https://everbloom-nepal-production.up.railway.app
WP_ADMIN_USER=admin
WP_ADMIN_EMAIL=admin@example.com
WP_ADMIN_PASSWORD=<store securely>
```

The MySQL service should communicate over Railway private networking. The WordPress service should have a persistent volume mounted at:

```text
/var/www/html/wp-content/uploads
```

The startup script installs WordPress on the first boot, activates WooCommerce and the custom code, configures the Nepal store, and imports the catalog. Later restarts preserve the existing database and skip first-time setup.

## Production notes

- Keep `WP_ADMIN_PASSWORD` private; never commit it to this repository.
- Payment methods currently provide manual/offline instructions. Configure real merchant credentials before accepting live payments.
- Product media requires the persistent uploads volume.
- If the public Railway domain changes, update `WP_HOME` and the WordPress `home`/`siteurl` options.
- Railway deployment logs should include `everbloom: database is up` and `everbloom: already installed, skipping setup` on subsequent restarts.

## License

This project is maintained for the Everbloom Nepal online flower shop.
