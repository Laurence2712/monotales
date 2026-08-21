<?php
/**
 * Template Name: Politique de confidentialité
 */
defined('ABSPATH') || exit;
get_header(); ?>

<main class="min-h-screen">
  <div style="padding: clamp(60px,10vh,120px) clamp(20px,5vw,90px) clamp(80px,12vh,140px); max-width: 760px; margin: 0 auto;">

    <h1 class="font-display italic" style="font-size: clamp(32px,5vw,52px); line-height: 1.05; margin-bottom: clamp(40px,6vh,64px);">
      <span class="mt-fr">Politique de confidentialité</span>
      <span class="mt-en">Privacy Policy</span>
    </h1>

    <!-- Cookies -->
    <section style="margin-bottom: clamp(36px,5vh,52px);">
      <h2 class="font-sans uppercase" style="font-size: 11px; letter-spacing: 0.26em; color: rgba(236,234,228,0.5); margin-bottom: 18px;">
        <span class="mt-fr">Cookies</span>
        <span class="mt-en">Cookies</span>
      </h2>
      <div class="font-body" style="font-size: 15px; line-height: 1.72; color: rgba(236,234,228,0.78);">
        <p class="mt-fr" style="margin-bottom: 14px;">Ce site utilise uniquement des cookies techniques strictement nécessaires à son fonctionnement. Aucun cookie publicitaire, aucun traceur tiers, aucun outil d'analyse comportementale n'est déployé.</p>
        <p class="mt-en" style="margin-bottom: 14px;">This site uses only technical cookies strictly necessary for its operation. No advertising cookies, no third-party trackers, and no behavioral analytics tools are deployed.</p>

        <p class="mt-fr" style="margin-bottom: 14px;">Les préférences de langue et de consentement sont stockées localement dans votre navigateur (<em>localStorage</em>) et ne sont jamais transmises à des tiers.</p>
        <p class="mt-en" style="margin-bottom: 14px;">Language and consent preferences are stored locally in your browser (<em>localStorage</em>) and are never transmitted to third parties.</p>
      </div>
    </section>

    <!-- Données personnelles -->
    <section style="margin-bottom: clamp(36px,5vh,52px);">
      <h2 class="font-sans uppercase" style="font-size: 11px; letter-spacing: 0.26em; color: rgba(236,234,228,0.5); margin-bottom: 18px;">
        <span class="mt-fr">Données personnelles</span>
        <span class="mt-en">Personal data</span>
      </h2>
      <div class="font-body" style="font-size: 15px; line-height: 1.72; color: rgba(236,234,228,0.78);">
        <p class="mt-fr" style="margin-bottom: 14px;">Ce site ne collecte aucune donnée personnelle. Aucun formulaire de contact, aucune inscription, aucun traitement de données à caractère personnel n'est mis en œuvre.</p>
        <p class="mt-en" style="margin-bottom: 14px;">This site does not collect any personal data. There are no contact forms, registrations, or processing of personal data of any kind.</p>
      </div>
    </section>

    <!-- Hébergement -->
    <section style="margin-bottom: clamp(36px,5vh,52px);">
      <h2 class="font-sans uppercase" style="font-size: 11px; letter-spacing: 0.26em; color: rgba(236,234,228,0.5); margin-bottom: 18px;">
        <span class="mt-fr">Hébergement</span>
        <span class="mt-en">Hosting</span>
      </h2>
      <div class="font-body" style="font-size: 15px; line-height: 1.72; color: rgba(236,234,228,0.78);">
        <p class="mt-fr" style="margin-bottom: 14px;">Ce site est hébergé par un prestataire professionnel. Les journaux de connexion (adresse IP, date et heure d'accès) sont conservés par l'hébergeur conformément à ses obligations légales, sans traitement de notre part.</p>
        <p class="mt-en" style="margin-bottom: 14px;">This site is hosted by a professional provider. Connection logs (IP address, date and time of access) are retained by the host in accordance with its legal obligations, without any processing on our part.</p>
      </div>
    </section>

    <!-- Droits -->
    <section style="margin-bottom: clamp(36px,5vh,52px);">
      <h2 class="font-sans uppercase" style="font-size: 11px; letter-spacing: 0.26em; color: rgba(236,234,228,0.5); margin-bottom: 18px;">
        <span class="mt-fr">Vos droits</span>
        <span class="mt-en">Your rights</span>
      </h2>
      <div class="font-body" style="font-size: 15px; line-height: 1.72; color: rgba(236,234,228,0.78);">
        <p class="mt-fr" style="margin-bottom: 14px;">Conformément au RGPD (Règlement Général sur la Protection des Données), vous disposez d'un droit d'accès, de rectification et de suppression des données vous concernant. Pour toute demande, contactez-nous à l'adresse indiquée sur ce site.</p>
        <p class="mt-en" style="margin-bottom: 14px;">In accordance with the GDPR (General Data Protection Regulation), you have the right to access, rectify and delete data concerning you. For any request, contact us at the address indicated on this site.</p>
      </div>
    </section>

    <!-- Retour -->
    <a href="<?php echo esc_url(home_url('/')); ?>" class="font-sans uppercase" style="font-size: 10px; letter-spacing: 0.22em; color: rgba(236,234,228,0.44); text-decoration: underline; text-underline-offset: 3px; transition: color .3s ease;" onmouseover="this.style.color='rgba(236,234,228,0.8)'" onmouseout="this.style.color='rgba(236,234,228,0.44)'">
      <span class="mt-fr">← Retour</span>
      <span class="mt-en">← Back</span>
    </a>

  </div>
</main>

<?php get_footer(); ?>
