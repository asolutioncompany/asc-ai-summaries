<?php
/**
 * Post Meta Panel Class
 *
 * Handles the post meta box functionality for AI excerpts and summaries.
 *
 * @package: asc-ai-summaries
 * @since: 0.1.0
 */

declare( strict_types = 1 );

namespace ASolutionCompany\AISummaries\Admin;

use ASolutionCompany\AISummaries\AISummaries as Settings;

/**
 * Post Meta Panel Class
 */
class PostMetaPanel {

	/**
	 * Flag to prevent recursion when updating post excerpt.
	 *
	 * @var bool
	 */
	private static bool $updating_excerpt = false;

	/**
	 * Initialize the post meta panel.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize post meta panel hooks.
	 *
	 * @return void
	 */
	private function init(): void {
		// Add meta box to post edit screen
		add_action( 'add_meta_boxes', array( $this, 'add_post_meta_box' ) );

		// Save meta box data
		add_action( 'save_post', array( $this, 'save_post_meta' ) );

		// Register AJAX handler for generating summaries
		add_action( 'wp_ajax_asc_ais_generate_summaries', array( $this, 'ajax_generate_summaries' ) );
	}

	/**
	 * Add meta box to post edit screen.
	 *
	 * @return void
	 */
	public function add_post_meta_box(): void {
		// Get selected post types from settings
		$settings = Settings::get_settings();
		$defaults = Settings::get_default_settings();
		$selected_post_types = $settings['post_types'] ?? $defaults['post_types'];

		// Ensure we have an array
		if ( ! is_array( $selected_post_types ) ) {
			$selected_post_types = array();
		}

		// Add meta box for each selected post type
		foreach ( $selected_post_types as $post_type ) {
			add_meta_box(
				'asc_ais_meta_box',
				__( 'AI Summaries', 'asc-ai-summaries' ),
				array( $this, 'render_post_meta_box' ),
				$post_type,
				'normal',
				'high'
			);
		}
	}

	/**
	 * Render the meta box content.
	 *
	 * @param \WP_Post $post The post object.
	 * @return void
	 */
	public function render_post_meta_box( \WP_Post $post ): void {
		// Add nonce for security
		wp_nonce_field( 'asc_ais_meta_box', 'asc_ais_meta_box_nonce' );

		// Get existing meta values
		$ai_excerpt = get_post_meta( $post->ID, '_asc_ais_excerpt', true );
		$ai_summary = get_post_meta( $post->ID, '_asc_ais_summary', true );

		// Get settings to display current model
		$settings = Settings::get_settings();
		$defaults = Settings::get_default_settings();
		$selected_model = $settings['ai_model'] ?? $defaults['ai_model'];
		$model_data = Settings::get_model_data( $selected_model );
		if ( ! $model_data ) {
			$model_data = Settings::get_model_data( $defaults['ai_model'] );
		}
		$model_label = $model_data['label'] ?? __( 'Unknown', 'asc-ai-summaries' );

		$is_manual = false;
		if ( 'none' === $selected_model ) {
			$is_manual = true;
		}

		?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label><?php esc_html_e( 'AI Model', 'asc-ai-summaries' ); ?></label>
				</th>
				<td>
					<strong><?php echo esc_html( $model_label ); ?></strong>
					<p class="description">
						<?php
						if ( $is_manual ) {
							esc_html_e( 'Manually copy and paste AI summaries into the fields below.', 'asc-ai-summaries' );
						} else {
							esc_html_e( 'Configure the AI model in Settings → AI Summaries.', 'asc-ai-summaries' );
						}
						?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"></th>
				<td>
					<button
						type="button"
						id="asc-ais-generate-button"
						class="button button-primary"
						data-post-id="<?php echo esc_attr( $post->ID ); ?>"
						<?php
						if ( $is_manual ) {
							echo ' disabled';
						}
						?>
					>
						<?php esc_html_e( 'Generate AI Summaries', 'asc-ai-summaries' ); ?>
					</button>
				</td>
			</tr>
			<tr>
				<th scope="row"></th>
				<td>
					<span id="asc-ais-generate-status"></span>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="asc_ais_excerpt"><?php esc_html_e( 'AI Excerpt', 'asc-ai-summaries' ); ?></label>
				</th>
				<td>
					<textarea
						name="asc_ais_excerpt"
						id="asc_ais_excerpt"
						rows="3"
						class="large-text"
						placeholder="<?php esc_attr_e( 'Enter AI-generated excerpt...', 'asc-ai-summaries' ); ?>"
					><?php echo esc_textarea( $ai_excerpt ); ?></textarea>
					<p class="description">
						<?php esc_html_e( 'A short AI-generated excerpt for this post.', 'asc-ai-summaries' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="asc_ais_summary"><?php esc_html_e( 'AI Summary', 'asc-ai-summaries' ); ?></label>
				</th>
				<td>
					<textarea
						name="asc_ais_summary"
						id="asc_ais_summary"
						rows="6"
						class="large-text"
						placeholder="<?php esc_attr_e( 'Enter AI-generated summary...', 'asc-ai-summaries' ); ?>"
					><?php echo esc_textarea( $ai_summary ); ?></textarea>
					<p class="description">
						<?php esc_html_e( 'A detailed AI-generated summary for this post.', 'asc-ai-summaries' ); ?>
					</p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id The post ID.
	 * @return void
	 */
	public function save_post_meta( int $post_id ): void {
		// Check if nonce is set
		if ( ! isset( $_POST['asc_ais_meta_box_nonce'] ) ) {
			return;
		}

		// Verify nonce
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['asc_ais_meta_box_nonce'] ) ), 'asc_ais_meta_box' ) ) {
			return;
		}

		// Check if this is an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save AI excerpt
		if ( isset( $_POST['asc_ais_excerpt'] ) ) {
			$ai_excerpt = sanitize_textarea_field( wp_unslash( $_POST['asc_ais_excerpt'] ) );
			update_post_meta( $post_id, '_asc_ais_excerpt', $ai_excerpt );

			// Sync to post excerpt if setting is enabled
			$settings = Settings::get_settings();
			if ( ! empty( $settings['sync_ai_excerpt_to_post_excerpt'] ) && ! self::$updating_excerpt ) {
				self::$updating_excerpt = true;

				// Update the post excerpt directly
				global $wpdb;
				$wpdb->update(
					$wpdb->posts,
					array( 'post_excerpt' => $ai_excerpt ),
					array( 'ID' => $post_id ),
					array( '%s' ),
					array( '%d' )
				);

				clean_post_cache( $post_id );
				self::$updating_excerpt = false;
			}
		} else {
			delete_post_meta( $post_id, '_asc_ais_excerpt' );
		}

		// Save AI summary
		if ( isset( $_POST['asc_ais_summary'] ) ) {
			$ai_summary = sanitize_textarea_field( wp_unslash( $_POST['asc_ais_summary'] ) );
			update_post_meta( $post_id, '_asc_ais_summary', $ai_summary );
		} else {
			delete_post_meta( $post_id, '_asc_ais_summary' );
		}
	}

	/**
	 * AJAX handler for generating summaries.
	 *
	 * @return void
	 */
	public function ajax_generate_summaries(): void {
		// Check nonce
		check_ajax_referer( 'asc_ais_generate_summaries', 'nonce' );

		// Check user permissions
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'asc-ai-summaries' ) ) );
		}

		// Get post ID
		$post_id = 0;
		if ( isset( $_POST['post_id'] ) ) {
			$post_id = absint( $_POST['post_id'] );
		}

		if ( ! $post_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'asc-ai-summaries' ) ) );
		}

		// Check user can edit this post
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'asc-ai-summaries' ) ) );
		}

		// Get post content
		$post = get_post( $post_id );
		if ( ! $post ) {
			wp_send_json_error( array( 'message' => __( 'Post not found.', 'asc-ai-summaries' ) ) );
		}

		$post_content = $post->post_content;
		if ( empty( $post_content ) ) {
			wp_send_json_error( array( 'message' => __( 'Post content is empty.', 'asc-ai-summaries' ) ) );
		}

		// Get settings
		$settings = Settings::get_settings();
		$defaults = Settings::get_default_settings();
		$selected_model = $settings['ai_model'] ?? $defaults['ai_model'];

		if ( 'none' === $selected_model ) {
			wp_send_json_error( array( 'message' => __( 'No AI model selected.', 'asc-ai-summaries' ) ) );
		}

		// Get prompts
		$excerpt_prompt = $settings['excerpt_prompt'] ?? $defaults['excerpt_prompt'];
		$summary_prompt = $settings['summary_prompt'] ?? $defaults['summary_prompt'];

		// Initialize API handler
		$api_handler = new APIHandler();

		// Generate excerpt
		$excerpt = '';
		$excerpt_result = $api_handler->generate_text( $selected_model, $post_content, $excerpt_prompt, 'excerpt' );
		if ( ! is_wp_error( $excerpt_result ) ) {
			$excerpt = $excerpt_result;
		} else {
			wp_send_json_error( array( 'message' => $excerpt_result->get_error_message() ) );
		}

		// Generate summary
		$summary = '';
		$summary_result = $api_handler->generate_text( $selected_model, $post_content, $summary_prompt, 'summary' );
		if ( ! is_wp_error( $summary_result ) ) {
			$summary = $summary_result;
		} else {
			wp_send_json_error( array( 'message' => $summary_result->get_error_message() ) );
		}

		// Return success with generated text
		wp_send_json_success( array(
			'excerpt' => $excerpt,
			'summary' => $summary,
		) );
	}
}