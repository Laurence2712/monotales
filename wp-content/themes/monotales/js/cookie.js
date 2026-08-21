/* Monotales — Cookie consent */
(function () {
  'use strict';

  var KEY = 'mt_consent';

  function get() {
    try { return localStorage.getItem(KEY); } catch(e) { return null; }
  }
  function set(v) {
    try { localStorage.setItem(KEY, v); } catch(e) {}
  }

  document.addEventListener('DOMContentLoaded', function () {
    var banner = document.getElementById('mt-cookie');
    if (!banner) return;
    if (get()) return; // already decided

    // Show after slight delay so page paints first
    setTimeout(function () {
      banner.classList.add('is-visible');
    }, 800);

    function dismiss(val) {
      set(val);
      banner.classList.remove('is-visible');
      setTimeout(function () { banner.style.display = 'none'; }, 500);
    }

    var ok = document.getElementById('mt-cookie-ok');
    var no = document.getElementById('mt-cookie-no');
    if (ok) ok.addEventListener('click', function () { dismiss('accepted'); });
    if (no) no.addEventListener('click', function () { dismiss('refused'); });
  });
})();
