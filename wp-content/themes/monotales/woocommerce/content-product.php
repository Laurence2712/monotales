<?php
defined('ABSPATH') || exit;
global $product;
if (!$product instanceof WC_Product) $product = wc_get_product(get_the_ID());

$post_id   = get_the_ID();
$tale_id   = (int) get_field('product_tale', $post_id);
$img       = $tale_id ? get_field('tale_image', $tale_id) : null;
$ar        = $tale_id ? (get_field('tale_aspect_ratio', $tale_id) ?: '3/4') : '3/4';
$tone      = $tale_id ? (get_field('tale_tone', $tale_id) ?: '#f4f3ef') : '#f4f3ef';
$title_fr  = get_field('product_title_fr', $post_id) ?: ($tale_id ? get_field('tale_title_fr', $tale_id) : '') ?: get_the_title();
$title_en  = get_field('product_title_en', $post_id) ?: ($tale_id ? get_field('tale_title_en', $tale_id) : '') ?: get_the_title();
$title     = $title_fr;
$min_price = $product->is_type('variable')
           ? $product->get_variation_price('min')
           : $product->get_price();
?>
<div class="mt-card-wrap" data-reveal>
  <a href="<?php the_permalink(); ?>" class="block">
    <div class="mt-card mt-card--photo" style="aspect-ratio:<?php echo esc_attr($ar); ?>;background-color:<?php echo esc_attr($tone); ?>;min-height:150px">
      <?php if ($img) : ?>
        <img src="<?php echo esc_url($img['sizes']['large'] ?? $img['url']); ?>"
             alt="<?php echo esc_attr($img['alt'] ?: $title); ?>"
             loading="lazy" decoding="async"
             class="absolute inset-0 w-full h-full object-cover grayscale">
      <?php else : ?>
        <div class="mt-card-ph-center">
          <span>
            <span class="mt-fr">Tirage N&B</span>
            <span class="mt-en">B&W Print</span>
          </span>
        </div>
      <?php endif; ?>
      <div class="mt-card-caption mt-card-caption--photo">
        <div class="mt-caption-photo-title">
          <span class="mt-fr"><?php echo esc_html($title_fr); ?></span>
          <span class="mt-en"><?php echo esc_html($title_en); ?></span>
        </div>
        <div class="mt-caption-meta">
          <?php if ($min_price) : ?>
            <span class="mt-fr">À partir de <?php echo number_format((float)$min_price, 0, ',', ' '); ?> €</span>
            <span class="mt-en">From €<?php echo number_format((float)$min_price, 0, ',', ' '); ?></span>
          <?php endif; ?>
          <?php if (!$product->is_in_stock()) : ?>
            <span class="mt-caption-meta-line"></span>
            <span class="mt-fr">Indisponible</span>
            <span class="mt-en">Unavailable</span>
          <?php endif; ?>
        </div>
      </div>
      <?php if (!$product->is_in_stock()) : ?>
        <div class="absolute top-3 right-3 font-sans uppercase" style="font-size:8.5px;letter-spacing:0.24em;color:rgba(236,234,228,0.5);background:rgba(10,10,10,0.6);padding:4px 8px">
          <span class="mt-fr">Indisponible</span>
          <span class="mt-en">Unavailable</span>
        </div>
      <?php endif; ?>
    </div>
  </a>
</div>
