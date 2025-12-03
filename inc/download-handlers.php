<?php
/**
 * Download handlers: signing, start/countdown and robust proxy serving.
 */

if ( ! function_exists('ph_generate_signed_download_url') ) {
    function ph_generate_signed_download_url($post_id, $expiry_seconds = 3600) {
        $expiry = time() + (int) $expiry_seconds;
        $data = $post_id . '|' . $expiry;
        $secret = wp_salt();
        $hash = hash_hmac('sha256', $data, $secret);
        $args = array(
            'ph_download_file' => $post_id,
            'ts' => $expiry,
            'h' => $hash,
        );
        return esc_url_raw(add_query_arg($args, home_url('/')));
    }
}

if ( ! function_exists('ph_validate_signed_download') ) {
    function ph_validate_signed_download($post_id, $ts, $hash) {
        $post_id = (int) $post_id;
        $ts = (int) $ts;
        if (! $post_id || ! $ts || empty($hash)) {
            return false;
        }
        if ($ts < time()) {
            return false;
        }
        $data = $post_id . '|' . $ts;
        $secret = wp_salt();
        $expected = hash_hmac('sha256', $data, $secret);
        if ( function_exists('hash_equals') ) {
            return hash_equals($expected, $hash);
        }
        return $expected === $hash;
    }
}

/**
 * AJAX move (kept as before). Ensure it updates ph_plugins_download_url and GUID.
 */
add_action('wp_ajax_ph_move_attachment_to_plugins_download', function () {
    if ( ! isset($_POST['_ajax_nonce']) || ! wp_verify_nonce($_POST['_ajax_nonce'], 'ph_admin_nonce') ) {
        wp_send_json_error(array('message' => 'nonce'));
    }

    if ( ! current_user_can('upload_files') ) {
        wp_send_json_error(array('message' => 'cap'));
    }

    $att_id = isset($_POST['attachment_id']) ? absint($_POST['attachment_id']) : 0;
    if ( ! $att_id ) {
        wp_send_json_error(array('message' => 'no_att'));
    }

    $file_path = get_attached_file($att_id);
    if ( ! $file_path || ! file_exists($file_path) ) {
        wp_send_json_error(array('message' => 'missing_file'));
    }

    $target_dir = WP_CONTENT_DIR . '/plugins-download';
    if ( ! file_exists($target_dir) ) {
        if ( ! wp_mkdir_p($target_dir) ) {
            wp_send_json_error(array('message' => 'mkdir_failed'));
        }
    }

    $orig_basename = wp_basename($file_path);
    $ext = pathinfo($orig_basename, PATHINFO_EXTENSION);
    $base = sanitize_file_name( pathinfo($orig_basename, PATHINFO_FILENAME) );
    $new_basename = $base . '-' . $att_id . '-' . time() . ($ext ? '.' . $ext : '');
    $new_path = trailingslashit($target_dir) . $new_basename;

    $moved = false;
    if ( @rename($file_path, $new_path) ) {
        $moved = true;
    } else {
        if ( @copy($file_path, $new_path) ) {
            @unlink($file_path);
            $moved = true;
        }
    }

    if ( ! $moved || ! file_exists($new_path) ) {
        wp_send_json_error(array('message' => 'move_failed'));
    }

    @chmod($new_path, 0644);

    $new_url = content_url('/plugins-download/' . $new_basename);

    // update attachment markers & GUID
    update_post_meta($att_id, 'ph_moved_to_plugins_download', 1);
    update_post_meta($att_id, 'ph_plugins_download_url', $new_url);
    wp_update_post( array( 'ID' => $att_id, 'guid' => $new_url ) );

    wp_send_json_success(array('url' => $new_url, 'basename' => $new_basename));
});

/**
 * Countdown/start page (unchanged; ensure it exists).
 * ... (same as previous implementation) ...
 * (You probably already have this; keep it)
 */

/**
 * Robust proxy: map stored_url to a filesystem path reliably.
 */
add_action('template_redirect', function () {
    if ( ! isset($_GET['ph_download_file']) ) {
        return;
    }
    $id = absint($_GET['ph_download_file']);
    $ts = isset($_GET['ts']) ? intval($_GET['ts']) : 0;
    $h  = isset($_GET['h']) ? wp_unslash($_GET['h']) : '';

    if ( ! $id || ! $ts || ! $h ) {
        wp_die(esc_html__('Invalid download link.', 'pluginhub-pro'));
    }

    if ( ! ph_validate_signed_download($id, $ts, $h) ) {
        wp_die(esc_html__('Download link is invalid or expired.', 'pluginhub-pro'));
    }

    $stored_url = get_post_meta($id, 'ph_download', true);

    if ( ! $stored_url ) {
        $attachment_url = get_post_meta($id, 'ph_plugins_download_url', true);
        if ( $attachment_url ) {
            $stored_url = $attachment_url;
        } else {
            wp_die(esc_html__('No download found.', 'pluginhub-pro'));
        }
    }

    // Convert URL to server file path robustly.
    $parsed_path = wp_parse_url( $stored_url, PHP_URL_PATH );
    if ( ! $parsed_path ) {
        wp_die(esc_html__('Invalid stored URL.', 'pluginhub-pro'));
    }

    // Look for /wp-content/ in the path and build a WP_CONTENT_DIR path
    $pos = strpos( $parsed_path, '/wp-content/' );
    if ( $pos !== false ) {
        // relative inside wp-content
        $relative = substr( $parsed_path, $pos + strlen('/wp-content/') );
        $file_path = WP_CONTENT_DIR . '/' . $relative;
    } else {
        // Fall back to ABSPATH-based mapping (if site in subdir)
        $docroot_path = ABSPATH . ltrim( $parsed_path, '/' );
        $file_path = $docroot_path;
    }

    if ( ! file_exists( $file_path ) ) {
        wp_die(esc_html__('File not found on server.', 'pluginhub-pro'));
    }

    // increment counter
    $count = (int) get_post_meta($id, 'ph_downloads', true);
    update_post_meta($id, 'ph_downloads', $count + 1);

    nocache_headers();
    $finfo = wp_check_filetype_and_ext($file_path, $file_path);
    $mime = $finfo['type'] ? $finfo['type'] : 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . rawurldecode(basename($file_path)) . '"');
    header('Content-Length: ' . filesize($file_path));
    @flush();
    readfile($file_path);
    exit;
});