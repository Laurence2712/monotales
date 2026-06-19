<?php get_header(); ?>

<section>

  <!-- Hero -->
  <div class="text-center mx-auto max-w-[1080px]" style="padding:clamp(54px,12vh,132px) clamp(20px,5vw,56px) clamp(40px,7vh,84px)">
    <p class="font-sans uppercase text-dim mb-8" style="font-size:11px;letter-spacing:0.42em">
      <span class="mt-fr">Photographies &amp; récits</span>
      <span class="mt-en">Photographs &amp; stories</span>
    </p>
    <h1 class="font-display text-paper m-0" style="font-weight:300;font-size:clamp(40px,8.4vw,120px);line-height:0.96;letter-spacing:0.01em">
      Monotales
    </h1>
    <p class="font-body italic text-muted mx-auto mt-8 max-w-[560px]" style="font-size:clamp(15px,2.1vw,22px);line-height:1.55">
      <span class="mt-fr">Des séries photographiques en noir et blanc. Choisissez un lieu, entrez dans son silence.</span>
      <span class="mt-en">Photographic series in black and white. Choose a place, step into its silence.</span>
    </p>
  </div>

  <!-- Galerie mosaïque des séries -->
  <div class="mt-gallery">
    <?php
    $series_query = mt_get_all_series();
    if ($series_query->have_posts()) :
      while ($series_query->have_posts()) : $series_query->the_post();
        get_template_part('template-parts/card', 'serie');
      endwhile;
      wp_reset_postdata();
    endif;
    ?>
  </div>

</section>

<?php get_footer(); ?>
