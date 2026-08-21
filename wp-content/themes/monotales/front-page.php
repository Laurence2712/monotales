<?php get_header(); ?>

<?php
/* ── Séries pour la sidebar ── */
$series_q   = mt_get_all_series();
$series_list = [];
if ($series_q->have_posts()) {
  while ($series_q->have_posts()) : $series_q->the_post();
    $sid = get_the_ID();
    $series_list[] = [
      'id' => $sid,
      'fr' => get_field('serie_title_fr', $sid) ?: get_the_title(),
      'en' => get_field('serie_title_en', $sid) ?: get_the_title(),
    ];
  endwhile;
  wp_reset_postdata();
}

/* ── Tales pour la galerie ── */
$tales_q = mt_get_all_tales();
$tales   = [];
if ($tales_q->have_posts()) {
  while ($tales_q->have_posts()) : $tales_q->the_post();
    $tid      = get_the_ID();
    $raw_serie = get_field('tale_serie', $tid);
    $sid       = is_a($raw_serie, 'WP_Post') ? $raw_serie->ID : (int) $raw_serie;
    $tales[]   = [
      'id'       => $tid,
      'serie_id' => $sid,
      'permalink'=> get_permalink(),
      'title_fr' => get_field('tale_title_fr', $tid) ?: get_the_title(),
      'title_en' => get_field('tale_title_en', $tid) ?: get_the_title(),
      'loc'      => get_field('tale_location', $tid) ?: '',
      'date'     => get_field('tale_date', $tid)     ?: '',
      'ar'       => get_field('tale_aspect_ratio', $tid) ?: '3/4',
      'tone'     => get_field('tale_tone', $tid)     ?: '#f4f3ef',
      'img'      => get_field('tale_image', $tid),
    ];
  endwhile;
  wp_reset_postdata();
}
/* ── Reorder: round-robin across 4 ratio buckets so no two same-format cards ever sit adjacent ── */
$_buckets = ['tall' => [], 'portrait' => [], 'square' => [], 'landscape' => []];
foreach ($tales as $_t) {
  $_p = explode('/', $_t['ar']);
  $_r = (count($_p) === 2 && (float)$_p[1] > 0) ? (float)$_p[0] / (float)$_p[1] : 1.0;
  if      ($_r < 0.72) $_buckets['tall'][]      = $_t;
  elseif  ($_r < 0.90) $_buckets['portrait'][]  = $_t;
  elseif  ($_r < 1.10) $_buckets['square'][]    = $_t;
  else                 $_buckets['landscape'][] = $_t;
}
$_order  = ['tall', 'landscape', 'portrait', 'square'];
$_idx    = array_fill_keys($_order, 0);
$_sorted = [];
$_remain = true;
while ($_remain) {
  $_remain = false;
  foreach ($_order as $_cat) {
    if ($_idx[$_cat] < count($_buckets[$_cat])) {
      $_sorted[] = $_buckets[$_cat][$_idx[$_cat]++];
      $_remain   = true;
    }
  }
}
$tales = $_sorted;
unset($_buckets, $_order, $_idx, $_sorted, $_remain, $_t, $_p, $_r, $_cat);
?>

<div style="display:flex;align-items:flex-start">

  <!-- ── Sidebar séries ── -->
  <nav id="mt-series-nav" aria-label="<?php esc_attr_e('Séries', 'monotales'); ?>"
       style="flex-shrink:0;
              align-self:flex-start;
              width:clamp(130px,12vw,190px);
              padding:clamp(28px,5vh,54px) 0 60px clamp(20px,4vw,52px);
              position:sticky;
              top:57px;
              max-height:calc(100vh - 57px);
              overflow-y:auto;
              scrollbar-width:none;">

    <button class="mt-serie-btn active" data-serie="all">
      <span class="mt-fr">Tout</span><span class="mt-en">All</span>
    </button>

    <?php foreach ($series_list as $s) : ?>
    <button class="mt-serie-btn" data-serie="<?php echo esc_attr($s['id']); ?>">
      <span class="mt-fr"><?php echo esc_html($s['fr']); ?></span>
      <span class="mt-en"><?php echo esc_html($s['en']); ?></span>
    </button>
    <?php endforeach; ?>

  </nav>

  <!-- ── Galerie tales — colonnes explicites pour contrôler les formats adjacents ── -->
  <?php
  /* Distribute round-robin to 5 columns.
     $tales is pre-sorted [tall, landscape, portrait, square, tall, landscape...],
     so column c gets items c, c+5, c+10… which cycles through all 4 formats.
     Adjacent columns start at different format offsets → no two same-format images side by side. */
  $nc = 5;
  $cols_data = array_fill(0, $nc, []);
  foreach ($tales as $i => $t) { $cols_data[$i % $nc][] = $t; }
  ?>
  <div id="mt-tales-grid"
       style="flex:1;
              display:flex;
              gap:2px;
              align-items:flex-start;
              padding:clamp(20px,4vh,44px) 0 0 0;">

    <?php for ($ci = 0; $ci < $nc; $ci++) : ?>
    <div class="mt-col" style="flex:1;min-width:0;display:flex;flex-direction:column;gap:2px;">

      <?php foreach ($cols_data[$ci] as $t) :
        $img = $t['img'];
      ?>
      <div class="mt-card-wrap" data-serie="<?php echo esc_attr($t['serie_id']); ?>" data-reveal>
        <a href="<?php echo esc_url($t['permalink']); ?>" class="block">
          <div class="mt-card mt-card--photo"
               style="aspect-ratio:<?php echo esc_attr($t['ar']); ?>;background-color:<?php echo esc_attr($t['tone']); ?>;min-height:150px">

            <?php if ($img) : ?>
              <img src="<?php echo esc_url($img['sizes']['large'] ?? $img['url']); ?>"
                   alt="<?php echo esc_attr($img['alt'] ?: $t['title_fr']); ?>"
                   loading="lazy" decoding="async"
                   class="absolute inset-0 w-full h-full object-cover grayscale">
            <?php else : ?>
              <div class="mt-card-ph-center">
                <span>
                  <span class="mt-fr">Photographie · N&B</span>
                  <span class="mt-en">Photograph · B&W</span>
                </span>
              </div>
            <?php endif; ?>

            <div class="mt-card-caption mt-card-caption--photo">
              <div class="mt-caption-photo-title">
                <span class="mt-fr"><?php echo esc_html($t['title_fr']); ?></span>
                <span class="mt-en"><?php echo esc_html($t['title_en']); ?></span>
              </div>
              <div class="mt-caption-meta">
                <span><?php echo esc_html($t['loc']); ?></span>
                <?php if ($t['loc'] && $t['date']) : ?>
                  <span class="mt-caption-meta-line"></span>
                <?php endif; ?>
                <span><?php echo esc_html($t['date']); ?></span>
              </div>
            </div>

          </div>
        </a>
      </div>
      <?php endforeach; ?>

    </div><!-- /.mt-col -->
    <?php endfor; ?>

  </div><!-- /#mt-tales-grid -->

</div>

<style>
.mt-serie-btn {
  display: block;
  width: 100%;
  text-align: left;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0 0 16px;
  font-family: 'EB Garamond', Garamond, serif;
  font-size: 10px;
  letter-spacing: 0.22em;
  text-transform: uppercase;
  color: rgba(236,234,228,0.38);
  transition: color .25s ease;
  line-height: 1.5;
}
.mt-serie-btn:hover          { color: rgba(236,234,228,0.7); }
.mt-serie-btn.active         { color: rgba(236,234,228,0.92); }
#mt-series-nav::-webkit-scrollbar { display: none; }
</style>

<script>
(function () {
  var btns  = document.querySelectorAll('.mt-serie-btn');
  var cols  = Array.from(document.querySelectorAll('#mt-tales-grid .mt-col'));
  var cards = Array.from(document.querySelectorAll('#mt-tales-grid .mt-card-wrap'));
  var current = 'all';
  var timer   = null;

  cards.forEach(function (c) {
    c.style.opacity   = '1';
    c.style.transform = 'translateY(0)';
  });

  function filter(serie) {
    if (serie === current) return;
    current = serie;
    if (timer) clearTimeout(timer);

    /* 1 — fondu sortant */
    var visible = cards.filter(function (c) { return c.style.display !== 'none'; });
    visible.forEach(function (c) {
      c.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
      c.style.opacity    = '0';
      c.style.transform  = 'translateY(8px)';
    });

    timer = setTimeout(function () {
      /* 2 — show / hide cards */
      var toShow = [];
      cards.forEach(function (c) {
        var show = serie === 'all' || c.dataset.serie === serie;
        if (show) {
          c.style.display    = '';
          c.style.transition = 'none';
          c.style.opacity    = '0';
          c.style.transform  = 'translateY(8px)';
          toShow.push(c);
        } else {
          c.style.display = 'none';
        }
      });

      /* hide columns that have no visible cards */
      cols.forEach(function (col) {
        var any = Array.from(col.querySelectorAll('.mt-card-wrap'))
                       .some(function (c) { return c.style.display !== 'none'; });
        col.style.display = any ? 'flex' : 'none';
      });

      /* 3 — fondu entrant échelonné */
      requestAnimationFrame(function () {
        requestAnimationFrame(function () {
          toShow.forEach(function (c, i) {
            var delay = Math.min(i * 18, 220);
            c.style.transition = 'opacity 0.5s ease ' + delay + 'ms, transform 0.5s ease ' + delay + 'ms';
            c.style.opacity    = '1';
            c.style.transform  = 'translateY(0)';
          });
        });
      });
    }, 380);
  }

  btns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      btns.forEach(function (b) { b.classList.remove('active'); });
      this.classList.add('active');
      filter(this.dataset.serie);
    });
  });
}());
</script>

<?php get_footer(); ?>
