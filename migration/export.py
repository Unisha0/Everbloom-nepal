"""One-off export of the legacy Django/SQLite catalog data to JSON for WordPress import."""
import json
import sqlite3

con = sqlite3.connect('db.sqlite3')
con.row_factory = sqlite3.Row
cur = con.cursor()

def rows(sql):
    cur.execute(sql)
    return [dict(r) for r in cur.fetchall()]

categories = rows('SELECT * FROM shop_category ORDER BY "order", name')
products = rows('SELECT * FROM shop_product ORDER BY id')
product_images = rows('SELECT * FROM shop_productimage ORDER BY id')
reviews = rows('SELECT * FROM shop_review ORDER BY id')

data = {
    'categories': categories,
    'products': products,
    'product_images': product_images,
    'reviews': reviews,
}

with open('migration/export.json', 'w', encoding='utf-8') as f:
    json.dump(data, f, ensure_ascii=False, indent=2)

print(f"categories={len(categories)} products={len(products)} product_images={len(product_images)} reviews={len(reviews)}")
