from django.contrib import admin

from .models import (Category, NewsletterSubscriber, Order, OrderItem,
                      Product, ProductImage, Review, Wishlist)


class ProductImageInline(admin.TabularInline):
    model = ProductImage
    extra = 1


@admin.register(Category)
class CategoryAdmin(admin.ModelAdmin):
    list_display = ('name', 'nepali_name', 'is_occasion', 'product_count', 'order')
    prepopulated_fields = {'slug': ('name',)}
    list_filter = ('is_occasion',)
    search_fields = ('name',)


@admin.register(Product)
class ProductAdmin(admin.ModelAdmin):
    list_display = ('name', 'category', 'price', 'stock', 'is_featured', 'is_bestseller', 'is_active')
    list_filter = ('category', 'is_featured', 'is_bestseller', 'is_new', 'is_active')
    search_fields = ('name', 'short_description')
    prepopulated_fields = {'slug': ('name',)}
    inlines = [ProductImageInline]


@admin.register(Review)
class ReviewAdmin(admin.ModelAdmin):
    list_display = ('product', 'name', 'rating', 'created_at')
    list_filter = ('rating',)


class OrderItemInline(admin.TabularInline):
    model = OrderItem
    extra = 0
    readonly_fields = ('product', 'product_name', 'price', 'quantity')


@admin.register(Order)
class OrderAdmin(admin.ModelAdmin):
    list_display = ('order_number', 'full_name', 'total', 'payment_method', 'payment_status', 'status', 'created_at')
    list_filter = ('status', 'payment_method', 'payment_status', 'province')
    search_fields = ('order_number', 'full_name', 'email', 'phone')
    inlines = [OrderItemInline]
    readonly_fields = ('order_number', 'subtotal', 'total')


@admin.register(Wishlist)
class WishlistAdmin(admin.ModelAdmin):
    list_display = ('user', 'product', 'added_at')


@admin.register(NewsletterSubscriber)
class NewsletterSubscriberAdmin(admin.ModelAdmin):
    list_display = ('email', 'subscribed_at')
