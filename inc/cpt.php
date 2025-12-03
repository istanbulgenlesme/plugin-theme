<?php
/**
 * Register Plugins CPT and taxonomies, and provide the Plugin Details meta box
 */

add_action('init', function () {
    register_post_type(
        'plugins',
        array(
            'label'         => __('Plugins', 'pluginhub-pro'),
            'public'        => true,
            'show_in_rest'  => true,
            'supports'      => array('title', 'editor', 'thumbnail', 'excerpt'),
            'menu_icon'     => 'dashicons-admin-plugins',
            'rewrite'       => array('slug' => 'plugins'),
            'has_archive'   => true,
        )
    );

    register_taxonomy(
        'plugin_category',
        'plugins',
        array(
            'label'        => __('Plugin Categories', 'pluginhub-pro'),
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite'      => array('slug' => 'plugin-category'),
        )
    );

    register_taxonomy(
        'plugin_tag',
        'plugins',
        array(
            'label'        => __('Plugin Tags', 'pluginhub-pro'),
            'hierarchical' => false,
            'show_in_rest' => true,
            'rewrite'      => array('slug' => 'plugin-tag'),
        )
    );
});

/**
 * Add meta box only for 'plugins' CPT
 */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'ph_plugin_meta',
        __('Plugin Details', 'pluginhub-pro'),
        'ph_plugin_meta_cb',
        'plugins',
        'side',
        'high'
    );
});

/**
 * Meta box callback: renders the Plugin Details UI in admin.
 * Ensure this function exists (fixes fatal error when WP tries to call it).
 */
if ( ! function_exists('ph_plugin_meta_cb') ) {
    function ph_plugin_meta_cb($post) {
        // Defensive: only for plugins CPT
        if ( get_post_type($post) !== 'plugins' ) {
            echo '<p>' . esc_html__('This panel is for Plugin posts only.', 'pluginhub-pro') . '</p>';
            return;
        }

        $version   = get_post_meta($post->ID, 'ph_version', true);
        $download  = get_post_meta($post->ID, 'ph_download', true);
        $changes   = get_post_meta($post->ID, 'ph_changelog', true);
        $screens   = get_post_meta($post->ID, 'ph_screens', true);
        $downloads = (int) get_post_meta($post->ID, 'ph_downloads', true);

        wp_nonce_field('ph_plugin_meta', 'ph_plugin_meta_nonce');

        $shots = array_filter(array_map('trim', explode(',', $screens)));
        ?>
        <div class="ph-meta-panel">
          <div class="ph-meta-row">
            <label class="ph-meta-label"><?php esc_html_e('Version', 'pluginhub-pro'); ?></label>
            <input class="ph-meta-input" name="ph_version" value="<?php echo esc_attr($version); ?>" />
          </div>

          <div class="ph-meta-row">
            <label class="ph-meta-label"><?php esc_html_e('Download File', 'pluginhub-pro'); ?></label>
            <input type="hidden" id="ph-download-field" name="ph_download" value="<?php echo esc_attr($download); ?>">
            <div class="ph-download-row">
              <span id="ph-download-filename" data-no-file="<?php esc_attr_e('No file selected', 'pluginhub-pro'); ?>">
                <?php echo $download ? esc_html(basename($download)) : esc_html__('No file selected', 'pluginhub-pro'); ?>
              </span>
              <div class="ph-download-actions">
                <button type="button" class="button button-primary" id="ph-upload-download-btn"><?php esc_html_e('Select / Upload', 'pluginhub-pro'); ?></button>
                <button type="button" class="button" id="ph-remove-download-btn"><?php esc_html_e('Remove', 'pluginhub-pro'); ?></button>
              </div>
            </div>
          </div>

          <div class="ph-meta-row">
            <label class="ph-meta-label"><?php esc_html_e('Screenshots (Gallery)', 'pluginhub-pro'); ?></label>
            <input type="hidden" id="ph-screens-field" name="ph_screens" value="<?php echo esc_attr($screens); ?>">
            <div class="ph-gallery-actions" style="margin-bottom:6px;">
              <button type="button" class="button" id="ph-upload-gallery-btn"><?php esc_html_e('Edit Gallery', 'pluginhub-pro'); ?></button>
              <button type="button" class="button" id="ph-clear-gallery-btn"><?php esc_html_e('Clear', 'pluginhub-pro'); ?></button>
            </div>
            <div id="ph-screens-preview" class="ph-screens-preview">
              <?php foreach ($shots as $img) : if (! $img) continue; ?>
                <div class="ph-screen-thumb" data-url="<?php echo esc_attr($img); ?>">
                  <img src="<?php echo esc_url($img); ?>" alt="">
                  <button type="button" class="button ph-remove-screen-btn" aria-label="<?php esc_attr_e('Remove screenshot', 'pluginhub-pro'); ?>">&times;</button>
                </div>
              <?php endforeach; ?>
            </div>
            <p class="description" style="margin-top:6px;"><?php esc_html_e('Use the gallery uploader to select multiple screenshots. URLs are stored automatically.', 'pluginhub-pro'); ?></p>
          </div>

          <div class="ph-meta-row">
            <label class="ph-meta-label"><?php esc_html_e('Changelog', 'pluginhub-pro'); ?></label>
            <textarea class="ph-meta-textarea" name="ph_changelog" rows="6"><?php echo esc_textarea($changes); ?></textarea>
          </div>

          <div class="ph-meta-row ph-meta-footer">
            <div class="ph-download-count"><?php printf(esc_html__('Downloads: %d', 'pluginhub-pro'), $downloads); ?></div>
          </div>
        </div>
        <?php
    }
}

/**
 * Save post meta for plugins CPT (nonce, capability and sanitization checks)
 */
add_action('save_post', function ($post_id) {
    if ( get_post_type($post_id) !== 'plugins' ) {
        return;
    }
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! isset($_POST['ph_plugin_meta_nonce']) || ! wp_verify_nonce($_POST['ph_plugin_meta_nonce'], 'ph_plugin_meta') ) {
        return;
    }
    if ( ! current_user_can('edit_post', $post_id) ) {
        return;
    }

    if (isset($_POST['ph_version'])) {
        update_post_meta($post_id, 'ph_version', sanitize_text_field(wp_unslash($_POST['ph_version'])));
    }
    if (isset($_POST['ph_download'])) {
        update_post_meta($post_id, 'ph_download', esc_url_raw(wp_unslash($_POST['ph_download'])));
    }
    if (isset($_POST['ph_changelog'])) {
        update_post_meta($post_id, 'ph_changelog', sanitize_textarea_field(wp_unslash($_POST['ph_changelog'])));
    }
    if (isset($_POST['ph_screens'])) {
        $raw = wp_unslash($_POST['ph_screens']);
        $parts = array_filter(array_map('trim', explode(',', $raw)));
        $clean = array();
        foreach ($parts as $p) {
            $p = esc_url_raw($p);
            if ($p) {
                $clean[] = $p;
            }
        }
        update_post_meta($post_id, 'ph_screens', implode(',', $clean));
    }
}, 10, 1);