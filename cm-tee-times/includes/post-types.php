<?php

if (!defined('ABSPATH')) {
    exit;
}

// Register Golf Courses post type with updated labels
function cm_register_golf_courses_post_type() {
    $labels = array(
        'name'                  => 'Fair Score',
        'singular_name'         => 'Golf Course',
        'menu_name'             => 'Fair Score',
        'name_admin_bar'        => 'Golf Course',
        'add_new'              => 'Add New Course',
        'add_new_item'         => 'Add New Golf Course',
        'new_item'             => 'New Golf Course',
        'edit_item'            => 'Edit Golf Course',
        'view_item'            => 'View Golf Course',
        'all_items'            => 'All Courses',
        'search_items'         => 'Search Courses',
        'not_found'            => 'No courses found.',
        'not_found_in_trash'   => 'No courses found in trash.'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-location',
        'supports' => array('title', 'editor', 'thumbnail'),
        'rewrite' => array('slug' => 'golf-courses'),
    );
    register_post_type('cm-golf-courses', $args);
}
add_action('init', 'cm_register_golf_courses_post_type');

// Add Bookings page
function cm_add_bookings_page() {
    add_submenu_page(
        'edit.php?post_type=cm-golf-courses',
        'Bookings',
        'Bookings',
        'manage_options',
        'fair-score-bookings',
        'cm_bookings_page_callback'
    );
}
add_action('admin_menu', 'cm_add_bookings_page');

// Add Settings Page
function cm_add_settings_page() {
    add_submenu_page(
        'edit.php?post_type=cm-golf-courses',
        'Settings',
        'Settings',
        'manage_options',
        'fair-score-settings',
        'cm_settings_page_callback'
    );
}
add_action('admin_menu', 'cm_add_settings_page');
