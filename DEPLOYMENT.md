# Production deployment

## Public URLs

- Site: https://everbloom-nepal-production.up.railway.app/
- My Account: https://everbloom-nepal-production.up.railway.app/my-account/
- Admin: https://everbloom-nepal-production.up.railway.app/wp-admin/

## Railway services

- Web service: `Everbloom-nepal`
- Database service: `MySQL`
- Database networking: Railway private networking
- Upload volume: `/var/www/html/wp-content/uploads`

## Required environment variables

```text
WORDPRESS_DB_HOST
WORDPRESS_DB_USER
WORDPRESS_DB_PASSWORD
WORDPRESS_DB_NAME
WP_HOME
WP_ADMIN_USER
WP_ADMIN_EMAIL
WP_ADMIN_PASSWORD
```

Set `WP_HOME` to the current public Railway URL. Do not use the Railway project dashboard URL.

## Health check

A successful first deployment should show:

```text
everbloom: database is up
everbloom: installing WordPress...
everbloom: setup complete
```

A normal restart should show:

```text
everbloom: database is up
everbloom: already installed, skipping setup
```

## Troubleshooting

If the application waits indefinitely for the database, verify the `WORDPRESS_DB_*` variables and the MySQL service status. If WordPress redirects to an old URL, update the `home` and `siteurl` options to the current `WP_HOME` value using WP-CLI.
