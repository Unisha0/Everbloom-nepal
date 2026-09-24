# Everbloom Nepal v1.0.0

**Release:** v1.0.0 — Railway production deployment  
**Date:** 2026-09-24  
**Live site:** https://everbloom-nepal-production.up.railway.app/

## Included

- WordPress + WooCommerce production image
- Custom Everbloom Nepal theme
- Custom Everbloom Nepal plugin
- Nepal-specific checkout and delivery configuration
- Custom order statuses and order numbers
- Wishlist, newsletter, and contact functionality
- Railway MySQL integration
- Persistent media uploads volume
- Catalog migration/import support

## Deployment

The application is deployed with Docker using `Dockerfile` and `railway.json`. The first container boot installs WordPress, enables WooCommerce and the custom code, configures the store, and imports the catalog. Subsequent restarts preserve the existing installation.

## Important

Payment gateways currently display manual/offline payment instructions. Configure and verify production merchant credentials before accepting real payments. Never commit Railway database or WordPress administrator credentials.
