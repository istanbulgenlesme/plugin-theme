<?php
get_header();
$layout = pluginhub_get_layout();
get_template_part('templates/archive', null, array('layout' => $layout));
get_footer();
