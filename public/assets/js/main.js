/* Eventzpro Consult — site interactions (no dependencies). */
(function () {
  'use strict';

  var root = document.documentElement;
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var desktop = window.matchMedia('(min-width: 1100px)');

  /* ---------- Sticky header ---------- */
  var header = document.querySelector('[data-header]');
  var onScroll = function () {
    var y = window.scrollY;
    if (header) header.classList.toggle('is-scrolled', y > 40);
    var top = document.querySelector('[data-to-top]');
    if (top) top.classList.toggle('is-visible', y > 700);
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* ---------- Mobile navigation ---------- */
  var toggle = document.querySelector('[data-nav-toggle]');
  var nav = document.querySelector('[data-nav]');
  var setNav = function (open) {
    if (!toggle || !nav) return;
    toggle.setAttribute('aria-expanded', String(open));
    nav.classList.toggle('is-open', open);
    root.classList.toggle('nav-open', open);
  };
  if (toggle) {
    toggle.addEventListener('click', function () {
      setNav(toggle.getAttribute('aria-expanded') !== 'true');
    });
  }
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      setNav(false);
      document.querySelectorAll('.has-mega.is-open').forEach(function (el) { el.classList.remove('is-open'); });
    }
  });
  desktop.addEventListener('change', function () { setNav(false); });

  /* ---------- Services mega menu (click / keyboard) ---------- */
  document.querySelectorAll('[data-mega-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var item = btn.closest('.has-mega');
      var open = !item.classList.contains('is-open');
      item.classList.toggle('is-open', open);
      btn.setAttribute('aria-expanded', String(open));
    });
  });
  document.addEventListener('click', function (e) {
    if (!desktop.matches) return;
    document.querySelectorAll('.has-mega.is-open').forEach(function (item) {
      if (!item.contains(e.target)) {
        item.classList.remove('is-open');
        var b = item.querySelector('[data-mega-toggle]');
        if (b) b.setAttribute('aria-expanded', 'false');
      }
    });
  });

  /* ---------- Back to top ---------- */
  var toTop = document.querySelector('[data-to-top]');
  if (toTop) {
    toTop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
    });
  }

  /* ---------- Revenue bars ---------- */
  document.querySelectorAll('.bar-fill[data-width]').forEach(function (bar) {
    bar.style.setProperty('--w', bar.getAttribute('data-width') + '%');
  });

  /* ---------- Count-up numbers ---------- */
  var format = function (n) { return Math.round(n).toLocaleString('en-US'); };
  var countUp = function (el) {
    var target = parseFloat(el.getAttribute('data-count'));
    if (reduceMotion || !target) { el.textContent = format(target); return; }
    var start = null, duration = 1600;
    var step = function (ts) {
      if (!start) start = ts;
      var p = Math.min((ts - start) / duration, 1);
      el.textContent = format(target * (1 - Math.pow(1 - p, 3)));
      if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  };

  /* ---------- Scroll reveal ---------- */
  var reveals = document.querySelectorAll('.reveal');
  var counters = document.querySelectorAll('[data-count]');
  if ('IntersectionObserver' in window && !reduceMotion) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var el = entry.target;
        if (el.hasAttribute('data-count')) countUp(el);
        else el.classList.add('is-visible');
        io.unobserve(el);
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    reveals.forEach(function (el, i) {
      el.style.setProperty('--delay', (i % 4) * 70 + 'ms');
      io.observe(el);
    });
    counters.forEach(function (el) { el.textContent = '0'; io.observe(el); });
  } else {
    reveals.forEach(function (el) { el.classList.add('is-visible'); });
  }

  /* ---------- Project filters ---------- */
  var filters = document.querySelector('[data-filters]');
  if (filters) {
    var items = document.querySelectorAll('[data-filter-list] [data-tags]');
    var empty = document.querySelector('[data-filter-empty]');
    filters.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-filter]');
      if (!btn) return;
      var key = btn.getAttribute('data-filter');
      filters.querySelectorAll('[data-filter]').forEach(function (b) {
        var on = b === btn;
        b.classList.toggle('is-active', on);
        b.setAttribute('aria-pressed', String(on));
      });
      var shown = 0;
      items.forEach(function (item) {
        var match = key === 'all' || item.getAttribute('data-tags').split(' ').indexOf(key) !== -1;
        item.hidden = !match;
        if (match) { shown++; item.classList.add('is-visible'); }
      });
      if (empty) empty.hidden = shown !== 0;
    });
  }

  /* ---------- Business units sub-menu: Bootstrap ScrollSpy ---------- */
  if (document.querySelector('.subnav') && window.bootstrap && window.bootstrap.ScrollSpy) {
    new window.bootstrap.ScrollSpy(document.body, { target: '.subnav', rootMargin: '0px 0px -55%' });
  }

  /* ---------- Contact form: light client-side checks ---------- */
  var form = document.querySelector('[data-contact-form]');
  if (form) {
    form.addEventListener('submit', function (e) {
      var firstInvalid = null;
      form.querySelectorAll('[required]').forEach(function (field) {
        var ok = field.type === 'checkbox' ? field.checked : field.checkValidity();
        field.setAttribute('aria-invalid', ok ? 'false' : 'true');
        if (!ok && !firstInvalid) firstInvalid = field;
      });
      var consent = form.querySelector('#consent');
      if (consent && !consent.checked) {
        consent.setAttribute('aria-invalid', 'true');
        firstInvalid = firstInvalid || consent;
      }
      if (firstInvalid) {
        e.preventDefault();
        firstInvalid.focus();
        return;
      }
      var submit = form.querySelector('[type="submit"]');
      if (submit) { submit.disabled = true; submit.classList.add('is-loading'); }
    });
  }
})();
