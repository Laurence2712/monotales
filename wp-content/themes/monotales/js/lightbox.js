/* Monotales — Lightbox */
(function () {
  'use strict';

  var overlay, lbImg, lbMeta, lbCounter;
  var items = [];

  function build() {
    overlay = document.createElement('div');
    overlay.id = 'mt-lb';
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.innerHTML =
      '<button class="mt-lb-close" aria-label="Fermer">&times;</button>' +
      '<button class="mt-lb-prev" aria-label="Photo précédente">&#8592;</button>' +
      '<button class="mt-lb-next" aria-label="Photo suivante">&#8594;</button>' +
      '<figure class="mt-lb-figure">' +
        '<img class="mt-lb-img" alt="" loading="eager">' +
        '<figcaption class="mt-lb-cap">' +
          '<p class="mt-lb-meta"></p>' +
          '<p class="mt-lb-title mt-fr"></p>' +
          '<p class="mt-lb-title mt-en"></p>' +
          '<div class="mt-lb-foot">' +
            '<a class="mt-lb-read mt-fr" href="#">Lire &rarr;</a>' +
            '<a class="mt-lb-read mt-en" href="#">Read &rarr;</a>' +
            '<span class="mt-lb-counter"></span>' +
          '</div>' +
        '</figcaption>' +
      '</figure>';
    document.body.appendChild(overlay);

    lbImg     = overlay.querySelector('.mt-lb-img');
    lbMeta    = overlay.querySelector('.mt-lb-meta');
    lbCounter = overlay.querySelector('.mt-lb-counter');

    overlay.querySelector('.mt-lb-close').addEventListener('click', close);
    overlay.querySelector('.mt-lb-prev').addEventListener('click', function () { nav(-1); });
    overlay.querySelector('.mt-lb-next').addEventListener('click', function () { nav(1); });
    overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });

    document.addEventListener('keydown', function (e) {
      if (!overlay.classList.contains('is-open')) return;
      if (e.key === 'Escape')     { close(); }
      if (e.key === 'ArrowLeft')  { nav(-1); }
      if (e.key === 'ArrowRight') { nav(1); }
    });
  }

  function open(idx) {
    if (!overlay) build();
    items._current = idx;
    render(idx);
    overlay.classList.add('is-open');
    document.body.classList.add('mt-lb-open');
  }

  function close() {
    overlay.classList.remove('is-open');
    document.body.classList.remove('mt-lb-open');
    setTimeout(function () { lbImg.src = ''; }, 350);
  }

  function nav(dir) {
    var next = (items._current + dir + items.length) % items.length;
    items._current = next;
    render(next);
  }

  function render(idx) {
    var d = items[idx];
    lbImg.src = '';
    lbImg.src = d.img;
    lbImg.alt = d.fr;
    lbMeta.textContent = [d.loc, d.date].filter(Boolean).join(' · ');
    overlay.querySelector('.mt-lb-title.mt-fr').textContent = d.fr;
    overlay.querySelector('.mt-lb-title.mt-en').textContent = d.en;
    overlay.querySelectorAll('.mt-lb-read').forEach(function (a) { a.href = d.url; });
    lbCounter.textContent = (idx + 1) + ' / ' + items.length;
  }

  document.addEventListener('DOMContentLoaded', function () {
    var links = document.querySelectorAll('[data-lb]');
    if (!links.length) return;

    items._current = 0;
    links.forEach(function (link) {
      var d;
      try { d = JSON.parse(link.getAttribute('data-lb')); } catch (e) { return; }
      var idx = items.length;
      items.push(d);
      link.addEventListener('click', function (e) {
        e.preventDefault();
        open(idx);
      });
    });
  });
})();
