<?php
// Table view with thumbnail column
?>
<table class="ph-table">
  <thead>
    <tr>
      <th></th>
      <th><?php esc_html_e('Plugin', 'pluginhub-pro'); ?></th>
      <th><?php esc_html_e('Version', 'pluginhub-pro'); ?></th>
      <th><?php esc_html_e('Downloads', 'pluginhub-pro'); ?></th>
      <th><?php esc_html_e('Action', 'pluginhub-pro'); ?></th>
    </tr>
  </thead>
  <tbody>
<?php while (have_posts()) : the_post();
  $ver   = get_post_meta(get_the_ID(), 'ph_version', true);
  $count = ph_get_downloads(get_the_ID());
  ?>
    <tr>
      <td class="ph-table-thumb">
        <?php if ( has_post_thumbnail() ) : ?>
          <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('ph-thumb'); ?></a>
        <?php else : ?>
          <div style="width:56px;height:36px;background:#f3f4f8;border-radius:6px;"></div>
        <?php endif; ?>
      </td>
      <td><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
      <td><?php echo esc_html($ver ?: ''); ?></td>
      <td><?php echo esc_html($count); ?></td>
      <td><a class="ph-btn" href="<?php the_permalink(); ?>">Open</a></td>
    </tr>
<?php endwhile; ?>
  </tbody>
</table>