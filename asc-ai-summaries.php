<?php

declare( strict_types = 1 );

/**
 * aSc AI Summaries Bootstrap
 *
 * Handles starting the plugin.
 *
 * @package: asc-ai-summaries
 * @since: 0.1.0
 *
 * @wordpress-plugin
 * Plugin Name: aSc AI Summaries
 * Plugin URI: https://asolution.company/asc-ai-summaries
 * Description: Add AI summaries to posts as meta data with manual copy and paste or model API.
 * Version: 0.1.0
 * Requires PHP: 8.1
 * Author: Keith Gardner, aSolution.company
 * Author URI: https://asolution.company
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: asc-ai-summaries
 * Domain Path: /languages
 */

namespace ASolutionCompany\AISummaries;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    exit;
}

// Load Composer autoloader
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

register_activation_hook( __FILE__, array( __NAMESPACE__ . '\\AISummaries', 'activate' ) );
register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\\AISummaries', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( __NAMESPACE__ . '\\AISummaries', 'uninstall' ) );

// Initialize main class object
$asc_ai_summaries = AISummaries::get_instance();