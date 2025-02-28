<?php

if (!defined('ABSPATH')) {
    exit;
}

function cm_add_admin_menus() {
    add_submenu_page(
        'edit.php?post_type=cm-golf-courses',
        'Bookings',
        'Bookings',
        'manage_options',
        'fair-score-bookings',
        'cm_bookings_page_callback'
    );

    add_submenu_page(
        'edit.php?post_type=cm-golf-courses',
        'Settings',
        'Settings',
        'manage_options',
        'fair-score-settings',
        'cm_settings_page_callback'
    );
}
add_action('admin_menu', 'cm_add_admin_menus');
