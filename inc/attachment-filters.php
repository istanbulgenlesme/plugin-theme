<?php
/**
 * Ensure media library / wp_get_attachment_url shows the plugins-download URL
 * if we moved that attachment there.
 */
add_filter('wp_get_attachment_url', function($url, $post_id) {
    $moved = get_post_meta($post_id, 'ph_moved_to_plugins_download', true);
    if ( $moved ) {
        $custom = get_post_meta($post_id, 'ph_plugins_download_url', true);
        if ( $custom ) {
            return esc_url_raw($custom);
        }
    }
    return $url;
}, 10, 2);

// Also show proper file link in admin list if possible (guid was updated earlier).