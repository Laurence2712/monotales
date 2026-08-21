<?php
/*
 * Template Name: Boutique
 * Template Post Type: page
 *
 * Infrastructure en place — page non référencée dans la navigation.
 * Pour activer : créer une page WordPress avec ce template, puis
 * décommenter le lien dans header.php.
 */
get_header();

$q = mt_get_produits(true);
?>

<section class="animate-fade mx-auto" style="padding:clamp(54px,10vh,110px) clamp(20px,5vw,56px) clamp(70px,10vh,120px);max-width:1160px">

  <div class="text-center" style="margin-bottom:clamp(40px,6vh,72px)">
    <h1 class="font-display text-paper m-0" style="font-weight:300;font-size:clamp(40px,7vw,86px);line-height:1">
      <span class="mt-fr">Boutique</span>
      <span class="mt-en">Shop</span>
    </h1>
    <p class="font-body italic text-muted mt-4" style="font-size:clamp(15px,1.6vw,19px)">
      <span class="mt-fr">Tirages photographiques N&B, éditions signées.</span>
      <span class="mt-en">Black &amp; white photographic prints, signed editions.</span>
    </p>
  </div>

  <?php if ($q->have_posts()) : ?>
    <div class="mt-gallery">
      <?php
      while ($q->have_posts()) : $q->the_post();
        get_template_part('template-parts/card', 'produit');
      endwhile;
      wp_reset_postdata();
      ?>
    </div>
  <?php else : ?>
    <p class="text-center font-sans uppercase text-faint" style="font-size:10px;letter-spacing:0.3em;padding:80px 0">
      <span class="mt-fr">Bientôt disponible.</span>
      <span class="mt-en">Coming soon.</span>
    </p>
  <?php endif; ?>

</section>

<?php get_footer(); ?>
