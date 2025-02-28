<?php

if (!defined('ABSPATH')) {
    exit;
}

// Modify booking shortcode to handle all steps
function cm_booking_shortcode() {
    $theme = get_option('fair_score_theme', 'light');
    ob_start();
    
    echo '<div class="fair-score-booking theme-' . esc_attr($theme) . '">';
    
    $step = isset($_POST['step']) ? intval($_POST['step']) : 1;
    
    switch ($step) {
        case 1:
            cm_render_step_one();
            break;
        case 2:
            cm_render_step_two();
            break;
        case 3:
            cm_render_step_three();
            break;
        case 4:
            cm_render_step_four();
            break;
        case 5:
            cm_process_booking();
            break;
    }
    
    echo '</div>';
    
    return ob_get_clean();
}
add_shortcode('booking_shortcode', 'cm_booking_shortcode');

// Render step one (course selection)
function cm_render_step_one() {
    $courses = get_posts(array(
        'post_type' => 'cm-golf-courses',
        'posts_per_page' => -1
    ));
    
    echo '<div class="booking-form step-1">';
    echo '<h2>Select Your Golf Course</h2>';
    echo '<form method="post">';
    echo '<div class="course-grid">';
    foreach ($courses as $course) {
        echo '<div class="course-card">';
        echo '<div class="course-card-inner">';
        if (has_post_thumbnail($course->ID)) {
            echo get_the_post_thumbnail($course->ID, 'full');
        }
        echo '<div class="course-info">';
        echo '<h3>' . $course->post_title . '</h3>';
        echo '<div class="course-excerpt">' . get_the_excerpt($course) . '</div>';
        echo '<label class="course-select">';
        echo '<input type="radio" name="course_id" value="' . $course->ID . '" required>';
        echo '<span class="select-button">Select This Course</span>';
        echo '</label>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
    echo '<input type="hidden" name="step" value="2">';
    echo '<button type="submit" class="fs-button">Next Step</button>';
    echo '</form>';
    echo '</div>';
}

// Render step two (time slot selection)
function cm_render_step_two() {
    if (!isset($_POST['course_id'])) {
        cm_render_step_one();
        return;
    }

    $course_id = intval($_POST['course_id']);
    $operating_hours = get_post_meta($course_id, '_operating_hours', true);
    $closed_dates = get_post_meta($course_id, '_closed_dates', true);
    
    // Get available time slots
    $available_slots = cm_get_available_time_slots($course_id, $operating_hours, $closed_dates);
    
    echo '<div class="booking-form step-2">';
    echo '<h2>Select Your Tee Time</h2>';
    
    // Add date filter
    echo '<div class="booking-filters">';
    echo '<input type="date" id="date-filter" min="' . date('Y-m-d') . '" value="' . date('Y-m-d') . '">';
    echo '</div>';
    
    echo '<form method="post">';
    echo '<div class="time-slots-grid">';
    foreach ($available_slots as $slot) {
        $available_spots = 4 - cm_get_booked_spots($course_id, $slot['date'], $slot['time']);
        
        echo '<div class="time-slot-card' . ($available_spots > 0 ? '' : ' fully-booked') . '">';
        echo '<div class="time-slot-time">' . date('g:i A', strtotime($slot['time'])) . '</div>';
        echo '<div class="time-slot-date">' . date('l, F j', strtotime($slot['date'])) . '</div>';
        echo '<div class="available-spots">' . $available_spots . ' spots available</div>';
        if ($available_spots > 0) {
            echo '<label class="time-slot-select">';
            echo '<input type="radio" name="selected_slot" value="' . esc_attr($slot['date'] . '|' . $slot['time']) . '" required>';
            echo '<span class="select-button">Select Time</span>';
            echo '</label>';
        }
        echo '</div>';
    }
    echo '</div>';
    
    echo '<input type="hidden" name="course_id" value="' . $course_id . '">';
    echo '<input type="hidden" name="step" value="3">';
    echo '<button type="submit" class="fs-button">Next Step</button>';
    echo '</form>';
    echo '</div>';
}

// Render step three (number of golfers and holes)
function cm_render_step_three() {
    if (!isset($_POST['selected_slot']) || !isset($_POST['course_id'])) {
        cm_render_step_two();
        return;
    }

    list($date, $time) = explode('|', $_POST['selected_slot']);
    $course_id = intval($_POST['course_id']);
    $available_spots = 4 - cm_get_booked_spots($course_id, $date, $time);

    echo '<div class="booking-form step-3">';
    echo '<h2>Booking Details</h2>';
    
    echo '<form method="post">';
    echo '<div class="booking-details-form">';
    
    echo '<div class="form-group">';
    echo '<label>Number of Golfers</label>';
    echo '<select name="num_golfers" required>';
    for ($i = 1; $i <= $available_spots; $i++) {
        echo '<option value="' . $i . '">' . $i . ' golfer' . ($i > 1 ? 's' : '') . '</option>';
    }
    echo '</select>';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<label>Number of Holes</label>';
    echo '<select name="num_holes" required>';
    echo '<option value="9">9 holes</option>';
    echo '<option value="18">18 holes</option>';
    echo '</select>';
    echo '</div>';
    
    echo '</div>';
    
    echo '<input type="hidden" name="course_id" value="' . $course_id . '">';
    echo '<input type="hidden" name="selected_slot" value="' . esc_attr($_POST['selected_slot']) . '">';
    echo '<input type="hidden" name="step" value="4">';
    echo '<button type="submit" class="fs-button">Next Step</button>';
    echo '</form>';
    echo '</div>';
}

// Render step four (contact information)
function cm_render_step_four() {
    if (!isset($_POST['num_golfers']) || !isset($_POST['course_id'])) {
        cm_render_step_three();
        return;
    }

    echo '<div class="booking-form step-4">';
    echo '<h2>Contact Information</h2>';
    
    echo '<form method="post">';
    echo '<div class="booking-details-form">';
    
    echo '<div class="form-group">';
    echo '<label>Full Name</label>';
    echo '<input type="text" name="golfer_name" required>';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<label>Email Address</label>';
    echo '<input type="email" name="golfer_email" required>';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<label>Phone Number (Optional)</label>';
    echo '<input type="tel" name="golfer_phone">';
    echo '</div>';
    
    echo '<div class="booking-notice">';
    echo '<p>Payment will be collected at the clubhouse upon arrival.</p>';
    echo '</div>';
    
    echo '</div>';
    
    // Pass through all previous form data
    echo '<input type="hidden" name="course_id" value="' . esc_attr($_POST['course_id']) . '">';
    echo '<input type="hidden" name="selected_slot" value="' . esc_attr($_POST['selected_slot']) . '">';
    echo '<input type="hidden" name="num_golfers" value="' . esc_attr($_POST['num_golfers']) . '">';
    echo '<input type="hidden" name="num_holes" value="' . esc_attr($_POST['num_holes']) . '">';
    echo '<input type="hidden" name="step" value="5">';
    echo '<button type="submit" class="fs-button">Complete Booking</button>';
    echo '</form>';
    echo '</div>';
}

function cm_process_booking() {
    global $wpdb;
    
    // Enable error logging
    error_log('Starting booking process...');
    
    // Debug: Log POST data
    error_log('POST data: ' . print_r($_POST, true));
    
    // Verify required fields with detailed logging
    $required_fields = array(
        'golfer_name',
        'golfer_email',
        'selected_slot',
        'course_id',
        'num_golfers',
        'num_holes'
    );
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            error_log("Missing required field: {$field}");
            echo '<div class="booking-error">Missing required field: ' . esc_html($field) . '</div>';
            cm_render_step_four();
            return;
        }
    }

    // Debug: Log that all required fields are present
    error_log('All required fields present');

    // Split date and time from selected_slot
    list($date, $time) = explode('|', $_POST['selected_slot']);
    
    error_log("Parsed date: {$date}, time: {$time}");

    // Validate date and time format
    if (!strtotime($date) || !strtotime($time)) {
        error_log("Invalid date or time format - Date: {$date}, Time: {$time}");
        echo '<div class="booking-error">Invalid date or time format.</div>';
        return;
    }

    // Check course exists
    $course_id = intval($_POST['course_id']);
    $course = get_post($course_id);
    if (!$course || $course->post_type !== 'cm-golf-courses') {
        error_log("Invalid course ID: {$course_id}");
        echo '<div class="booking-error">Invalid course selection.</div>';
        return;
    }

    // Validate available spots
    $available_spots = 4 - cm_get_booked_spots($course_id, $date, $time);
    error_log("Available spots: {$available_spots}");
    
    if ($available_spots < intval($_POST['num_golfers'])) {
        error_log("Not enough spots available. Requested: {$_POST['num_golfers']}, Available: {$available_spots}");
        echo '<div class="booking-error">Selected time slot is no longer available.</div>';
        cm_render_step_one();
        return;
    }

    // Prepare booking data
    $booking_data = array(
        'course_id' => $course_id,
        'booking_date' => $date,
        'booking_time' => $time,
        'golfer_name' => sanitize_text_field($_POST['golfer_name']),
        'golfer_email' => sanitize_email($_POST['golfer_email']),
        'golfer_phone' => isset($_POST['golfer_phone']) ? sanitize_text_field($_POST['golfer_phone']) : '',
        'num_golfers' => intval($_POST['num_golfers']),
        'num_holes' => intval($_POST['num_holes']),
        'booking_status' => 'confirmed'
    );

    error_log('Prepared booking data: ' . print_r($booking_data, true));

    // Verify table exists
    $table_name = $wpdb->prefix . 'golf_bookings';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
    
    if (!$table_exists) {
        error_log("Table {$table_name} does not exist!");
        // Try to create the table
        cm_create_booking_tables();
        error_log("Attempted to create table {$table_name}");
    }

    // Insert booking with detailed error checking
    $result = $wpdb->insert(
        $table_name,
        $booking_data,
        array(
            '%d', // course_id
            '%s', // booking_date
            '%s', // booking_time
            '%s', // golfer_name
            '%s', // golfer_email
            '%s', // golfer_phone
            '%d', // num_golfers
            '%d', // num_holes
            '%s'  // booking_status
        )
    );

    if ($result === false) {
        error_log('Database insertion failed. Last error: ' . $wpdb->last_error);
        error_log('Last query: ' . $wpdb->last_query);
        echo '<div class="booking-error">Database error: ' . esc_html($wpdb->last_error) . '</div>';
        return;
    }

    $booking_id = $wpdb->insert_id;
    error_log("Successful booking created with ID: {$booking_id}");

    // Send confirmation email
    $course_name = get_the_title($_POST['course_id']);
    
    $message = "Thank you for booking your tee time with us!\n\n";
    $message .= "Booking Reference: #" . $booking_id . "\n\n";
    $message .= "Booking Details:\n";
    $message .= "Course: " . $course_name . "\n";
    $message .= "Date: " . date('F j, Y', strtotime($date)) . "\n";
    $message .= "Time: " . date('g:i A', strtotime($time)) . "\n";
    $message .= "Number of Golfers: " . $_POST['num_golfers'] . "\n";
    $message .= "Number of Holes: " . $_POST['num_holes'] . "\n\n";
    $message .= "Please arrive at least 15 minutes before your tee time.\n";
    $message .= "Payment will be collected at the clubhouse.\n\n";
    $message .= "We look forward to seeing you!";
    
    $headers = array('Content-Type: text/plain; charset=UTF-8');
    wp_mail($_POST['golfer_email'], 'Golf Tee Time Confirmation - Booking #' . $booking_id, $message, $headers);

    // Display success message
    echo '<div class="booking-form booking-confirmation">';
    echo '<h2>Booking Confirmed!</h2>';
    echo '<div class="confirmation-details">';
    echo '<p class="booking-reference">Booking Reference: #' . esc_html($booking_id) . '</p>';
    echo '<p>Thank you for booking your tee time at ' . esc_html($course_name) . '.</p>';
    echo '<div class="booking-summary">';
    echo '<h3>Booking Details:</h3>';
    echo '<p><strong>Date:</strong> ' . date('F j, Y', strtotime($date)) . '</p>';
    echo '<p><strong>Time:</strong> ' . date('g:i A', strtotime($time)) . '</p>';
    echo '<p><strong>Number of Golfers:</strong> ' . esc_html($_POST['num_golfers']) . '</p>';
    echo '<p><strong>Number of Holes:</strong> ' . esc_html($_POST['num_holes']) . '</p>';
    echo '</div>';
    echo '<p>A confirmation email has been sent to ' . esc_html($_POST['golfer_email']) . '.</p>';
    echo '<p class="arrival-notice">Please arrive at least 15 minutes before your tee time.</p>';
    echo '</div>';
    echo '<a href="' . esc_url(get_permalink()) . '" class="fs-button">Book Another Tee Time</a>';
    echo '</div>';
}