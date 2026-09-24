# Production image for Railway (or any Docker host): official WordPress image
# + WooCommerce + this repo's theme/plugin, bootstrapped by docker/start.sh.
FROM wordpress:php8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends unzip less default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Railway's managed MySQL presents a self-signed cert; mariadb-check (the
# version bundled here) defaults --ssl-verify-server-cert to on, which
# fails against it. This is a private network connection
# (mysql.railway.internal), so disable verification. (docker/start.sh
# itself avoids this tool for the readiness check, but this keeps ad hoc
# `mysql`/`mariadb-check` usage, e.g. over `railway ssh`, working too.)
RUN printf '[client]\nssl-verify-server-cert=0\n' > /etc/mysql/conf.d/no-ssl-verify.cnf

# WP-CLI
RUN curl -fsSL -o /usr/local/bin/wp \
      https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x /usr/local/bin/wp

# WooCommerce, downloaded at build time (no DB needed for a plain file extract).
RUN curl -fsSL -o /tmp/woocommerce.zip \
      https://downloads.wordpress.org/plugin/woocommerce.latest-stable.zip \
    && unzip -q /tmp/woocommerce.zip -d /usr/src/wordpress/wp-content/plugins/ \
    && rm /tmp/woocommerce.zip

# This repo's actual source. docker/start.sh seeds /var/www/html from
# /usr/src/wordpress on first boot, so anything placed here ends up there
# without needing a symlink.
COPY theme/everbloom /usr/src/wordpress/wp-content/themes/everbloom
COPY plugin/everbloom-nepal /usr/src/wordpress/wp-content/plugins/everbloom-nepal
COPY migration /usr/src/wordpress/migration

COPY docker/start.sh /usr/local/bin/everbloom-start.sh
RUN chmod +x /usr/local/bin/everbloom-start.sh

CMD ["everbloom-start.sh"]

