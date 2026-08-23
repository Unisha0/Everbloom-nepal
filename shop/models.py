import random
import string

from django.conf import settings
from django.db import models
from django.urls import reverse
from django.utils.text import slugify


class Category(models.Model):
    name = models.CharField(max_length=100, unique=True)
    nepali_name = models.CharField(max_length=100, blank=True)
    slug = models.SlugField(max_length=120, unique=True, blank=True)
    description = models.TextField(blank=True)
    image_url = models.URLField(max_length=500)
    icon = models.CharField(max_length=50, default='fa-seedling', help_text='Font Awesome icon class')
    order = models.PositiveIntegerField(default=0)
    is_occasion = models.BooleanField(default=False, help_text='Show under "Shop by Occasion" instead of main categories')

    class Meta:
        verbose_name_plural = 'Categories'
        ordering = ['order', 'name']

    def __str__(self):
        return self.name

    def save(self, *args, **kwargs):
        if not self.slug:
            self.slug = slugify(self.name)
        super().save(*args, **kwargs)

    def get_absolute_url(self):
        return reverse('shop:category_detail', args=[self.slug])

    @property
    def product_count(self):
        return self.products.filter(is_active=True).count()


class Product(models.Model):
    category = models.ForeignKey(Category, related_name='products', on_delete=models.CASCADE)
    name = models.CharField(max_length=200)
    slug = models.SlugField(max_length=220, unique=True, blank=True)
    short_description = models.CharField(max_length=250)
    description = models.TextField()
    price = models.DecimalField(max_digits=10, decimal_places=2)
    compare_at_price = models.DecimalField(max_digits=10, decimal_places=2, null=True, blank=True,
                                            help_text='Original price to show a strike-through discount')
    image_url = models.URLField(max_length=500)
    image_url_secondary = models.URLField(max_length=500, blank=True)
    stem_count = models.CharField(max_length=50, blank=True, help_text='e.g. "12 stems", "20 stems"')
    vase_included = models.BooleanField(default=False)
    is_featured = models.BooleanField(default=False)
    is_bestseller = models.BooleanField(default=False)
    is_new = models.BooleanField(default=False)
    stock = models.PositiveIntegerField(default=50)
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        ordering = ['-created_at']

    def __str__(self):
        return self.name

    def save(self, *args, **kwargs):
        if not self.slug:
            base_slug = slugify(self.name)
            slug = base_slug
            i = 1
            while Product.objects.filter(slug=slug).exclude(pk=self.pk).exists():
                i += 1
                slug = f'{base_slug}-{i}'
            self.slug = slug
        super().save(*args, **kwargs)

    def get_absolute_url(self):
        return reverse('shop:product_detail', args=[self.slug])

    @property
    def in_stock(self):
        return self.stock > 0

    @property
    def discount_percent(self):
        if self.compare_at_price and self.compare_at_price > self.price:
            return round((1 - (self.price / self.compare_at_price)) * 100)
        return 0

    @property
    def average_rating(self):
        agg = self.reviews.aggregate(models.Avg('rating'))['rating__avg']
        return round(agg, 1) if agg else 0

    @property
    def rating_percent(self):
        return round((self.average_rating / 5) * 100)

    @property
    def review_count(self):
        return self.reviews.count()

    @property
    def gallery_images(self):
        images = [self.image_url]
        if self.image_url_secondary:
            images.append(self.image_url_secondary)
        images.extend(img.image_url for img in self.extra_images.all())
        return images


class ProductImage(models.Model):
    product = models.ForeignKey(Product, related_name='extra_images', on_delete=models.CASCADE)
    image_url = models.URLField(max_length=500)
    alt_text = models.CharField(max_length=200, blank=True)

    def __str__(self):
        return f'Image for {self.product.name}'


class Review(models.Model):
    RATING_CHOICES = [(i, str(i)) for i in range(1, 6)]

    product = models.ForeignKey(Product, related_name='reviews', on_delete=models.CASCADE)
    user = models.ForeignKey(settings.AUTH_USER_MODEL, related_name='reviews', on_delete=models.CASCADE,
                              null=True, blank=True)
    name = models.CharField(max_length=100, help_text='Reviewer display name')
    rating = models.PositiveSmallIntegerField(choices=RATING_CHOICES, default=5)
    comment = models.TextField()
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        ordering = ['-created_at']

    def __str__(self):
        return f'{self.name} - {self.product.name} ({self.rating}★)'


class Wishlist(models.Model):
    user = models.ForeignKey(settings.AUTH_USER_MODEL, related_name='wishlist_items', on_delete=models.CASCADE)
    product = models.ForeignKey(Product, related_name='wishlisted_by', on_delete=models.CASCADE)
    added_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        unique_together = ('user', 'product')
        ordering = ['-added_at']

    def __str__(self):
        return f'{self.user} ♥ {self.product}'


def generate_order_number():
    date_part = ''.join(random.choices(string.digits, k=4))
    letters = ''.join(random.choices(string.ascii_uppercase, k=2))
    return f'EB{letters}{date_part}'


class Order(models.Model):
    PROVINCE_CHOICES = [
        ('Koshi', 'Koshi Province'),
        ('Madhesh', 'Madhesh Province'),
        ('Bagmati', 'Bagmati Province'),
        ('Gandaki', 'Gandaki Province'),
        ('Lumbini', 'Lumbini Province'),
        ('Karnali', 'Karnali Province'),
        ('Sudurpashchim', 'Sudurpashchim Province'),
    ]

    PAYMENT_CHOICES = [
        ('cod', 'Cash on Delivery'),
        ('esewa', 'eSewa'),
        ('khalti', 'Khalti'),
        ('imepay', 'IME Pay'),
        ('bank', 'Bank Transfer'),
    ]

    PAYMENT_STATUS_CHOICES = [
        ('pending', 'Pending'),
        ('paid', 'Paid'),
    ]

    STATUS_CHOICES = [
        ('placed', 'Order Placed'),
        ('confirmed', 'Confirmed'),
        ('preparing', 'Preparing Bouquet'),
        ('out_for_delivery', 'Out for Delivery'),
        ('delivered', 'Delivered'),
        ('cancelled', 'Cancelled'),
    ]

    order_number = models.CharField(max_length=20, unique=True, default=generate_order_number)
    user = models.ForeignKey(settings.AUTH_USER_MODEL, related_name='orders', on_delete=models.CASCADE)

    full_name = models.CharField(max_length=150)
    email = models.EmailField()
    phone = models.CharField(max_length=20)

    province = models.CharField(max_length=20, choices=PROVINCE_CHOICES, default='Bagmati')
    district = models.CharField(max_length=100)
    city = models.CharField(max_length=100)
    street_address = models.CharField(max_length=255)
    landmark = models.CharField(max_length=255, blank=True)
    delivery_date = models.DateField(null=True, blank=True)
    delivery_notes = models.TextField(blank=True)

    payment_method = models.CharField(max_length=20, choices=PAYMENT_CHOICES, default='cod')
    payment_status = models.CharField(max_length=20, choices=PAYMENT_STATUS_CHOICES, default='pending')
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='placed')

    subtotal = models.DecimalField(max_digits=10, decimal_places=2)
    delivery_fee = models.DecimalField(max_digits=10, decimal_places=2, default=0)
    total = models.DecimalField(max_digits=10, decimal_places=2)

    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        ordering = ['-created_at']

    def __str__(self):
        return self.order_number

    def get_absolute_url(self):
        return reverse('shop:order_confirmation', args=[self.order_number])


class OrderItem(models.Model):
    order = models.ForeignKey(Order, related_name='items', on_delete=models.CASCADE)
    product = models.ForeignKey(Product, related_name='order_items', on_delete=models.SET_NULL, null=True)
    product_name = models.CharField(max_length=200)
    image_url = models.URLField(max_length=500, blank=True)
    price = models.DecimalField(max_digits=10, decimal_places=2)
    quantity = models.PositiveIntegerField(default=1)

    @property
    def subtotal(self):
        return self.price * self.quantity

    def __str__(self):
        return f'{self.quantity} x {self.product_name}'


class NewsletterSubscriber(models.Model):
    email = models.EmailField(unique=True)
    subscribed_at = models.DateTimeField(auto_now_add=True)

    def __str__(self):
        return self.email
