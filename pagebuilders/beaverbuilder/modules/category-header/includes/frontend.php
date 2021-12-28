<?php
/**
 * @var object $settings defined by beaver builder module
 */
$args = (array)$settings;
$args['background_overlay_gradient'] = FLBuilderColor::gradient($settings->background_overlay_gradient);

load_template_transient(get_template_directory() . '/template-parts/layout-blocks/category-header.php', false,  $args);
