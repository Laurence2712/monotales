<?php
$post_id    = get_the_ID();
$tale_id    = (int) get_field('produit_tale', $post_id);
$title_fr   = get_field('produit_titre_fr', $post_id) ?: ($tale_id ? get_field('tale_title_fr', $tale_id) : get_the_title());
$title_en   = get_field('produit_titre_en', $post_id) ?: ($tale_id ? get_field('tale_title_en', $tale_id) : get_the_title());
$prix       = get_field('produit_prix_depuis', $post_id);
$edition    = get_field('produit_edition', $post_id) ?: 'open';
$dispo      = get_field('produit_disponible', $post_id);

$img        = mt_produit_image($post_id);
$ar         = $tale_id ? (get_field('tale_aspect_ratio', $tale_id) ?: '3/4') : '3/4';
$tone       = $tale_id ? (get_field('tale_tone', $tale_id) ?: '#f4f3ef') : '#f4f3ef';

$edition_labels = [
    'open'    => ['fr' => 'Édition ouverte',   'en' => 'Open edition'],
    'limitee' => ['fr' => 'Édition limitée',   'en' => 'Limited edition'],
    'unique'  => ['fr' => 'Œuvre unique',       'en' => 'Unique work'],
];
$ed_fr = $edition_labels[$edition]['fr'] ?? '';
$ed_en = $edition_labels[$edition]['en'] ?? '';
?>
<div class="mt-card-wrap" data-reveal>
  <div class="block">
    <div class="mt-card" style="aspect-ratio:<?php echo esc_attr($ar); ?>;background-color:<?php echo esc_attr($tone); ?>;min-height:150px">
      <?php if ($img) : ?>
        <img src="<?php echo esc_url($img['sizes']['large'] ?? $img['url']); ?>"
             alt="<?php echo esc_attr($img['alt'] ?: $title_fr); ?>"
             class="absolute inset-0 w-full h-full object-cover grayscale">
      <?php else : ?>
        <div class="mt-card-ph-center">
          <span>
            <span class="mt-fr">Tirage N&B</span>
            <span class="mt-en">B&W Print</span>
          </span>
        </div>
      <?php endif; ?>
      <div class="mt-card-inset"></div>
      <div class="mt-card-caption mt-card-caption--photo">
        <div class="mt-caption-photo-title">
          <span class="mt-fr"><?php echo esc_html($title_fr); ?></span>
          <span class="mt-en"><?php echo esc_html($title_en); ?></span>
        </div>
        <div class="mt-caption-meta">
          <span class="mt-fr"><?php echo esc_html($ed_fr); ?></span>
          <span class="mt-en"><?php echo esc_html($ed_en); ?></span>
          <?php if ($prix) : ?>
            <span class="mt-caption-meta-line"></span>
            <span>
              <span class="mt-fr">À partir de <?php echo esc_html(number_format((float)$prix, 0, ',', ' ')); ?> €</span>
              <span class="mt-en">From €<?php echo esc_html(number_format((float)$prix, 0, ',', ' ')); ?></span>
            </span>
          <?php endif; ?>
        </div>
      </div>
      <?php if (!$dispo) : ?>
        <div class="absolute top-3 right-3 font-sans uppercase" style="font-size:8.5px;letter-spacing:0.24em;color:rgba(236,234,228,0.5);background:rgba(10,10,10,0.6);padding:4px 8px">
          <span class="mt-fr">Indisponible</span>
          <span class="mt-en">Unavailable</span>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
