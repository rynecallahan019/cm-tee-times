<?php

if (!defined('ABSPATH')) {
    exit;
}

// Bookings page callback
function cm_bookings_page_callback() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'golf_bookings';
    
    // Handle booking status updates
    if (isset($_POST['update_status']) && isset($_POST['booking_id'])) {
        $wpdb->update(
            $table_name,
            array('booking_status' => sanitize_text_field($_POST['new_status'])),
            array('id' => intval($_POST['booking_id'])),
            array('%s'),
            array('%d')
        );
    }

    // Get all bookings
    $bookings = $wpdb->get_results("
        SELECT b.*, p.post_title as course_name 
        FROM {$table_name} b 
        LEFT JOIN {$wpdb->posts} p ON b.course_id = p.ID 
        ORDER BY b.booking_date DESC, b.booking_time DESC
    ");

    ?>
    <div class="wrap">
        <h1>Golf Course Bookings</h1>
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <select id="filter-status">
                    <option value="">All Statuses</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <input type="date" id="filter-date" placeholder="Filter by date">
                <button class="button" id="apply-filters">Apply Filters</button>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Date & Time</th>
                    <th>Golfer Details</th>
                    <th>Booking Details</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?php echo esc_html($booking->course_name); ?></td>
                    <td>
                        <?php 
                        echo esc_html(date('F j, Y', strtotime($booking->booking_date))) . '<br>';
                        echo esc_html(date('g:i A', strtotime($booking->booking_time)));
                        ?>
                    </td>
                    <td>
                        <?php 
                        echo esc_html($booking->golfer_name) . '<br>';
                        echo esc_html($booking->golfer_email) . '<br>';
                        if ($booking->golfer_phone) {
                            echo esc_html($booking->golfer_phone);
                        }
                        ?>
                    </td>
                    <td>
                        <?php 
                        echo esc_html($booking->num_golfers) . ' golfers<br>';
                        echo esc_html($booking->num_holes) . ' holes';
                        ?>
                    </td>
                    <td>
                        <span class="status-<?php echo esc_attr($booking->booking_status); ?>">
                            <?php echo esc_html(ucfirst($booking->booking_status)); ?>
                        </span>
                    </td>
                    <td>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="booking_id" value="<?php echo esc_attr($booking->id); ?>">
                            <select name="new_status">
                                <option value="confirmed" <?php selected($booking->booking_status, 'confirmed'); ?>>Confirmed</option>
                                <option value="completed" <?php selected($booking->booking_status, 'completed'); ?>>Completed</option>
                                <option value="cancelled" <?php selected($booking->booking_status, 'cancelled'); ?>>Cancelled</option>
                            </select>
                            <input type="submit" name="update_status" class="button" value="Update">
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}



// Operating hours meta box callback
function cm_operating_hours_callback($post) {
    wp_nonce_field('cm_save_operating_hours', 'cm_operating_hours_nonce');
    
    $days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
    $operating_hours = get_post_meta($post->ID, '_operating_hours', true);
    $closed_dates = get_post_meta($post->ID, '_closed_dates', true);
    
    // Operating hours form
    echo '<div class="operating-hours-container">';
    foreach ($days as $day) {
        $is_open = isset($operating_hours[$day]['is_open']) ? $operating_hours[$day]['is_open'] : '';
        $open_time = isset($operating_hours[$day]['open_time']) ? $operating_hours[$day]['open_time'] : '';
        $close_time = isset($operating_hours[$day]['close_time']) ? $operating_hours[$day]['close_time'] : '';
        
        echo '<div class="day-row">';
        echo '<label><input type="checkbox" name="operating_hours[' . $day . '][is_open]" value="1" ' . checked($is_open, 1, false) . '> ' . $day . '</label>';
        echo '<input type="time" name="operating_hours[' . $day . '][open_time]" value="' . esc_attr($open_time) . '">';
        echo '<input type="time" name="operating_hours[' . $day . '][close_time]" value="' . esc_attr($close_time) . '">';
        echo '</div>';
    }
    echo '</div>';
    
    // Closed dates
    echo '<h3>Days Closed</h3>';
    echo '<div id="closed-dates-container">';
    if (!empty($closed_dates)) {
        foreach ($closed_dates as $index => $date) {
            echo '<div class="closed-date-row">';
            echo '<input type="date" name="closed_dates[]" value="' . esc_attr($date) . '">';
            echo '<button type="button" class="remove-date">Remove</button>';
            echo '</div>';
        }
    }
    echo '</div>';
    echo '<button type="button" id="add-closed-date">Add Closed Date</button>';
}

// Save meta box data
function cm_save_operating_hours($post_id) {
    if (!isset($_POST['cm_operating_hours_nonce']) || 
        !wp_verify_nonce($_POST['cm_operating_hours_nonce'], 'cm_save_operating_hours')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (isset($_POST['operating_hours'])) {
        update_post_meta($post_id, '_operating_hours', $_POST['operating_hours']);
    }
    
    if (isset($_POST['closed_dates'])) {
        update_post_meta($post_id, '_closed_dates', $_POST['closed_dates']);
    }
}
add_action('save_post_cm-golf-courses', 'cm_save_operating_hours');


// Helper function to get available time slots
function cm_get_available_time_slots($course_id, $operating_hours, $closed_dates) {
    $slots = array();
    $current_date = current_time('Y-m-d');
    
    // Generate slots for the next 14 days
    for ($i = 0; $i < 14; $i++) {
        $date = date('Y-m-d', strtotime("+$i days"));
        $day_of_week = date('l', strtotime($date));
        
        // Skip if date is in closed dates
        if (is_array($closed_dates) && in_array($date, $closed_dates)) {
            continue;
        }
        
        // Skip if day is not operating
        if (!isset($operating_hours[$day_of_week]['is_open']) || !$operating_hours[$day_of_week]['is_open']) {
            continue;
        }
        
        $open_time = $operating_hours[$day_of_week]['open_time'];
        $close_time = $operating_hours[$day_of_week]['close_time'];
        
        // Generate time slots in 10-minute intervals
        $current_time = strtotime($open_time);
        $end_time = strtotime($close_time);
        
        while ($current_time <= $end_time) {
            // Skip past times for current day
            if ($date === $current_date && date('H:i:s', $current_time) <= current_time('H:i:s')) {
                $current_time += 600; // Add 10 minutes
                continue;
            }
            
            $slots[] = array(
                'date' => $date,
                'time' => date('H:i:s', $current_time)
            );
            
            $current_time += 600; // Add 10 minutes
        }
    }
    
    return $slots;
}

function cm_get_booked_spots($course_id, $date, $time) {
    global $wpdb;
    
    $booked = $wpdb->get_var($wpdb->prepare(
        "SELECT COALESCE(SUM(num_golfers), 0)
        FROM {$wpdb->prefix}golf_bookings 
        WHERE course_id = %d 
        AND booking_date = %s 
        AND booking_time = %s 
        AND booking_status = 'confirmed'",
        $course_id,
        $date,
        $time
    ));
    
    return intval($booked);
}