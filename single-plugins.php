<?php
get_header();
$layout = pluginhub_get_single_layout();
get_template_part('templates/single-plugin/' . $layout);
get_footer();
