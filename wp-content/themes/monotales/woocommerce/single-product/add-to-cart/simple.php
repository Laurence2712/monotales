<?php
defined('ABSPATH') || exit;

global $product;
if (!$product->is_purchasable()) return;
?>

<form class="cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data'>
  <?php do_action('woocommerce_before_add_to_cart_button'); ?>

  <?php if (!$product->is_sold_individually()) : ?>
    <div class="mt-wc-qty">
      <?php
      woocommerce_quantity_input([
          'min_value'   => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product),
          'max_value'   => apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product),
          'input_value' => isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : $product->get_min_purchase_quantity(),
      ]);
      ?>
    </div>
  <?php endif; ?>

  <button type="submit"
          name="add-to-cart"
          value="<?php echo esc_attr($product->get_id()); ?>"
          class="single_add_to_cart_button mt-wc-btn-cart">
    <?php if ($product->is_in_stock()) : ?>
      <span class="mt-fr">Ajouter au panier</span>
      <span class="mt-en">Add to cart</span>
    <?php else : ?>
      <span class="mt-fr">Indisponible</span>
      <span class="mt-en">Unavailable</span>
    <?php endif; ?>
  </button>

  <?php do_action('woocommerce_after_add_to_cart_button'); ?>
</form>
