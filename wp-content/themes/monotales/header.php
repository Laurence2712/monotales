<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class('bg-ink min-h-screen'); ?>>
<?php wp_body_open(); ?>

<header class="mt-header" id="mt-header">

  <a href="<?php echo esc_url(home_url('/')); ?>" class="mt-logo">MONOTALES</a>

  <nav class="mt-nav" aria-label="Navigation principale">

    <?php
    $screen   = get_post_type() ?: 'page';
    $is_serie = is_singular('serie') || is_singular('tale') || (is_front_page() && !is_home());
    $is_home  = is_front_page();

    $gallery_active   = $is_home || is_singular('serie') || is_singular('tale');
    $journal_active   = is_page_template('page-journal.php');
    $manifesto_active = is_page_template('page-manifesto.php');

    $nav_label_gallery   = '<span class="mt-fr">Galerie</span><span class="mt-en">Gallery</span>';
    $nav_label_journal   = '<span class="mt-fr">Journal</span><span class="mt-en">Journal</span>';
    $nav_label_manifesto = '<span class="mt-fr">Manifeste</span><span class="mt-en">Manifesto</span>';

    $journal_page   = get_page_by_path('journal');
    $manifesto_page = get_page_by_path('manifeste') ?: get_page_by_path('manifest');
    $journal_url    = $journal_page   ? get_page_link($journal_page)   : home_url('/journal/');
    $manifesto_url  = $manifesto_page ? get_page_link($manifesto_page) : home_url('/manifeste/');
    ?>

    <a href="<?php echo esc_url(home_url('/')); ?>"
       class="mt-nav-item"
       style="opacity:<?php echo $gallery_active ? 1 : 0.5; ?>">
      <?php echo $nav_label_gallery; ?>
    </a>

    <a href="<?php echo esc_url($journal_url ?: home_url('/journal/')); ?>"
       class="mt-nav-item"
       style="opacity:<?php echo $journal_active ? 1 : 0.5; ?>">
      <?php echo $nav_label_journal; ?>
    </a>

    <a href="<?php echo esc_url($manifesto_url ?: home_url('/manifeste/')); ?>"
       class="mt-nav-item"
       style="opacity:<?php echo $manifesto_active ? 1 : 0.5; ?>">
      <?php echo $nav_label_manifesto; ?>
    </a>

    <?php if (defined('MT_BOUTIQUE_ACTIVE') && MT_BOUTIQUE_ACTIVE && function_exists('wc_get_page_permalink')) :
    $boutique_active = is_woocommerce() || is_cart() || is_checkout() || is_account_page();
    $boutique_url    = wc_get_page_permalink('shop');
    ?>
    <a href="<?php echo esc_url($boutique_url); ?>"
       class="mt-nav-item"
       style="opacity:<?php echo $boutique_active ? 1 : 0.5; ?>">
      <span class="mt-fr">Boutique</span>
      <span class="mt-en">Shop</span>
    </a>
    <?php endif; ?>

    <div class="mt-lang" role="group" aria-label="Langue">
      <button class="mt-lang-btn" id="btn-fr" aria-pressed="true" style="opacity:1">FR</button>
      <span class="mt-lang-sep">/</span>
      <button class="mt-lang-btn" id="btn-en" aria-pressed="false" style="opacity:0.42">EN</button>
    </div>

  </nav>
</header>

<main id="mt-main">
