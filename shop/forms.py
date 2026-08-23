from django import forms

from .models import Order, Review

NEPAL_DISTRICTS = [
    'Kathmandu', 'Lalitpur', 'Bhaktapur', 'Kavrepalanchok', 'Chitwan',
    'Pokhara / Kaski', 'Makwanpur', 'Morang', 'Sunsari', 'Jhapa',
    'Rupandehi', 'Kailali', 'Banke', 'Dang', 'Dhading', 'Nuwakot',
    'Kaski', 'Syangja', 'Palpa', 'Parsa', 'Bara', 'Rautahat', 'Other',
]
DISTRICT_CHOICES = [(d, d) for d in NEPAL_DISTRICTS]


class ReviewForm(forms.ModelForm):
    class Meta:
        model = Review
        fields = ['name', 'rating', 'comment']
        widgets = {
            'name': forms.TextInput(attrs={'class': 'form-input', 'placeholder': 'Your name'}),
            'rating': forms.Select(attrs={'class': 'form-input'}),
            'comment': forms.Textarea(attrs={'class': 'form-input', 'rows': 4,
                                              'placeholder': 'Share your experience with this bouquet...'}),
        }


class CheckoutForm(forms.ModelForm):
    district = forms.ChoiceField(choices=DISTRICT_CHOICES, widget=forms.Select(attrs={'class': 'form-input'}))

    class Meta:
        model = Order
        fields = ['full_name', 'email', 'phone', 'province', 'district', 'city',
                  'street_address', 'landmark', 'delivery_date', 'delivery_notes', 'payment_method']
        widgets = {
            'full_name': forms.TextInput(attrs={'class': 'form-input', 'placeholder': 'Recipient full name'}),
            'email': forms.EmailInput(attrs={'class': 'form-input', 'placeholder': 'you@example.com'}),
            'phone': forms.TextInput(attrs={'class': 'form-input', 'placeholder': '98XXXXXXXX'}),
            'province': forms.Select(attrs={'class': 'form-input'}),
            'city': forms.TextInput(attrs={'class': 'form-input', 'placeholder': 'e.g. Baneshwor, New Road'}),
            'street_address': forms.TextInput(attrs={'class': 'form-input', 'placeholder': 'House no., street, tole'}),
            'landmark': forms.TextInput(attrs={'class': 'form-input', 'placeholder': 'Nearby landmark (optional)'}),
            'delivery_date': forms.DateInput(attrs={'class': 'form-input', 'type': 'date'}),
            'delivery_notes': forms.Textarea(attrs={'class': 'form-input', 'rows': 3,
                                                      'placeholder': 'Any special instructions for delivery or the message card...'}),
            'payment_method': forms.RadioSelect,
        }
