<?php get_header(); ?>

<main style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:60vh;padding:clamp(60px,10vh,120px) clamp(20px,5vw,56px);text-align:center">

  <p class="font-sans uppercase" style="font-size:10px;letter-spacing:0.32em;color:rgba(236,234,228,0.36);margin-bottom:24px">404</p>

  <h1 class="font-display italic" style="font-weight:300;font-size:clamp(36px,6vw,72px);line-height:1.02;color:#ECEAE4;margin:0 0 20px">
    <span class="mt-fr">Page introuvable</span>
    <span class="mt-en">Page not found</span>
  </h1>

  <p class="font-body" style="font-size:clamp(15px,1.6vw,19px);line-height:1.7;color:rgba(236,234,228,0.56);max-width:38ch;margin:0 0 44px">
    <span class="mt-fr">Cette page n'existe pas ou a été déplacée.</span>
    <span class="mt-en">This page doesn't exist or has been moved.</span>
  </p>

  <a href="<?php echo esc_url(home_url('/')); ?>"
     class="font-sans uppercase"
     style="font-size:11px;letter-spacing:0.24em;color:rgba(236,234,228,0.6);border-bottom:1px solid rgba(236,234,228,0.28);padding-bottom:3px;transition:color .3s ease"
     onmouseover="this.style.color='#ECEAE4'" onmouseout="this.style.color='rgba(236,234,228,0.6)'">
    <span class="mt-fr">← Retour à la galerie</span>
    <span class="mt-en">← Back to gallery</span>
  </a>

</main>

<?php get_footer(); ?>
