<?php
get_header();
$layout = pluginhub_get_layout();
?>
<section class="ph-hero">
  <h1 class="ph-hero-title">Discover WordPress Plugins</h1>
  <p class="ph-hero-sub">Minimal, clean directory theme for your extensions.</p>
</section>

<?php get_template_part('templates/archive', null, array('layout' => $layout)); ?>

<?php
get_footer();
