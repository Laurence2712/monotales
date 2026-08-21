<?php
defined('ABSPATH') || exit;

/* ══════════════════════════════════════════════════════
   MONOTALES — Pipeline Make.com
   ── Webhook sortant (tale publié → réseaux sociaux)
   ── REST API entrante (Google Drive → WordPress)
   ══════════════════════════════════════════════════════ */

/* ── Enregistrement des meta ACF pour le REST API ─── */
add_action('init', function () {
    $text_fields = [
        'tale_title_fr', 'tale_title_en',
        'tale_location', 'tale_date',
        'tale_aspect_ratio', 'tale_tone',
    ];
    foreach ($text_fields as $key) {
        register_post_meta('tale', $key, [
            'type'              => 'string',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback'     => fn() => current_user_can('edit_posts'),
        ]);
    }
    foreach (['tale_text_fr', 'tale_text_en'] as $key) {
        register_post_meta('tale', $key, [
            'type'          => 'string',
            'single'        => true,
            'show_in_rest'  => true,
            'auth_callback' => fn() => current_user_can('edit_posts'),
        ]);
    }
    register_post_meta('tale', 'tale_serie', [
        'type'              => 'integer',
        'single'            => true,
        'show_in_rest'      => true,
        'sanitize_callback' => 'absint',
        'auth_callback'     => fn() => current_user_can('edit_posts'),
    ]);
});

/* ── REST API : endpoints Make.com ───────────────── */
add_action('rest_api_init', function () {

    // Publier un tale (Make.com → WordPress)
    register_rest_route('monotales/v1', '/publish', [
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'mt_rest_publish_tale',
        'permission_callback' => fn() => current_user_can('publish_posts'),
        'args'                => [
            'title_fr' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'title_en' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'text_fr'  => ['type' => 'string'],
            'text_en'  => ['type' => 'string'],
            'location' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'date'     => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'serie'    => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'serie_id' => ['type' => 'integer', 'sanitize_callback' => 'absint'],
            'image_url'=> ['type' => 'string', 'sanitize_callback' => 'esc_url_raw'],
            'image_id' => ['type' => 'integer', 'sanitize_callback' => 'absint'],
            'aspect_ratio' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'tone'     => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
        ],
    ]);

    // Lister les séries (pour que Make connaisse les IDs)
    register_rest_route('monotales/v1', '/series', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'mt_rest_list_series',
        'permission_callback' => fn() => current_user_can('edit_posts'),
    ]);
});

function mt_rest_publish_tale(WP_REST_Request $req): WP_REST_Response|WP_Error {
    $p = $req->get_params();

    // Résolution de la série par titre FR ou ID direct
    $serie_id = (int) ($p['serie_id'] ?? 0);
    if (!$serie_id && !empty($p['serie'])) {
        $serie_id = mt_find_serie_by_title($p['serie']);
    }
    if (!$serie_id) {
        return new WP_Error('serie_not_found',
            "Série introuvable : « {$p['serie']} ». Vérifier via GET /monotales/v1/series.",
            ['status' => 422]);
    }

    // Gestion de l'image
    $image_id = (int) ($p['image_id'] ?? 0);
    if (!$image_id && !empty($p['image_url'])) {
        $image_id = mt_sideload_image($p['image_url'], $p['title_fr']);
        if (!$image_id) {
            return new WP_Error('image_failed',
                "Impossible de télécharger l'image depuis : {$p['image_url']}",
                ['status' => 422]);
        }
    }

    // Création du post
    $post_id = wp_insert_post([
        'post_type'   => 'tale',
        'post_title'  => $p['title_fr'],
        'post_status' => 'publish',
    ], true);
    if (is_wp_error($post_id)) return $post_id;

    // Champs ACF
    $text_fields = ['tale_title_fr' => $p['title_fr'] ?? '', 'tale_title_en' => $p['title_en'] ?? '',
                    'tale_location' => $p['location'] ?? '', 'tale_date' => $p['date'] ?? '',
                    'tale_aspect_ratio' => $p['aspect_ratio'] ?? '3/4', 'tale_tone' => $p['tone'] ?? '#f4f3ef'];
    foreach ($text_fields as $key => $val) {
        if ($val !== '') update_field($key, $val, $post_id);
    }
    if (!empty($p['text_fr'])) update_field('tale_text_fr', mt_text_to_html($p['text_fr']), $post_id);
    if (!empty($p['text_en'])) update_field('tale_text_en', mt_text_to_html($p['text_en']), $post_id);

    update_field('tale_serie', $serie_id, $post_id);

    if ($image_id) {
        update_field('tale_image', $image_id, $post_id);
        set_post_thumbnail($post_id, $image_id);
    }

    return new WP_REST_Response([
        'id'        => $post_id,
        'permalink' => get_permalink($post_id),
        'title_fr'  => $p['title_fr'],
        'serie_id'  => $serie_id,
        'image_id'  => $image_id,
    ], 201);
}

function mt_rest_list_series(): WP_REST_Response {
    $q = new WP_Query(['post_type' => 'serie', 'posts_per_page' => -1,
                       'orderby' => 'menu_order', 'order' => 'ASC']);
    $out = [];
    while ($q->have_posts()) {
        $q->the_post();
        $id    = get_the_ID();
        $out[] = [
            'id'       => $id,
            'title_fr' => get_field('serie_title_fr', $id) ?: get_the_title(),
            'title_en' => get_field('serie_title_en', $id) ?: get_the_title(),
            'slug'     => get_post_field('post_name'),
        ];
    }
    wp_reset_postdata();
    return new WP_REST_Response($out, 200);
}

/* ── Webhook sortant : tale publié → Make.com ─────── */
add_action('transition_post_status', function (string $new, string $old, WP_Post $post) {
    if ($post->post_type !== 'tale' || $new !== 'publish' || $old === 'publish') return;

    $url     = get_option('mt_webhook_url', '');
    $enabled = get_option('mt_webhook_enabled', '0');
    if (!$url || $enabled !== '1') return;

    $id       = $post->ID;
    $img      = get_field('tale_image', $id);
    $serie_id = (int) get_field('tale_serie', $id);

    $ig_caption_fr  = get_field('tale_ig_caption_fr', $id) ?: '';
    $ig_caption_en  = get_field('tale_ig_caption_en', $id) ?: '';
    $ig_hashtags_fr = get_field('tale_ig_hashtags_fr', $id) ?: '';
    $ig_hashtags_en = get_field('tale_ig_hashtags_en', $id) ?: '';

    wp_remote_post($url, [
        'headers'  => ['Content-Type' => 'application/json'],
        'body'     => wp_json_encode([
            // Métadonnées
            'tale_id'   => $id,
            'permalink' => get_permalink($id),
            'title_fr'  => get_field('tale_title_fr', $id) ?: $post->post_title,
            'title_en'  => get_field('tale_title_en', $id) ?: $post->post_title,
            'text_fr'   => wp_strip_all_tags(get_field('tale_text_fr', $id) ?: ''),
            'text_en'   => wp_strip_all_tags(get_field('tale_text_en', $id) ?: ''),
            'location'  => get_field('tale_location', $id) ?: '',
            'date'      => get_field('tale_date', $id) ?: '',
            'serie_fr'  => $serie_id ? (get_field('serie_title_fr', $serie_id) ?: '') : '',
            'serie_en'  => $serie_id ? (get_field('serie_title_en', $serie_id) ?: '') : '',
            // Image
            'image_url'      => $img ? ($img['sizes']['large'] ?? $img['url']) : '',
            'image_url_full' => $img ? $img['url'] : '',
            // Instagram
            'ig_caption_fr'  => $ig_caption_fr ? $ig_caption_fr . ($ig_hashtags_fr ? "\n\n" . $ig_hashtags_fr : '') : '',
            'ig_caption_en'  => $ig_caption_en ? $ig_caption_en . ($ig_hashtags_en ? "\n\n" . $ig_hashtags_en : '') : '',
            // Facebook
            'fb_caption_fr'  => get_field('tale_fb_caption_fr', $id) ?: '',
            'fb_caption_en'  => get_field('tale_fb_caption_en', $id) ?: '',
            // Pinterest
            'pin_title_fr'   => get_field('tale_pin_title_fr', $id) ?: (get_field('tale_title_fr', $id) ?: ''),
            'pin_title_en'   => get_field('tale_pin_title_en', $id) ?: (get_field('tale_title_en', $id) ?: ''),
            'pin_desc_fr'    => get_field('tale_pin_desc_fr', $id) ?: '',
            'pin_desc_en'    => get_field('tale_pin_desc_en', $id) ?: '',
            'pin_board'      => get_field('tale_pin_board', $id) ?: '',
        ]),
        'timeout'  => 10,
        'blocking' => false,
    ]);
}, 10, 3);

/* ── Page de réglages WP admin ───────────────────── */
add_action('admin_menu', function () {
    add_options_page('Monotales', 'Monotales', 'manage_options', 'monotales-settings', 'mt_settings_page');
});
add_action('admin_init', function () {
    register_setting('monotales', 'mt_webhook_url',       ['sanitize_callback' => 'esc_url_raw']);
    register_setting('monotales', 'mt_webhook_enabled',   ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('monotales', 'mt_plausible_domain',  ['sanitize_callback' => 'sanitize_text_field']);
});

function mt_settings_page(): void {
    $endpoint_publish = get_rest_url(null, 'monotales/v1/publish');
    $endpoint_series  = get_rest_url(null, 'monotales/v1/series');
    ?>
    <div class="wrap">
      <h1>Monotales — Pipeline réseaux sociaux</h1>

      <form method="post" action="options.php">
        <?php settings_fields('monotales'); ?>
        <h2>1. Webhook sortant (publication → Make.com)</h2>
        <table class="form-table">
          <tr>
            <th>URL webhook Make.com</th>
            <td>
              <input type="url" name="mt_webhook_url"
                     value="<?php echo esc_attr(get_option('mt_webhook_url')); ?>"
                     class="regular-text"
                     placeholder="https://hook.eu2.make.com/xxxxxxxxxxxxxxxx">
              <p class="description">Dans Make.com : Webhooks → Custom webhook → Copy address</p>
            </td>
          </tr>
          <tr>
            <th>Activer l'envoi</th>
            <td>
              <label>
                <input type="checkbox" name="mt_webhook_enabled" value="1"
                       <?php checked(get_option('mt_webhook_enabled'), '1'); ?>>
                Envoyer aux réseaux sociaux lors de la publication d'un tale
              </label>
            </td>
          </tr>
        </table>
        <?php submit_button('Enregistrer'); ?>
      </form>

      <hr>
      <h2>Analytics (Plausible)</h2>
      <form method="post" action="options.php">
        <?php settings_fields('monotales'); ?>
        <table class="form-table">
          <tr>
            <th>Domaine Plausible</th>
            <td>
              <input type="text" name="mt_plausible_domain"
                     value="<?php echo esc_attr(get_option('mt_plausible_domain')); ?>"
                     class="regular-text"
                     placeholder="monotales.com">
              <p class="description">
                Laisser vide pour désactiver. Créer un site sur
                <a href="https://plausible.io" target="_blank">plausible.io</a>
                et saisir le domaine exact (sans https://).
                Aucun cookie, aucun consentement requis.
              </p>
            </td>
          </tr>
        </table>
        <?php submit_button('Enregistrer'); ?>
      </form>

      <hr>
      <h2>2. Endpoint Make.com → WordPress (Google Drive)</h2>
      <p>Make.com envoie une requête <strong>POST</strong> à cette URL pour créer un tale :</p>
      <code style="display:block;padding:10px;background:#f0f0f0;margin:8px 0"><?php echo esc_url($endpoint_publish); ?></code>

      <h3>Authentification</h3>
      <ol>
        <li>Aller dans <strong>Utilisateurs → Votre profil</strong></li>
        <li>Section <em>Mots de passe d'application</em> → créer un mot de passe nommé "Make.com"</li>
        <li>Dans Make.com : HTTP module → Basic Auth → login WordPress + mot de passe d'application</li>
      </ol>

      <h3>Corps JSON attendu</h3>
      <pre style="background:#f0f0f0;padding:12px;border-radius:4px;overflow-x:auto"><?php echo esc_html('{
  "title_fr":     "Le premier tramway",   ← requis
  "title_en":     "The first tram",
  "text_fr":      "À l\'heure où la ville hésite...",
  "text_en":      "In the hour when the city wavers...",
  "location":     "Alfama, Lisboa",
  "date":         "14.03.2026",
  "serie":        "Lisboa",              ← titre FR de la série
  "image_id":     42,                    ← ID média WP (si uploadé séparément)
  "image_url":    "https://...",         ← ou URL publique directe
  "aspect_ratio": "3/4",                 ← optionnel
  "tone":         "#f4f3ef"              ← optionnel
}'); ?></pre>

      <h3>Lister les séries disponibles</h3>
      <code style="display:block;padding:10px;background:#f0f0f0;margin:8px 0">GET <?php echo esc_url($endpoint_series); ?></code>

      <hr>
      <h2>3. Structure Google Drive</h2>
      <pre style="background:#f0f0f0;padding:12px;border-radius:4px">Monotales/
├── À publier/
│   ├── 2026-07-15_bedroom-2.jpg       ← image haute résolution
│   └── 2026-07-15_bedroom-2.gdoc      ← Google Doc avec les métadonnées
└── Publié/                             ← Make.com déplace ici après publication</pre>

      <h3>Template Google Doc</h3>
      <pre style="background:#f0f0f0;padding:12px;border-radius:4px;overflow-x:auto"><?php echo esc_html(
'TITRE FR: bedroom 2
TITRE EN: bedroom 2
SÉRIE: Habitat
LIEU: Bruxelles, Belgique
DATE: 15.07.2026

--- TEXTE FR ---
Le texte principal de la photo (affiché sur le site)...

--- TEXTE EN ---
The main text of the photograph (displayed on the site)...

--- INSTAGRAM FR ---
Caption Instagram en français.
(texte court, percutant, max ~150 mots)

#photographie #noiretblanc #argentique #monotales

--- INSTAGRAM EN ---
Instagram caption in English.

#photography #blackandwhite #filmphotography #monotales

--- FACEBOOK FR ---
Texte Facebook, peut être plus long, plus narratif.

--- PINTEREST FR ---
TITRE PIN: bedroom 2 — tirage Fine Art N&B
BOARD: Black & White Photography
Description: Photographie de chambre en noir et blanc...'); ?></pre>

      <h2>4. Scénario Make.com — étapes complètes</h2>
      <ol style="line-height:2.2">
        <li><strong>Google Drive → Watch Files</strong> : surveille <em>Monotales/À publier/</em>, filtre <code>.gdoc</code></li>
        <li><strong>Google Docs → Get a Document</strong> : récupère le contenu du doc</li>
        <li><strong>Text Parser → Parse text</strong> : extrait chaque section (TITRE FR:, --- INSTAGRAM FR ---, etc.)</li>
        <li><strong>Google Drive → Search Files</strong> : trouve le .jpg du même nom</li>
        <li><strong>Google Drive → Download File</strong> : télécharge l'image</li>
        <li><strong>HTTP → Upload vers WordPress</strong> : <code>POST /wp/v2/media</code> (Basic Auth)</li>
        <li><strong>HTTP → Créer le tale</strong> : <code>POST /monotales/v1/publish</code> avec le JSON complet</li>
        <li><strong>Router</strong> : 3 branches en parallèle ↓</li>
        <li style="margin-left:20px"><strong>Meta for Instagram → Create a Photo</strong> : image + <code>ig_caption_fr</code></li>
        <li style="margin-left:20px"><strong>Facebook Pages → Create a Post</strong> : image + <code>fb_caption_fr</code> + lien permalink</li>
        <li style="margin-left:20px"><strong>Pinterest → Create a Pin</strong> : image + <code>pin_title_fr</code> + <code>pin_desc_fr</code> + board + permalink</li>
        <li><strong>Google Drive → Move File</strong> : déplace .jpg et .gdoc vers <em>Monotales/Publié/</em></li>
      </ol>

      <h2>5. Tokens et connexions à configurer dans Make.com</h2>
      <table class="form-table">
        <tr><th>Instagram</th><td>Meta for Business → Instagram Graph API. Nécessite un compte Instagram <strong>Business ou Creator</strong> lié à une Page Facebook.<br>
        Dans Make.com : module "Meta for Instagram" → connecter votre compte Meta.</td></tr>
        <tr><th>Facebook</th><td>Module "Facebook Pages" dans Make.com → autoriser la Page Monotales.</td></tr>
        <tr><th>Pinterest</th><td>Module "Pinterest" dans Make.com → connecter le compte Pinterest.<br>
        L'image doit être une URL publique (utiliser l'URL WordPress après upload).</td></tr>
      </table>

      <h2>6. Roadmap réseaux — phase 2</h2>
      <table class="form-table" style="opacity:0.7">
        <tr><th>Bluesky</th><td>AT Protocol, API gratuite et ouverte. Module HTTP → <code>api.bsky.app</code>. Simple à ajouter.</td></tr>
        <tr><th>X / Twitter</th><td>API v2, plan Basic requis (100 $/mois ou accès gratuit limité). Module "Twitter" dans Make.</td></tr>
        <tr><th>LinkedIn</th><td>LinkedIn Pages API. Module "LinkedIn" dans Make.com.</td></tr>
        <tr><th>Flickr / 500px</th><td>APIs disponibles. Module HTTP + clé API.</td></tr>
        <tr><th>Substack</th><td>Pas d'API de publication. Approche : Make envoie un draft par email à votre adresse Substack.</td></tr>
        <tr><th>TikTok</th><td>Nécessite du contenu vidéo. Hors scope photo fixe — à reconsidérer si Reels/Shorts.</td></tr>
      </table>
    </div>
    <?php
}

/* ── Helpers ─────────────────────────────────────── */

function mt_find_serie_by_title(string $title): int {
    // Cherche d'abord par meta serie_title_fr, puis par titre du post
    $q = new WP_Query([
        'post_type'      => 'serie',
        'meta_query'     => [['key' => 'serie_title_fr', 'value' => $title, 'compare' => '=']],
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ]);
    if ($q->have_posts()) return (int) $q->posts[0];

    $q2 = new WP_Query([
        'post_type'      => 'serie',
        'title'          => $title,
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ]);
    return $q2->have_posts() ? (int) $q2->posts[0] : 0;
}

function mt_sideload_image(string $url, string $title): int {
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $tmp = download_url($url);
    if (is_wp_error($tmp)) return 0;

    $ext  = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
    $file = [
        'name'     => sanitize_file_name($title) . '.' . $ext,
        'tmp_name' => $tmp,
        'error'    => 0,
        'size'     => filesize($tmp),
    ];

    $id = media_handle_sideload($file, 0, $title);
    @unlink($tmp);
    return is_wp_error($id) ? 0 : (int) $id;
}

function mt_text_to_html(string $text): string {
    // Convertit le texte brut (Google Docs) en HTML simple avec paragraphes
    $text  = wp_kses_post(trim($text));
    $paras = preg_split('/\n{2,}/', $text);
    $html  = '';
    foreach ($paras as $para) {
        $para = trim(str_replace("\n", '<br>', $para));
        if ($para !== '') $html .= "<p>{$para}</p>\n";
    }
    return $html ?: "<p>{$text}</p>";
}
