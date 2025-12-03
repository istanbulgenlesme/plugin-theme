<?php
// flow.php - Single plugin flow layout (featured image + categories + tags)
if ( ! have_posts() ) {
    return;
}
the_post();

$ver     = get_post_meta(get_the_ID(), 'ph_version', true);
$dl      = get_post_meta(get_the_ID(), 'ph_download', true);
$changes = get_post_meta(get_the_ID(), 'ph_changelog', true);
$screens = get_post_meta(get_the_ID(), 'ph_screens', true);
$shots   = array_filter(array_map('trim', explode(',', $screens)));
$count   = ph_get_downloads(get_the_ID());
$tags    = get_the_terms(get_the_ID(), 'plugin_tag');
$cats    = get_the_terms(get_the_ID(), 'plugin_category');
?>
<article class="ph-single ph-single-flow">
  <?php if ( has_post_thumbnail() ) : ?>
    <div class="ph-single-hero">
      <?php echo get_the_post_thumbnail(get_the_ID(), 'ph-single', array('class' => 'ph-single-hero-img', 'alt' => get_the_title())); ?>
    </div>
  <?php endif; ?>

  <header class="ph-single-header">
    <h1 class="ph-single-title"><?php echo esc_html( get_the_title() ); ?></h1>
    <div class="ph-single-meta">
      <span class="ph-meta-chip"><?php echo esc_html( $ver ?: '1.0' ); ?></span>
      <span class="ph-meta-text"><?php echo esc_html( $count ); ?> <?php esc_html_e('downloads', 'pluginhub-pro'); ?></span>
    </div>

    <?php if ( $dl ) : ?>
      <a class="ph-btn-download" href="<?php echo esc_url( add_query_arg( 'ph_download_start', get_the_ID(), home_url( '/' ) ) ); ?>">
        <?php esc_html_e( 'Download', 'pluginhub-pro' ); ?>
      </a>
    <?php endif; ?>
  </header>

  <?php if ( $cats && ! is_wp_error( $cats ) ) : ?>
    <div class="ph-categories" style="margin-top:8px;">
      <?php foreach ( $cats as $c ) : ?>
        <a class="ph-meta-chip ph-cat-chip" href="<?php echo esc_url( get_term_link($c) ); ?>"><?php echo esc_html( $c->name ); ?></a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ( $tags && ! is_wp_error( $tags ) ) : ?>
    <div class="ph-tags" style="margin-top:8px;">
      <?php foreach ( $tags as $t ) : ?>
        <a class="ph-tag" href="<?php echo esc_url( get_term_link($t) ); ?>"><?php echo esc_html( $t->name ); ?></a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <section class="ph-block">
    <h2 class="ph-block-title"><?php esc_html_e('Description', 'pluginhub-pro'); ?></h2>
    <div class="ph-block-body">
      <?php the_content(); ?>
    </div>
  </section>

  <?php if ( $shots ) : ?>
  <section class="ph-block">
    <h2 class="ph-block-title"><?php esc_html_e('Screenshots', 'pluginhub-pro'); ?></h2>
    <div class="ph-screens-grid">
      <?php foreach ( $shots as $img ) : if ( ! $img ) continue; ?>
        <figure class="ph-screen-wrap">
          <img class="ph-screen" src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( get_the_title() . ' screenshot' ); ?>">
        </figure>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( $changes ) : ?>
  <section class="ph-block">
    <h2 class="ph-block-title"><?php esc_html_e('Changelog', 'pluginhub-pro'); ?></h2>
    <pre class="ph-changelog"><?php echo esc_html( $changes ); ?></pre>
  </section>
  <?php endif; ?>
</article>