<?php
// list/index.php - vertical list layout with thumbnail, title, meta and action
?>
<div class="ph-list">
<?php while ( have_posts() ) : the_post();
  $ver   = get_post_meta(get_the_ID(), 'ph_version', true);
  $count = ph_get_downloads(get_the_ID());
  $cats  = get_the_terms(get_the_ID(), 'plugin_category');
  ?>
  <article class="ph-item" id="post-<?php the_ID(); ?>">
    <div class="ph-item-main" style="display:flex;gap:12px;align-items:center;">
      <div class="ph-item-thumb" style="width:64px;flex:0 0 64px;">
        <?php if ( has_post_thumbnail() ) : ?>
          <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('ph-thumb'); ?></a>
        <?php else : ?>
          <div style="width:64px;height:44px;background:#f3f4f8;border-radius:6px;"></div>
        <?php endif; ?>
      </div>

      <div style="min-width:0;">
        <h3 class="ph-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <div class="ph-meta">
          <?php if ( $cats && ! is_wp_error( $cats ) ) : ?>
            <?php foreach ( $cats as $c ) : ?>
              <a class="ph-meta-chip ph-cat-chip" href="<?php echo esc_url( get_term_link($c) ); ?>"><?php echo esc_html( $c->name ); ?></a>
            <?php endforeach; ?>
          <?php endif; ?>
          <span class="ph-meta-text"><?php echo esc_html( $ver ?: '' ); ?></span>
          <span class="ph-meta-text"><?php echo esc_html( $count ); ?> <?php esc_html_e('downloads','pluginhub-pro'); ?></span>
        </div>
      </div>
    </div>

    <div class="ph-item-side">
      <a class="ph-btn" href="<?php the_permalink(); ?>"><?php esc_html_e('Open','pluginhub-pro'); ?></a>
    </div>
  </article>
<?php endwhile; ?>
</div>