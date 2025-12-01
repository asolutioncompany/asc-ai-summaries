<?php
/**
 * Public Class
 *
 * Core public class that maintains constants and initializes public components.
 *
 * @package: asc-ai-summaries
 * @since: 0.1.0
 */

declare( strict_types = 1 );

namespace ASolutionCompany\AISummaries\Public;

use ASolutionCompany\AISummaries\AISummaries as Settings;

/**
 * Admin Class
 */
class Front {
	/**
	 * Initialize the public class.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize admin components.
	 *
	 * @return void
	 */
	private function init(): void {
		// Add summary to post content if enabled
		add_filter( 'the_content', array( $this, 'add_summary_to_content' ) );
	}

	/**
	 * Add summary to post content if enabled and summary exists.
	 *
	 * @param string $content The post content.
	 * @return string The modified post content.
	 */
	public function add_summary_to_content( string $content ): string {
		// Only process on single posts
		if ( ! is_singular( 'post' ) ) {
			return $content;
		}

		// Get settings
		$settings = Settings::get_settings();

		// Check if feature is enabled
		if ( empty( $settings['add_summary_to_content'] ) ) {
			return $content;
		}

		// Get current post ID
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return $content;
		}

		// Get summary from post meta
		$summary = get_post_meta( $post_id, '_asc_ais_summary', true );
		if ( empty( $summary ) ) {
			return $content;
		}

		// Get header and footer HTML
		$header = $settings['summary_header'];
		$footer = $settings['summary_footer'];

		// Build the summary HTML
		$summary_html = '<div class="asc-ais-summary">';

		if ( ! empty( $header ) ) {
			$summary_html .= $header . "\n";
		}

		$summary_html .= wp_kses_post( wpautop( $summary ) );

		if ( ! empty( $footer ) ) {
			$summary_html .= "\n" . $footer;
		}

		$summary_html .= '</div>';

		// Prepend summary to content
		return $summary_html . "\n\n" . $content;
	}
}