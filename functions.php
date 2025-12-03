<?php
require_once get_template_directory() . '/inc/cpt.php';
require_once get_template_directory() . '/inc/settings.php';
require_once get_template_directory() . '/inc/views.php';
require_once get_template_directory() . '/inc/download-handlers.php';
require_once get_template_directory() . '/inc/move-on-add.php';
require_once get_template_directory() . '/inc/attachment-filters.php';

/**
 * Theme setup: add basic supports
 */
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_image_size('ph-thumb', 140, 90, true);      // archive / table thumbnail
    add_image_size('ph-single', 900, 400, false);   // hero single image (not cropped)
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
});

/**
 * Enqueue frontend assets
 */
function ph_enqueue_assets() {
    $style_path = get_template_directory() . '/assets/css/style.css';
    $script_path = get_template_directory() . '/assets/js/main.js';
    $ver = file_exists($style_path) ? filemtime($style_path) : wp_get_theme()->get('Version');

    wp_enqueue_style(
        'pluginhub-style',
        get_template_directory_uri() . '/assets/css/style.css',
        array(),
        $ver
    );
    wp_enqueue_script(
        'pluginhub-main',
        get_template_directory_uri() . '/assets/js/main.js',
        array(),
        file_exists($script_path) ? filemtime($script_path) : $ver,
        true
    );
    wp_localize_script(
        'pluginhub-main',
        'PluginHub',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'accent'   => ph_get_accent(),
        )
    );
}
add_action('wp_enqueue_scripts', 'ph_enqueue_assets');

/**
 * Admin assets (media uploader + admin UI CSS/JS) - only on plugins CPT edit screens
 */
function ph_admin_assets($hook) {
    $screen = get_current_screen();
    if ( ! $screen || $screen->post_type !== 'plugins' ) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_style('pluginhub-admin', get_template_directory_uri() . '/assets/css/admin.css', array(), filemtime(get_template_directory() . '/assets/css/admin.css'));
    wp_enqueue_script('pluginhub-admin', get_template_directory_uri() . '/assets/js/admin.js', array('jquery'), filemtime(get_template_directory() . '/assets/js/admin.js'), true);

    wp_localize_script('pluginhub-admin', 'PluginHub', array(
        'i18n' => array(
            'select_file'    => __('Select or upload file', 'pluginhub-pro'),
            'use_file'       => __('Use this file', 'pluginhub-pro'),
            'no_file'        => __('No file selected', 'pluginhub-pro'),
            'select_screens' => __('Select Screenshots', 'pluginhub-pro'),
            'add_to_gallery' => __('Add to gallery', 'pluginhub-pro'),
            'moving'         => __('Moving...', 'pluginhub-pro'),
            'move_failed'    => __('Could not move file to plugins folder — using original upload location.', 'pluginhub-pro'),
        ),
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ph_admin_nonce'),
    ));
}
add_action('admin_enqueue_scripts', 'ph_admin_assets');

/**
 * Add multipart param to plupload so we can detect uploads and redirect them to our custom folder.
 * NOTE: This adds a param; we already handle upload_dir filter to watch for it.
 */
add_filter('plupload_init', function ($plupload_init) {
    if (! isset($plupload_init['multipart_params']) || ! is_array($plupload_init['multipart_params'])) {
        $plupload_init['multipart_params'] = array();
    }
    $plupload_init['multipart_params']['ph_upload_target'] = 'plugins-download';
    return $plupload_init;
});

/**
 * Redirect uploads to wp-content/plugins-download when ph_upload_target is present.
 */
add_filter('upload_dir', function ($dir) {
    // Look for ph_upload_target in REQUEST (plupload multipart or fallback)
    if ( (isset($_REQUEST['ph_upload_target']) && $_REQUEST['ph_upload_target'] === 'plugins-download')
         || (isset($_POST['ph_upload_target']) && $_POST['ph_upload_target'] === 'plugins-download')
         || (isset($_GET['ph_upload_target']) && $_GET['ph_upload_target'] === 'plugins-download')
    ) {
        // Ensure directory path
        $custom_base = WP_CONTENT_DIR . '/plugins-download';
        if ( ! file_exists($custom_base) ) {
            // attempt to create, with proper permissions
            wp_mkdir_p($custom_base);
        }

        // Force no subdir (no year/month)
        $subdir = '';

        $dir['path']    = $custom_base . $subdir;
        $dir['basedir'] = $custom_base;
        $dir['url']     = content_url('/plugins-download' . $subdir);
        $dir['baseurl'] = content_url('/plugins-download');
        $dir['subdir']  = $subdir;
    }

    return $dir;
});

/**
 * Start page for download: displays a 10s countdown then redirects to signed URL.
 */
add_action('template_redirect', function () {
    if ( ! isset($_GET['ph_download_start']) ) {
        return;
    }
    $id = absint($_GET['ph_download_start']);
    if ( ! $id ) {
        wp_safe_redirect(home_url('/'));
        exit;
    }

    $download_url = get_post_meta($id, 'ph_download', true);
    if ( ! $download_url ) {
        wp_die(esc_html__('No download available for this plugin.', 'pluginhub-pro'));
    }

    // Generate signed URL valid for 1 hour (3600s)
    $signed = function_exists('ph_generate_signed_download_url') ? ph_generate_signed_download_url($id, 3600) : add_query_arg('ph_download', $id, home_url('/'));

    // Simple minimal page with countdown and redirect
    // NOTE: We output a minimal standalone page then exit to avoid theme wrappers
    ?>
    <!doctype html>
    <html>
      <head>
        <meta charset="utf-8">
        <title><?php echo esc_html(get_the_title($id) . ' - ' . __('Preparing download', 'pluginhub-pro')); ?></title>
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <style>
          body{font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;background:#f7fafc;color:#111;padding:40px;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
          .ph-download-page{background:#fff;padding:28px;border-radius:12px;box-shadow:0 8px 30px rgba(15,23,42,0.08);max-width:520px;width:100%;text-align:center}
          .ph-download-title{font-size:20px;margin:0 0 8px}
          .ph-download-desc{color:#6b7280;margin:0 0 16px}
          .ph-count{font-size:42px;font-weight:700;color:#1d4ed8;margin-bottom:16px}
          .ph-cancel{display:inline-block;margin-top:8px;color:#6b7280;text-decoration:none}
        </style>
      </head>
      <body>
        <div class="ph-download-page" role="main" aria-live="polite">
          <h1 class="ph-download-title"><?php echo esc_html(get_the_title($id)); ?></h1>
          <p class="ph-download-desc"><?php esc_html_e('Your download will start automatically in', 'pluginhub-pro'); ?></p>
          <div class="ph-count" id="ph-count">10</div>
          <a class="ph-cancel" href="<?php echo esc_url(get_permalink($id)); ?>"><?php esc_html_e('Cancel and return to plugin page', 'pluginhub-pro'); ?></a>
        </div>

        <script>
        (function(){
          var secs = 10;
          var el = document.getElementById('ph-count');
          var intv = setInterval(function(){
            secs--;
            if ( el ) el.textContent = secs;
            if ( secs <= 0 ) {
              clearInterval(intv);
              // redirect to signed download link
              window.location = <?php echo json_encode($signed); ?>;
            }
          }, 1000);
        })();
        </script>
      </body>
    </html>
    <?php
    exit;
});

/**
 * Actual download handler: validates signed URL (ts + h) and redirects to the stored URL.
 * Endpoint: ?ph_download_file=ID&ts=...&h=...
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

    if ( ! function_exists('ph_validate_signed_download') || ! ph_validate_signed_download($id, $ts, $h) ) {
        wp_die(esc_html__('Download link is invalid or expired.', 'pluginhub-pro'));
    }

    $url = get_post_meta($id, 'ph_download', true);
    if ( ! $url ) {
        wp_die(esc_html__('No download found.', 'pluginhub-pro'));
    }

    $count = (int) get_post_meta($id, 'ph_downloads', true);
    update_post_meta($id, 'ph_downloads', $count + 1);

    // Finally redirect to the actual file
    wp_safe_redirect(esc_url_raw($url));
    exit;
});