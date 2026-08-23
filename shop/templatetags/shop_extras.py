from django import template
from django.conf import settings

register = template.Library()


@register.filter
def npr(value):
    """Format a numeric value as Nepali Rupees, e.g. 2200 -> रु. 2,200"""
    try:
        value = float(value)
    except (TypeError, ValueError):
        return value
    if value == int(value):
        formatted = f'{int(value):,}'
    else:
        formatted = f'{value:,.2f}'
    return f'{settings.CURRENCY_SYMBOL} {formatted}'


@register.filter
def npr_amount(value):
    """Format a numeric value with thousand separators, no currency symbol."""
    try:
        value = float(value)
    except (TypeError, ValueError):
        return value
    if value == int(value):
        return f'{int(value):,}'
    return f'{value:,.2f}'


@register.filter
def mul(value, arg):
    try:
        return float(value) * float(arg)
    except (TypeError, ValueError):
        return ''


@register.simple_tag
def star_icons(rating, max_stars=5):
    """Render solid/regular star <i> tags for a given rating (rounded to nearest whole star)."""
    try:
        rating = round(float(rating))
    except (TypeError, ValueError):
        rating = 0
    icons = []
    for i in range(1, max_stars + 1):
        cls = 'fa-solid fa-star' if i <= rating else 'fa-regular fa-star'
        icons.append(cls)
    return icons
