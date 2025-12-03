<?php
// sidebar.php - Single plugin with sidebar layout (featured image + categories + tags)
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
$terms   = get_the_terms(get_the_ID(), 'plugin_category');
$tags    = get_the_terms(get_the_ID(), 'plugin_tag');
?>
<article class="ph-single ph-single-sidebar">
  <div class="ph-single-layout">
    <aside class="ph-single-aside">
      <?php if ( has_post_thumbnail() ) : ?>
        <div class="ph-aside-thumb">
          <?php echo get_the_post_thumbnail(get_the_ID(), 'ph-thumb', array('alt' => get_the_title())); ?>
        </div>
      <?php endif; ?>

      <h1 class="ph-single-title"><?php echo esc_html( get_the_title() ); ?></h1>
      <div class="ph-single-meta">
        <span class="ph-meta-chip"><?php echo esc_html( $ver ?: '1.0' ); ?></span>
        <span class="ph-meta-text"><?php echo esc_html( $count ); ?> <?php esc_html_e('downloads', 'pluginhub-pro'); ?></span>
      </div>

      <?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
        <div class="ph-tags">
          <?php foreach ( $terms as $t ) : ?>
            <span class="ph-tag"><?php echo esc_html( $t->name ); ?></span>
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

      <?php if ( $dl ) : ?>
        <a class="ph-btn-download ph-btn-full" href="<?php echo esc_url( add_query_arg( 'ph_download_start', get_the_ID(), home_url( '/' ) ) ); ?>">
          <?php esc_html_e( 'Download', 'pluginhub-pro' ); ?>
        </a>
      <?php endif; ?>
    </aside>

    <div class="ph-single-main">
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
    </div>
  </div>
</article>