from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core.paginator import Paginator
from django.db.models import Avg, Count, Q
from django.http import JsonResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.urls import reverse
from django.views.decorators.http import require_POST

from .cart import Cart
from .forms import CheckoutForm, ReviewForm
from .models import Category, Order, OrderItem, Product, Wishlist

PAGE_SIZE = 12


def _is_ajax(request):
    return request.headers.get('x-requested-with') == 'XMLHttpRequest'


def shop_list(request, slug=None):
    products = Product.objects.filter(is_active=True).select_related('category')
    current_category = None

    if slug:
        current_category = get_object_or_404(Category, slug=slug)
        products = products.filter(category=current_category)

    selected_categories = request.GET.getlist('category')
    if selected_categories and not slug:
        products = products.filter(category__slug__in=selected_categories)

    query = request.GET.get('q', '').strip()
    if query:
        products = products.filter(
            Q(name__icontains=query) | Q(short_description__icontains=query) |
            Q(description__icontains=query) | Q(category__name__icontains=query)
        )

    price_min = request.GET.get('price_min')
    price_max = request.GET.get('price_max')
    if price_min:
        products = products.filter(price__gte=price_min)
    if price_max:
        products = products.filter(price__lte=price_max)

    products = products.annotate(avg_rating=Avg('reviews__rating'), num_reviews=Count('reviews'))

    sort = request.GET.get('sort', 'newest')
    sort_map = {
        'price_asc': 'price',
        'price_desc': '-price',
        'rating': '-avg_rating',
        'bestseller': '-is_bestseller',
        'newest': '-created_at',
    }
    products = products.order_by(sort_map.get(sort, '-created_at'))

    paginator = Paginator(products, PAGE_SIZE)
    page_obj = paginator.get_page(request.GET.get('page'))

    all_categories = Category.objects.filter(is_occasion=False).order_by('order', 'name')

    wishlist_ids = set()
    if request.user.is_authenticated:
        wishlist_ids = set(Wishlist.objects.filter(user=request.user).values_list('product_id', flat=True))

    context = {
        'page_obj': page_obj,
        'products': page_obj.object_list,
        'all_categories': all_categories,
        'current_category': current_category,
        'selected_categories': selected_categories,
        'query': query,
        'sort': sort,
        'price_min': price_min or '',
        'price_max': price_max or '',
        'wishlist_ids': wishlist_ids,
        'total_results': paginator.count,
    }
    return render(request, 'shop/shop_list.html', context)


def product_detail(request, slug):
    product = get_object_or_404(Product.objects.select_related('category'), slug=slug, is_active=True)
    related_products = Product.objects.filter(category=product.category, is_active=True).exclude(pk=product.pk)[:4]
    reviews = product.reviews.all()[:20]

    in_wishlist = False
    if request.user.is_authenticated:
        in_wishlist = Wishlist.objects.filter(user=request.user, product=product).exists()

    if request.method == 'POST' and 'submit_review' in request.POST:
        review_form = ReviewForm(request.POST)
        if review_form.is_valid():
            review = review_form.save(commit=False)
            review.product = product
            if request.user.is_authenticated:
                review.user = request.user
            review.save()
            messages.success(request, 'Thank you! Your review has been posted.')
            return redirect('shop:product_detail', slug=product.slug)
    else:
        review_form = ReviewForm()

    context = {
        'product': product,
        'related_products': related_products,
        'reviews': reviews,
        'review_form': review_form,
        'in_wishlist': in_wishlist,
        'rating_range': range(1, 6),
    }
    return render(request, 'shop/product_detail.html', context)


@require_POST
def cart_add(request, product_id):
    product = get_object_or_404(Product, id=product_id, is_active=True)
    cart = Cart(request)
    try:
        quantity = int(request.POST.get('quantity', 1))
    except (TypeError, ValueError):
        quantity = 1
    cart.add(product, quantity=quantity)

    if _is_ajax(request):
        return JsonResponse({'success': True, 'cart_count': cart.total_items,
                              'cart_subtotal': str(cart.subtotal), 'message': f'{product.name} added to your cart.'})
    messages.success(request, f'{product.name} added to your cart.')
    return redirect('shop:cart')


@require_POST
def cart_update(request, product_id):
    product = get_object_or_404(Product, id=product_id)
    cart = Cart(request)
    try:
        quantity = int(request.POST.get('quantity', 1))
    except (TypeError, ValueError):
        quantity = 1
    if quantity < 1:
        cart.remove(product)
        quantity = 0
    else:
        cart.add(product, quantity=quantity, replace=True)

    if _is_ajax(request):
        return JsonResponse({'success': True, 'cart_count': cart.total_items,
                              'cart_subtotal': str(cart.subtotal), 'cart_total': str(cart.total),
                              'cart_delivery_fee': str(cart.delivery_fee),
                              'line_total': str(product.price * quantity)})
    return redirect('shop:cart')


@require_POST
def cart_remove(request, product_id):
    product = get_object_or_404(Product, id=product_id)
    cart = Cart(request)
    cart.remove(product)
    if _is_ajax(request):
        return JsonResponse({'success': True, 'cart_count': cart.total_items, 'cart_subtotal': str(cart.subtotal)})
    messages.info(request, f'{product.name} removed from your cart.')
    return redirect('shop:cart')


def cart_detail(request):
    cart = Cart(request)
    return render(request, 'shop/cart.html', {'cart': cart})


@login_required
def wishlist_detail(request):
    items = Wishlist.objects.filter(user=request.user).select_related('product', 'product__category')
    return render(request, 'shop/wishlist.html', {'items': items})


@require_POST
def wishlist_toggle(request, product_id):
    if not request.user.is_authenticated:
        if _is_ajax(request):
            return JsonResponse({'success': False, 'login_required': True,
                                  'login_url': reverse('accounts:login')}, status=401)
        return redirect('accounts:login')

    product = get_object_or_404(Product, id=product_id)
    wishlist_item, created = Wishlist.objects.get_or_create(user=request.user, product=product)
    added = True
    if not created:
        wishlist_item.delete()
        added = False

    if _is_ajax(request):
        return JsonResponse({'success': True, 'added': added})
    if added:
        messages.success(request, f'{product.name} added to your wishlist.')
    else:
        messages.info(request, f'{product.name} removed from your wishlist.')
    return redirect(request.META.get('HTTP_REFERER', 'shop:shop_list'))


@login_required
def checkout(request):
    cart = Cart(request)
    if len(cart) == 0:
        messages.info(request, 'Your cart is empty. Add some beautiful flowers first!')
        return redirect('shop:shop_list')

    profile = getattr(request.user, 'profile', None)
    initial = {
        'full_name': request.user.get_full_name() or request.user.username,
        'email': request.user.email,
    }
    if profile:
        initial.update({
            'phone': profile.phone,
            'province': profile.province or 'Bagmati',
            'district': profile.district,
            'city': profile.city,
            'street_address': profile.street_address,
        })

    if request.method == 'POST':
        form = CheckoutForm(request.POST, initial=initial)
        if form.is_valid():
            order = form.save(commit=False)
            order.user = request.user
            order.subtotal = cart.subtotal
            order.delivery_fee = cart.delivery_fee
            order.total = cart.total
            order.save()

            for item in cart:
                OrderItem.objects.create(
                    order=order,
                    product=item['product'],
                    product_name=item['product'].name,
                    image_url=item['product'].image_url,
                    price=item['product'].price,
                    quantity=item['quantity'],
                )

            cart.clear()
            messages.success(request, 'Your order has been placed successfully!')
            return redirect('shop:order_confirmation', order_number=order.order_number)
    else:
        form = CheckoutForm(initial=initial)

    return render(request, 'shop/checkout.html', {'form': form, 'cart': cart})


@login_required
def order_confirmation(request, order_number):
    order = get_object_or_404(Order, order_number=order_number, user=request.user)
    return render(request, 'shop/order_confirmation.html', {'order': order})


@login_required
def order_history(request):
    orders = Order.objects.filter(user=request.user).prefetch_related('items')
    return render(request, 'shop/order_history.html', {'orders': orders})


@login_required
def order_detail(request, order_number):
    order = get_object_or_404(Order, order_number=order_number, user=request.user)
    status_steps = ['Placed', 'Confirmed', 'Preparing', 'Out for Delivery', 'Delivered']
    return render(request, 'shop/order_detail.html', {'order': order, 'status_steps': status_steps})
