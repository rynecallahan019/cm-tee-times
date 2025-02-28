<?php

if (!defined('ABSPATH')) {
    exit;
}

// Add custom meta boxes for operating hours
function cm_add_operating_hours_meta_box() {
    add_meta_box(
        'operating_hours',
        'Operating Hours',
        'cm_operating_hours_callback',
        'cm-golf-courses',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'cm_add_operating_hours_meta_box');