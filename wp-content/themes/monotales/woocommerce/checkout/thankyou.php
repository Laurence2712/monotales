<?php
defined('ABSPATH') || exit;
?>

<div class="animate-fade" style="padding:clamp(60px,10vh,120px) clamp(20px,5vw,56px);max-width:680px;margin:0 auto;text-align:center">

  <?php if ($order) : ?>

    <p class="font-sans uppercase text-faint" style="font-size:10px;letter-spacing:0.3em;margin:0 0 24px">
      <span class="mt-fr">Commande confirmée</span>
      <span class="mt-en">Order confirmed</span>
    </p>

    <h1 class="font-display text-paper" style="font-weight:300;font-style:italic;font-size:clamp(34px,4.6vw,58px);line-height:1.02;margin:0 0 28px">
      <span class="mt-fr">Merci.</span>
      <span class="mt-en">Thank you.</span>
    </h1>

    <p class="font-display text-silver" style="font-style:italic;font-size:clamp(15px,1.4vw,19px);line-height:1.65;margin:0 0 40px">
      <span class="mt-fr">
        Votre commande <strong style="color:rgba(236,234,228,0.7);font-weight:400">#<?php echo esc_html($order->get_order_number()); ?></strong>
        a bien été reçue. Vous recevrez un email de confirmation à
        <strong style="color:rgba(236,234,228,0.7);font-weight:400"><?php echo esc_html($order->get_billing_email()); ?></strong>.
      </span>
      <span class="mt-en">
        Your order <strong style="color:rgba(236,234,228,0.7);font-weight:400">#<?php echo esc_html($order->get_order_number()); ?></strong>
        has been received. A confirmation email has been sent to
        <strong style="color:rgba(236,234,228,0.7);font-weight:400"><?php echo esc_html($order->get_billing_email()); ?></strong>.
      </span>
    </p>

    <!-- Récap commande -->
    <div style="border:1px solid rgba(236,234,228,0.1);padding:clamp(24px,4vh,40px);text-align:left;margin:0 0 48px">
      <?php foreach ($order->get_items() as $item) :
          $product = $item->get_product();
          $tale_id = $product ? (int) get_field('product_tale', $product->get_id()) : 0;
          $img     = $tale_id ? get_field('tale_image', $tale_id) : null;
          $title_fr = get_field('product_title_fr', $product->get_id()) ?: $item->get_name();
          $title_en = get_field('product_title_en', $product->get_id()) ?: $item->get_name();
      ?>
        <div style="display:flex;gap:20px;align-items:center;padding:16px 0;border-bottom:1px solid rgba(236,234,228,0.06)">
          <?php if ($img) : ?>
            <div style="width:60px;height:60px;flex-shrink:0;overflow:hidden">
              <img src="<?php echo esc_url($img['sizes']['thumbnail'] ?? $img['url']); ?>"
                   alt="" style="width:100%;height:100%;object-fit:cover;filter:grayscale(1)">
            </div>
          <?php endif; ?>
          <div style="flex:1;min-width:0">
            <p class="font-display text-paper" style="font-style:italic;font-size:16px;margin:0 0 4px">
              <span class="mt-fr"><?php echo esc_html($title_fr); ?></span>
              <span class="mt-en"><?php echo esc_html($title_en); ?></span>
            </p>
            <?php $meta = $item->get_formatted_meta_data(''); ?>
            <?php if ($meta) : foreach ($meta as $m) : ?>
              <p class="font-sans text-faint" style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;margin:0">
                <?php echo wp_kses_post($m->display_value); ?>
              </p>
            <?php endforeach; endif; ?>
          </div>
          <div class="font-sans text-silver" style="font-size:13px;flex-shrink:0">
            <?php echo wp_kses_post(wc_price($item->get_subtotal())); ?>
          </div>
        </div>
      <?php endforeach; ?>

      <div style="padding-top:20px;text-align:right">
        <span class="font-sans text-faint" style="font-size:10px;letter-spacing:0.2em;text-transform:uppercase;margin-right:16px">
          <span class="mt-fr">Total</span><span class="mt-en">Total</span>
        </span>
        <span class="font-sans text-paper" style="font-size:16px">
          <?php echo wp_kses_post($order->get_formatted_order_total()); ?>
        </span>
      </div>
    </div>

    <a href="<?php echo esc_url(home_url('/')); ?>"
       class="font-sans uppercase text-faint hover:text-paper transition-colors duration-300"
       style="font-size:10px;letter-spacing:0.28em">
      <span class="mt-fr">← Retour à la galerie</span>
      <span class="mt-en">← Back to gallery</span>
    </a>

  <?php else : ?>

    <p class="font-display text-silver" style="font-style:italic;font-size:18px">
      <span class="mt-fr">Merci pour votre commande.</span>
      <span class="mt-en">Thank you for your order.</span>
    </p>

  <?php endif; ?>

</div>
