<?php
/**
 * Move newly added attachments into wp-content/plugins-download
 * when upload originates from Plugin post editor. Update attachment meta/guid.
 */

add_action('add_attachment', function( $attachment_id ) {
    if ( ! is_admin() ) {
        return;
    }

    $file = get_attached_file( $attachment_id );
    if ( ! $file || ! file_exists( $file ) ) {
        return;
    }

    $referer = '';
    if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
        $referer = wp_unslash( $_SERVER['HTTP_REFERER'] );
    } else {
        $referer = wp_get_referer() ?: '';
    }

    if ( ! $referer ) {
        return;
    }

    $u = wp_parse_url( $referer );
    parse_str( isset( $u['query'] ) ? $u['query'] : '', $q );
    $post_id = isset( $q['post'] ) ? absint( $q['post'] ) : 0;

    $is_plugins_editor = false;
    if ( $post_id ) {
        $ptype = get_post_type( $post_id );
        if ( $ptype === 'plugins' ) {
            $is_plugins_editor = true;
        }
    } else {
        if ( isset( $q['post_type'] ) && $q['post_type'] === 'plugins' ) {
            $is_plugins_editor = true;
        }
    }

    if ( ! $is_plugins_editor ) {
        return;
    }

    $target_dir = WP_CONTENT_DIR . '/plugins-download';
    if ( ! file_exists( $target_dir ) ) {
        if ( ! wp_mkdir_p( $target_dir ) ) {
            error_log( 'PluginHub: could not create plugins-download dir: ' . $target_dir );
            return;
        }
    }

    $orig_basename = wp_basename( $file );
    $ext = pathinfo( $orig_basename, PATHINFO_EXTENSION );
    $base = sanitize_file_name( pathinfo( $orig_basename, PATHINFO_FILENAME ) );
    $new_basename = $base . '-' . $attachment_id . '-' . time() . ($ext ? '.' . $ext : '');
    $new_path = trailingslashit( $target_dir ) . $new_basename;

    $moved = false;
    if ( @rename( $file, $new_path ) ) {
        $moved = true;
    } else {
        if ( @copy( $file, $new_path ) ) {
            @unlink( $file );
            $moved = true;
        }
    }

    if ( $moved && file_exists( $new_path ) ) {
        @chmod( $new_path, 0644 );

        $new_url = content_url( '/plugins-download/' . $new_basename );

        // Store marker & public URL on attachment
        update_post_meta( $attachment_id, 'ph_moved_to_plugins_download', 1 );
        update_post_meta( $attachment_id, 'ph_plugins_download_url', $new_url );

        // Update GUID so admin list shows better reference (helpful)
        wp_update_post( array(
            'ID'  => $attachment_id,
            'guid' => $new_url,
        ) );

        // If we have post_id in referer, update that plugin post's ph_download meta
        if ( $post_id ) {
            update_post_meta( $post_id, 'ph_download', $new_url );
        }
    } else {
        error_log( 'PluginHub: failed to move attachment to plugins-download for attachment ' . $attachment_id );
    }
});