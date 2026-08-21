<?php
/*
 * Template Name: Manifeste
 * Template Post Type: page
 */
get_header();
?>

<?php if (have_posts()) : the_post(); ?>

<section class="animate-fade mx-auto" style="padding:clamp(60px,13vh,150px) clamp(24px,6vw,56px) clamp(70px,11vh,140px);max-width:680px">

  <p class="font-sans uppercase text-dim text-center mb-11" style="font-size:11px;letter-spacing:0.42em">
    <span class="mt-fr">Manifeste</span>
    <span class="mt-en">Manifesto</span>
  </p>

  <?php
  $lead_fr   = get_field('manifesto_lead_fr');
  $lead_en   = get_field('manifesto_lead_en');
  $body_fr   = get_field('manifesto_body_fr');
  $body_en   = get_field('manifesto_body_en');

  // Fallback sur le contenu WordPress classique si pas de champs ACF
  if (!$lead_fr) {
      $lead_fr = __('MONOTALES est né d\'un refus : celui de la vitesse.', 'monotales');
      $lead_en = 'MONOTALES was born of a refusal: the refusal of speed.';
  }
  if (!$body_fr) {
      $body_fr = get_the_content();
      $body_en = '';
  }
  ?>

  <p class="font-display text-paper m-0 mb-10" style="font-weight:300;font-size:clamp(28px,4.4vw,46px);line-height:1.18">
    <span class="mt-fr"><?php echo esc_html($lead_fr); ?></span>
    <span class="mt-en"><?php echo esc_html($lead_en); ?></span>
  </p>

  <div class="font-body text-quote" style="font-size:clamp(16px,1.5vw,20px);line-height:1.78">
    <div class="mt-fr"><?php echo wp_kses_post(wpautop($body_fr)); ?></div>
    <div class="mt-en"><?php echo $body_en ? wp_kses_post(wpautop($body_en)) : ''; ?></div>
  </div>

  <div class="mx-auto mt-12" style="width:40px;height:1px;background:rgba(236,234,228,0.4)"></div>

</section>

<?php endif; ?>
<?php get_footer(); ?>
