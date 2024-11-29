<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// Remove plugin options
delete_option('feedtune_enabled_feeds');
