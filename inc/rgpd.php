<?php
defined('ABSPATH') || exit;

/* ══════════════════════════════════════════════════════
   MONOTALES — RGPD / Consentement cookies
   ══════════════════════════════════════════════════════ */

// Bandeau cookie injecté avant </body>
add_action('wp_footer', function (): void { ?>
<div id="mt-cookie" role="dialog" aria-label="Gestion des cookies" aria-live="polite">
  <p class="mt-cookie-text">
    <span class="mt-fr">Ce site utilise uniquement des cookies techniques indispensables à son fonctionnement. Aucun cookie publicitaire ni de traçage tiers.</span>
    <span class="mt-en">This site uses only technical cookies essential to its operation. No advertising or third-party tracking cookies.</span>
  </p>
  <div class="mt-cookie-actions">
    <button id="mt-cookie-ok" class="mt-cookie-btn mt-cookie-btn--ok">
      <span class="mt-fr">Accepter</span><span class="mt-en">Accept</span>
    </button>
    <button id="mt-cookie-no" class="mt-cookie-btn">
      <span class="mt-fr">Continuer sans accepter</span><span class="mt-en">Continue without accepting</span>
    </button>
  </div>
  <a href="<?php echo esc_url(home_url('/politique-de-confidentialite/')); ?>" class="mt-cookie-link">
    <span class="mt-fr">Politique de confidentialité</span><span class="mt-en">Privacy policy</span>
  </a>
</div>
<?php }, 20);

// JS cookie consent
add_action('wp_enqueue_scripts', function (): void {
    wp_enqueue_script('mt-cookie', get_template_directory_uri() . '/js/cookie.js', [], wp_get_theme()->get('Version'), true);
}, 11);
