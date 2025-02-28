<?php

if (!defined('ABSPATH')) {
    exit;
}


// Settings Page Callback
function cm_settings_page_callback() {
    if (isset($_POST['cm_save_settings'])) {
        update_option('fair_score_theme', sanitize_text_field($_POST['theme']));
        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }

    $current_theme = get_option('fair_score_theme', 'light');
    ?>
    <div class="wrap">
        <h1>Fair Score Settings</h1>
        
        <div class="fs-settings-container">
            <form method="post">
                <div class="fs-setting-section">
                    <h2>Theme Settings</h2>
                    <p class="description">Choose between light and dark theme for your booking form.</p>
                    
                    <div class="theme-preview-container">
                        <div class="theme-option">
                            <label>
                                <input type="radio" name="theme" value="light" <?php checked($current_theme, 'light'); ?>>
                                <div class="theme-preview light-preview">
                                    <div class="preview-header">Light Theme</div>
                                    <div class="preview-content"></div>
                                </div>
                            </label>
                        </div>
                        
                        <div class="theme-option">
                            <label>
                                <input type="radio" name="theme" value="dark" <?php checked($current_theme, 'dark'); ?>>
                                <div class="theme-preview dark-preview">
                                    <div class="preview-header">Dark Theme</div>
                                    <div class="preview-content"></div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="fs-setting-section">
                    <h2>Instructions</h2>
                    <div class="instructions-container">
                        <h3>🏌️ Quick Start Guide</h3>
                        <ol>
                            <li><strong>Add Golf Courses:</strong> Create new courses under the "Golf Courses" menu.</li>
                            <li><strong>Set Operating Hours:</strong> For each course, set regular operating hours and special closed dates.</li>
                            <li><strong>Add the Booking Form:</strong> Use the shortcode [booking_shortcode] on any page to display the booking form.</li>
                            <li><strong>Theme Selection:</strong> Choose your preferred theme above to match your website's style.</li>
                        </ol>

                        <h3>💡 Pro Tips</h3>
                        <ul>
                            <li>Regular maintenance of closed dates keeps your booking calendar accurate.</li>
                            <li>The booking form automatically adjusts to show only available time slots.</li>
                            <li>Each time slot can accommodate up to 4 golfers.</li>
                        </ul>
                    </div>
                </div>

                <input type="submit" name="cm_save_settings" class="button button-primary" value="Save Settings">
            </form>
        </div>
    </div>
    <?php
}
