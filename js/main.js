/* Monotales — lang toggle + scroll reveal */
(function () {
  'use strict';

  /* ── Language toggle ──────────────────────────── */
  const LANG_KEY = 'mt_lang';
  let currentLang = localStorage.getItem(LANG_KEY) || 'fr';

  function applyLang(lang) {
    currentLang = lang;
    localStorage.setItem(LANG_KEY, lang);
    document.documentElement.classList.toggle('lang-en', lang === 'en');

    const btnFr = document.getElementById('btn-fr');
    const btnEn = document.getElementById('btn-en');
    if (btnFr) {
      btnFr.style.opacity = lang === 'fr' ? '1' : '0.42';
      btnFr.setAttribute('aria-pressed', String(lang === 'fr'));
    }
    if (btnEn) {
      btnEn.style.opacity = lang === 'en' ? '1' : '0.42';
      btnEn.setAttribute('aria-pressed', String(lang === 'en'));
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    applyLang(currentLang);

    const btnFr = document.getElementById('btn-fr');
    const btnEn = document.getElementById('btn-en');
    if (btnFr) btnFr.addEventListener('click', () => applyLang('fr'));
    if (btnEn) btnEn.addEventListener('click', () => applyLang('en'));
  });

  /* ── Scroll reveal ────────────────────────────── */
  document.addEventListener('DOMContentLoaded', function () {
    const cards = document.querySelectorAll('[data-reveal]');
    if (!cards.length) return;

    if (!('IntersectionObserver' in window)) {
      cards.forEach(el => el.classList.add('is-visible'));
      return;
    }

    const observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.04 });

    // Stagger: slight delay per card to create waterfall effect
    cards.forEach(function (el, i) {
      setTimeout(function () {
        observer.observe(el);
      }, Math.min(i * 60, 500));
    });
  });

})();
