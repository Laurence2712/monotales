<?php get_header(); ?>

<?php if (have_posts()) : the_post(); ?>
<?php
$post_id  = get_the_ID();
$title_fr = get_field('serie_title_fr', $post_id) ?: get_the_title();
$title_en = get_field('serie_title_en', $post_id) ?: get_the_title();
$place_fr = get_field('serie_place_fr', $post_id) ?: '';
$place_en = get_field('serie_place_en', $post_id) ?: '';
$intro_fr = get_field('serie_intro_fr', $post_id) ?: '';
$intro_en = get_field('serie_intro_en', $post_id) ?: '';
$tales_q  = mt_get_tales($post_id);
$count    = $tales_q->found_posts;
?>

<section class="animate-fade">

  <!-- En-tête série -->
  <div class="text-center mx-auto max-w-[1080px]" style="padding:clamp(40px,7vh,86px) clamp(20px,5vw,56px) clamp(30px,5vh,56px)">

    <div class="mt-breadcrumb justify-center mb-8">
      <a href="<?php echo esc_url(home_url('/')); ?>">
        <span class="mt-fr">Galerie</span>
        <span class="mt-en">Gallery</span>
      </a>
      <span class="mt-breadcrumb-sep">/</span>
      <span class="mt-breadcrumb-cur">
        <span class="mt-fr"><?php echo esc_html($place_fr); ?></span>
        <span class="mt-en"><?php echo esc_html($place_en); ?></span>
      </span>
    </div>

    <h1 class="font-display m-0 text-paper" style="font-weight:300;font-style:italic;font-size:clamp(36px,6.6vw,82px);line-height:1;letter-spacing:0.005em">
      <span class="mt-fr"><?php echo esc_html($title_fr); ?></span>
      <span class="mt-en"><?php echo esc_html($title_en); ?></span>
    </h1>

    <p class="font-body text-muted mx-auto mt-6" style="font-size:clamp(15px,1.8vw,20px);line-height:1.6;max-width:520px">
      <span class="mt-fr"><?php echo esc_html($intro_fr); ?></span>
      <span class="mt-en"><?php echo esc_html($intro_en); ?></span>
    </p>

    <p class="font-sans uppercase text-faint mt-6" style="font-size:10px;letter-spacing:0.26em">
      <?php echo $count; ?>
      <span class="mt-fr"> photographies</span>
      <span class="mt-en"> photographs</span>
    </p>

  </div>

  <!-- Grille photos -->
  <div class="mt-gallery mt-gallery--narrow">
    <?php
    if ($tales_q->have_posts()) :
      while ($tales_q->have_posts()) : $tales_q->the_post();
        get_template_part('template-parts/card', 'photo', ['serie_post_id' => $post_id]);
      endwhile;
      wp_reset_postdata();
    endif;
    ?>
  </div>

</section>

<?php endif; ?>

<?php get_footer(); ?>
