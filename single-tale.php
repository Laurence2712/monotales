<?php get_header(); ?>

<?php if (have_posts()) : the_post(); ?>
<?php
$post_id   = get_the_ID();
$title_fr  = get_field('tale_title_fr',  $post_id) ?: get_the_title();
$title_en  = get_field('tale_title_en',  $post_id) ?: get_the_title();
$text_fr   = get_field('tale_text_fr',   $post_id) ?: '';
$text_en   = get_field('tale_text_en',   $post_id) ?: '';
$loc       = get_field('tale_location',  $post_id) ?: '';
$date      = get_field('tale_date',      $post_id) ?: '';
$ar        = get_field('tale_aspect_ratio', $post_id) ?: '3/4';
$tone      = get_field('tale_tone',      $post_id) ?: '#f4f3ef';
$img       = get_field('tale_image',     $post_id);

// Série parente
$serie_obj = get_field('tale_serie', $post_id);
$serie_id  = is_array($serie_obj) ? ($serie_obj['ID'] ?? 0) : (is_object($serie_obj) ? $serie_obj->ID : (int)$serie_obj);
$serie_title_fr = $serie_id ? (get_field('serie_title_fr', $serie_id) ?: get_the_title($serie_id)) : '';
$serie_title_en = $serie_id ? (get_field('serie_title_en', $serie_id) ?: get_the_title($serie_id)) : '';
$serie_url      = $serie_id ? get_permalink($serie_id) : home_url('/');

// Numéro dans la série
$tales_in_serie  = $serie_id ? mt_get_tales($serie_id) : null;
$all_tale_ids    = [];
$current_num     = 1;
if ($tales_in_serie && $tales_in_serie->have_posts()) {
    $i = 1;
    while ($tales_in_serie->have_posts()) {
        $tales_in_serie->the_post();
        $all_tale_ids[] = get_the_ID();
        if (get_the_ID() === $post_id) $current_num = $i;
        $i++;
    }
    wp_reset_postdata();
}
$total        = count($all_tale_ids);
$current_idx  = array_search($post_id, $all_tale_ids);
$prev_id      = ($current_idx > 0) ? $all_tale_ids[$current_idx - 1] : $all_tale_ids[$total - 1];
$next_id      = ($current_idx < $total - 1) ? $all_tale_ids[$current_idx + 1] : $all_tale_ids[0];
$num_label    = str_pad($current_num, 2, '0', STR_PAD_LEFT);
?>

<article class="animate-fade" style="padding:clamp(26px,5vh,60px) clamp(20px,5vw,56px) clamp(60px,9vh,110px);max-width:1300px;margin:0 auto">

  <!-- Breadcrumb -->
  <div class="mt-breadcrumb">
    <a href="<?php echo esc_url(home_url('/')); ?>">
      <span class="mt-fr">Galerie</span>
      <span class="mt-en">Gallery</span>
    </a>
    <span class="mt-breadcrumb-sep">/</span>
    <a href="<?php echo esc_url($serie_url); ?>">
      <span class="mt-fr"><?php echo esc_html($serie_title_fr); ?></span>
      <span class="mt-en"><?php echo esc_html($serie_title_en); ?></span>
    </a>
    <span class="mt-breadcrumb-sep">/</span>
    <span class="mt-breadcrumb-cur"><?php echo esc_html($num_label); ?></span>
  </div>

  <!-- Layout image + texte -->
  <div class="flex flex-wrap items-start" style="gap:clamp(28px,4.5vw,72px);margin-top:clamp(22px,3vh,38px)">

    <!-- Image -->
    <div class="flex-1 min-w-[280px]" style="flex-basis:460px">
      <div class="mt-tale-image" style="aspect-ratio:<?php echo esc_attr($ar); ?>;background-color:<?php echo esc_attr($tone); ?>">
        <?php if ($img) : ?>
          <img src="<?php echo esc_url($img['sizes']['large'] ?? $img['url']); ?>"
               alt="<?php echo esc_attr($img['alt'] ?: $title_fr); ?>"
               class="absolute inset-0 w-full h-full object-cover grayscale">
        <?php else : ?>
          <div class="mt-tale-image-inset">
            <span class="mt-tale-image-label">
              <span class="mt-fr">Photographie · N&B</span>
              <span class="mt-en">Photograph · B&W</span>
            </span>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Texte -->
    <div class="flex-1 min-w-[280px] pt-1" style="flex-basis:380px">

      <div class="flex items-center gap-3.5 font-sans uppercase text-dim" style="font-size:10.5px;letter-spacing:0.22em">
        <span><?php echo esc_html($num_label); ?></span>
        <span class="flex-1 h-px bg-white/[0.16]"></span>
        <span><?php echo esc_html($date); ?></span>
      </div>

      <p class="font-sans uppercase text-silver mt-6" style="font-size:12px;letter-spacing:0.24em">
        <?php echo esc_html($loc); ?>
      </p>

      <h2 class="font-display text-paper mt-2.5" style="font-weight:300;font-style:italic;font-size:clamp(34px,4.6vw,58px);line-height:1.02">
        <span class="mt-fr"><?php echo esc_html($title_fr); ?></span>
        <span class="mt-en"><?php echo esc_html($title_en); ?></span>
      </h2>

      <div class="font-body text-text mt-8" style="font-size:clamp(17px,1.5vw,21px);line-height:1.72">
        <div class="mt-fr"><?php echo wp_kses_post($text_fr); ?></div>
        <div class="mt-en"><?php echo wp_kses_post($text_en); ?></div>
      </div>

      <!-- Version bilingue -->
      <div class="mt-8 pt-7" style="border-top:1px solid rgba(236,234,228,0.13)">
        <p class="font-sans uppercase text-faint mb-3" style="font-size:10px;letter-spacing:0.24em">
          <span class="mt-fr">English</span>
          <span class="mt-en">Français</span>
        </p>
        <div class="font-body italic text-alt" style="font-size:clamp(15px,1.3vw,18px);line-height:1.7">
          <div class="mt-fr"><?php echo wp_kses_post($text_en); ?></div>
          <div class="mt-en"><?php echo wp_kses_post($text_fr); ?></div>
        </div>
      </div>

      <!-- Navigation précédent / suivant -->
      <div class="flex justify-between gap-4 mt-10 font-sans uppercase text-warm" style="font-size:10.5px;letter-spacing:0.2em">
        <a href="<?php echo esc_url(get_permalink($prev_id)); ?>" class="hover:text-paper transition-colors duration-300">
          &larr;&nbsp;&nbsp;<span class="mt-fr">Précédent</span><span class="mt-en">Previous</span>
        </a>
        <a href="<?php echo esc_url($serie_url); ?>" class="hover:text-paper transition-colors duration-300">
          <span class="mt-fr">Toute la série</span><span class="mt-en">Whole series</span>
        </a>
        <a href="<?php echo esc_url(get_permalink($next_id)); ?>" class="hover:text-paper transition-colors duration-300">
          <span class="mt-fr">Suivant</span><span class="mt-en">Next</span>&nbsp;&nbsp;&rarr;
        </a>
      </div>

    </div>
  </div>
</article>

<?php endif; ?>
<?php get_footer(); ?>
