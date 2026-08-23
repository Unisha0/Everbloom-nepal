from django import forms
from django.contrib.auth.forms import AuthenticationForm, UserCreationForm
from django.contrib.auth.models import User

from .models import Profile


class SignupForm(UserCreationForm):
    first_name = forms.CharField(max_length=100, required=True,
                                  widget=forms.TextInput(attrs={'class': 'form-input', 'placeholder': 'First name'}))
    last_name = forms.CharField(max_length=100, required=True,
                                 widget=forms.TextInput(attrs={'class': 'form-input', 'placeholder': 'Last name'}))
    email = forms.EmailField(required=True,
                              widget=forms.EmailInput(attrs={'class': 'form-input', 'placeholder': 'you@example.com'}))
    phone = forms.CharField(max_length=20, required=False,
                             widget=forms.TextInput(attrs={'class': 'form-input', 'placeholder': '98XXXXXXXX'}))

    class Meta:
        model = User
        fields = ('first_name', 'last_name', 'username', 'email', 'password1', 'password2')

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.fields['username'].widget.attrs.update({'class': 'form-input', 'placeholder': 'Choose a username'})
        self.fields['password1'].widget.attrs.update({'class': 'form-input', 'placeholder': 'Create a password'})
        self.fields['password2'].widget.attrs.update({'class': 'form-input', 'placeholder': 'Confirm password'})

    def save(self, commit=True):
        user = super().save(commit=commit)
        if commit:
            Profile.objects.update_or_create(user=user, defaults={'phone': self.cleaned_data.get('phone', '')})
        return user


class EmailAuthenticationForm(AuthenticationForm):
    username = forms.CharField(widget=forms.TextInput(attrs={'class': 'form-input', 'placeholder': 'Username or email', 'autofocus': True}))
    password = forms.CharField(widget=forms.PasswordInput(attrs={'class': 'form-input', 'placeholder': 'Password'}))


class ProfileForm(forms.ModelForm):
    class Meta:
        model = Profile
        fields = ['phone', 'province', 'district', 'city', 'street_address']
        widgets = {
            'phone': forms.TextInput(attrs={'class': 'form-input'}),
            'province': forms.TextInput(attrs={'class': 'form-input'}),
            'district': forms.TextInput(attrs={'class': 'form-input'}),
            'city': forms.TextInput(attrs={'class': 'form-input'}),
            'street_address': forms.TextInput(attrs={'class': 'form-input'}),
        }
