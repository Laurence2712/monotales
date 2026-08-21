<?php get_header(); ?>

<?php while (have_posts()) : the_post();
  $pid   = get_the_ID();
  $tales = get_field('article_tales', $pid) ?: [];
?>

<article class="animate-fade mx-auto"
         style="padding:clamp(54px,10vh,110px) clamp(20px,5vw,56px) clamp(70px,10vh,120px);max-width:760px">

  <!-- Breadcrumb -->
  <nav class="mt-breadcrumb" style="margin-bottom:clamp(28px,4vh,44px)">
    <a href="<?php echo esc_url(get_page_link(get_page_by_path('journal'))); ?>">
      <span class="mt-fr">Journal</span><span class="mt-en">Journal</span>
    </a>
    <span class="mt-breadcrumb-sep">—</span>
    <span class="mt-breadcrumb-cur"><?php the_title(); ?></span>
  </nav>

  <!-- En-tête -->
  <header style="margin-bottom:clamp(32px,5vh,56px)">
    <p class="font-sans uppercase text-faint" style="font-size:10px;letter-spacing:0.24em;margin-bottom:14px">
      <?php echo get_the_date('d.m.Y'); ?>
    </p>
    <h1 class="font-display text-paper m-0"
        style="font-weight:300;font-size:clamp(28px,4.5vw,54px);line-height:1.08">
      <?php the_title(); ?>
    </h1>
  </header>

  <!-- Contenu -->
  <div class="font-body text-text"
       style="font-size:clamp(16px,1.7vw,19px);line-height:1.75;max-width:680px">
    <?php the_content(); ?>
  </div>

  <!-- Photos liées -->
  <?php if ($tales) : ?>
    <div style="margin-top:clamp(56px,8vh,96px)">
      <p class="font-sans uppercase text-faint"
         style="font-size:10px;letter-spacing:0.26em;margin-bottom:clamp(20px,3vh,32px);border-top:1px solid rgba(236,234,228,0.12);padding-top:24px">
        <span class="mt-fr">Photos liées</span>
        <span class="mt-en">Related photos</span>
        <span style="margin-left:10px;opacity:.45"><?php echo count($tales); ?></span>
      </p>

      <div style="column-width:clamp(180px,22vw,280px);column-gap:8px">
        <?php foreach ($tales as $tale) :
          $tid      = $tale->ID;
          $title_fr = get_field('tale_title_fr', $tid) ?: get_the_title($tid);
          $title_en = get_field('tale_title_en', $tid) ?: get_the_title($tid);
          $loc      = get_field('tale_location', $tid) ?: '';
          $date     = get_field('tale_date', $tid)     ?: '';
          $ar       = get_field('tale_aspect_ratio', $tid) ?: '3/4';
          $tone     = get_field('tale_tone', $tid)     ?: '#f4f3ef';
          $img      = get_field('tale_image', $tid);
        ?>
          <div class="mt-card-wrap" data-reveal>
            <a href="<?php echo esc_url(get_permalink($tid)); ?>" class="block">
              <div class="mt-card mt-card--photo"
                   style="aspect-ratio:<?php echo esc_attr($ar); ?>;background-color:<?php echo esc_attr($tone); ?>;min-height:150px">
                <?php if ($img) : ?>
                  <img src="<?php echo esc_url($img['sizes']['large'] ?? $img['url']); ?>"
                       alt="<?php echo esc_attr($img['alt'] ?: $title_fr); ?>"
                       loading="lazy" decoding="async"
                       class="absolute inset-0 w-full h-full object-cover grayscale">
                <?php else : ?>
                  <div class="mt-card-ph-center">
                    <span><span class="mt-fr">Photographie · N&B</span><span class="mt-en">Photograph · B&W</span></span>
                  </div>
                <?php endif; ?>
                <div class="mt-card-caption mt-card-caption--photo">
                  <div class="mt-caption-photo-title">
                    <span class="mt-fr"><?php echo esc_html($title_fr); ?></span>
                    <span class="mt-en"><?php echo esc_html($title_en); ?></span>
                  </div>
                  <div class="mt-caption-meta">
                    <span><?php echo esc_html($loc); ?></span>
                    <?php if ($loc && $date) : ?><span class="mt-caption-meta-line"></span><?php endif; ?>
                    <span><?php echo esc_html($date); ?></span>
                  </div>
                </div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Navigation précédent / suivant -->
  <nav style="margin-top:clamp(56px,8vh,96px);border-top:1px solid rgba(236,234,228,0.12);padding-top:28px;display:flex;justify-content:space-between;gap:20px">
    <?php
    $prev = get_previous_post();
    $next = get_next_post();
    ?>
    <div>
      <?php if ($prev) : ?>
        <a href="<?php echo esc_url(get_permalink($prev)); ?>"
           class="font-sans uppercase text-faint transition hover:text-paper"
           style="font-size:10px;letter-spacing:0.2em">
          ← <?php echo esc_html(get_the_title($prev)); ?>
        </a>
      <?php endif; ?>
    </div>
    <div>
      <?php if ($next) : ?>
        <a href="<?php echo esc_url(get_permalink($next)); ?>"
           class="font-sans uppercase text-faint transition hover:text-paper"
           style="font-size:10px;letter-spacing:0.2em">
          <?php echo esc_html(get_the_title($next)); ?> →
        </a>
      <?php endif; ?>
    </div>
  </nav>

</article>

<?php endwhile; ?>

<?php get_footer(); ?>
