<?php
$post_id  = get_the_ID();
$title_fr = get_field('tale_title_fr', $post_id) ?: get_the_title();
$title_en = get_field('tale_title_en', $post_id) ?: get_the_title();
$loc      = get_field('tale_location', $post_id)  ?: '';
$date     = get_field('tale_date', $post_id)      ?: '';
$ar       = get_field('tale_aspect_ratio', $post_id) ?: '3/4';
$tone     = get_field('tale_tone', $post_id)         ?: '#f4f3ef';
$img      = get_field('tale_image', $post_id);
$lb_data  = $img ? esc_attr(wp_json_encode([
    'img'  => $img['sizes']['large'] ?? $img['url'],
    'fr'   => $title_fr,
    'en'   => $title_en,
    'loc'  => $loc,
    'date' => $date,
    'url'  => get_permalink(),
])) : '';
?>
<div class="mt-card-wrap" data-reveal>
  <a href="<?php the_permalink(); ?>" class="block"<?php if ($lb_data) echo ' data-lb="' . $lb_data . '"'; ?>>
    <div class="mt-card mt-card--photo" style="aspect-ratio:<?php echo esc_attr($ar); ?>;background-color:<?php echo esc_attr($tone); ?>;min-height:150px">
      <?php if ($img) : ?>
        <img src="<?php echo esc_url($img['sizes']['large'] ?? $img['url']); ?>"
             <?php if (!empty($img['ID'])) echo 'srcset="' . esc_attr(wp_get_attachment_image_srcset($img['ID'], 'large') ?: '') . '" sizes="(max-width:768px) 100vw, 33vw"'; ?>
             alt="<?php echo esc_attr($img['alt'] ?: $title_fr); ?>"
             loading="lazy" decoding="async"
             class="absolute inset-0 w-full h-full object-cover grayscale">
      <?php else : ?>
        <div class="mt-card-ph-center">
          <span>
            <span class="mt-fr">Photographie · N&B</span>
            <span class="mt-en">Photograph · B&W</span>
          </span>
        </div>
      <?php endif; ?>
      <div class="mt-card-caption mt-card-caption--photo">
        <div class="mt-caption-photo-title">
          <span class="mt-fr"><?php echo esc_html($title_fr); ?></span>
          <span class="mt-en"><?php echo esc_html($title_en); ?></span>
        </div>
        <div class="mt-caption-meta">
          <span><?php echo esc_html($loc); ?></span>
          <span class="mt-caption-meta-line"></span>
          <span><?php echo esc_html($date); ?></span>
        </div>
      </div>
    </div>
  </a>
</div>
