<?php

if (!defined('ABSPATH')) {
    exit;
}

// Create database tables on plugin activation
function cm_create_booking_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . 'golf_bookings';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        course_id bigint(20) NOT NULL,
        booking_date date NOT NULL,
        booking_time time NOT NULL,
        golfer_name varchar(100) NOT NULL,
        golfer_email varchar(100) NOT NULL,
        golfer_phone varchar(20),
        num_golfers int(11) NOT NULL,
        num_holes int(11) NOT NULL,
        booking_status varchar(20) DEFAULT 'confirmed',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'cm_create_booking_tables');

