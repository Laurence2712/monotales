<?php
/*
 * Template Name: Journal
 * Template Post Type: page
 */
get_header();

$q = mt_get_all_tales();
$total = $q->found_posts;
?>

<section class="animate-fade mx-auto" style="padding:clamp(54px,10vh,110px) clamp(20px,5vw,56px) clamp(70px,10vh,120px);max-width:980px">

  <div class="text-center" style="margin-bottom:clamp(40px,6vh,72px)">
    <h1 class="font-display text-paper m-0" style="font-weight:300;font-size:clamp(40px,7vw,86px);line-height:1">Journal</h1>
    <p class="font-body italic text-muted mt-4" style="font-size:clamp(15px,1.6vw,19px)">
      <span class="mt-fr">Toutes les parutions, de la plus récente à la première.</span>
      <span class="mt-en">Every publication, from the most recent to the very first.</span>
    </p>
    <p class="font-sans uppercase text-faint mt-5" style="font-size:10px;letter-spacing:0.26em">
      <span class="mt-fr">Trois parutions par semaine</span>
      <span class="mt-en">Three publications a week</span>
    </p>
  </div>

  <div style="border-top:1px solid rgba(236,234,228,0.12)">
    <?php
    $idx = 1;
    if ($q->have_posts()) :
      while ($q->have_posts()) : $q->the_post();
        $pid      = get_the_ID();
        $title_fr = get_field('tale_title_fr', $pid) ?: get_the_title();
        $title_en = get_field('tale_title_en', $pid) ?: get_the_title();
        $text_fr  = get_field('tale_text_fr',  $pid) ?: '';
        $text_en  = get_field('tale_text_en',  $pid) ?: '';
        $loc      = get_field('tale_location', $pid) ?: '';
        $date     = get_field('tale_date',     $pid) ?: '';
        $serie    = get_field('tale_serie',    $pid);
        $serie_id = is_array($serie) ? ($serie['ID'] ?? 0) : (is_object($serie) ? $serie->ID : (int)$serie);
        $cat_fr   = $serie_id ? (get_field('serie_title_fr', $serie_id) ?: get_the_title($serie_id)) : '';
        $cat_en   = $serie_id ? (get_field('serie_title_en', $serie_id) ?: get_the_title($serie_id)) : '';
        $num      = str_pad($idx, 2, '0', STR_PAD_LEFT);
        ?>
        <a href="<?php the_permalink(); ?>" class="mt-journal-row block">
          <div class="journal-row-meta font-sans uppercase text-dim flex-shrink-0" style="min-width:140px;font-size:10.5px;letter-spacing:0.18em;display:flex;gap:14px;align-items:baseline">
            <span><?php echo esc_html($num); ?></span>
            <span><?php echo esc_html($date); ?></span>
          </div>
          <div style="flex:1 1 320px;min-width:220px">
            <div class="flex items-baseline justify-between flex-wrap gap-4">
              <span class="font-display italic text-paper" style="font-size:clamp(22px,2.6vw,30px);line-height:1.1">
                <span class="mt-fr"><?php echo esc_html($title_fr); ?></span>
                <span class="mt-en"><?php echo esc_html($title_en); ?></span>
              </span>
              <span class="font-sans uppercase text-faint whitespace-nowrap" style="font-size:9.5px;letter-spacing:0.18em">
                <?php echo esc_html($loc); ?>
              </span>
            </div>
            <p class="font-sans uppercase text-faint mt-1.5" style="font-size:9px;letter-spacing:0.2em">
              <span class="mt-fr"><?php echo esc_html($cat_fr); ?></span>
              <span class="mt-en"><?php echo esc_html($cat_en); ?></span>
            </p>
            <p class="font-body text-low mt-1.5 max-w-[560px]" style="font-size:15px;line-height:1.6">
              <span class="mt-fr"><?php echo esc_html(wp_trim_words(strip_tags($text_fr), 30)); ?></span>
              <span class="mt-en"><?php echo esc_html(wp_trim_words(strip_tags($text_en), 30)); ?></span>
            </p>
          </div>
        </a>
        <?php
        $idx++;
      endwhile;
      wp_reset_postdata();
    endif;
    ?>
  </div>

</section>

<?php get_footer(); ?>
