<?php

declare( strict_types = 1 );

/**
 * aS.c AI Summaries
 *
 * Automatically add AI summaries to your WordPress content
 *
 * The aS.c AI Summaries WordPress plugin automatically generates excerpts and summaries for
 * posts, pages, and/or custom post types. Ideal for publishers, syndicators, bloggers, and news
 * aggregators, it helps improve SEO and gives busy readers a quick, accurate overview of your
 * content.
 *
 * Features:
 *
 * - Automatic generation for existing content
 * - Generate and edit summaries when creating or editing content
 * - Customize AI prompting for writing style
 * - Provides excerpts and summaries with customizable word length
 * - Select from multiple AI models or perform manual copy and paste
 * - Select from multiple styles with fully exposed CSS for full customization
 *
 * Visit the Github page for the Setup Guide and more information:
 *
 * https://github.com/asolutioncompany/asc-ai-summaries
 *
 * @wordpress-plugin
 * Plugin Name: aS.c AI Summaries
 * Plugin URI: https://github.com/asolutioncompany/asc-ai-summaries
 * Description: Add AI summaries to WordPress content.
 * Version: 0.1.0
 * Requires PHP: 8.1
 * Author: aSolution.company
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