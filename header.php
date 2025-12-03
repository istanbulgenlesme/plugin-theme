<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<header class="ph-header">
  <div class="ph-container ph-header-inner">
    <div class="ph-logo-wrap">
      <span class="ph-logo-icon"></span>
      <span class="ph-logo-text">PluginHub</span>
    </div>
    <nav class="ph-nav">
      <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
      <a href="<?php echo esc_url(get_post_type_archive_link('plugins')); ?>">Plugins</a>
    </nav>
  </div>
</header>
<main class="ph-main">
  <div class="ph-container">
