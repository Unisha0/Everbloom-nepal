from django.contrib import messages
from django.shortcuts import redirect, render

from shop.models import Category, NewsletterSubscriber, Product, Review

from .forms import ContactForm, NewsletterForm


def home(request):
    featured_categories = Category.objects.filter(is_occasion=False).order_by('order')[:6]
    occasion_categories = Category.objects.filter(is_occasion=True).order_by('order')[:6]
    bestsellers = Product.objects.filter(is_active=True, is_bestseller=True)[:8]
    new_arrivals = Product.objects.filter(is_active=True, is_new=True)[:4]
    reviews = Review.objects.select_related('product').order_by('-rating', '-created_at')[:6]
    newsletter_form = NewsletterForm()
    context = {
        'featured_categories': featured_categories,
        'occasion_categories': occasion_categories,
        'bestsellers': bestsellers,
        'new_arrivals': new_arrivals,
        'reviews': reviews,
        'newsletter_form': newsletter_form,
    }
    return render(request, 'core/home.html', context)


def about(request):
    return render(request, 'core/about.html')


def contact(request):
    if request.method == 'POST':
        form = ContactForm(request.POST)
        if form.is_valid():
            form.save()
            messages.success(request, "Thank you for reaching out! Our team will get back to you shortly.")
            return redirect('core:contact')
    else:
        form = ContactForm()
    return render(request, 'core/contact.html', {'form': form})


def newsletter_subscribe(request):
    if request.method == 'POST':
        form = NewsletterForm(request.POST)
        if form.is_valid():
            NewsletterSubscriber.objects.get_or_create(email=form.cleaned_data['email'])
            messages.success(request, "You're subscribed! Watch your inbox for fresh floral inspiration.")
        else:
            messages.error(request, "Please enter a valid email address.")
    return redirect(request.META.get('HTTP_REFERER', 'core:home'))
