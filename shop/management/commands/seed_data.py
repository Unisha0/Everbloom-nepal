import random
from decimal import Decimal

from django.contrib.auth.models import User
from django.core.management.base import BaseCommand

from shop.models import Category, Product, Review

# All images are real, freely-licensed photographs from Wikimedia Commons,
# hand-selected and visually verified to match their subject.
IMG = {
    'rose_red_1': 'https://upload.wikimedia.org/wikipedia/commons/a/a8/A_bouquet_of_red_roses.jpg',
    'rose_red_2': 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b1/Bouquet_of_Red_Roses.jpg/1920px-Bouquet_of_Red_Roses.jpg',
    'rose_red_hand': 'https://upload.wikimedia.org/wikipedia/commons/c/c4/Hand_holding_a_bouquet_of_red_roses.jpg',
    'rose_garden_red': 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Beautiful_red_roses_in_the_garden_closeup._%2849875816787%29.jpg/1920px-Beautiful_red_roses_in_the_garden_closeup._%2849875816787%29.jpg',
    'rose_pink_lush': 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/00/Bouquet_de_roses_roses.jpg/1920px-Bouquet_de_roses_roses.jpg',
    'rose_white_vase': 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b9/Bouquet_of_white_roses_%282486808368%29.jpg/1920px-Bouquet_of_white_roses_%282486808368%29.jpg',
    'rose_white_painting': 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1e/Bouquet_of_roses_in_front_of_painting.jpg/1920px-Bouquet_of_roses_in_front_of_painting.jpg',
    'rose_single_dramatic': 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1f/Roses_In_Dalat_%28257067009%29.jpeg/1920px-Roses_In_Dalat_%28257067009%29.jpeg',
    'rose_bridal_stephanotis': 'https://upload.wikimedia.org/wikipedia/commons/7/75/Bridal_bouquet_white_pink_rose_stephanotis.jpg',
    'rose_wedding_white': 'https://upload.wikimedia.org/wikipedia/commons/0/06/Wedding_bouquet_white_roses.jpg',
    'rose_bridal_white': 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/21/White_rose_bridal_bouquet.jpg/1920px-White_rose_bridal_bouquet.jpg',
    'tulip_orange_table': 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b1/A_bouquet_of_orange_tulips_on_a_table.jpg/1920px-A_bouquet_of_orange_tulips_on_a_table.jpg',
    'tulip_red_basket': 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e9/A_bouquet_of_red_tulips_in_a_basket.jpg/1920px-A_bouquet_of_red_tulips_in_a_basket.jpg',
    'tulip_pink_field': 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/13/Pink-Tulip-Fields_Bollenstreek_Hillegom.jpg/1920px-Pink-Tulip-Fields_Bollenstreek_Hillegom.jpg',
    'tulip_pink_drop': 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b8/Pink-red_tulip_with_droplets.jpg/1920px-Pink-red_tulip_with_droplets.jpg',
    'tulip_pink_clean': 'https://upload.wikimedia.org/wikipedia/commons/3/34/Pink_tulip_flower.jpg',
    'tulip_rainbow_bunch': 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/bf/Colorful_tulip_bouquets_%28Unsplash%29.jpg/1920px-Colorful_tulip_bouquets_%28Unsplash%29.jpg',
    'tulip_field_colorful': 'https://upload.wikimedia.org/wikipedia/commons/8/8b/Colorful_tulip_garden_Field_of_red_pink_and_yellow_tulips_Tulips_in_full_bloom.jpg',
    'tulip_field_wide': 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/46/Field_of_colorful_tulips_red_field_spring_%2852829438249%29.jpg/1920px-Field_of_colorful_tulips_red_field_spring_%2852829438249%29.jpg',
    'lily_white_table': 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/aa/A_bunch_of_white_lilies_on_a_black_table.jpg/1920px-A_bunch_of_white_lilies_on_a_black_table.jpg',
    'lily_white_macro': 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d1/Toronto_Ontario_-_Canada_-_Allan_Gardens_Conservatory_-_Toronto_Tropical_Garden_-_White_Lily_Macro_-_Flickr_-_Onasill_~_Bill_-_73_Million.jpg/1920px-Toronto_Ontario_-_Canada_-_Allan_Gardens_Conservatory_-_Toronto_Tropical_Garden_-_White_Lily_Macro_-_Flickr_-_Onasill_~_Bill_-_73_Million.jpg',
    'lily_stargazer': 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c8/Stargazer_Lilies_in_Old_Wilmington%2C_North_Carolina.jpg/1920px-Stargazer_Lilies_in_Old_Wilmington%2C_North_Carolina.jpg',
    'sunflower_bee_macro': 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/50/Sunflower_and_a_bee.jpg/1920px-Sunflower_and_a_bee.jpg',
    'sunflower_field_1': 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/de/Sunflower_Field_%287848091146%29.jpg/1920px-Sunflower_Field_%287848091146%29.jpg',
    'sunflower_field_2': 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/42/Sunflower_Field_-_Nebraska_USA.jpg/1920px-Sunflower_Field_-_Nebraska_USA.jpg',
    'sunflower_vase': 'https://upload.wikimedia.org/wikipedia/commons/0/0d/HK_big_yellow_3_Sunflower_Aug-2012.jpg',
    'sunflower_rose_gold': 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ab/DFC_0794_Bright_bouquet_of_pink_roses_and_a_cheerful_sunflower_arranged_with_greenery_on_a_golden_surface.jpg/1920px-DFC_0794_Bright_bouquet_of_pink_roses_and_a_cheerful_sunflower_arranged_with_greenery_on_a_golden_surface.jpg',
    'mixed_rainbow_spring': 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Bunter_Fr%C3%BChlingsstrau%C3%9F_Colorful_Spring_Bouquet_%28207270593%29.jpeg/1920px-Bunter_Fr%C3%BChlingsstrau%C3%9F_Colorful_Spring_Bouquet_%28207270593%29.jpeg',
    'mixed_gerbera_dark': 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/9c/COLORFUL_SPRING_BOUQUET.jpg/1920px-COLORFUL_SPRING_BOUQUET.jpg',
    'mixed_autumn_desk': 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8d/Colorful_flowers_on_a_desk.jpg/1920px-Colorful_flowers_on_a_desk.jpg',
    'wildflower_pastel': 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/Bouquet_of_wild_flowers_1.jpg/1920px-Bouquet_of_wild_flowers_1.jpg',
    'peony_2': 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d2/Bouquet_of_peonies_02.JPG/1920px-Bouquet_of_peonies_02.JPG',
    'peony_3': 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c0/Bouquet_of_peonies_03.JPG/1920px-Bouquet_of_peonies_03.JPG',
    'peony_vase': 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/00/Peonies_in_a_vase.jpg/1920px-Peonies_in_a_vase.jpg',
    'gerbera_pink_1': 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/71/Barbeton_daisy_%286176641267%29.jpg/1920px-Barbeton_daisy_%286176641267%29.jpg',
    'gerbera_pink_2': 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8d/Barbeton_daisy_%286176645509%29.jpg/1920px-Barbeton_daisy_%286176645509%29.jpg',
    'gerbera_pink_3': 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/61/Barbeton_daisy_%286177171554%29.jpg/1920px-Barbeton_daisy_%286177171554%29.jpg',
    'carnation_pink_unsplash': 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/75/Pink_carnation_bouquets_%28Unsplash%29.jpg/1920px-Pink_carnation_bouquets_%28Unsplash%29.jpg',
    'hydrangea_bouquet': 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5a/Hydrangea_bouquet_%289580026536%29.jpg/1920px-Hydrangea_bouquet_%289580026536%29.jpg',
    'hydrangea_wedding_elegant': 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/bb/Rose_hydrangea_calla_wedding_bouquet.jpg/1920px-Rose_hydrangea_calla_wedding_bouquet.jpg',
    'wedding_beach_pastel': 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/49/Beach_Wedding_Bouquet.jpg/1920px-Beach_Wedding_Bouquet.jpg',
    'wedding_bridal_generic': 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/11/Bridal_bouquet.jpg/1920px-Bridal_bouquet.jpg',
    'wedding_anniversary_60th': 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3b/Photo_of_a_flower_arrangement_we_received_on_our_60th_wedding_anniversary.jpg/1920px-Photo_of_a_flower_arrangement_we_received_on_our_60th_wedding_anniversary.jpg',
    'wedding_bengali_marigold': 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/62/A_Bengali_Hindu_Wedding_Arrangement_%28Chadhna_Tala%29.jpg/1920px-A_Bengali_Hindu_Wedding_Arrangement_%28Chadhna_Tala%29.jpg',
    'marigold_garland_closeup2': 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/71/Marigold_Flowers_as_garland_in_India.jpg/1920px-Marigold_Flowers_as_garland_in_India.jpg',
    'nepal_garland_vendor': 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e8/Nepal_and_west_Papua_%2832750936958%29.jpg/1920px-Nepal_and_west_Papua_%2832750936958%29.jpg',
    'nepal_temple_janakpur': 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/fe/Raajshri_Janak_Temple_Janakpur_Dhanusha_Nepal_Rajesh_Dhungana.jpg/1920px-Raajshri_Janak_Temple_Janakpur_Dhanusha_Nepal_Rajesh_Dhungana.jpg',
    'nepal_temple_offering': 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4b/Offering_Flower_in_Vase_Mahankaal_Temple%2C_Kathmandu-6323.jpg/1920px-Offering_Flower_in_Vase_Mahankaal_Temple%2C_Kathmandu-6323.jpg',
    'nepal_flowers_garden': 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/de/Flowers_Kathmandu.jpg/1920px-Flowers_Kathmandu.jpg',
    'nepal_floating_flowers': 'https://upload.wikimedia.org/wikipedia/commons/8/8d/The_Dwarika%27s_Hotel%2C_Kathmandu_Floating_Flowers_4531135816.jpg',
    'dried_strawflower_colorful': 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/06/Dried_flower_bouquets_%287566089788%29.jpg/1920px-Dried_flower_bouquets_%287566089788%29.jpg',
    'dried_vase_sparse': 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/ce/2026.02.27_Hliak_with_Bouquet_of_Dried_Flowers_and_Straw_Flowers_in_Interior_of_Rajca_Manor_Museum.jpg/1920px-2026.02.27_Hliak_with_Bouquet_of_Dried_Flowers_and_Straw_Flowers_in_Interior_of_Rajca_Manor_Museum.jpg',
    'fondant_cake_roses': 'https://upload.wikimedia.org/wikipedia/commons/1/1a/FondantCake.png',
    'shop_storefront': 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f7/24h_unmanned_flower_shop.jpg/1920px-24h_unmanned_flower_shop.jpg',
    'shop_market_display': 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a0/Flowers_for_sale_at_Bedford_Fruit_Market%2C_Williamsburg%2C_Brooklyn%2C_New_York_-_20221123.jpg/1920px-Flowers_for_sale_at_Bedford_Fruit_Market%2C_Williamsburg%2C_Brooklyn%2C_New_York_-_20221123.jpg',
    'shop_retail_display': 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4c/Retail_flower_display.JPG/1920px-Retail_flower_display.JPG',
    'greenhouse_blooming': 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c9/Blooming_garden_center.jpg/1920px-Blooming_garden_center.jpg',
}


CATEGORIES = [
    dict(name='Roses', nepali_name='गुलाब', slug='roses', icon='fa-solid fa-heart',
         image_url=IMG['rose_garden_red'], order=1,
         description='Timeless, romantic and endlessly elegant — our roses are hand-picked daily for freshness.'),
    dict(name='Tulips', nepali_name='ट्युलिप', slug='tulips', icon='fa-solid fa-fan',
         image_url=IMG['tulip_orange_table'], order=2,
         description='Vibrant Dutch tulips in every shade, flown in fresh to brighten any room.'),
    dict(name='Lilies', nepali_name='लिली', slug='lilies', icon='fa-solid fa-spa',
         image_url=IMG['lily_stargazer'], order=3,
         description='Graceful, fragrant lilies that bring a touch of elegance to every occasion.'),
    dict(name='Sunflowers', nepali_name='सूर्यमुखी', slug='sunflowers', icon='fa-solid fa-sun',
         image_url=IMG['sunflower_field_2'], order=4,
         description='Bold, cheerful sunflowers that radiate warmth and happiness.'),
    dict(name='Mixed Bouquets', nepali_name='मिश्रित गुच्छा', slug='mixed-bouquets', icon='fa-solid fa-seedling',
         image_url=IMG['mixed_rainbow_spring'], order=5,
         description='Artfully combined seasonal blooms for a rich, colourful statement.'),
    dict(name='Birthday Flowers', nepali_name='जन्मदिन फूल', slug='birthday-flowers', icon='fa-solid fa-cake-candles',
         image_url=IMG['mixed_gerbera_dark'], order=6, is_occasion=True,
         description='Bright, joyful arrangements to make their birthday bloom.'),
    dict(name='Anniversary Flowers', nepali_name='वार्षिकोत्सव फूल', slug='anniversary-flowers', icon='fa-solid fa-heart-circle-check',
         image_url=IMG['rose_single_dramatic'], order=7, is_occasion=True,
         description='Romantic arrangements to celebrate every year of love.'),
    dict(name='Wedding Flowers', nepali_name='विवाह फूल', slug='wedding-flowers', icon='fa-solid fa-ring',
         image_url=IMG['wedding_beach_pastel'], order=8, is_occasion=True,
         description='Bridal bouquets and mandap florals crafted for your big day.'),
    dict(name='Dried Flowers', nepali_name='सुकेका फूल', slug='dried-flowers', icon='fa-solid fa-leaf',
         image_url=IMG['dried_strawflower_colorful'], order=9,
         description='Everlasting dried arrangements that keep their beauty for months.'),
    dict(name='Gift Hampers', nepali_name='उपहार', slug='gift-hampers', icon='fa-solid fa-gift',
         image_url=IMG['hydrangea_wedding_elegant'], order=10,
         description='Flowers paired with chocolates, wine and treats — a gift they will remember.'),
]

OCCASION_EXTRA = [
    dict(name='Dashain & Tihar Specials', nepali_name='दशैं तिहार विशेष', slug='dashain-tihar-specials',
         icon='fa-solid fa-star-of-life', image_url=IMG['nepal_garland_vendor'], order=11, is_occasion=True,
         description='Festive marigold and mixed flower specials for Dashain and Tihar celebrations.'),
    dict(name='Get Well Soon', nepali_name='चाँडै निको होस्', slug='get-well-soon',
         icon='fa-solid fa-hand-holding-heart', image_url=IMG['wildflower_pastel'], order=12, is_occasion=True,
         description='Gentle, cheerful blooms to brighten someone’s recovery.'),
]

PRODUCTS = [
    # Roses
    dict(name='Royal Red Rose Bouquet', category='roses', price=2200, compare_at_price=2600,
         stem_count='12 red roses', image=IMG['rose_red_1'], image2=IMG['rose_red_hand'],
         short='A classic dozen of velvety red roses, hand-tied with eucalyptus.',
         desc='Twelve premium long-stem red roses wrapped with fresh eucalyptus and finished in soft kraft paper with satin ribbon. A timeless way to say "I love you." Freshly cut and delivered same day across the Kathmandu Valley.',
         featured=True, bestseller=True),
    dict(name='Classic Dozen Red Roses', category='roses', price=2800,
         stem_count='12 roses, vase optional', image=IMG['rose_red_2'], image2=IMG['rose_garden_red'],
         short='Twelve deep-red roses arranged in a rounded, romantic dome.',
         desc='Our signature dome-style arrangement of twelve deep red roses, complemented with seasonal greens. Available with or without a glass vase. Perfect for anniversaries and grand romantic gestures.',
         bestseller=True, vase=True),
    dict(name='Pastel Pink Rose Basket', category='roses', price=3200,
         stem_count='18 pink roses', image=IMG['rose_pink_lush'], image2=IMG['wildflower_pastel'],
         short='Soft blush roses arranged in a woven cane basket.',
         desc='Eighteen blush-pink roses nestled in a handwoven cane basket with baby’s breath and trailing greenery. A graceful gift for mothers, mentors, and cherished friends.',
         featured=True, vase=True),
    dict(name='White Rose Elegance Bouquet', category='roses', price=2600,
         stem_count='15 white roses', image=IMG['rose_white_vase'], image2=IMG['rose_white_painting'],
         short='Pure white roses for moments that call for quiet elegance.',
         desc='Fifteen pristine white roses hand-tied with silver dollar eucalyptus. Ideal for sympathy, weddings, or simply celebrating new beginnings.',
         new=True),

    # Tulips
    dict(name='Dutch Tulip Delight', category='tulips', price=2400,
         stem_count='20 mixed tulips', image=IMG['tulip_orange_table'], image2=IMG['tulip_rainbow_bunch'],
         short='Twenty fresh Dutch tulips in a lively mixed palette.',
         desc='A cheerful bunch of twenty imported Dutch tulips in shades of pink, yellow and red, wrapped in textured paper. Tulips continue to grow after cutting, giving your gift days of changing beauty.',
         bestseller=True),
    dict(name='Rainbow Tulip Bunch', category='tulips', price=2900,
         stem_count='24 tulips', image=IMG['tulip_field_wide'], image2=IMG['tulip_field_colorful'],
         short='A joyful rainbow mix of twenty-four premium tulips.',
         desc='Twenty-four tulips spanning the full colour spectrum, gathered into a generous, garden-style bunch. A statement gift for someone who loves bold colour.',
         featured=True),
    dict(name='Pink Tulip Romance', category='tulips', price=2100,
         stem_count='15 pink tulips', image=IMG['tulip_pink_clean'], image2=IMG['tulip_pink_drop'],
         short='Delicate pink tulips for a soft, romantic gesture.',
         desc='Fifteen soft pink tulips finished with a satin wrap. Simple, elegant, and perfect for a first date or a quiet thank-you.', new=True),

    # Lilies
    dict(name='White Lily Grace Bouquet', category='lilies', price=2700,
         stem_count='6 stems oriental lily', image=IMG['lily_white_table'], image2=IMG['lily_white_macro'],
         short='Fragrant white oriental lilies with elegant, open blooms.',
         desc='Six stems of oriental lilies known for their heady fragrance and star-shaped blooms, paired with soft greenery. A graceful choice for congratulations or sympathy.',
         featured=True),
    dict(name='Stargazer Lily Basket', category='lilies', price=3100,
         stem_count='8 stems stargazer lily', image=IMG['lily_stargazer'], image2=IMG['lily_white_macro'],
         short='Vivid pink stargazer lilies arranged in a ceramic basket.',
         desc='Eight dramatic stargazer lilies with their signature crimson-pink petals, arranged in a natural cane basket. Long-lasting and deeply fragrant.', vase=True),
    dict(name='Lily & Rose Fusion', category='lilies', price=3400,
         stem_count='6 lilies + 10 roses', image=IMG['lily_white_table'], image2=IMG['rose_red_1'],
         short='White lilies and blush roses combined for maximum elegance.',
         desc='A refined pairing of white oriental lilies and blush pink roses, finished with eucalyptus sprigs. One of our most requested arrangements for anniversaries.', bestseller=True),

    # Sunflowers
    dict(name='Sunshine Sunflower Bunch', category='sunflowers', price=1800,
         stem_count='8 sunflowers', image=IMG['sunflower_bee_macro'], image2=IMG['sunflower_field_1'],
         short='Eight cheerful sunflowers wrapped in rustic brown paper.',
         desc='A simple, joyful bunch of eight large-headed sunflowers. Guaranteed to bring a smile — perfect for birthdays, get-well wishes, or just because.', bestseller=True),
    dict(name='Golden Sunflower Basket', category='sunflowers', price=2300,
         stem_count='10 sunflowers', image=IMG['sunflower_vase'], image2=IMG['sunflower_field_2'],
         short='A generous basket of golden sunflowers and green accents.',
         desc='Ten sunflowers arranged in a woven basket with lush foliage. Bright and long-lasting, this arrangement brings warmth to any home or office.', vase=True),
    dict(name='Sunflower & Daisy Mix', category='sunflowers', price=2000,
         stem_count='6 sunflowers + daisies', image=IMG['sunflower_rose_gold'], image2=IMG['gerbera_pink_1'],
         short='Sunflowers paired with gerbera daisies for a fresh garden feel.',
         desc='A fresh, garden-inspired mix of sunflowers and gerbera daisies gathered with seasonal greens. A wonderfully cheerful gift for any occasion.', new=True),

    # Mixed Bouquets
    dict(name='Everbloom Signature Mix', category='mixed-bouquets', price=3500, compare_at_price=3900,
         stem_count='Seasonal mixed stems', image=IMG['mixed_rainbow_spring'], image2=IMG['peony_2'],
         short='Our most-loved seasonal mix, curated fresh by our florists daily.',
         desc='Our signature arrangement changes with the season, always featuring the freshest premium blooms available — think garden roses, peonies, ranunculus and delicate fillers. No two bouquets are exactly alike.',
         featured=True, bestseller=True),
    dict(name='Garden Fresh Mixed Bouquet', category='mixed-bouquets', price=2600,
         stem_count='Mixed seasonal stems', image=IMG['mixed_gerbera_dark'], image2=IMG['mixed_autumn_desk'],
         short='A relaxed, garden-style gathering of seasonal flowers.',
         desc='Loose and romantic, this garden-style bouquet combines whatever is freshest that day — from gerbera to spray roses — for an effortlessly beautiful gift.'),
    dict(name='Pastel Dream Bouquet', category='mixed-bouquets', price=2900,
         stem_count='Mixed pastel stems', image=IMG['wildflower_pastel'], image2=IMG['peony_3'],
         short='Soft pastel tones in a dreamy, romantic bouquet.',
         desc='A soft palette of blush, lilac and cream blooms come together in this dreamy bouquet, perfect for baby showers, birthdays, or simply brightening someone’s day.', new=True),
    dict(name='Vibrant Spring Mix', category='mixed-bouquets', price=2750,
         stem_count='Mixed spring stems', image=IMG['nepal_flowers_garden'], image2=IMG['mixed_autumn_desk'],
         short='A lively mix bursting with spring colour.',
         desc='Bold and bright, this mix layers vivid seasonal flowers for a gift that demands attention — ideal for celebrations and congratulations.'),

    # Birthday
    dict(name='Happy Birthday Bloom Box', category='birthday-flowers', price=2600,
         stem_count='Mixed birthday blooms', image=IMG['gerbera_pink_3'], image2=IMG['carnation_pink_unsplash'],
         short='A festive box of bright blooms to celebrate their big day.',
         desc='A cheerful hatbox arrangement bursting with gerberas, roses and seasonal fillers in birthday-bright colours. Comes with a complimentary "Happy Birthday" card.',
         featured=True, bestseller=True, vase=True),
    dict(name='Confetti Birthday Bouquet', category='birthday-flowers', price=2400,
         stem_count='Mixed colourful stems', image=IMG['gerbera_pink_2'], image2=IMG['gerbera_pink_1'],
         short='A playful, colour-popping bouquet for birthday celebrations.',
         desc='Vivid multicoloured gerbera daisies tied together for a fun, celebratory feel — because birthdays deserve a burst of colour.'),
    dict(name='Birthday Cake & Flowers Combo', category='birthday-flowers', price=3800,
         stem_count='Bouquet + cake add-on', image=IMG['fondant_cake_roses'], image2=IMG['mixed_gerbera_dark'],
         short='A birthday bouquet paired with a delicious mini celebration cake.',
         desc='Our birthday bouquet paired with a freshly baked mini celebration cake, decorated with delicate sugar roses — a complete birthday-in-a-box surprise.', new=True),

    # Anniversary
    dict(name='Eternal Love Anniversary Bouquet', category='anniversary-flowers', price=3600,
         stem_count='24 roses', image=IMG['rose_red_1'], image2=IMG['rose_single_dramatic'],
         short='Two dozen red roses for a love that lasts.',
         desc='Twenty-four premium red roses arranged in a lush, romantic dome — an unforgettable gesture for milestone anniversaries.', bestseller=True),
    dict(name='Romantic Rose & Lily Duo', category='anniversary-flowers', price=3900,
         stem_count='Roses + lilies', image=IMG['rose_single_dramatic'], image2=IMG['lily_white_table'],
         short='Roses and lilies combined for a rich, romantic statement.',
         desc='A luxurious pairing of garden roses and fragrant lilies, styled in a low, romantic arrangement perfect for a candlelit anniversary dinner table.', featured=True),
    dict(name='Golden Years Anniversary Basket', category='anniversary-flowers', price=4200,
         stem_count='Premium mixed stems', image=IMG['peony_vase'], image2=IMG['peony_3'],
         short='An elegant premium basket for milestone anniversaries.',
         desc='A grand, premium basket arrangement featuring peonies and seasonal luxury blooms — reserved for the anniversaries worth celebrating in style.', vase=True),

    # Wedding
    dict(name='Bridal Elegance Bouquet', category='wedding-flowers', price=5500,
         stem_count='Bridal hand-tied', image=IMG['wedding_beach_pastel'], image2=IMG['rose_bridal_white'],
         short='A romantic hand-tied bridal bouquet in ivory and blush.',
         desc='A cascading hand-tied bridal bouquet featuring garden roses, ranunculus and trailing greenery in soft ivory and blush tones. Custom colour palettes available on request.',
         featured=True),
    dict(name='Wedding Aisle Arrangement', category='wedding-flowers', price=6500,
         stem_count='Large statement piece', image=IMG['wedding_anniversary_60th'], image2=IMG['wedding_bridal_generic'],
         short='A grand floral piece designed for aisles and mandaps.',
         desc='A large-scale floral arrangement designed to make a statement at your mandap or aisle entrance. Fully customisable in colour and flower type for your wedding theme.'),
    dict(name='Mandap Marigold & Rose Special', category='wedding-flowers', price=4800,
         stem_count='Marigold + rose garlands', image=IMG['marigold_garland_closeup2'], image2=IMG['wedding_bengali_marigold'],
         short='Traditional marigold and rose styling for Nepali weddings.',
         desc='A traditional-meets-modern arrangement combining vibrant marigolds and roses, styled for mandap decor and Nepali wedding ceremonies.', new=True),

    # Dried Flowers
    dict(name='Rustic Dried Bouquet', category='dried-flowers', price=1900,
         stem_count='Dried mixed stems', image=IMG['dried_strawflower_colorful'], image2=IMG['dried_vase_sparse'],
         short='A rustic, long-lasting bouquet of naturally dried flowers.',
         desc='A beautifully textured bouquet of dried strawflowers and wildflowers that keeps its charm for months without water. A wonderful low-maintenance gift.', bestseller=True),
    dict(name='Everlasting Dried Flower Bunch', category='dried-flowers', price=2100,
         stem_count='Dried mixed stems', image=IMG['dried_vase_sparse'],
         short='A soft, everlasting bunch of naturally dried blooms.',
         desc='A gently textured bunch of naturally dried flowers — an on-trend, everlasting accent that keeps its beauty for months with zero maintenance.', new=True),

    # Gift Hampers
    dict(name='Flowers & Chocolate Hamper', category='gift-hampers', price=3300,
         stem_count='Bouquet + chocolate box', image=IMG['hydrangea_bouquet'], image2=IMG['carnation_pink_unsplash'],
         short='A fresh bouquet paired with premium assorted chocolates.',
         desc='A charming hand-tied hydrangea bouquet presented alongside a box of premium assorted chocolates — a complete gift for any celebration.', featured=True),
    dict(name='Blooms & Wine Gift Box', category='gift-hampers', price=4500,
         stem_count='Bouquet + wine bottle', image=IMG['hydrangea_wedding_elegant'], image2=IMG['greenhouse_blooming'],
         short='Fresh blooms paired with a bottle of wine, beautifully boxed.',
         desc='An elegant gift box combining a rose, hydrangea and calla lily bouquet with a bottle of wine, wrapped and ribboned for an impressive presentation.', bestseller=True),

    # Dashain & Tihar Special
    dict(name='Nepali Festive Hamper', category='dashain-tihar-specials', price=3900,
         stem_count='Marigold garland + treats', image=IMG['nepal_garland_vendor'], image2=IMG['nepal_temple_offering'],
         short='A festive hamper of marigolds and treats for Dashain & Tihar.',
         desc='Celebrate Dashain and Tihar with this festive hamper featuring traditional marigold garlands paired with sweets and treats — delivered to your loved ones anywhere in Nepal.', new=True, featured=True),

    # Get Well Soon
    dict(name='Cheerful Get Well Bouquet', category='get-well-soon', price=1900,
         stem_count='Mixed gerbera & wildflowers', image=IMG['gerbera_pink_1'], image2=IMG['wildflower_pastel'],
         short='A bright, gentle bouquet to bring comfort and cheer.',
         desc='A soft, uplifting mix of gerbera daisies and wildflowers, thoughtfully arranged to bring warmth and encouragement to someone on the mend.', new=True),
]

REVIEWERS = [
    'Priya Shrestha', 'Anish Gurung', 'Sabina Rai', 'Bibek Thapa', 'Nisha Maharjan',
    'Suman Karki', 'Anjali Tamang', 'Rohit Shakya', 'Manisha Adhikari', 'Prakash Lama',
    'Kabita Basnet', 'Aayush Poudel',
]

REVIEW_COMMENTS = [
    ('The flowers were fresher than I expected and lasted almost two weeks! Delivery to Baneshwor was right on time.', 5),
    ('Beautifully arranged, exactly like the photos. My mother loved her birthday bouquet.', 5),
    ('Great quality and the packaging felt very premium. Will definitely order again for our anniversary.', 5),
    ('Ordered for a same-day delivery in Lalitpur and it arrived within hours. Very impressed!', 5),
    ('Lovely bouquet, though I wish there were a few more stems for the price.', 4),
    ('Everbloom made my wedding decor so easy to plan from Kathmandu. Highly recommend their team.', 5),
    ('Good service overall, flowers were fresh but arrived a little later than the estimated slot.', 4),
    ('The dried flower bouquet is gorgeous and still looks perfect a month later.', 5),
    ('Customer support helped me change the delivery address last minute — really appreciated it.', 5),
    ('Nicely packed and the card message was a thoughtful touch. Recipient was delighted.', 5),
]


class Command(BaseCommand):
    help = 'Seed the database with Everbloom Nepal demo categories, products and reviews.'

    def handle(self, *args, **options):
        self.stdout.write('Seeding categories...')
        category_objs = {}
        for cat in CATEGORIES + OCCASION_EXTRA:
            obj, _ = Category.objects.update_or_create(
                slug=cat['slug'],
                defaults={k: v for k, v in cat.items() if k != 'slug'} | {'is_occasion': cat.get('is_occasion', False)},
            )
            category_objs[cat['slug']] = obj
        self.stdout.write(self.style.SUCCESS(f'  {len(category_objs)} categories ready.'))

        self.stdout.write('Seeding products...')
        product_objs = []
        for p in PRODUCTS:
            obj, _ = Product.objects.update_or_create(
                name=p['name'],
                defaults=dict(
                    category=category_objs[p['category']],
                    price=Decimal(str(p['price'])),
                    compare_at_price=Decimal(str(p['compare_at_price'])) if p.get('compare_at_price') else None,
                    short_description=p['short'],
                    description=p['desc'],
                    image_url=p['image'],
                    image_url_secondary=p.get('image2', ''),
                    stem_count=p.get('stem_count', ''),
                    vase_included=p.get('vase', False),
                    is_featured=p.get('featured', False),
                    is_bestseller=p.get('bestseller', False),
                    is_new=p.get('new', False),
                    stock=random.randint(15, 60),
                ),
            )
            product_objs.append(obj)
        stale = Product.objects.exclude(name__in=[p['name'] for p in PRODUCTS])
        if stale.exists():
            self.stdout.write(self.style.WARNING(f'  Removing {stale.count()} stale product(s) no longer in seed data.'))
            stale.delete()
        self.stdout.write(self.style.SUCCESS(f'  {len(product_objs)} products ready.'))

        self.stdout.write('Seeding reviews...')
        review_count = 0
        rng = random.Random(42)
        for product in product_objs:
            if product.reviews.exists():
                continue
            num_reviews = rng.randint(2, 5)
            chosen = rng.sample(REVIEW_COMMENTS, num_reviews)
            names = rng.sample(REVIEWERS, num_reviews)
            for name, (comment, rating) in zip(names, chosen):
                Review.objects.create(product=product, name=name, rating=rating, comment=comment)
                review_count += 1
        self.stdout.write(self.style.SUCCESS(f'  {review_count} reviews created.'))

        if not User.objects.filter(username='admin').exists():
            User.objects.create_superuser('admin', 'admin@everbloomnepal.com', 'EverbloomAdmin123')
            self.stdout.write(self.style.SUCCESS('  Superuser "admin" created (password: EverbloomAdmin123).'))

        self.stdout.write(self.style.SUCCESS('Everbloom Nepal demo data seeded successfully!'))
