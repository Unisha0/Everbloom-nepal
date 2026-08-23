// Everbloom Nepal — front-end interactions (vanilla JS, no build step)

function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
  return null;
}
const CSRF_TOKEN = getCookie('csrftoken');

function showToast(message, type = 'success') {
  const container = document.getElementById('toast-container');
  if (!container) return;
  const toast = document.createElement('div');
  toast.className = 'toast';
  const icon = type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check';
  const color = type === 'error' ? '#c0392b' : '#a8465c';
  toast.innerHTML = `<i class="fa-solid ${icon}" style="color:${color}"></i><span>${message}</span>`;
  container.appendChild(toast);
  setTimeout(() => {
    toast.classList.add('toast-out');
    setTimeout(() => toast.remove(), 300);
  }, 3200);
}

function updateCartBadges(count) {
  document.querySelectorAll('.cart-count-badge').forEach(el => {
    el.textContent = count;
    el.classList.toggle('hidden', count === 0);
  });
}

async function postForm(url, data = {}) {
  const body = new URLSearchParams(data);
  const res = await fetch(url, {
    method: 'POST',
    headers: {
      'X-CSRFToken': CSRF_TOKEN,
      'X-Requested-With': 'XMLHttpRequest',
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body,
  });
  return res;
}

document.addEventListener('DOMContentLoaded', () => {
  // Mobile menu toggle
  const menuBtn = document.getElementById('mobile-menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');
  if (menuBtn && mobileMenu) {
    menuBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
      const icon = menuBtn.querySelector('i');
      icon.classList.toggle('fa-bars');
      icon.classList.toggle('fa-xmark');
    });
  }

  // Search overlay toggle
  const searchBtn = document.getElementById('search-toggle-btn');
  const searchOverlay = document.getElementById('search-overlay');
  const searchClose = document.getElementById('search-close-btn');
  if (searchBtn && searchOverlay) {
    searchBtn.addEventListener('click', () => {
      searchOverlay.classList.remove('hidden');
      setTimeout(() => searchOverlay.querySelector('input')?.focus(), 50);
    });
  }
  if (searchClose) {
    searchClose.addEventListener('click', () => searchOverlay.classList.add('hidden'));
  }

  // Quick add-to-cart (AJAX) buttons on product cards
  document.querySelectorAll('.js-quick-add').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      const productId = btn.dataset.productId;
      const originalHtml = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
      try {
        const res = await postForm(`/shop/cart/add/${productId}/`, { quantity: 1 });
        const data = await res.json();
        if (data.success) {
          updateCartBadges(data.cart_count);
          showToast(data.message);
          btn.innerHTML = '<i class="fa-solid fa-check"></i>';
          setTimeout(() => { btn.innerHTML = originalHtml; btn.disabled = false; }, 900);
        }
      } catch (err) {
        showToast('Something went wrong. Please try again.', 'error');
        btn.innerHTML = originalHtml;
        btn.disabled = false;
      }
    });
  });

  // Wishlist toggle buttons
  document.querySelectorAll('.js-wishlist-toggle').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      const productId = btn.dataset.productId;
      try {
        const res = await postForm(`/shop/wishlist/toggle/${productId}/`);
        if (res.status === 401) {
          const data = await res.json();
          showToast('Please log in to save items to your wishlist.', 'error');
          setTimeout(() => { window.location.href = data.login_url + '?next=' + window.location.pathname; }, 900);
          return;
        }
        const data = await res.json();
        if (data.success) {
          btn.classList.toggle('is-active', data.added);
          const icon = btn.querySelector('i');
          icon.classList.toggle('fa-regular', !data.added);
          icon.classList.toggle('fa-solid', data.added);
          showToast(data.added ? 'Added to your wishlist.' : 'Removed from wishlist.');
        }
      } catch (err) {
        showToast('Something went wrong. Please try again.', 'error');
      }
    });
  });

  // Quantity steppers (product detail + cart page)
  document.querySelectorAll('.qty-stepper').forEach(stepper => {
    const input = stepper.querySelector('input');
    const max = parseInt(input.dataset.max || '99', 10);
    stepper.querySelector('.qty-minus').addEventListener('click', () => {
      input.value = Math.max(1, parseInt(input.value || '1', 10) - 1);
      input.dispatchEvent(new Event('change'));
    });
    stepper.querySelector('.qty-plus').addEventListener('click', () => {
      input.value = Math.min(max, parseInt(input.value || '1', 10) + 1);
      input.dispatchEvent(new Event('change'));
    });
  });

  // Cart page: live update quantity via AJAX
  document.querySelectorAll('.js-cart-qty').forEach(input => {
    input.addEventListener('change', async () => {
      const productId = input.dataset.productId;
      const qty = Math.max(1, parseInt(input.value || '1', 10));
      const res = await postForm(`/shop/cart/update/${productId}/`, { quantity: qty });
      const data = await res.json();
      if (data.success) {
        updateCartBadges(data.cart_count);
        const lineTotalEl = document.querySelector(`.line-total-${productId}`);
        if (lineTotalEl) {
          lineTotalEl.textContent = 'रु. ' + Number(data.line_total).toLocaleString('en-IN');
        }
        const subtotalEl = document.getElementById('cart-subtotal');
        const totalEl = document.getElementById('cart-total');
        const deliveryEl = document.getElementById('cart-delivery-fee');
        if (subtotalEl) subtotalEl.textContent = 'रु. ' + Number(data.cart_subtotal).toLocaleString('en-IN');
        if (totalEl) totalEl.textContent = 'रु. ' + Number(data.cart_total).toLocaleString('en-IN');
        if (deliveryEl) deliveryEl.textContent = Number(data.cart_delivery_fee) === 0 ? 'FREE' : ('रु. ' + Number(data.cart_delivery_fee).toLocaleString('en-IN'));
      }
    });
  });

  // Cart page: remove item
  document.querySelectorAll('.js-cart-remove').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      const productId = btn.dataset.productId;
      const res = await postForm(`/shop/cart/remove/${productId}/`);
      const data = await res.json();
      if (data.success) {
        updateCartBadges(data.cart_count);
        document.getElementById(`cart-row-${productId}`)?.remove();
        if (data.cart_count === 0) window.location.reload();
      }
    });
  });

  // Product detail: thumbnail gallery
  document.querySelectorAll('.js-gallery-thumb').forEach(thumb => {
    thumb.addEventListener('click', () => {
      const mainImg = document.getElementById('product-main-image');
      if (mainImg) mainImg.src = thumb.dataset.fullImage;
      document.querySelectorAll('.js-gallery-thumb').forEach(t => t.classList.remove('border-eb-rose'));
      thumb.classList.add('border-eb-rose');
    });
  });

  // Star rating select on review form
  document.querySelectorAll('.star-select').forEach(group => {
    const labels = group.querySelectorAll('label');
    labels.forEach(label => {
      label.addEventListener('mouseenter', () => {
        const val = parseInt(label.dataset.val, 10);
        labels.forEach(l => {
          l.querySelector('i').className = parseInt(l.dataset.val, 10) <= val ? 'fa-solid fa-star' : 'fa-regular fa-star';
        });
      });
    });
    group.addEventListener('mouseleave', () => {
      const checked = group.querySelector('input:checked');
      const val = checked ? parseInt(checked.value, 10) : 0;
      labels.forEach(l => {
        l.querySelector('i').className = parseInt(l.dataset.val, 10) <= val ? 'fa-solid fa-star' : 'fa-regular fa-star';
      });
    });
  });

  // Scroll reveal animations
  const revealEls = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window && revealEls.length) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    revealEls.forEach(el => observer.observe(el));
  } else {
    revealEls.forEach(el => el.classList.add('is-visible'));
  }

  // Testimonial carousel
  const track = document.getElementById('testimonial-track');
  if (track) {
    const prevBtn = document.getElementById('testimonial-prev');
    const nextBtn = document.getElementById('testimonial-next');
    const scrollAmount = 340;
    prevBtn?.addEventListener('click', () => track.scrollBy({ left: -scrollAmount, behavior: 'smooth' }));
    nextBtn?.addEventListener('click', () => track.scrollBy({ left: scrollAmount, behavior: 'smooth' }));
  }

  // Auto-dismiss server-rendered Django messages
  document.querySelectorAll('.django-message').forEach(msg => {
    setTimeout(() => {
      msg.classList.add('toast-out');
      setTimeout(() => msg.remove(), 300);
    }, 4000);
  });
});
