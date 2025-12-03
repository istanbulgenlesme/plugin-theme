<?php
/**
 * Theme settings page
 */
add_action('admin_menu', function () {
    add_theme_page(
        __('PluginHub Settings', 'pluginhub-pro'),
        __('PluginHub Settings', 'pluginhub-pro'),
        'manage_options',
        'pluginhub-settings',
        'pluginhub_settings_page'
    );
});

function pluginhub_settings_page() {
    if (isset($_POST['ph_settings_nonce']) && wp_verify_nonce($_POST['ph_settings_nonce'], 'ph_save_settings')) {
        // validate allowed choices
        $allowed_layouts = array('list', 'card', 'table');
        $allowed_single = array('tabs', 'flow', 'sidebar');

        if (isset($_POST['pluginhub_layout'])) {
            $value = sanitize_text_field($_POST['pluginhub_layout']);
            if ( in_array($value, $allowed_layouts, true) ) {
                update_option('pluginhub_layout', $value);
            }
        }
        if (isset($_POST['pluginhub_single_layout'])) {
            $value = sanitize_text_field($_POST['pluginhub_single_layout']);
            if ( in_array($value, $allowed_single, true) ) {
                update_option('pluginhub_single_layout', $value);
            }
        }
        if (isset($_POST['pluginhub_accent'])) {
            $color = sanitize_hex_color($_POST['pluginhub_accent']);
            update_option('pluginhub_accent', $color ?: '#0066ff');
        }
        echo '<div class="updated"><p>' . esc_html__('Settings saved.', 'pluginhub-pro') . '</p></div>';
    }

    $layout = get_option('pluginhub_layout', 'list');
    $single = get_option('pluginhub_single_layout', 'tabs');
    $accent = ph_get_accent();
    ?>
    <div class="wrap">
      <h1><?php esc_html_e('PluginHub Settings', 'pluginhub-pro'); ?></h1>
      <form method="post">
        <?php wp_nonce_field('ph_save_settings', 'ph_settings_nonce'); ?>

        <h2><?php esc_html_e('Archive Layout', 'pluginhub-pro'); ?></h2>
        <select name="pluginhub_layout">
          <option value="list" <?php selected($layout, 'list'); ?>>List</option>
          <option value="card" <?php selected($layout, 'card'); ?>>Card</option>
          <option value="table" <?php selected($layout, 'table'); ?>>Table</option>
        </select>

        <h2 style="margin-top:20px;"><?php esc_html_e('Single Plugin Layout', 'pluginhub-pro'); ?></h2>
        <select name="pluginhub_single_layout">
          <option value="tabs" <?php selected($single, 'tabs'); ?>>Tabs</option>
          <option value="flow" <?php selected($single, 'flow'); ?>>Flow</option>
          <option value="sidebar" <?php selected($single, 'sidebar'); ?>>Sidebar</option>
        </select>

        <h2 style="margin-top:20px;"><?php esc_html_e('Accent Color', 'pluginhub-pro'); ?></h2>
        <input type="text" name="pluginhub_accent" value="<?php echo esc_attr($accent); ?>" />
        <p class="description"><?php esc_html_e('Use any valid hex color, e.g., #0066ff', 'pluginhub-pro'); ?></p>

        <p><button class="button button-primary" type="submit"><?php esc_html_e('Save Changes', 'pluginhub-pro'); ?></button></p>
      </form>
    </div>
    <?php
}