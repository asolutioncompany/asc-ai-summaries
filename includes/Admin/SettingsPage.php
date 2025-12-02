<?php
/**
 * Settings Page Class
 *
 * Handles the plugin settings page functionality.
 *
 * @package: asc-ai-summaries
 * @since: 0.1.0
 */

declare( strict_types = 1 );

namespace ASolutionCompany\AISummaries\Admin;

use ASolutionCompany\AISummaries\AISummaries as Settings;

/**
 * Settings Page Class
 */
class SettingsPage {
	/**
	 * Initialize the settings page.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize settings page hooks.
	 *
	 * @return void
	 */
	private function init(): void {
		// Add settings page
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );

		// Register settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Override CSS settings save to add file processing
		add_filter( 'pre_update_option_' . Admin::OPTION_NAME, array( $this, 'save_css_settings_with_files' ), 10, 2 );
	}

	/**
	 * Add settings page to WordPress admin menu.
	 *
	 * @return void
	 */
	public function add_settings_page(): void {
		add_options_page(
			__( 'AI Summaries Settings', 'asc-ai-summaries' ),
			__( 'AI Summaries', 'asc-ai-summaries' ),
			'manage_options',
			Admin::PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		// Register settings group
		register_setting(
			'asc_ais_settings_group',
			Admin::OPTION_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);

		// Add settings section
		add_settings_section(
			'asc_ais_main_section',
			__( 'AI Model Configuration', 'asc-ai-summaries' ),
			array( $this, 'render_section_description' ),
			Admin::PAGE_SLUG
		);

		// Add AI Model field
		add_settings_field(
			'ai_model',
			__( 'AI Model', 'asc-ai-summaries' ),
			array( $this, 'render_ai_model_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_main_section'
		);

		// Add API Key field
		add_settings_field(
			'openai_api_key',
			__( 'OpenAI API Key', 'asc-ai-summaries' ),
			array( $this, 'render_api_key_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_main_section'
		);

		// Add Sync Excerpt field
		add_settings_field(
			'sync_ai_excerpt_to_post_excerpt',
			__( 'Sync Post Excerpt', 'asc-ai-summaries' ),
			array( $this, 'render_sync_excerpt_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_main_section'
		);

		// Add Excerpt Word Length field
		add_settings_field(
			'excerpt_word_length',
			__( 'Excerpt Word Length', 'asc-ai-summaries' ),
			array( $this, 'render_excerpt_word_length_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_main_section'
		);

		// Add Summary Word Length field
		add_settings_field(
			'summary_word_length',
			__( 'Summary Word Length', 'asc-ai-summaries' ),
			array( $this, 'render_summary_word_length_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_main_section'
		);

		// Add Prose Style field
		add_settings_field(
			'prose_style',
			__( 'Prose Style', 'asc-ai-summaries' ),
			array( $this, 'render_prose_style_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_main_section'
		);

		// Add Display Settings section
		add_settings_section(
			'asc_ais_display_section',
			__( 'Display Settings', 'asc-ai-summaries' ),
			array( $this, 'render_display_section_description' ),
			Admin::PAGE_SLUG
		);

		// Add Post Types field
		add_settings_field(
			'post_types',
			__( 'Add Summaries To', 'asc-ai-summaries' ),
			array( $this, 'render_post_types_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		// Add Show Options field
		add_settings_field(
			'show_options',
			__( 'Show', 'asc-ai-summaries' ),
			array( $this, 'render_show_options_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		// Add Style field
		add_settings_field(
			'style',
			__( 'Style', 'asc-ai-summaries' ),
			array( $this, 'render_style_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		// Add Block fields
		add_settings_field(
			'block_excerpt_title',
			__( 'Block Excerpt Title', 'asc-ai-summaries' ),
			array( $this, 'render_block_excerpt_title_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		add_settings_field(
			'block_summary_title',
			__( 'Block Summary Title', 'asc-ai-summaries' ),
			array( $this, 'render_block_summary_title_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		add_settings_field(
			'block_css',
			__( 'Block CSS', 'asc-ai-summaries' ),
			array( $this, 'render_block_css_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		// Add Writer fields
		add_settings_field(
			'writer_excerpt_title',
			__( 'Writer Excerpt Title', 'asc-ai-summaries' ),
			array( $this, 'render_writer_excerpt_title_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		add_settings_field(
			'writer_summary_title',
			__( 'Writer Summary Title', 'asc-ai-summaries' ),
			array( $this, 'render_writer_summary_title_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		add_settings_field(
			'writer_css',
			__( 'Writer CSS', 'asc-ai-summaries' ),
			array( $this, 'render_writer_css_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		// Add Card fields
		add_settings_field(
			'card_excerpt_title',
			__( 'Card Excerpt Title', 'asc-ai-summaries' ),
			array( $this, 'render_card_excerpt_title_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		add_settings_field(
			'card_summary_title',
			__( 'Card Summary Title', 'asc-ai-summaries' ),
			array( $this, 'render_card_summary_title_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		add_settings_field(
			'card_css',
			__( 'Card CSS', 'asc-ai-summaries' ),
			array( $this, 'render_card_css_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		// Add Tab fields
		add_settings_field(
			'tab_excerpt_title',
			__( 'Tab Excerpt Title', 'asc-ai-summaries' ),
			array( $this, 'render_tab_excerpt_title_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		add_settings_field(
			'tab_summary_title',
			__( 'Tab Summary Title', 'asc-ai-summaries' ),
			array( $this, 'render_tab_summary_title_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		add_settings_field(
			'tab_css',
			__( 'Tab CSS', 'asc-ai-summaries' ),
			array( $this, 'render_tab_css_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @param array $input Raw input data.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( array $input ): array {
		$sanitized = array();
		$defaults = Settings::get_default_settings();

		/*
		 * AI model settings
		 */

		$ai_models = Settings::get_ai_models();

		// Sanitize AI model selection
		if ( isset( $input['ai_model'] ) && array_key_exists( $input['ai_model'], $ai_models ) ) {
			$sanitized['ai_model'] = sanitize_text_field( $input['ai_model'] );
		} else {
			$sanitized['ai_model'] = $defaults['ai_model'];
		}

		// Sanitize API key (only if a ChatGPT model is selected)
		if ( isset( $input['openai_api_key'] ) ) {
			$sanitized['openai_api_key'] = sanitize_text_field( $input['openai_api_key'] );
		} else {
			$sanitized['openai_api_key'] = $defaults['openai_api_key'];
		}

		// Sanitize sync excerpt setting
		$sanitized['sync_ai_excerpt_to_post_excerpt'] = 0;
		if ( isset( $input['sync_ai_excerpt_to_post_excerpt'] ) ) {
			$sanitized['sync_ai_excerpt_to_post_excerpt'] = 1;
		}

		// Sanitize excerpt word length
		if ( isset( $input['excerpt_word_length'] ) ) {
			$sanitized['excerpt_word_length'] = absint( $input['excerpt_word_length'] );
			if ( $sanitized['excerpt_word_length'] < 1 ) {
				$sanitized['excerpt_word_length'] = $defaults['excerpt_word_length'];
			}
		} else {
			$sanitized['excerpt_word_length'] = $defaults['excerpt_word_length'];
		}

		// Sanitize summary word length
		if ( isset( $input['summary_word_length'] ) ) {
			$sanitized['summary_word_length'] = absint( $input['summary_word_length'] );
			if ( $sanitized['summary_word_length'] < 1 ) {
				$sanitized['summary_word_length'] = $defaults['summary_word_length'];
			}
		} else {
			$sanitized['summary_word_length'] = $defaults['summary_word_length'];
		}

		// Sanitize prose style
		if ( isset( $input['prose_style'] ) ) {
			$sanitized['prose_style'] = sanitize_textarea_field( $input['prose_style'] );
		} else {
			$sanitized['prose_style'] = $defaults['prose_style'];
		}

		/*
		 * Style settings
		 */

		// Sanitize post types
		if ( isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
			$valid_post_types = array_keys( get_post_types( array( 'public' => true ) ) );
			$sanitized['post_types'] = array_intersect( $input['post_types'], $valid_post_types );
		} else {
			$sanitized['post_types'] = $defaults['post_types'];
		}

		// Sanitize show options
		$sanitized['show_excerpt'] = 0;
		if ( isset( $input['show_excerpt'] ) ) {
			$sanitized['show_excerpt'] = 1;
		}

		$sanitized['show_summary'] = 0;
		if ( isset( $input['show_summary'] ) ) {
			$sanitized['show_summary'] = 1;
		}

		// Sanitize style
		$valid_styles = array( 'block', 'writer', 'card', 'tab' );
		if ( isset( $input['style'] ) && in_array( $input['style'], $valid_styles, true ) ) {
			$sanitized['style'] = sanitize_text_field( $input['style'] );
		} else {
			$sanitized['style'] = $defaults['style'];
		}

		/*
		 * Block styles
		 */

		// Sanitize block CSS
		if ( isset( $input['block_css'] ) ) {
			$sanitized['block_css'] = wp_strip_all_tags( $input['block_css'] );
		} else {
			$sanitized['block_css'] = $defaults['block_css'];
		}

		// Sanitize block excerpt title
		if ( isset( $input['block_excerpt_title'] ) ) {
			$sanitized['block_excerpt_title'] = sanitize_text_field( $input['block_excerpt_title'] );
		} else {
			$sanitized['block_excerpt_title'] = $defaults['block_excerpt_title'];
		}

		// Sanitize block summary title
		if ( isset( $input['block_summary_title'] ) ) {
			$sanitized['block_summary_title'] = sanitize_text_field( $input['block_summary_title'] );
		} else {
			$sanitized['block_summary_title'] = $defaults['block_summary_title'];
		}

		/*
		 * Writer styles
		 */

		// Sanitize writer CSS
		if ( isset( $input['writer_css'] ) ) {
			$sanitized['writer_css'] = wp_strip_all_tags( $input['writer_css'] );
		} else {
			$sanitized['writer_css'] = $defaults['writer_css'];
		}

		// Sanitize writer excerpt title
		if ( isset( $input['writer_excerpt_title'] ) ) {
			$sanitized['writer_excerpt_title'] = sanitize_text_field( $input['writer_excerpt_title'] );
		} else {
			$sanitized['writer_excerpt_title'] = $defaults['writer_excerpt_title'];
		}

		// Sanitize writer summary title
		if ( isset( $input['writer_summary_title'] ) ) {
			$sanitized['writer_summary_title'] = sanitize_text_field( $input['writer_summary_title'] );
		} else {
			$sanitized['writer_summary_title'] = $defaults['writer_summary_title'];
		}

		/*
		 * Card styles
		 */

		// Sanitize card CSS
		if ( isset( $input['card_css'] ) ) {
			$sanitized['card_css'] = wp_strip_all_tags( $input['card_css'] );
		} else {
			$sanitized['card_css'] = $defaults['card_css'];
		}

		// Sanitize card excerpt title
		if ( isset( $input['card_excerpt_title'] ) ) {
			$sanitized['card_excerpt_title'] = sanitize_text_field( $input['card_excerpt_title'] );
		} else {
			$sanitized['card_excerpt_title'] = $defaults['card_excerpt_title'];
		}

		// Sanitize card summary title
		if ( isset( $input['card_summary_title'] ) ) {
			$sanitized['card_summary_title'] = sanitize_text_field( $input['card_summary_title'] );
		} else {
			$sanitized['card_summary_title'] = $defaults['card_summary_title'];
		}

		/*
		 * Tab styles
		 */

		// Sanitize tab CSS
		if ( isset( $input['tab_css'] ) ) {
			$sanitized['tab_css'] = wp_strip_all_tags( $input['tab_css'] );
		} else {
			$sanitized['tab_css'] = $defaults['tab_css'];
		}

		// Sanitize tab excerpt title
		if ( isset( $input['tab_excerpt_title'] ) ) {
			$sanitized['tab_excerpt_title'] = sanitize_text_field( $input['tab_excerpt_title'] );
		} else {
			$sanitized['tab_excerpt_title'] = $defaults['tab_excerpt_title'];
		}

		// Sanitize tab summary title
		if ( isset( $input['tab_summary_title'] ) ) {
			$sanitized['tab_summary_title'] = sanitize_text_field( $input['tab_summary_title'] );
		} else {
			$sanitized['tab_summary_title'] = $defaults['tab_summary_title'];
		}

		return $sanitized;
	}

	/**
	 * Save CSS settings with file processing.
	 *
	 * This method intercepts the option save to process CSS fields and save them to files,
	 * while still allowing the normal database save to proceed.
	 *
	 * @param array $value The new value being saved.
	 * @param array $old_value The previous value.
	 * @return array The value to save (unchanged, so normal save proceeds).
	 */
	public function save_css_settings_with_files( array $value, array $old_value ): array {
		$styles = array( 'block', 'writer', 'card', 'tab' );
		$plugin_path = plugin_dir_path( dirname( dirname( __FILE__ ) ) );
		$css_dir = $plugin_path . 'assets/css/';

		// Ensure the CSS directory exists
		if ( ! file_exists( $css_dir ) ) {
			wp_mkdir_p( $css_dir );
		}

		// Process each style's CSS
		foreach ( $styles as $style ) {
			$css_key = $style . '_css';
			$css_content = '';
			if ( isset( $value[$css_key] ) ) {
				$css_content = $value[$css_key];
			}

			$css_file = $css_dir . $style . '.css';

			// Save CSS to file if content exists, otherwise delete the file
			if ( ! empty( $css_content ) ) {
				// Save CSS content to file
				$result = file_put_contents( $css_file, $css_content );
				if ( false === $result ) {
					// Log error if file write fails
					error_log( 'Failed to write CSS file: ' . $css_file );
				}
			} else {
				// If CSS is empty, delete the file if it exists
				if ( file_exists( $css_file ) ) {
					$result = unlink( $css_file );
					if ( false === $result ) {
						// Log error if file deletion fails
						error_log( 'Failed to delete CSS file: ' . $css_file );
					}
				}
			}
		}

		// Return the value unchanged so normal database save proceeds
		return $value;
	}

	/**
	 * Render section description.
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__( 'Select the AI model to use for generating summaries. Choose "None" if you prefer to manually add summaries.', 'asc-ai-summaries' ) . '</p>';
	}

	/**
	 * Render AI Model field.
	 *
	 * @return void
	 */
	public function render_ai_model_field(): void {
		$settings = Settings::get_settings();
		$defaults = Settings::get_default_settings();
		$ai_models = Settings::get_ai_models();
		$selected = $settings['ai_model'] ?? $defaults['ai_model'];

		?>
		<select name="<?php echo esc_attr( Admin::OPTION_NAME . '[ai_model]' ); ?>" id="asc-ais-ai-model">
			<?php foreach ( $ai_models as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $selected, $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Render API Key field.
	 *
	 * @return void
	 */
	public function render_api_key_field(): void {
		$settings = Settings::get_settings();
		$defaults = Settings::get_default_settings();
		$api_key  = $settings['openai_api_key'] ?? $defaults['openai_api_key'];

		$readonly_attr = '';
		if ( empty( $api_key ) ) {
			$readonly_attr = 'readonly';
		}

		?>
		<div id="asc-ais-api-key-wrapper">
			<input
				type="password"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[openai_api_key]' ); ?>"
				id="asc-ais-openai-api-key"
				value="<?php echo esc_attr( $api_key ); ?>"
				class="regular-text"
				autocomplete="new-password"
				data-lpignore="true"
				data-form-type="other"
				data-1p-ignore="true"
				<?php echo esc_attr( $readonly_attr ); ?>
				placeholder="<?php esc_attr_e( 'Enter your OpenAI API key', 'asc-ai-summaries' ); ?>"
			/>
			<button type="button" class="button" id="asc-ais-toggle-api-key" style="margin-left: 5px;">
				<?php esc_html_e( 'Show', 'asc-ai-summaries' ); ?>
			</button>
			<p class="description">
				<?php esc_html_e( 'Enter your OpenAI API key. This is required when using ChatGPT models.', 'asc-ai-summaries' ); ?>
				<br>
				<a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Get your API key from OpenAI', 'asc-ai-summaries' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Sync Excerpt field.
	 *
	 * @return void
	 */
	public function render_sync_excerpt_field(): void {
		$settings = Settings::get_settings();
		$enabled = true;
		if ( isset( $settings['sync_ai_excerpt_to_post_excerpt'] ) ) {
			$enabled = (bool) $settings['sync_ai_excerpt_to_post_excerpt'];
		}

		?>
		<label for="asc-ais-sync-excerpt">
			<input
				type="checkbox"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[sync_ai_excerpt_to_post_excerpt]' ); ?>"
				id="asc-ais-sync-excerpt"
				value="1"
				<?php checked( $enabled, true ); ?>
			/>
			<?php esc_html_e( 'Automatically sync AI excerpt with the default WordPress excerpt field', 'asc-ai-summaries' ); ?>
		</label>
		<?php
	}

	/**
	 * Render Excerpt Word Length field.
	 *
	 * @return void
	 */
	public function render_excerpt_word_length_field(): void {
		$settings = Settings::get_settings();
		$length = Admin::DEFAULT_EXCERPT_WORD_LENGTH;
		if ( isset( $settings['excerpt_word_length'] ) ) {
			$length = absint( $settings['excerpt_word_length'] );
		}

		?>
		<input
			type="number"
			name="<?php echo esc_attr( Admin::OPTION_NAME . '[excerpt_word_length]' ); ?>"
			id="asc-ais-excerpt-word-length"
			value="<?php echo esc_attr( $length ); ?>"
			class="small-text"
			min="1"
			step="1"
		/>
		<p class="description">
			<?php
			printf(
				/* translators: %d: Default excerpt word length */
				esc_html__( 'Maximum number of words for the AI-generated excerpt. Default is %d words.', 'asc-ai-summaries' ),
				Admin::DEFAULT_EXCERPT_WORD_LENGTH
			);
			?>
		</p>
		<?php
	}

	/**
	 * Render Summary Word Length field.
	 *
	 * @return void
	 */
	public function render_summary_word_length_field(): void {
		$settings = Settings::get_settings();
		$length = Admin::DEFAULT_SUMMARY_WORD_LENGTH;
		if ( isset( $settings['summary_word_length'] ) ) {
			$length = absint( $settings['summary_word_length'] );
		}

		?>
		<input
			type="number"
			name="<?php echo esc_attr( Admin::OPTION_NAME . '[summary_word_length]' ); ?>"
			id="asc-ais-summary-word-length"
			value="<?php echo esc_attr( $length ); ?>"
			class="small-text"
			min="1"
			step="1"
		/>
		<p class="description">
			<?php
			printf(
				/* translators: %d: Default summary word length */
				esc_html__( 'Maximum number of words for the AI-generated summary. Default is %d words.', 'asc-ai-summaries' ),
				Admin::DEFAULT_SUMMARY_WORD_LENGTH
			);
			?>
		</p>
		<?php
	}

	/**
	 * Render Prose Style field.
	 *
	 * @return void
	 */
	public function render_prose_style_field(): void {
		$settings = Settings::get_settings();
		$defaults = Settings::get_default_settings();
		$prose_style = $settings['prose_style'] ?? $defaults['prose_style'];

		?>
		<textarea
			name="<?php echo esc_attr( Admin::OPTION_NAME . '[prose_style]' ); ?>"
			id="asc-ais-prose-style"
			rows="3"
			class="large-text"
			placeholder="<?php esc_attr_e( 'Describe to AI how to write your summaries', 'asc-ai-summaries' ); ?>"
		><?php echo esc_textarea( $prose_style ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'Describe to AI how to write your summaries. As an example, "Write the summary in the style of the article."', 'asc-ai-summaries' ); ?>
		</p>
		<?php
	}

	/**
	 * Render Display section description.
	 *
	 * @return void
	 */
	public function render_display_section_description(): void {
		echo '<p>' . esc_html__( 'Configure how AI summaries are displayed.', 'asc-ai-summaries' ) . '</p>';
	}

	/**
	 * Render Post Types field.
	 *
	 * @return void
	 */
	public function render_post_types_field(): void {
		$settings   = Settings::get_settings();
		$defaults   = Settings::get_default_settings();
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$selected = $defaults['post_types'];
		if ( isset( $settings['post_types'] ) && is_array( $settings['post_types'] ) ) {
			$selected = $settings['post_types'];
		}

		?>
		<fieldset>
			<?php foreach ( $post_types as $post_type ) : ?>
				<label for="asc-ais-post-type-<?php echo esc_attr( $post_type->name ); ?>" style="display: block; margin-bottom: 0.5em;">
					<input
						type="checkbox"
						name="<?php echo esc_attr( Admin::OPTION_NAME . '[post_types][]' ); ?>"
						id="asc-ais-post-type-<?php echo esc_attr( $post_type->name ); ?>"
						value="<?php echo esc_attr( $post_type->name ); ?>"
						<?php checked( in_array( $post_type->name, $selected, true ), true ); ?>
					/>
					<?php echo esc_html( $post_type->label ); ?>
				</label>
			<?php endforeach; ?>
		</fieldset>
		<p class="description">
			<?php esc_html_e( 'Select which post types should automatically display summaries.', 'asc-ai-summaries' ); ?>
		</p>
		<?php
	}

	/**
	 * Render Show Options field.
	 *
	 * @return void
	 */
	public function render_show_options_field(): void {
		$settings    = Settings::get_settings();
		$defaults    = Settings::get_default_settings();
		$show_excerpt = (bool) $defaults['show_excerpt'];
		if ( isset( $settings['show_excerpt'] ) ) {
			$show_excerpt = (bool) $settings['show_excerpt'];
		}

		$show_summary = (bool) $defaults['show_summary'];
		if ( isset( $settings['show_summary'] ) ) {
			$show_summary = (bool) $settings['show_summary'];
		}

		?>
		<fieldset>
			<label for="asc-ais-show-excerpt" style="display: block; margin-bottom: 0.5em;">
				<input
					type="checkbox"
					name="<?php echo esc_attr( Admin::OPTION_NAME . '[show_excerpt]' ); ?>"
					id="asc-ais-show-excerpt"
					value="1"
					<?php checked( $show_excerpt, true ); ?>
				/>
				<?php esc_html_e( 'Show Excerpt', 'asc-ai-summaries' ); ?>
			</label>
			<label for="asc-ais-show-summary" style="display: block; margin-bottom: 0.5em;">
				<input
					type="checkbox"
					name="<?php echo esc_attr( Admin::OPTION_NAME . '[show_summary]' ); ?>"
					id="asc-ais-show-summary"
					value="1"
					<?php checked( $show_summary, true ); ?>
				/>
				<?php esc_html_e( 'Show Summary', 'asc-ai-summaries' ); ?>
			</label>
		</fieldset>
		<p class="description">
			<?php esc_html_e( 'Select what content to display.', 'asc-ai-summaries' ); ?>
		</p>
		<?php
	}

	/**
	 * Render Style field.
	 *
	 * @return void
	 */
	public function render_style_field(): void {
		$settings = Settings::get_settings();
		$defaults = Settings::get_default_settings();
		$styles   = array(
			'block'  => __( 'Block', 'asc-ai-summaries' ),
			'writer' => __( 'Writer', 'asc-ai-summaries' ),
			'card'   => __( 'Card', 'asc-ai-summaries' ),
			'tab'    => __( 'Tab', 'asc-ai-summaries' ),
		);
		$selected = $settings['style'] ?? $defaults['style'];

		?>
		<select name="<?php echo esc_attr( Admin::OPTION_NAME . '[style]' ); ?>" id="asc-ais-style">
			<?php foreach ( $styles as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $selected, $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			<?php esc_html_e( 'Select the display style for summaries.', 'asc-ai-summaries' ); ?>
		</p>
		<?php
	}

	/*
	 * Render Block fields
	 */

	/**
	 * Render Block CSS field
	 *
	 * @return void
	 */
	public function render_block_css_field(): void {
		$settings = Settings::get_settings();
		$defaults = Settings::get_default_settings();
		$plugin_path = plugin_dir_path( dirname( dirname( __FILE__ ) ) );
		$css_file = $plugin_path . 'assets/css/block.css';
		$default_css_file = $plugin_path . 'assets/css/block-default.css';

		$css = '';
		if ( file_exists( $css_file ) ) {
			$css = file_get_contents( $css_file );
		} else {
			if ( file_exists( $default_css_file ) ) {
				$css = file_get_contents( $default_css_file );
			} else {
				$css = $settings['block_css'] ?? $defaults['block_css'];
			}
		}

		?>
		<div id="asc-ais-block-css-wrapper">
			<textarea
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[block_css]' ); ?>"
				id="asc-ais-block-css"
				rows="10"
				class="large-text code"
				placeholder="<?php esc_attr_e( 'Enter custom CSS for the block style', 'asc-ai-summaries' ); ?>"
			><?php echo esc_textarea( $css ); ?></textarea>
			<p class="description">
				<?php esc_html_e( 'Enter custom CSS for the block style. This CSS will be saved to a file and loaded on the frontend.', 'asc-ai-summaries' ); ?>
				<br>
				<?php esc_html_e( 'To restore defaults for the style, delete all the CSS (ctrl-a ctrl-x) for the style and save the settings. Defaults will be restored after saving the style.', 'asc-ai-summaries' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Block Excerpt Title field
	 *
	 * @return void
	 */
	public function render_block_excerpt_title_field(): void {
		$settings = Settings::get_settings();
		$defaults = Settings::get_default_settings();
		$title = $settings['block_excerpt_title'] ?? $defaults['block_excerpt_title'];

		?>
		<div id="asc-ais-block-excerpt-wrapper">
			<input
				type="text"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[block_excerpt_title]' ); ?>"
 				id="asc-ais-block-excerpt-title"
				value="<?php echo esc_attr( $title ); ?>"
				class="regular-text"
				placeholder="<?php esc_attr_e( 'Leave blank for no title', 'asc-ai-summaries' ); ?>"
			/>
			<p class="description">
				<?php esc_html_e( 'Title to display above the excerpt. Leave blank for no title.', 'asc-ai-summaries' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Block Summary Title field
	 *
	 * @return void
	 */
	public function render_block_summary_title_field(): void {
		$settings = Settings::get_settings();
		$defaults = Settings::get_default_settings();
		$title = $settings['block_summary_title'] ?? $defaults['block_summary_title'];

		?>
		<div id="asc-ais-block-summary-wrapper">
			<input
				type="text"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[block_summary_title]' ); ?>"
 				id="asc-ais-block-summary-title"
				value="<?php echo esc_attr( $title ); ?>"
				class="regular-text"
				placeholder="<?php esc_attr_e( 'Leave blank for no title', 'asc-ai-summaries' ); ?>"
			/>
			<p class="description">
				<?php esc_html_e( 'Title to display above the summary. Leave blank for no title.', 'asc-ai-summaries' ); ?>
			</p>
		</div>
		<?php
	}

	/*
	 * Render Writer fields
	 */

	/**
	 * Render Writer CSS field
	 *
	 * @return void
	 */
	public function render_writer_css_field(): void {
		$settings = Settings::get_settings();
		$defaults = Settings::get_default_settings();
		$plugin_path = plugin_dir_path( dirname( dirname( __FILE__ ) ) );
		$css_file = $plugin_path . 'assets/css/writer.css';
		$default_css_file = $plugin_path . 'assets/css/writer-default.css';

		$css = '';
		if ( file_exists( $css_file ) ) {
			$css = file_get_contents( $css_file );
		} else {
			if ( file_exists( $default_css_file ) ) {
				$css = file_get_contents( $default_css_file );
			} else {
				$css = $settings['writer_css'] ?? $defaults['writer_css'];
			}
		}

		?>
		<div id="asc-ais-writer-css-wrapper">
			<textarea
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[writer_css]' ); ?>"
				id="asc-ais-writer-css"
				rows="10"
				class="large-text code"
				placeholder="<?php esc_attr_e( 'Enter custom CSS for the writer style', 'asc-ai-summaries' ); ?>"
			><?php echo esc_textarea( $css ); ?></textarea>
			<p class="description">
				<?php esc_html_e( 'Enter custom CSS for the writer style. This CSS will be saved to a file and loaded on the frontend.', 'asc-ai-summaries' ); ?>
				<br>
				<?php esc_html_e( 'To restore defaults for the style, delete all the CSS (ctrl-a ctrl-x) for the style and save the settings. Defaults will be restored after saving the style.', 'asc-ai-summaries' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Writer Excerpt Title field
	 *
	 * @return void
	 */
	public function render_writer_excerpt_title_field(): void {
		$settings = Settings::get_settings();
		$defaults = Settings::get_default_settings();
		$title = $settings['writer_excerpt_title'] ?? $defaults['writer_excerpt_title'];

		?>
		<div id="asc-ais-writer-excerpt-wrapper">
			<input
				type="text"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[writer_excerpt_title]' ); ?>"
 				id="asc-ais-writer-excerpt-title"
				value="<?php echo esc_attr( $title ); ?>"
				class="regular-text"
				placeholder="<?php esc_attr_e( 'Leave blank for no title', 'asc-ai-summaries' ); ?>"
			/>
			<p class="description">
				<?php esc_html_e( 'Title to display above the excerpt. Leave blank for no title.', 'asc-ai-summaries' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Writer Summary Title field
	 *
	 * @return void
	 */
	public function render_writer_summary_title_field(): void {
		$settings = Settings::get_settings();
		$defaults = Settings::get_default_settings();
		$title = $settings['writer_summary_title'] ?? $defaults['writer_summary_title'];

		?>
		<div id="asc-ais-writer-summary-wrapper">
			<input
				type="text"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[writer_summary_title]' ); ?>"
 				id="asc-ais-writer-summary-title"
				value="<?php echo esc_attr( $title ); ?>"
				class="regular-text"
				placeholder="<?php esc_attr_e( 'Leave blank for no title', 'asc-ai-summaries' ); ?>"
			/>
			<p class="description">
				<?php esc_html_e( 'Title to display above the summary. Leave blank for no title.', 'asc-ai-summaries' ); ?>
			</p>
		</div>
		<?php
	}

	/*
	 * Render Card fields
	 */

	/**
	 * Render Card CSS field
	 *
	 * @return void
	 */
	public function render_card_css_field(): void {
		$settings = Settings::get_settings();
		$defaults = Settings::get_default_settings();
		$plugin_path = plugin_dir_path( dirname( dirname( __FILE__ ) ) );
		$css_file = $plugin_path . 'assets/css/card.css';
		$default_css_file = $plugin_path . 'assets/css/card-default.css';

		$css = '';
		if ( file_exists( $css_file ) ) {
			$css = file_get_contents( $css_file );
		} else {
			if ( file_exists( $default_css_file ) ) {
				$css = file_get_contents( $default_css_file );
			} else {
				$css = $settings['card_css'] ?? $defaults['card_css'];
			}
		}

		?>
		<div id="asc-ais-card-css-wrapper">
			<textarea
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[card_css]' ); ?>"
				id="asc-ais-card-css"
				rows="10"
				class="large-text code"
				placeholder="<?php esc_attr_e( 'Enter custom CSS for the card style', 'asc-ai-summaries' ); ?>"
			><?php echo esc_textarea( $css ); ?></textarea>
			<p class="description">
				<?php esc_html_e( 'Enter custom CSS for the card style. This CSS will be saved to a file and loaded on the frontend.', 'asc-ai-summaries' ); ?>
				<br>
				<?php esc_html_e( 'To restore defaults for the style, delete all the CSS (ctrl-a ctrl-x) for the style and save the settings. Defaults will be restored after saving the style.', 'asc-ai-summaries' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Card Excerpt Title field
	 *
	 * @return void
	 */
	public function render_card_excerpt_title_field(): void {
		$settings = Settings::get_settings();
		$defaults = Settings::get_default_settings();
		$title = $settings['card_excerpt_title'] ?? $defaults['card_excerpt_title'];

		?>
		<div id="asc-ais-card-excerpt-wrapper">
			<input
				type="text"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[card_excerpt_title]' ); ?>"
 				id="asc-ais-card-excerpt-title"
				value="<?php echo esc_attr( $title ); ?>"
				class="regular-text"
				placeholder="<?php esc_attr_e( 'Leave blank for no title', 'asc-ai-summaries' ); ?>"
			/>
			<p class="description">
				<?php esc_html_e( 'Title to display above the excerpt. Leave blank for no title.', 'asc-ai-summaries' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Card Summary Title field
	 *
	 * @return void
	 */
	public function render_card_summary_title_field(): void {
		$settings = Settings::get_settings();
		$defaults = Settings::get_default_settings();
		$title = $settings['card_summary_title'] ?? $defaults['card_summary_title'];

		?>
		<div id="asc-ais-card-summary-wrapper">
			<input
				type="text"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[card_summary_title]' ); ?>"
 				id="asc-ais-card-summary-title"
				value="<?php echo esc_attr( $title ); ?>"
				class="regular-text"
				placeholder="<?php esc_attr_e( 'Leave blank for no title', 'asc-ai-summaries' ); ?>"
			/>
			<p class="description">
				<?php esc_html_e( 'Title to display above the summary. Leave blank for no title.', 'asc-ai-summaries' ); ?>
			</p>
		</div>
		<?php
	}

	/*
	 * Render Tab fields
	 */

	/**
	 * Render Tab CSS field
	 *
	 * @return void
	 */
	public function render_tab_css_field(): void {
		$settings = Settings::get_settings();
		$defaults = Settings::get_default_settings();
		$plugin_path = plugin_dir_path( dirname( dirname( __FILE__ ) ) );
		$css_file = $plugin_path . 'assets/css/tab.css';
		$default_css_file = $plugin_path . 'assets/css/tab-default.css';

		$css = '';
		if ( file_exists( $css_file ) ) {
			$css = file_get_contents( $css_file );
		} else {
			if ( file_exists( $default_css_file ) ) {
				$css = file_get_contents( $default_css_file );
			} else {
				$css = $settings['tab_css'] ?? $defaults['tab_css'];
			}
		}

		?>
		<div id="asc-ais-tab-css-wrapper">
			<textarea
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[tab_css]' ); ?>"
				id="asc-ais-tab-css"
				rows="10"
				class="large-text code"
				placeholder="<?php esc_attr_e( 'Enter custom CSS for the tab style', 'asc-ai-summaries' ); ?>"
			><?php echo esc_textarea( $css ); ?></textarea>
			<p class="description">
				<?php esc_html_e( 'Enter custom CSS for the tab style. This CSS will be saved to a file and loaded on the frontend.', 'asc-ai-summaries' ); ?>
				<br>
				<?php esc_html_e( 'To restore defaults for the style, delete all the CSS (ctrl-a ctrl-x) for the style and save the settings. Defaults will be restored after saving the style.', 'asc-ai-summaries' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Tab Excerpt Title field
	 *
	 * @return void
	 */
	public function render_tab_excerpt_title_field(): void {
		$settings = Settings::get_settings();
		$defaults = Settings::get_default_settings();
		$title = $settings['tab_excerpt_title'] ?? $defaults['tab_excerpt_title'];

		?>
		<div id="asc-ais-tab-excerpt-wrapper">
			<input
				type="text"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[tab_excerpt_title]' ); ?>"
 				id="asc-ais-tab-excerpt-title"
				value="<?php echo esc_attr( $title ); ?>"
				class="regular-text"
				placeholder="<?php esc_attr_e( 'Leave blank for no title', 'asc-ai-summaries' ); ?>"
			/>
			<p class="description">
				<?php esc_html_e( 'Title to display above the excerpt. Leave blank for no title.', 'asc-ai-summaries' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Tab Summary Title field
	 *
	 * @return void
	 */
	public function render_tab_summary_title_field(): void {
		$settings = Settings::get_settings();
		$defaults = Settings::get_default_settings();
		$title = $settings['tab_summary_title'] ?? $defaults['tab_summary_title'];

		?>
		<div id="asc-ais-tab-summary-wrapper">
			<input
				type="text"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[tab_summary_title]' ); ?>"
 				id="asc-ais-tab-summary-title"
				value="<?php echo esc_attr( $title ); ?>"
				class="regular-text"
				placeholder="<?php esc_attr_e( 'Leave blank for no title', 'asc-ai-summaries' ); ?>"
			/>
			<p class="description">
				<?php esc_html_e( 'Title to display above the summary. Leave blank for no title.', 'asc-ai-summaries' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Show success message if settings were saved
		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error(
				'asc_ais_messages',
				'asc_ais_message',
				__( 'Settings saved successfully.', 'asc-ai-summaries' ),
				'success'
			);
		}
		settings_errors( 'asc_ais_messages' );

		// Get current settings
		$settings = Settings::get_settings();

		/*
		 * Tab panels
		 */

		// Tabs available
		$tabs = array( 'models', 'styles' );

		// Get active tab or default to 'models'
		$active_tab = 'models';
		if ( isset( $_GET['tab'] ) ) {
			$active_tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
		}

		// Set tab that is active with active class
		$active_tab_class = array(
			'models' => '',
			'styles' => '',
		);
		$active_tab_class[$active_tab] = ' nav-tab-active';

		// Hide all tabs with CSS that are not active
		$inactive_tab_css = array(
			'models' => '',
			'styles' => '',
		);

		foreach( $tabs as $tab ) {
			if ( $tab !== $active_tab ) {
				$inactive_tab_css[$tab] = ' style="display: none;"';
			}
		}

		/*
		 * Style panels
		 */

		// Styles available
		$styles = array( 'block', 'writer', 'card', 'tab' );

		// Get current style or default to 'block'
		$current_style = 'block';
		if ( $settings['style'] ) {
			$current_style = $settings['style'];
		}

		// Hide all style panels with CSS that are not active
		$inactive_panel_css = array(
			'block' => '',
			'writer' => '',
			'card' => '',
			'tab' => '',
		);

		foreach( $styles as $style ) {
			if ( $style !== $current_style ) {
				$inactive_panel_css[$style] = ' style="display: none;"';
			}
		}

		/*
		 * Render Settings Sections and Fields
		 */

		global $wp_settings_sections, $wp_settings_fields;

		?>
		<div class="wrap asc-ais-admin">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<nav class="nav-tab-wrapper asc-ais-tabs">
				<a class="nav-tab<?php echo $active_tab_class['models']; ?>" data-tab="models">
					<?php esc_html_e( 'Models', 'asc-ai-summaries' ); ?>
				</a>
				<a class="nav-tab<?php echo $active_tab_class['styles']; ?>" data-tab="styles">
					<?php esc_html_e( 'Styles', 'asc-ai-summaries' ); ?>
				</a>
			</nav>

			<form action="options.php" method="post">
				<?php
				settings_fields( 'asc_ais_settings_group' );
				?>

				<div class="asc-ais-tab-content asc-ais-models-tab"<?php echo $inactive_tab_css['models']; ?>>
					<table class="form-table" role="presentation">
						<tbody>
							<?php
							$section = $wp_settings_sections[ Admin::PAGE_SLUG ]['asc_ais_main_section'];

							if ( isset( $section['callback'] ) && is_callable( $section['callback'] ) ) {
								call_user_func( $section['callback'] );
							}

							foreach ( (array) $wp_settings_fields[ Admin::PAGE_SLUG ]['asc_ais_main_section'] as $field ) {
								?>
								<tr>
									<th scope="row"><?php echo esc_html( $field['title'] ); ?></th>
									<td>
										<?php call_user_func( $field['callback'], $field['args'] ?? array() ); ?>
									</td>
								</tr>
							<?php
							}
							?>
						</tbody>
					</table>
				</div>

				<div class="asc-ais-tab-content asc-ais-styles-tab"<?php echo $inactive_tab_css['styles']; ?>>
					<table class="form-table" role="presentation">
						<tbody>
							<?php
							$section = $wp_settings_sections[ Admin::PAGE_SLUG ]['asc_ais_display_section'];

							if ( isset( $section['callback'] ) && is_callable( $section['callback'] ) ) {
								call_user_func( $section['callback'] );
							}

							foreach ( (array) $wp_settings_fields[ Admin::PAGE_SLUG ]['asc_ais_display_section'] as $field ) {

								$tr_tag = '<tr>';

								// if field is a style field, add the style to the class of the tr tag to hide and show
								$first_part = strstr( $field['id'], '_', true );
								if ( in_array( $first_part, $styles, true ) ) {
									$tr_tag = '<tr class="asc-ais-tr-style-row asc-ais-tr-' . $first_part . '"';
									$tr_tag .= $inactive_panel_css[$first_part] . '>';
								}

								echo $tr_tag;
								?>
									<th scope="row"><?php echo esc_html( $field['title'] ); ?></th>
									<td>
										<?php call_user_func( $field['callback'], $field['args'] ?? array() ); ?>
									</td>
								</tr>
							<?php
							}
							?>
						</tbody>
					</table>
				</div>

				<?php
				submit_button( __( 'Save Settings', 'asc-ai-summaries' ) );
				?>
			</form>
		</div>
		<?php
	}
}