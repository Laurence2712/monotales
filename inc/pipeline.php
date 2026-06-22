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

    wp_remote_post($url, [
        'headers'  => ['Content-Type' => 'application/json'],
        'body'     => wp_json_encode([
            'tale_id'   => $id,
            'permalink' => get_permalink($id),
            'title_fr'  => get_field('tale_title_fr', $id) ?: $post->post_title,
            'title_en'  => get_field('tale_title_en', $id) ?: $post->post_title,
            'text_fr'   => wp_strip_all_tags(get_field('tale_text_fr', $id) ?: ''),
            'text_en'   => wp_strip_all_tags(get_field('tale_text_en', $id) ?: ''),
            'location'  => get_field('tale_location', $id) ?: '',
            'date'      => get_field('tale_date', $id) ?: '',
            'image_url' => $img ? ($img['sizes']['large'] ?? $img['url']) : '',
            'serie_fr'  => $serie_id ? (get_field('serie_title_fr', $serie_id) ?: '') : '',
            'serie_en'  => $serie_id ? (get_field('serie_title_en', $serie_id) ?: '') : '',
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
      <h2>3. Scénario Make.com — étapes</h2>
      <ol style="line-height:2">
        <li><strong>Google Drive → Watch Files</strong> : surveille le dossier <em>Monotales/À publier</em></li>
        <li><strong>Google Drive → Download File</strong> : récupère l'image (.jpg)</li>
        <li><strong>Google Docs → Get a Document</strong> : lit le Google Doc</li>
        <li><strong>Text Parser</strong> : extrait les champs (TITRE FR:, LIEU:, etc.)</li>
        <li><strong>WordPress → Upload Media</strong> (ou HTTP POST vers <code>/wp/v2/media</code>) : upload l'image</li>
        <li><strong>HTTP → Make a request</strong> : POST vers <code>/monotales/v1/publish</code> avec le JSON</li>
        <li><strong>Router → Instagram / Facebook / Pinterest</strong></li>
        <li><strong>Google Drive → Move File</strong> : déplace le doc vers <em>Monotales/Publié</em></li>
      </ol>
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
