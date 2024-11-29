<?php

function feedtune_verify_user_access()
{
    if (!current_user_can('manage_options')) {
        // Properly escape the output
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'feedtune'));
    }
}
add_action('admin_init', 'feedtune_verify_user_access');
