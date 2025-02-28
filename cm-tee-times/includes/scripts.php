<?php

if (!defined('ABSPATH')) {
    exit;
}

function cm_tee_times_enqueue_scripts() {
    wp_enqueue_style('cm-tee-times-style', plugin_dir_url(__FILE__) . '../assets/css/style.css', [], filemtime(plugin_dir_path(__FILE__) . '../assets/css/style.css'));
    wp_enqueue_script('cm-tee-times-script', plugin_dir_url(__FILE__) . '../assets/js/script.js', ['jquery'], filemtime(plugin_dir_path(__FILE__) . '../assets/js/script.js'), true);
    
    wp_localize_script('cm-tee-times-script', 'cmAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('cm_tee_times_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'cm_tee_times_enqueue_scripts');
