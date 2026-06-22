</main><!-- #mt-main -->

<footer class="mt-footer">
  <div class="flex justify-between items-end gap-8 flex-wrap max-w-[1300px] mx-auto">
    <div>
      <a href="<?php echo esc_url(home_url('/')); ?>" class="mt-footer-logo">MONOTALES</a>
      <p class="font-body italic text-warm mt-2.5" style="font-size:15px">
        <span class="mt-fr">Une photo. Un lieu. Un texte.</span>
        <span class="mt-en">One photo. One place. One text.</span>
      </p>
    </div>
    <div class="flex flex-wrap font-sans uppercase" style="gap:clamp(16px,2.4vw,30px);font-size:11px;letter-spacing:0.18em">
      <a href="#" class="text-muted hover:text-paper transition-colors duration-300">Instagram</a>
      <a href="#" class="text-muted hover:text-paper transition-colors duration-300">Facebook</a>
      <a href="#" class="text-muted hover:text-paper transition-colors duration-300">Pinterest</a>
      <a href="<?php echo esc_url(get_feed_link()); ?>" class="text-muted hover:text-paper transition-colors duration-300" title="Flux RSS">RSS</a>
    </div>
  </div>
  <p class="font-sans uppercase text-ash max-w-[1300px] mx-auto" style="font-size:10px;letter-spacing:0.16em;margin-top:clamp(30px,5vh,46px)">
    <span class="mt-fr">© <?php echo date('Y'); ?> MONOTALES — Photographies en noir et blanc. Français &amp; English.</span>
    <span class="mt-en">© <?php echo date('Y'); ?> MONOTALES — Photographs in black and white. Français &amp; English.</span>
  </p>
</footer>

<?php wp_footer(); ?>
</body>
</html>
