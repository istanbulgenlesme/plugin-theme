<table class="ph-table">
  <thead>
    <tr>
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
      <td><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
      <td><?php echo esc_html($ver ?: ''); ?></td>
      <td><?php echo esc_html($count); ?></td>
      <td><a class="ph-btn" href="<?php the_permalink(); ?>">Open</a></td>
    </tr>
<?php endwhile; ?>
  </tbody>
</table>
