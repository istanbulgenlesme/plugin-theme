<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php
// Provide wp_body_open for plugins/analytics that hook into it (WP 5.2+).
if ( function_exists( 'wp_body_open' ) ) {
    wp_body_open();
}
?>
<header class="ph-header">
  <div class="ph-container ph-header-inner">
    <div class="ph-logo-wrap">
      <span class="ph-logo-icon" aria-hidden="true"></span>
      <span class="ph-logo-text"><?php echo esc_html_x( 'PluginHub', 'site title', 'pluginhub-pro' ); ?></span>
    </div>
    <nav class="ph-nav" aria-label="<?php echo esc_attr_x( 'Primary', 'nav aria label', 'pluginhub-pro' ); ?>">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'pluginhub-pro' ); ?></a>
      <a href="<?php echo esc_url( get_post_type_archive_link( 'plugins' ) ); ?>"><?php esc_html_e( 'Plugins', 'pluginhub-pro' ); ?></a>
    </nav>
  </div>
</header>
<main class="ph-main">
  <div class="ph-container">
