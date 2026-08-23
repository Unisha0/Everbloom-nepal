from decimal import Decimal

from django.conf import settings

from .models import Product

CART_SESSION_KEY = 'cart'


class Cart:
    """A simple session-backed shopping cart (no login required)."""

    def __init__(self, request):
        self.session = request.session
        cart = self.session.get(CART_SESSION_KEY)
        if cart is None:
            cart = self.session[CART_SESSION_KEY] = {}
        self.cart = cart

    def add(self, product, quantity=1, replace=False):
        product_id = str(product.id)
        if product_id not in self.cart:
            self.cart[product_id] = {'quantity': 0}
        if replace:
            self.cart[product_id]['quantity'] = quantity
        else:
            self.cart[product_id]['quantity'] += quantity
        self.cart[product_id]['quantity'] = max(1, min(self.cart[product_id]['quantity'], product.stock or 99))
        self.save()

    def remove(self, product):
        product_id = str(product.id)
        if product_id in self.cart:
            del self.cart[product_id]
            self.save()

    def save(self):
        self.session.modified = True

    def clear(self):
        self.session[CART_SESSION_KEY] = {}
        self.save()

    def __iter__(self):
        product_ids = self.cart.keys()
        products = Product.objects.filter(id__in=product_ids)
        products_map = {str(p.id): p for p in products}
        for product_id, item in self.cart.items():
            product = products_map.get(product_id)
            if not product:
                continue
            quantity = item['quantity']
            yield {
                'product': product,
                'quantity': quantity,
                'line_total': product.price * quantity,
            }

    def __len__(self):
        return sum(item['quantity'] for item in self.cart.values())

    @property
    def total_items(self):
        return len(self)

    @property
    def subtotal(self):
        total = Decimal('0')
        for item in self:
            total += item['line_total']
        return total

    @property
    def delivery_fee(self):
        if self.subtotal == 0:
            return Decimal('0')
        if self.subtotal >= settings.FREE_DELIVERY_THRESHOLD:
            return Decimal('0')
        return Decimal(settings.STANDARD_DELIVERY_FEE)

    @property
    def total(self):
        return self.subtotal + self.delivery_fee

    @property
    def amount_to_free_delivery(self):
        remaining = Decimal(settings.FREE_DELIVERY_THRESHOLD) - self.subtotal
        return remaining if remaining > 0 else Decimal('0')
