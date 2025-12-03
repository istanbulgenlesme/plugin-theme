<?php
$layout = isset($args['layout']) ? $args['layout'] : pluginhub_get_layout();
$terms  = get_terms(
    array(
        'taxonomy'   => 'plugin_category',
        'hide_empty' => false,
    )
);
?>
<section class="ph-archive-header">
  <div class="ph-archive-title-wrap">
    <h2 class="ph-archive-title">Plugins</h2>
    <p class="ph-archive-sub">Browse, search and filter.</p>
  </div>
  <form class="ph-filter-bar" id="ph-filter-form">
    <input type="text" id="ph-search-input" placeholder="Search plugins..." />
    <select id="ph-category-filter">
      <option value=""><?php esc_html_e('All Categories', 'pluginhub-pro'); ?></option>
      <?php foreach ($terms as $term) : ?>
        <option value="<?php echo esc_attr($term->slug); ?>"><?php echo esc_html($term->name); ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</section>

<div id="ph-results">
  <?php
  get_template_part('templates/' . $layout . '/index');
  ?>
</div>
