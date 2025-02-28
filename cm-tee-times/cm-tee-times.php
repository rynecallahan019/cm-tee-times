<?php
/*
Plugin Name: Fair Score
Plugin URI: https://github.com/rynecallahan019/cm-tee-times
Description: A plugin for booking golf course tee times with flexible management options.
Version: 2.2.0
Author: Callahan Media
Author URI: http://calmc.net/
* License: GPL-2.0+
* License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/activations.php';
require_once plugin_dir_path(__FILE__) . 'includes/post-types.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-menu.php';
require_once plugin_dir_path(__FILE__) . 'includes/bookings.php';
require_once plugin_dir_path(__FILE__) . 'includes/scripts.php';
require_once plugin_dir_path(__FILE__) . 'includes/meta-boxes.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';



function cm_verify_database_setup() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'golf_bookings';
    
    // Check if table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
    
    if (!$table_exists) {
        error_log("Golf bookings table does not exist. Attempting to create...");
        cm_create_booking_tables();
        
        // Verify table was created
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        if (!$table_exists) {
            error_log("Failed to create golf bookings table!");
            return false;
        }
    }
    
    // Verify table structure
    $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
    error_log("Table structure: " . print_r($columns, true));
    
    return true;
}

// Call this function on plugin activation
add_action('plugins_loaded', 'cm_verify_database_setup');

function cm_register_plugin_template($templates) {
    $templates['fairscore-page-template.php'] = 'Fairscore';
    return $templates;
}
add_filter('theme_page_templates', 'cm_register_plugin_template');

function cm_load_plugin_template($template) {
    global $post;
    
    if (!$post) {
        return $template;
    }

    $plugin_template = get_post_meta($post->ID, '_wp_page_template', true);

    if ('fairscore-page-template.php' === $plugin_template) {
        $plugin_template_path = plugin_dir_path(__FILE__) . 'fairscore-page-template.php';
        
        if (file_exists($plugin_template_path)) {
            return $plugin_template_path;
        }
    }

    return $template;
}
add_filter('template_include', 'cm_load_plugin_template');


require 'plugin-update-checker-master/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/rynecallahan019/cm-tee-times/', // GitHub repository URL
    __FILE__, // Full path to the main plugin file
    'cm-tee-times' // Plugin slug (usually the folder name)
);

// Optional: Set branch to check updates from (default is "main" or "master")
$myUpdateChecker->setBranch('main');

// Optional: Set authentication if your repo is private (replace with GitHub token)
// $myUpdateChecker->setAuthentication('your_github_personal_access_token');
