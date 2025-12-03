<?php
/**
 * Front Class
 *
 * Core front class that maintains constants and initializes front components.
 *
 * @package: asc-ai-summaries
 * @since: 0.1.0
 */

declare( strict_types = 1 );

namespace ASolutionCompany\AISummaries\Front;

use ASolutionCompany\AISummaries\AISummaries as Settings;

/**
 * Front Class
 */
class Front {
	/**
	 * Initialize the front class.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize front components.
	 *
	 * @return void
	 */
	private function init(): void {
		// Add excerpts and summaries to post content
		add_filter( 'the_content', array( $this, 'add_summaries_to_content' ) );

		// Register shortcode
		add_shortcode( 'asc_ais_summary', array( $this, 'render_summary_shortcode' ) );
	}

	/**
	 * Add excerpts and summaries to post content based on settings.
	 *
	 * @param string $content The post content.
	 * @return string The modified post content.
	 */
	public function add_summaries_to_content( string $content ): string {
		// Get current post ID
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return $content;
		}

		// Get current post type
		$post_type = get_post_type( $post_id );
		if ( ! $post_type ) {
			return $content;
		}

		// Get settings
		$settings = Settings::get_settings();

		// Check if current post type is in selected post types
		$selected_post_types = $settings['post_types'] ?? array();
		if ( ! is_array( $selected_post_types ) || ! in_array( $post_type, $selected_post_types, true ) ) {
			return $content;
		}

		// Get style
		$style = $settings['style'] ?? 'block';

		// Get show options
		$show_excerpt = ! empty( $settings['show_excerpt'] );
		$show_summary = ! empty( $settings['show_summary'] );

		// Get excerpt and summary from post meta
		$excerpt = get_post_meta( $post_id, '_asc_ais_excerpt', true );
		$summary = get_post_meta( $post_id, '_asc_ais_summary', true );

		// Check if we have anything to display
		if ( ! $show_excerpt && ! $show_summary ) {
			return $content;
		}

		if ( empty( $excerpt ) && empty( $summary ) ) {
			return $content;
		}

		// Generate the HTML
		$html = $this->generate_summary_html( $post_id );

		// Return empty string if no HTML generated
		if ( empty( $html ) ) {
			return $content;
		}

		// Prepend to content
		return $html . "\n\n" . $content;
	}

	/**
	 * Render summary shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The summary HTML.
	 */
	public function render_summary_shortcode( array $atts ): string {
		// Get current post ID
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return '';
		}

		// Generate and return the HTML
		return $this->generate_summary_html( $post_id );
	}

	/**
	 * Generate summary HTML for a post.
	 *
	 * @param int $post_id The post ID.
	 * @return string The generated HTML, or empty string if nothing to display.
	 */
	private function generate_summary_html( int $post_id ): string {
		// Get settings
		$settings = Settings::get_settings();

		// Get default settings
		$defaults = Settings::get_default_settings();

		// Get style
		$style = $settings['style'] ?? 'block';

		// Get show options
		$show_excerpt = ! empty( $settings['show_excerpt'] );
		$show_summary = ! empty( $settings['show_summary'] );

		// Get excerpt and summary from post meta
		$excerpt = get_post_meta( $post_id, '_asc_ais_excerpt', true );
		$summary = get_post_meta( $post_id, '_asc_ais_summary', true );

		// Check if we have anything to display
		if ( ! $show_excerpt && ! $show_summary ) {
			return '';
		}

		if ( empty( $excerpt ) && empty( $summary ) ) {
			return '';
		}

		// Get titles for the selected style
		$excerpt_title_key = $style . '_excerpt_title';
		$summary_title_key = $style . '_summary_title';
		$excerpt_title = $settings[$excerpt_title_key] ?? '';
		$summary_title = $settings[$summary_title_key] ?? '';

		// Build the HTML
		$html = '<div class="asc-ais-' . esc_attr( $style ) . '-wrapper">' . "\n";

		// Determine if tab style needs tabs
		$has_tabs = false;
		if ( $style == 'tab' ) {
			if ( $show_excerpt && ! empty( $excerpt ) ) {
				$has_tabs = true;
			}
			if ( $show_summary && ! empty( $summary ) ) {
				$has_tabs = true;
			}
		}

		$hide_summary_css = ''; // hide summary tab

		// Display tabs for tab style. Should be false if not tab style.
		if ( $has_tabs ) {
			$need_active = true; // set first tab to active

			$html .= "\t" . '<div class="asc-ais-tab-bar">' . "\n";

			if ( $show_excerpt && ! empty( $excerpt ) ) {
				$html .= "\t\t" . '<div class="asc-ais-tab-item asc-ais-tab-item-active" data-tab="excerpt">';

				if ( ! empty( $excerpt_title ) ) {
					$html .= esc_html( $excerpt_title );
				} else {
					$html .= esc_html( $defaults['tab_excerpt_title'] );
				}

				$html .= '</div>' . "\n";
				$need_active = false;
			}

			if ( $show_summary && ! empty( $summary ) ) {
				if ( $need_active ) {
					$html .= "\t\t" . '<div class="asc-ais-tab-item asc-ais-tab-bar-item-active" data-tab="summary">';
				} else {
					$html .= "\t\t" . '<div class="asc-ais-tab-item" data-tab="summary">' . "\n";
					$hide_summary_css = ' style="display: none;"';
				}

				if ( ! empty( $summary_title ) ) {
					$html .= esc_html( $summary_title );
				} else {
					$html .= esc_html( $defaults['tab_summary_title'] );
				}

				$html .= '</div>' . "\n";
				$need_active = false;
			}

			$html .= "\t" . '</div>' . "\n";
		}

		$html .= "\t" . '<div class="asc-ais-' . esc_attr( $style ) . '">' . "\n";

		// Add excerpt if enabled and exists
		if ( $show_excerpt && ! empty( $excerpt ) ) {
			$html .= "\t\t" . '<div class="asc-ais-' . esc_attr( $style ) . '-excerpt-wrapper">' . "\n";

			if ( ! empty( $excerpt_title ) && ! $has_tabs ) {
				$html .= "\t\t\t" . '<div class="asc-ais-' . esc_attr( $style ) . '-excerpt-title">' . esc_html( $excerpt_title ) . '</div>' . "\n";
			}

			$html .= "\t\t\t" . '<div class="asc-ais-' . esc_attr( $style ) . '-excerpt">' . wp_kses_post( wpautop( $excerpt ) ) . '</div>' . "\n";

			$html .= "\t\t" . '</div>' . "\n";
		}

		// Add summary if enabled and exists
		if ( $show_summary && ! empty( $summary ) ) {
			$html .= "\t\t" . '<div class="asc-ais-' . esc_attr( $style ) . '-summary-wrapper"' . $hide_summary_css . '>' . "\n";

			if ( ! empty( $summary_title && ! $has_tabs ) ) {
				$html .= "\t\t\t" . '<div class="asc-ais-' . esc_attr( $style ) . '-summary-title">' . esc_html( $summary_title ) . '</div>' . "\n";
			}

			$html .= "\t\t\t" . '<div class="asc-ais-' . esc_attr( $style ) . '-summary">' . wp_kses_post( wpautop( $summary ) ) . '</div>' . "\n";

			$html .= "\t\t" . '</div>' . "\n";
		}

		$html .= "\t" . '</div>' . "\n";
		$html .= '</div>';

		return $html;
	}
}