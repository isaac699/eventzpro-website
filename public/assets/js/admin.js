/* Eventzpro admin: sidebar toggle, confirm dialogs, chart sizing (no dependencies, CSP-safe). */
(function () {
  'use strict';

  /* ---------- Mobile sidebar ---------- */
  var sidebar = document.querySelector('[data-sidebar]');
  var toggle = document.querySelector('[data-sidebar-toggle]');
  var setSidebar = function (open) {
    if (!sidebar || !toggle) return;
    sidebar.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', String(open));
  };
  if (toggle) {
    toggle.addEventListener('click', function () { setSidebar(!sidebar.classList.contains('is-open')); });
  }
  document.querySelectorAll('[data-sidebar-close]').forEach(function (el) {
    el.addEventListener('click', function () { setSidebar(false); });
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') setSidebar(false);
  });

  /* ---------- Confirm before destructive actions ---------- */
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!window.confirm(form.getAttribute('data-confirm'))) e.preventDefault();
    });
  });

  /* ---------- Charts: sizes come from data attributes (inline styles are blocked by the CSP) ---------- */
  var apply = function () {
    document.querySelectorAll('[data-h]').forEach(function (bar) {
      bar.style.height = bar.getAttribute('data-h') + '%';
    });
    document.querySelectorAll('[data-w]').forEach(function (bar) {
      bar.style.width = bar.getAttribute('data-w') + '%';
    });
  };
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) apply();
  else requestAnimationFrame(function () { requestAnimationFrame(apply); });
})();
