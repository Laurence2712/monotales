<?php get_header(); ?>

<section>
<?php
$sq  = mt_get_all_series();
$all = [];
if ($sq->have_posts()) {
  while ($sq->have_posts()) : $sq->the_post();
    $all[] = [get_post(), get_field('serie_card_size') ?: 'normal'];
  endwhile;
  wp_reset_postdata();
}

// Partition: normal cards before featured / featured / normal cards after
$before = []; $featured = null; $after = []; $found = false;
foreach ($all as [$post_obj, $size]) {
  if (!$found && $size === 'large') { $featured = $post_obj; $found = true; }
  elseif (!$found)                  { $before[] = $post_obj; }
  else                              { $after[]  = $post_obj; }
}
if (!$featured) { $before = array_column($all, 0); } // fallback: no featured set
?>

<?php if ($before) : ?>
  <div class="mt-gallery">
    <?php global $post; foreach ($before as $post) : setup_postdata($post); ?>
      <?php get_template_part('template-parts/card', 'serie'); ?>
    <?php endforeach; wp_reset_postdata(); ?>
  </div>
<?php endif; ?>

<?php if ($featured) : global $post; $post = $featured; setup_postdata($post); ?>
  <div class="mt-gallery-featured">
    <?php get_template_part('template-parts/card', 'serie-featured'); ?>
  </div>
  <?php wp_reset_postdata(); ?>
<?php endif; ?>

<?php if ($after) : ?>
  <div class="mt-gallery mt-gallery--notop">
    <?php global $post; foreach ($after as $post) : setup_postdata($post); ?>
      <?php get_template_part('template-parts/card', 'serie'); ?>
    <?php endforeach; wp_reset_postdata(); ?>
  </div>
<?php endif; ?>

</section>

<?php get_footer(); ?>
