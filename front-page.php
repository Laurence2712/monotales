<?php get_header(); ?>

<section>
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
