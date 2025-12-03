<?php
/**
 * Theme fallback index template.
 */

get_header();

if ( have_posts() ) {
    /* Prefer list template as a sensible default */
    get_template_part( 'templates/list/index' );
} else {
    echo '<div class="ph-container"><p>' . esc_html__( 'No items found.', 'pluginhub-pro' ) . '</p></div>';
}

get_footer();
