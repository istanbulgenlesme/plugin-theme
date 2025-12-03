<?php
function pluginhub_get_layout() {
    return get_option('pluginhub_layout', 'list');
}
function pluginhub_get_single_layout() {
    return get_option('pluginhub_single_layout', 'tabs');
}
function ph_get_accent() {
    $accent = get_option('pluginhub_accent', '#0066ff');
    if (! $accent) {
        $accent = '#0066ff';
    }
    return $accent;
}
function ph_get_downloads($post_id = null) {
    $post_id = $post_id ?: get_the_ID();
    return (int) get_post_meta($post_id, 'ph_downloads', true);
}

/**
 * Generate a signed download URL that is valid until $expiry_seconds (defaults to 3600 => 1 hour)
 * Returned URL points to the ph_download_file endpoint which validates signature before redirecting.
 */
function ph_generate_signed_download_url($post_id, $expiry_seconds = 3600) {
    $expiry = time() + (int) $expiry_seconds;
    $data = $post_id . '|' . $expiry;
    $secret = wp_salt(); // uses WP salts
    $hash = hash_hmac('sha256', $data, $secret);
    $args = array(
        'ph_download_file' => $post_id,
        'ts' => $expiry,
        'h' => $hash,
    );
    return esc_url_raw(add_query_arg($args, home_url('/')));
}

/**
 * Validate signed download.
 * Returns true if valid and not expired.
 */
function ph_validate_signed_download($post_id, $ts, $hash) {
    $post_id = (int) $post_id;
    $ts = (int) $ts;
    if (! $post_id || ! $ts || empty($hash)) {
        return false;
    }
    if ($ts < time()) {
        return false; // expired
    }
    $data = $post_id . '|' . $ts;
    $secret = wp_salt();
    $expected = hash_hmac('sha256', $data, $secret);
    // use hash_equals when available
    if ( function_exists('hash_equals') ) {
        return hash_equals($expected, $hash);
    }
    return $expected === $hash;
}