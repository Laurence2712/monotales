<?php
defined('ABSPATH') || exit;

/* ── Pipeline Make.com (REST API + webhook réseaux) ─ */
require_once get_template_directory() . '/inc/pipeline.php';

/* ── SEO ─────────────────────────────────────────── */
require_once get_template_directory() . '/inc/seo.php';

/* ── RGPD ────────────────────────────────────────── */
require_once get_template_directory() . '/inc/rgpd.php';

/* ── Theme setup ─────────────────────────────────── */
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption']);
    load_theme_textdomain('monotales', get_template_directory() . '/languages');

    register_nav_menus(['primary' => __('Navigation principale', 'monotales')]);
});

/* ── Enqueue ─────────────────────────────────────── */
add_action('wp_enqueue_scripts', function () {
    $ver = wp_get_theme()->get('Version');

    // Google Fonts
    wp_enqueue_style('monotales-fonts', 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500&family=EB+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&display=swap', [], null);

    // Compiled Tailwind CSS
    wp_enqueue_style('monotales-style', get_template_directory_uri() . '/dist/style.css', ['monotales-fonts'], $ver);

    // Main JS
    wp_enqueue_script('monotales-main', get_template_directory_uri() . '/js/main.js', [], $ver, true);

    // Pass data to JS
    wp_localize_script('monotales-main', 'MT', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('monotales'),
    ]);

    // Lightbox — pages série uniquement
    if (is_singular('serie')) {
        wp_enqueue_script('mt-lightbox', get_template_directory_uri() . '/js/lightbox.js', [], $ver, true);
    }
});

/* ── Analytics (Plausible) ──────────────────────── */
add_action('wp_head', function (): void {
    $domain = get_option('mt_plausible_domain', '');
    if (!$domain) return;
    printf(
        '<script defer data-domain="%s" src="https://plausible.io/js/script.js"></script>' . "\n",
        esc_attr($domain)
    );
}, 5);

/* ── Custom Post Types ───────────────────────────── */
add_action('init', function () {

    // Série photographique
    register_post_type('serie', [
        'labels' => [
            'name'          => 'Séries',
            'singular_name' => 'Série',
            'add_new_item'  => 'Ajouter une série',
            'edit_item'     => 'Modifier la série',
        ],
        'public'              => true,
        'has_archive'         => false,
        'show_in_rest'        => true,
        'supports'            => ['title', 'thumbnail', 'page-attributes'],
        'menu_icon'           => 'dashicons-format-gallery',
        'rewrite'             => ['slug' => 'series', 'with_front' => false],
    ]);

    // Produit (boutique) — non public, visible en admin uniquement
    register_post_type('produit', [
        'labels' => [
            'name'          => 'Boutique',
            'singular_name' => 'Produit',
            'add_new_item'  => 'Ajouter un produit',
            'edit_item'     => 'Modifier le produit',
            'all_items'     => 'Tous les produits',
        ],
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => false,
        'supports'           => ['title', 'page-attributes'],
        'menu_icon'          => 'dashicons-store',
        'rewrite'            => false,
    ]);

    // Photo / Tale
    register_post_type('tale', [
        'labels' => [
            'name'          => 'Tales (photos)',
            'singular_name' => 'Tale',
            'add_new_item'  => 'Ajouter un tale',
            'edit_item'     => 'Modifier le tale',
        ],
        'public'              => true,
        'has_archive'         => false,
        'show_in_rest'        => true,
        'supports'            => ['title', 'thumbnail', 'page-attributes'],
        'menu_icon'           => 'dashicons-camera',
        'rewrite'             => ['slug' => 'tales', 'with_front' => false],
    ]);
});

/* ── ACF: local JSON save/load ───────────────────── */
add_filter('acf/settings/save_json', function () {
    return get_template_directory() . '/acf-json';
});
add_filter('acf/settings/load_json', function ($paths) {
    $paths[] = get_template_directory() . '/acf-json';
    return $paths;
});

/* ── Helpers ─────────────────────────────────────── */

/**
 * Render bilingual content: outputs two spans, JS toggles visibility.
 * $fr and $en are already-escaped or safe HTML strings.
 */
function mt_bilingual(string $fr, string $en, string $tag = 'span', string $extra_class = ''): string {
    $c = $extra_class ? " $extra_class" : '';
    return sprintf(
        '<%1$s class="mt-fr%3$s">%4$s</%1$s><%1$s class="mt-en%3$s">%5$s</%1$s>',
        esc_attr($tag), '', $c,
        $fr, $en
    );
}

/**
 * Return ACF field value for a given lang key, with fallback.
 */
function mt_field(string $fr_key, string $en_key, int $post_id = 0): array {
    $id = $post_id ?: get_the_ID();
    return [
        'fr' => get_field($fr_key, $id) ?: '',
        'en' => get_field($en_key, $id) ?: '',
    ];
}

/**
 * Return aspect-ratio style string for inline use.
 */
function mt_aspect(string $ar): string {
    return esc_attr($ar); // e.g. "3/4" → used in style="aspect-ratio:3/4"
}

/**
 * Get all tales for a given serie post ID, ordered by menu_order then date.
 */
function mt_get_tales(int $serie_id): WP_Query {
    return new WP_Query([
        'post_type'      => 'tale',
        'posts_per_page' => -1,
        'orderby'        => ['menu_order' => 'ASC', 'date' => 'DESC'],
        'meta_query'     => [[
            'key'     => 'tale_serie',
            'value'   => $serie_id,
            'compare' => '=',
        ]],
    ]);
}

/**
 * Get all tales sorted by date DESC (for Journal).
 */
function mt_get_all_tales(): WP_Query {
    return new WP_Query([
        'post_type'      => 'tale',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
}

/**
 * Get all series ordered by menu_order.
 */
function mt_get_all_series(): WP_Query {
    return new WP_Query([
        'post_type'      => 'serie',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ]);
}

/* ── WooCommerce ─────────────────────────────────────── */
add_action('after_setup_theme', function () {
    add_theme_support('woocommerce', [
        'thumbnail_image_width' => 800,
        'single_image_width'    => 1200,
    ]);
}, 20);

// Retirer les styles WooCommerce par défaut (on les remplace intégralement)
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

// Flush rewrite rules une seule fois après activation WooCommerce
add_action('init', function () {
    if (function_exists('WC') && !get_transient('mt_wc_rewrite_flushed')) {
        flush_rewrite_rules(false);
        set_transient('mt_wc_rewrite_flushed', true, DAY_IN_SECONDS);
    }
}, 99);

// Retirer breadcrumb WooCommerce et sidebar
add_action('init', function () {
    remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
    remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
});

// Rediriger la boutique vers la home si désactivée
add_action('template_redirect', function () {
    if (defined('MT_BOUTIQUE_ACTIVE') && MT_BOUTIQUE_ACTIVE) return;
    if (is_woocommerce() || is_cart() || is_checkout() || is_account_page()) {
        wp_safe_redirect(home_url('/'), 302);
        exit;
    }
});

// Message "ajouté au panier" bilingue
add_filter('woocommerce_add_to_cart_message_html', function (string $_msg, array $_products): string {
    $url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/');
    return sprintf(
        '<span class="mt-fr">Ajouté au panier.</span><span class="mt-en">Added to cart.</span>'
        . ' <a href="%s" class="wc-forward button">'
        . '<span class="mt-fr">Voir le panier</span><span class="mt-en">View cart</span>'
        . '</a>',
        esc_url($url)
    );
}, 10, 2);

/**
 * Récupère l'image ACF du Tale lié à un produit WooCommerce.
 */
function mt_wc_product_image(int $product_id): array|false {
    $tale_id = get_field('product_tale', $product_id);
    if (!$tale_id) return false;
    $img = get_field('tale_image', (int) $tale_id);
    return is_array($img) ? $img : false;
}

/* ── Boutique helpers (infrastructure — non publique) ── */

/**
 * Get produits from the shop. Pass false to include unavailable ones.
 */
function mt_get_produits(bool $disponibles_seulement = true): WP_Query {
    $args = [
        'post_type'      => 'produit',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ];
    if ($disponibles_seulement) {
        $args['meta_query'] = [[
            'key'     => 'produit_disponible',
            'value'   => '1',
            'compare' => '=',
        ]];
    }
    return new WP_Query($args);
}

/**
 * Return the tale image array linked to a produit, or false.
 */
function mt_produit_image(int $produit_id): array|false {
    $tale_id = get_field('produit_tale', $produit_id);
    if (!$tale_id) return false;
    $img = get_field('tale_image', (int) $tale_id);
    return is_array($img) ? $img : false;
}
