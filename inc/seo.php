<?php
defined('ABSPATH') || exit;

/* ══════════════════════════════════════════════════════
   MONOTALES — SEO
   Open Graph · Twitter Card · Schema.org · hreflang
   ══════════════════════════════════════════════════════ */

add_action('wp_head', 'mt_seo_head', 1);

function mt_seo_head(): void {
    if (is_singular('tale'))                    mt_seo_tale();
    elseif (is_singular('serie'))               mt_seo_serie();
    elseif (is_front_page())                    mt_seo_home();
    elseif (is_page_template('page-manifesto.php')) mt_seo_manifesto();
    else                                        mt_seo_default();

    // hreflang — même URL sert FR et EN
    $url = get_permalink() ?: home_url('/');
    echo "\n";
    printf('<link rel="alternate" hreflang="fr" href="%s">' . "\n",         esc_url($url));
    printf('<link rel="alternate" hreflang="en" href="%s">' . "\n",         esc_url($url));
    printf('<link rel="alternate" hreflang="x-default" href="%s">' . "\n",  esc_url($url));

    // Canonical
    printf('<link rel="canonical" href="%s">' . "\n", esc_url($url));
}

/* ── Données par type de page ─────────────────────── */

function mt_seo_tale(): void {
    $id      = get_the_ID();
    $tfr     = get_field('tale_title_fr', $id) ?: get_the_title();
    $ten     = get_field('tale_title_en', $id) ?: $tfr;
    $textfr  = wp_strip_all_tags(get_field('tale_text_fr', $id) ?: '');
    $texten  = wp_strip_all_tags(get_field('tale_text_en', $id) ?: '');
    $loc     = get_field('tale_location', $id) ?: '';
    $date    = get_field('tale_date', $id) ?: '';
    $img     = get_field('tale_image', $id);
    $img_url = $img ? ($img['sizes']['large'] ?? $img['url']) : mt_og_fallback();
    $desc_fr = trim(($loc ? "{$loc} — " : '') . mb_substr($textfr, 0, 150));
    $desc_en = trim(($loc ? "{$loc} — " : '') . mb_substr($texten, 0, 150));

    mt_og([
        'title' => "{$tfr} — MONOTALES",
        'desc'  => $desc_fr,
        'url'   => get_permalink($id),
        'image' => $img_url,
        'iw'    => $img['sizes']['large-width']  ?? null,
        'ih'    => $img['sizes']['large-height'] ?? null,
        'type'  => 'article',
    ]);
    printf('<meta name="description" content="%s">' . "\n", esc_attr($desc_fr));

    $serie_id = (int) get_field('tale_serie', $id);
    $serie_fr = $serie_id ? get_field('serie_title_fr', $serie_id) : '';
    mt_schema(array_filter([
        '@context'        => 'https://schema.org',
        '@type'           => 'Photograph',
        'name'            => $tfr,
        'description'     => $desc_fr,
        'url'             => get_permalink($id),
        'inLanguage'      => ['fr', 'en'],
        'author'          => ['@type' => 'Person', 'name' => 'MONOTALES'],
        'contentLocation' => $loc  ? ['@type' => 'Place', 'name' => $loc] : null,
        'datePublished'   => mt_date_iso($date),
        'image'           => $img_url ?: null,
        'isPartOf'        => $serie_fr ? ['@type' => 'ImageGallery', 'name' => $serie_fr] : null,
    ]));
}

function mt_seo_serie(): void {
    $id    = get_the_ID();
    $tfr   = get_field('serie_title_fr', $id) ?: get_the_title();
    $place = get_field('serie_place_fr', $id) ?: '';
    $intro = get_field('serie_intro_fr', $id) ?: '';
    $desc  = trim(($place ? "{$place} — " : '') . mb_substr($intro, 0, 150))
           ?: "Série photographique en noir et blanc — {$tfr}";

    mt_og(['title' => "{$tfr} — MONOTALES", 'desc' => $desc,
           'url' => get_permalink($id), 'image' => mt_og_fallback(), 'type' => 'article']);
    printf('<meta name="description" content="%s">' . "\n", esc_attr($desc));
    mt_schema(array_filter([
        '@context'        => 'https://schema.org',
        '@type'           => 'ImageGallery',
        'name'            => $tfr,
        'description'     => $desc,
        'url'             => get_permalink($id),
        'author'          => ['@type' => 'Person', 'name' => 'MONOTALES'],
        'contentLocation' => $place ? ['@type' => 'Place', 'name' => $place] : null,
    ]));
}

function mt_seo_home(): void {
    $desc = 'Photographies en noir et blanc. Un lieu, une photo, un texte. Bilingue français / anglais.';
    mt_og(['title' => 'MONOTALES — Photographies en noir et blanc',
           'desc' => $desc, 'url' => home_url('/'), 'image' => mt_og_fallback(), 'type' => 'website']);
    printf('<meta name="description" content="%s">' . "\n", esc_attr($desc));
    mt_schema(['@context' => 'https://schema.org', '@type' => 'WebSite',
               'name' => 'MONOTALES', 'description' => $desc, 'url' => home_url('/'),
               'inLanguage' => ['fr', 'en'], 'author' => ['@type' => 'Person', 'name' => 'MONOTALES']]);
}

function mt_seo_manifesto(): void {
    $desc = 'Une photo. Un lieu. Un texte. Le manifeste de MONOTALES.';
    mt_og(['title' => 'Manifeste — MONOTALES', 'desc' => $desc,
           'url' => get_permalink(), 'image' => mt_og_fallback(), 'type' => 'article']);
    printf('<meta name="description" content="%s">' . "\n", esc_attr($desc));
}

function mt_seo_default(): void {
    $desc = 'Photographies en noir et blanc — MONOTALES';
    mt_og(['title' => get_bloginfo('name') . ' — MONOTALES', 'desc' => $desc,
           'url' => get_permalink() ?: home_url('/'), 'image' => mt_og_fallback(), 'type' => 'website']);
    printf('<meta name="description" content="%s">' . "\n", esc_attr($desc));
}

/* ── Helpers ─────────────────────────────────────── */

function mt_og(array $d): void {
    printf('<meta property="og:title" content="%s">'       . "\n", esc_attr($d['title']));
    printf('<meta property="og:description" content="%s">' . "\n", esc_attr($d['desc']));
    printf('<meta property="og:url" content="%s">'         . "\n", esc_url($d['url']));
    printf('<meta property="og:type" content="%s">'        . "\n", esc_attr($d['type'] ?? 'website'));
    printf('<meta property="og:site_name" content="MONOTALES">' . "\n");
    printf('<meta property="og:locale" content="fr_FR">'   . "\n");
    printf('<meta property="og:locale:alternate" content="en_US">' . "\n");
    if (!empty($d['image'])) {
        printf('<meta property="og:image" content="%s">'   . "\n", esc_url($d['image']));
        if (!empty($d['iw'])) printf('<meta property="og:image:width" content="%d">'  . "\n", (int)$d['iw']);
        if (!empty($d['ih'])) printf('<meta property="og:image:height" content="%d">' . "\n", (int)$d['ih']);
        printf('<meta property="og:image:alt" content="%s">' . "\n", esc_attr($d['title']));
    }
    // Twitter Card
    printf('<meta name="twitter:card" content="summary_large_image">' . "\n");
    printf('<meta name="twitter:title" content="%s">'       . "\n", esc_attr($d['title']));
    printf('<meta name="twitter:description" content="%s">' . "\n", esc_attr($d['desc']));
    if (!empty($d['image'])) printf('<meta name="twitter:image" content="%s">' . "\n", esc_url($d['image']));
}

function mt_schema(array $data): void {
    printf('<script type="application/ld+json">%s</script>' . "\n",
        wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
}

function mt_og_fallback(): string {
    // Première image média uploadée, sinon vide
    $q = new WP_Query(['post_type' => 'attachment', 'post_mime_type' => 'image/jpeg',
                       'posts_per_page' => 1, 'orderby' => 'date', 'order' => 'ASC']);
    if ($q->have_posts()) { $q->the_post(); $url = wp_get_attachment_url(get_the_ID()); wp_reset_postdata(); return $url ?: ''; }
    return '';
}

function mt_date_iso(string $d): string {
    $p = explode('.', $d);
    return count($p) === 3 ? "{$p[2]}-{$p[1]}-{$p[0]}" : $d;
}

// S'assure que les CPT apparaissent dans le sitemap XML natif WP
add_filter('wp_sitemaps_post_types', function(array $types): array {
    return $types; // tale et serie déjà inclus via show_in_rest = true
});
