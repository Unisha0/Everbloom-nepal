# Everbloom Nepal 🌸

*Flowers that make every moment bloom.*

A modern, elegant e-commerce website for a Nepal-based online flower shop, built with Django. Fresh flowers, handcrafted bouquets, and gift hampers — priced in NPR, delivered across Nepal.

## Features

- Full product catalog — 12 categories, 32 products, ratings & reviews
- Search, category & price filtering, sorting
- Session-based cart with AJAX add/update/remove
- Login-gated wishlist
- Checkout with Nepal provinces/districts, delivery date, and COD / eSewa / Khalti / IME Pay / Bank Transfer
- Order confirmation, order history, and order status tracking
- Accounts: signup, login (by username or email), profile with saved address
- Django admin for managing catalog, orders, and messages

## Stack

Django 5.2 · SQLite · Tailwind CSS (CDN) · Font Awesome · Google Fonts — no frontend build step required.

## Getting started

```bash
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt

python manage.py migrate
python manage.py seed_data   # populates categories, products, reviews, and an admin user
python manage.py runserver
```

Visit `http://127.0.0.1:8000/`. Admin panel at `/admin/` (superuser `admin` / `EverbloomAdmin123`, created by `seed_data` — change this before deploying anywhere real).
