<?php
defined('ABSPATH') || exit;
get_header();

do_action('woocommerce_before_main_content');

while (have_posts()) :
    the_post();

    global $product;
    if (!$product instanceof WC_Product) {
        $product = wc_get_product(get_the_ID());
    }
    if (!$product) continue;

    do_action('woocommerce_before_single_product');

    $post_id   = get_the_ID();

    // Tale lié
    $tale_id   = (int) get_field('product_tale', $post_id);
    $img       = $tale_id ? get_field('tale_image', $tale_id)        : null;
    $ar        = $tale_id ? (get_field('tale_aspect_ratio', $tale_id) ?: '3/4') : '3/4';
    $tone      = $tale_id ? (get_field('tale_tone', $tale_id)        ?: '#f4f3ef') : '#f4f3ef';
    $tale_url  = $tale_id ? get_permalink($tale_id)                   : '';

    // Série
    $serie_raw   = $tale_id ? get_field('tale_serie', $tale_id) : null;
    $serie_id    = is_array($serie_raw) ? ($serie_raw['ID'] ?? 0)
                 : (is_object($serie_raw) ? $serie_raw->ID : (int)$serie_raw);
    $serie_title = $serie_id ? (get_field('serie_title_fr', $serie_id) ?: get_the_title($serie_id)) : '';
    $serie_url   = $serie_id ? get_permalink($serie_id) : '';

    // Champs bilingues ACF
    $title_fr    = get_field('product_title_fr', $post_id) ?: ($tale_id ? get_field('tale_title_fr', $tale_id) : '') ?: get_the_title();
    $title_en    = get_field('product_title_en', $post_id) ?: ($tale_id ? get_field('tale_title_en', $tale_id) : '') ?: get_the_title();
    $desc_fr     = get_field('product_desc_fr',  $post_id);
    $desc_en     = get_field('product_desc_en',  $post_id);
    $edition     = get_field('product_edition_type',  $post_id) ?: 'unique';
    $count       = (int) get_field('product_edition_count', $post_id);
    $paper_fr    = get_field('product_paper_fr', $post_id);
    $paper_en    = get_field('product_paper_en', $post_id);

    $edition_labels = [
        'unique'  => ['fr' => 'Œuvre unique',    'en' => 'Unique work'],
        'limitee' => ['fr' => 'Édition limitée', 'en' => 'Limited edition'],
        'open'    => ['fr' => 'Édition ouverte', 'en' => 'Open edition'],
    ];
    $edition_fr = $edition_labels[$edition]['fr'] ?? 'Œuvre unique';
    $edition_en = $edition_labels[$edition]['en'] ?? 'Unique work';
    if ($count && $edition !== 'open') {
        $edition_fr .= ' · ' . $count . ' ex.';
        $edition_en .= ' · ' . $count . ' copies';
    }

    $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/boutique/');
?>

<article class="animate-fade woocommerce" style="padding:clamp(26px,5vh,60px) clamp(20px,5vw,56px) clamp(60px,9vh,110px);max-width:1300px;margin:0 auto">

  <!-- Breadcrumb -->
  <div class="mt-breadcrumb">
    <a href="<?php echo esc_url($shop_url); ?>">
      <span class="mt-fr">Boutique</span>
      <span class="mt-en">Shop</span>
    </a>
    <?php if ($serie_title) : ?>
      <span class="mt-breadcrumb-sep">/</span>
      <a href="<?php echo esc_url($serie_url); ?>"><?php echo esc_html($serie_title); ?></a>
    <?php endif; ?>
    <span class="mt-breadcrumb-sep">/</span>
    <span class="mt-breadcrumb-cur">
      <span class="mt-fr"><?php echo esc_html($title_fr); ?></span>
      <span class="mt-en"><?php echo esc_html($title_en); ?></span>
    </span>
  </div>

  <!-- Layout image + infos -->
  <div class="mt-tale-layout">

    <!-- Image -->
    <div class="mt-tale-image-col">
      <div class="mt-tale-image" style="aspect-ratio:<?php echo esc_attr($ar); ?>;background-color:<?php echo esc_attr($tone); ?>">
        <?php if ($img) : ?>
          <img src="<?php echo esc_url($img['sizes']['large'] ?? $img['url']); ?>"
               <?php if (!empty($img['ID'])) : ?>
                 srcset="<?php echo esc_attr(wp_get_attachment_image_srcset($img['ID'], 'large') ?: ''); ?>"
                 sizes="(max-width:768px) 100vw, 55vw"
               <?php endif; ?>
               alt="<?php echo esc_attr($img['alt'] ?: $title_fr); ?>"
               loading="eager" decoding="async"
               class="absolute inset-0 w-full h-full object-cover grayscale">
        <?php else : ?>
          <div class="mt-tale-image-inset">
            <span class="mt-tale-image-label">
              <span class="mt-fr">Tirage N&amp;B</span>
              <span class="mt-en">B&amp;W Print</span>
            </span>
          </div>
        <?php endif; ?>
      </div>

      <?php if ($tale_url) : ?>
        <p class="font-sans uppercase text-faint mt-4" style="font-size:10px;letter-spacing:0.22em">
          <a href="<?php echo esc_url($tale_url); ?>" class="hover:text-paper transition-colors duration-300">
            <span class="mt-fr">← Voir la photographie</span>
            <span class="mt-en">← View photograph</span>
          </a>
        </p>
      <?php endif; ?>
    </div>

    <!-- Infos produit -->
    <div class="mt-tale-text-col">

      <?php if ($serie_title) : ?>
        <p class="font-sans uppercase text-silver" style="font-size:12px;letter-spacing:0.24em;margin:0 0 8px">
          <?php echo esc_html($serie_title); ?>
        </p>
      <?php endif; ?>

      <h1 class="font-display text-paper" style="font-weight:300;font-style:italic;font-size:clamp(28px,3.8vw,52px);line-height:1.04;margin:0 0 6px">
        <span class="mt-fr"><?php echo esc_html($title_fr); ?></span>
        <span class="mt-en"><?php echo esc_html($title_en); ?></span>
      </h1>

      <!-- Édition -->
      <p class="font-sans uppercase text-silver" style="font-size:11px;letter-spacing:0.22em;margin:0 0 24px">
        <span class="mt-fr"><?php echo esc_html($edition_fr); ?></span>
        <span class="mt-en"><?php echo esc_html($edition_en); ?></span>
      </p>

      <?php if ($desc_fr || $desc_en) : ?>
        <div class="font-display text-silver" style="font-style:italic;font-size:clamp(15px,1.4vw,18px);line-height:1.6;margin:0 0 28px">
          <?php if ($desc_fr) : ?>
            <p class="mt-fr" style="margin:0"><?php echo esc_html($desc_fr); ?></p>
          <?php endif; ?>
          <?php if ($desc_en) : ?>
            <p class="mt-en" style="margin:0"><?php echo esc_html($desc_en); ?></p>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- Prix + variantes + panier -->
      <div class="mt-wc-price">
        <?php echo $product->get_price_html(); ?>
      </div>

      <div class="mt-wc-add-to-cart">
        <?php woocommerce_template_single_add_to_cart(); ?>
      </div>

      <!-- Métadonnées -->
      <div class="mt-wc-meta font-sans uppercase text-faint"
           style="font-size:10px;letter-spacing:0.2em;display:flex;flex-direction:column;gap:10px;
                  margin-top:clamp(24px,4vh,40px);padding-top:clamp(20px,3vh,30px);
                  border-top:1px solid rgba(236,234,228,0.1)">

        <?php if ($paper_fr || $paper_en) : ?>
          <span>
            <?php if ($paper_fr) : ?>
              <span class="mt-fr"><?php echo esc_html($paper_fr); ?></span>
            <?php endif; ?>
            <?php if ($paper_en) : ?>
              <span class="mt-en"><?php echo esc_html($paper_en); ?></span>
            <?php endif; ?>
          </span>
        <?php else : ?>
          <span>
            <span class="mt-fr">Impression N&amp;B sur papier Fine Art · Encres pigmentées</span>
            <span class="mt-en">B&amp;W Fine Art print · Pigment inks</span>
          </span>
        <?php endif; ?>

        <span>
          <span class="mt-fr">Œuvre signée · Livraison soignée</span>
          <span class="mt-en">Signed work · Careful shipping</span>
        </span>

        <?php if ($product->get_sku()) : ?>
          <span>
            <span class="mt-fr">Réf.</span><span class="mt-en">Ref.</span>
            <?php echo esc_html($product->get_sku()); ?>
          </span>
        <?php endif; ?>
      </div>

    </div>
  </div>

  <?php do_action('woocommerce_after_single_product'); ?>

</article>

<?php endwhile; ?>

<?php do_action('woocommerce_after_main_content'); ?>

<?php get_footer(); ?>
