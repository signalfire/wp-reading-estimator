<?php
/**
 * Uninstall script for Signalfire Reading Estimator
 * 
 * This file is called when the plugin is uninstalled.
 * It removes all plugin data from the database.
 * 
 * @package SignalfireReadingEstimator
 * @version 1.0.0
 * @license GPLv3 or later
 * @license-uri https://www.gnu.org/licenses/gpl-3.0.html
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('sigukrest_settings');

// For multisite installations
if (is_multisite()) {
    $sites = get_sites();
    foreach ($sites as $site) {
        switch_to_blog($site->blog_id);
        delete_option('sigukrest_settings');
        restore_current_blog();
    }
}

// Clear any cached data that might be related to the plugin
wp_cache_flush();