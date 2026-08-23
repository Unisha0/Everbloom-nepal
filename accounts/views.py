from django.contrib import messages
from django.contrib.auth import login
from django.contrib.auth.decorators import login_required
from django.contrib.auth.views import LoginView, LogoutView
from django.shortcuts import redirect, render
from django.urls import reverse_lazy
from django.views.generic import CreateView

from shop.models import Order, Wishlist

from .forms import EmailAuthenticationForm, ProfileForm, SignupForm


class SignupView(CreateView):
    form_class = SignupForm
    template_name = 'accounts/signup.html'
    success_url = reverse_lazy('core:home')

    def form_valid(self, form):
        response = super().form_valid(form)
        login(self.request, self.object, backend='accounts.backends.EmailOrUsernameBackend')
        messages.success(self.request, f'Welcome to Everbloom, {self.object.first_name}! Your account is ready.')
        return response


class EverbloomLoginView(LoginView):
    template_name = 'accounts/login.html'
    authentication_form = EmailAuthenticationForm
    redirect_authenticated_user = True

    def form_valid(self, form):
        messages.success(self.request, 'Welcome back to Everbloom!')
        return super().form_valid(form)


class EverbloomLogoutView(LogoutView):
    next_page = 'core:home'


@login_required
def profile(request):
    if request.method == 'POST':
        form = ProfileForm(request.POST, instance=request.user.profile)
        if form.is_valid():
            form.save()
            messages.success(request, 'Your profile has been updated.')
            return redirect('accounts:profile')
    else:
        form = ProfileForm(instance=request.user.profile)

    recent_orders = Order.objects.filter(user=request.user)[:5]
    wishlist_count = Wishlist.objects.filter(user=request.user).count()

    context = {
        'form': form,
        'recent_orders': recent_orders,
        'wishlist_count': wishlist_count,
    }
    return render(request, 'accounts/profile.html', context)
