<?php
$post_id  = get_the_ID();
$title_fr = get_field('serie_title_fr', $post_id) ?: get_the_title();
$title_en = get_field('serie_title_en', $post_id) ?: get_the_title();
$place_fr = get_field('serie_place_fr', $post_id) ?: '';
$place_en = get_field('serie_place_en', $post_id) ?: '';
$tone     = get_field('serie_tone', $post_id)     ?: '#f4f3ef';
$tales_q  = mt_get_tales($post_id);
$count    = $tales_q->found_posts;
wp_reset_postdata();
?>
<div class="mt-card-wrap" data-reveal>
  <a href="<?php the_permalink(); ?>" class="block">
    <div class="mt-card--featured" style="background-color:<?php echo esc_attr($tone); ?>">
      <div class="mt-card-inset"></div>
      <div class="mt-card-ph-featured">
        <span class="mt-card-ph-featured-line"></span>
        <span class="mt-fr">À la une</span>
        <span class="mt-en">Featured</span>
      </div>
      <div class="mt-card-caption mt-card-caption--featured">
        <div class="mt-caption-featured-place">
          <span class="mt-fr"><?php echo esc_html($place_fr); ?></span>
          <span class="mt-en"><?php echo esc_html($place_en); ?></span>
        </div>
        <div class="mt-caption-featured-title">
          <span class="mt-fr"><?php echo esc_html($title_fr); ?></span>
          <span class="mt-en"><?php echo esc_html($title_en); ?></span>
        </div>
        <div class="mt-caption-featured-count">
          <span class="mt-caption-featured-line"></span>
          <span>
            <?php echo $count; ?>
            <span class="mt-fr"> photographies</span>
            <span class="mt-en"> photographs</span>
          </span>
        </div>
      </div>
    </div>
  </a>
</div>
