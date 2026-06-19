<?php
$post_id    = get_the_ID();
$title_fr   = get_field('serie_title_fr', $post_id)   ?: get_the_title();
$title_en   = get_field('serie_title_en', $post_id)   ?: get_the_title();
$place_fr   = get_field('serie_place_fr', $post_id)   ?: '';
$place_en   = get_field('serie_place_en', $post_id)   ?: '';
$ar         = get_field('serie_aspect_ratio', $post_id) ?: '3/4';
$tone       = get_field('serie_tone', $post_id)        ?: '#f4f3ef';
$card_size  = get_field('serie_card_size', $post_id)   ?: 'normal';
$tales_q    = mt_get_tales($post_id);
$count      = $tales_q->found_posts;
wp_reset_postdata();
$wrap_class = $card_size === 'large' ? 'mt-card-wrap mt-card-wrap--large' : 'mt-card-wrap';
?>
<div class="<?php echo $wrap_class; ?>" data-reveal>
  <a href="<?php the_permalink(); ?>" class="block">
    <div class="mt-card" style="aspect-ratio:<?php echo esc_attr($ar); ?>;background-color:<?php echo esc_attr($tone); ?>">
      <div class="mt-card-inset"></div>
      <div class="mt-card-ph-corner">
        <span class="mt-fr">Série · N&B</span>
        <span class="mt-en">Series · B&W</span>
      </div>
      <div class="mt-card-caption">
        <div class="mt-caption-place">
          <span class="mt-fr"><?php echo esc_html($place_fr); ?></span>
          <span class="mt-en"><?php echo esc_html($place_en); ?></span>
        </div>
        <div class="mt-caption-title">
          <span class="mt-fr"><?php echo esc_html($title_fr); ?></span>
          <span class="mt-en"><?php echo esc_html($title_en); ?></span>
        </div>
        <div class="mt-caption-count">
          <span class="mt-caption-line"></span>
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
