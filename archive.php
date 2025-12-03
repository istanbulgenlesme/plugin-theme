<?php
/**
 * Archive wrapper — use templates/archive.php for layout rendering.
 */

get_header();

$layout = function_exists('pluginhub_get_layout') ? pluginhub_get_layout() : 'list';
/* Pass layout into template part so templates/archive.php can render the chosen layout */
get_template_part( 'templates/archive', null, array( 'layout' => $layout ) );

get_footer();
