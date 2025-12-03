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
		$ai_models = Settings::get_ai_models();
		$selected_model = $settings['ai_model'] ?? $defaults['ai_model'];
		$model_label = $ai_models[$selected_model] ?? $ai_models[$defaults['ai_model']];

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
						disabled
					>
						<?php esc_html_e( 'Generate AI Summaries', 'asc-ai-summaries' ); ?>
					</button>
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
}