from django.conf import settings

from .cart import Cart
from .models import Category


def cart_context(request):
    return {'cart': Cart(request)}


def category_context(request):
    return {
        'nav_categories': Category.objects.filter(is_occasion=False).order_by('order', 'name'),
        'nav_occasions': Category.objects.filter(is_occasion=True).order_by('order', 'name'),
        'FREE_DELIVERY_THRESHOLD': f'{settings.FREE_DELIVERY_THRESHOLD:,}',
        'CURRENCY_SYMBOL': settings.CURRENCY_SYMBOL,
        'SITE_NAME': settings.SITE_NAME,
    }
