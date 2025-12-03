<?php
// card/index.php - grid of cards with featured image, title, desc, meta and actions
?>
<div class="ph-card-grid">
<?php while ( have_posts() ) : the_post();
  $ver   = get_post_meta(get_the_ID(), 'ph_version', true);
  $count = ph_get_downloads(get_the_ID());
  $cats  = get_the_terms(get_the_ID(), 'plugin_category');
  ?>
  <article class="ph-card" id="post-<?php the_ID(); ?>">
    <?php if ( has_post_thumbnail() ) : ?>
      <a href="<?php the_permalink(); ?>" class="ph-card-thumb">
        <?php the_post_thumbnail('ph-single', array('class' => 'ph-card-image', 'alt' => get_the_title())); ?>
      </a>
    <?php endif; ?>

    <div>
      <h3 class="ph-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
      <p class="ph-desc"><?php echo wp_trim_words( get_the_excerpt() ?: get_the_content(), 20 ); ?></p>
    </div>

    <div class="ph-meta" style="margin-top:auto;display:flex;align-items:center;justify-content:space-between;">
      <div>
        <?php if ( $cats && ! is_wp_error( $cats ) ) : ?>
          <?php foreach ( $cats as $c ) : ?>
            <a class="ph-meta-chip ph-cat-chip" href="<?php echo esc_url( get_term_link($c) ); ?>"><?php echo esc_html( $c->name ); ?></a>
          <?php endforeach; ?>
        <?php endif; ?>
        <span class="ph-meta-text"><?php echo esc_html( $ver ?: '' ); ?></span>
      </div>

      <div style="display:flex;gap:8px;align-items:center;">
        <span class="ph-meta-text"><?php echo esc_html( $count ); ?></span>
        <a class="ph-btn" href="<?php the_permalink(); ?>"><?php esc_html_e('Open','pluginhub-pro'); ?></a>
      </div>
    </div>
  </article>
<?php endwhile; ?>
</div>