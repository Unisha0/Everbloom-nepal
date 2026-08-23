// Everbloom Nepal — front-end interactions (vanilla JS + a little jQuery for WooCommerce events)

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

async function postAjax(action, data = {}) {
  const body = new URLSearchParams({ action, nonce: window.everbloomData?.nonce || '', ...data });
  const res = await fetch(window.everbloomData?.ajaxUrl || '/wp-admin/admin-ajax.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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

  // Quick add-to-cart buttons: WooCommerce's own add-to-cart.js performs the AJAX
  // request (they carry the .ajax_add_to_cart class); we just add loading/toast UX.
  document.querySelectorAll('.js-quick-add').forEach(btn => {
    const originalHtml = btn.innerHTML;
    btn.addEventListener('click', () => {
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    });
    document.body.addEventListener('added_to_cart', () => {
      btn.innerHTML = '<i class="fa-solid fa-check"></i>';
      setTimeout(() => { btn.innerHTML = originalHtml; }, 900);
    });
  });
  if (window.jQuery) {
    // WooCommerce's add-to-cart.js fires this after a successful AJAX add; bump the
    // header badge optimistically and show a toast (server re-renders it on next load).
    jQuery(document.body).on('added_to_cart', () => {
      showToast('Added to your cart.');
      document.querySelectorAll('.cart-count-badge').forEach(el => {
        const next = (parseInt(el.textContent || '0', 10) || 0) + 1;
        updateCartBadges(next);
      });
    });
  }

  // Wishlist toggle buttons
  document.querySelectorAll('.js-wishlist-toggle').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      const productId = btn.dataset.productId;
      try {
        const res = await postAjax('everbloom_wishlist_toggle', { product_id: productId });
        if (res.status === 401) {
          const data = await res.json();
          showToast('Please log in to save items to your wishlist.', 'error');
          setTimeout(() => { window.location.href = data.data ? data.data.login_url : data.login_url; }, 900);
          return;
        }
        const payload = await res.json();
        const data = payload.data || payload;
        if (data.success !== false) {
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

  // Quantity steppers: progressively enhance any WooCommerce .quantity wrapper
  // (product page, cart) with -/+ buttons, matching the original qty-stepper UI.
  document.querySelectorAll('.quantity').forEach(wrap => {
    if (wrap.dataset.ebEnhanced) return;
    const input = wrap.querySelector('input.qty');
    if (!input) return;
    wrap.dataset.ebEnhanced = '1';
    wrap.classList.add('qty-stepper');

    const minus = document.createElement('button');
    minus.type = 'button';
    minus.textContent = '−';
    const plus = document.createElement('button');
    plus.type = 'button';
    plus.textContent = '+';

    wrap.insertBefore(minus, input);
    wrap.appendChild(plus);

    minus.addEventListener('click', () => {
      input.stepDown ? input.stepDown() : (input.value = Math.max(parseInt(input.min || '0', 10), (parseInt(input.value, 10) || 1) - 1));
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });
    plus.addEventListener('click', () => {
      input.stepUp ? input.stepUp() : (input.value = (parseInt(input.value, 10) || 1) + 1);
      input.dispatchEvent(new Event('change', { bubbles: true }));
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

  // Auto-dismiss server-rendered notices (contact form / newsletter flash messages)
  document.querySelectorAll('.eb-message').forEach(msg => {
    setTimeout(() => {
      msg.classList.add('toast-out');
      setTimeout(() => msg.remove(), 300);
    }, 4000);
  });
});
