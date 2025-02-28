<?php
/**
 * Template Name: Fairscore Page Template
 * Description: A custom page template provided by the plugin.
 */

get_header(); ?>

<main class="plugin-page-container">
    <div class="plugin-page-content">
        <div class="plugin-shortcode-wrapper">
            <?php echo do_shortcode('[booking_shortcode]'); ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>

