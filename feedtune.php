<?php

/**
 * Plugin Name: FeedTune
 * Plugin URI: https://hitendra.co/plugin/feedtune
 * Description: Lightweight plugin to manage WordPress's default feeds (RSS, Atom, and comments feeds). Disable or enable feeds with a simple interface.
 * Version: 1.0.0
 * Author: Hitendra Singh Rathore
 * Author URI: https://hitendra.co/
 * Text Domain: feedtune
 * Domain Path: /languages
 * Requires at least: 5.5
 * Tested up to: 6.7.1
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('FEEDTUNE_PATH', plugin_dir_path(__FILE__));
define('FEEDTUNE_URL', plugin_dir_url(__FILE__));

// Include required files
require_once FEEDTUNE_PATH . 'includes/settings.php';
require_once FEEDTUNE_PATH . 'includes/feed-management.php';
require_once FEEDTUNE_PATH . 'includes/security.php';

// Plugin activation hook
register_activation_hook(__FILE__, 'feedtune_activate');
function feedtune_activate()
{
    add_option('feedtune_enabled_feeds', [
        'rss' => true,
        'rss2' => true,
        'atom' => true,
        'comments' => true,
    ]);
    add_option('feedtune_feed_redirect', 'none'); // Default: no redirection
}

// Plugin deactivation hook
register_deactivation_hook(__FILE__, 'feedtune_deactivate');
function feedtune_deactivate()
{
    delete_option('feedtune_enabled_feeds');
    delete_option('feedtune_feed_redirect');
}

// Initialize plugin functionality
add_action('init', 'feedtune_init');
function feedtune_init()
{
    feedtune_manage_default_feeds();
    feedtune_handle_feed_redirects();
}

// Add a "Settings" link to the plugins page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'feedtune_add_settings_link');
function feedtune_add_settings_link($links)
{
    $settings_link = '<a href="' . admin_url('options-general.php?page=feedtune') . '">' . esc_html__('Settings', 'feedtune') . '</a>';
    array_unshift($links, $settings_link); // Add the settings link at the beginning
    return $links;
}