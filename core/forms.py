from django import forms

from .models import ContactMessage


class ContactForm(forms.ModelForm):
    class Meta:
        model = ContactMessage
        fields = ['name', 'email', 'subject', 'message']
        widgets = {
            'name': forms.TextInput(attrs={'class': 'form-input', 'placeholder': 'Your name'}),
            'email': forms.EmailInput(attrs={'class': 'form-input', 'placeholder': 'you@example.com'}),
            'subject': forms.TextInput(attrs={'class': 'form-input', 'placeholder': 'Subject'}),
            'message': forms.Textarea(attrs={'class': 'form-input', 'rows': 5, 'placeholder': 'Your message...'}),
        }


class NewsletterForm(forms.Form):
    email = forms.EmailField(widget=forms.EmailInput(attrs={
        'class': 'newsletter-input', 'placeholder': 'Enter your email address'}))
