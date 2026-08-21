<?php
/*
 * Template Name: Journal
 * Template Post Type: page
 */
get_header();

$q = new WP_Query([
  'post_type'      => 'post',
  'post_status'    => 'publish',
  'posts_per_page' => -1,
  'orderby'        => 'date',
  'order'          => 'DESC',
]);
$total = $q->found_posts;
?>

<section class="animate-fade mx-auto" style="padding:clamp(54px,10vh,110px) clamp(20px,5vw,56px) clamp(70px,10vh,120px);max-width:980px">

  <div class="text-center" style="margin-bottom:clamp(40px,6vh,72px)">
    <h1 class="font-display text-paper m-0" style="font-weight:300;font-size:clamp(40px,7vw,86px);line-height:1">Journal</h1>
    <p class="font-body italic text-muted mt-4" style="font-size:clamp(15px,1.6vw,19px)">
      <span class="mt-fr">Réflexions, récits et regards sur la photographie.</span>
      <span class="mt-en">Reflections, stories and views on photography.</span>
    </p>
    <p class="font-sans uppercase text-faint mt-5" style="font-size:10px;letter-spacing:0.26em">
      <?php echo $total; ?> <span class="mt-fr">articles</span><span class="mt-en">articles</span>
    </p>
  </div>

  <div style="border-top:1px solid rgba(236,234,228,0.12)">
    <?php
    $idx = 1;
    if ($q->have_posts()) :
      while ($q->have_posts()) : $q->the_post();
        $pid    = get_the_ID();
        $tales  = get_field('article_tales', $pid) ?: [];
        $num    = str_pad($idx, 2, '0', STR_PAD_LEFT);
        $date   = get_the_date('d.m.Y');
        $excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words(strip_tags(get_the_content()), 28);
        ?>
        <a href="<?php the_permalink(); ?>" class="mt-journal-row">

          <div class="font-sans uppercase text-dim flex-shrink-0"
               style="min-width:130px;font-size:10.5px;letter-spacing:0.18em;display:flex;gap:14px;align-items:baseline">
            <span><?php echo esc_html($num); ?></span>
            <span><?php echo esc_html($date); ?></span>
          </div>

          <div style="flex:1 1 320px;min-width:220px">
            <div class="flex items-baseline justify-between flex-wrap gap-4">
              <span class="font-display italic text-paper"
                    style="font-size:clamp(22px,2.6vw,30px);line-height:1.1">
                <?php the_title(); ?>
              </span>
              <?php if ($tales) : ?>
                <span class="font-sans uppercase text-faint whitespace-nowrap"
                      style="font-size:9.5px;letter-spacing:0.18em">
                  <?php echo count($tales); ?>
                  <span class="mt-fr"><?php echo count($tales) > 1 ? 'photos' : 'photo'; ?></span>
                  <span class="mt-en"><?php echo count($tales) > 1 ? 'photos' : 'photo'; ?></span>
                </span>
              <?php endif; ?>
            </div>

            <?php if ($excerpt) : ?>
              <p class="font-body text-low mt-1.5 max-w-[560px]" style="font-size:15px;line-height:1.6">
                <?php echo esc_html($excerpt); ?>
              </p>
            <?php endif; ?>
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
