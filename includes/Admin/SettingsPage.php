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
			'block_margins',
			__( 'Block Margins', 'asc-ai-summaries' ),
			array( $this, 'render_block_margins_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		add_settings_field(
			'block_foreground_color',
			__( 'Block Foreground Color', 'asc-ai-summaries' ),
			array( $this, 'render_block_foreground_color_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		add_settings_field(
			'block_background_color',
			__( 'Block Background Color', 'asc-ai-summaries' ),
			array( $this, 'render_block_background_color_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

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

		// Add Writer fields
		add_settings_field(
			'writer_margins',
			__( 'Writer Margins', 'asc-ai-summaries' ),
			array( $this, 'render_writer_margins_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		add_settings_field(
			'writer_foreground_color',
			__( 'Writer Foreground Color', 'asc-ai-summaries' ),
			array( $this, 'render_writer_foreground_color_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		add_settings_field(
			'writer_background_color',
			__( 'Writer Background Color', 'asc-ai-summaries' ),
			array( $this, 'render_writer_background_color_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

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

		// Add Card fields
		add_settings_field(
			'card_margins',
			__( 'Card Margins', 'asc-ai-summaries' ),
			array( $this, 'render_card_margins_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		add_settings_field(
			'card_foreground_color',
			__( 'Card Foreground Color', 'asc-ai-summaries' ),
			array( $this, 'render_card_foreground_color_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		add_settings_field(
			'card_background_color',
			__( 'Card Background Color', 'asc-ai-summaries' ),
			array( $this, 'render_card_background_color_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

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

		// Add Tab fields
		add_settings_field(
			'tab_margins',
			__( 'Tab Margins', 'asc-ai-summaries' ),
			array( $this, 'render_tab_margins_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		add_settings_field(
			'tab_foreground_color',
			__( 'Tab Foreground Color', 'asc-ai-summaries' ),
			array( $this, 'render_tab_foreground_color_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

		add_settings_field(
			'tab_background_color',
			__( 'Tab Background Color', 'asc-ai-summaries' ),
			array( $this, 'render_tab_background_color_field' ),
			Admin::PAGE_SLUG,
			'asc_ais_display_section'
		);

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
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @param array $input Raw input data.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( array $input ): array {
		$sanitized = array();

		/*
		 * AI model settings
		 */

		$ai_models = Settings::get_ai_models();

		// Sanitize AI model selection
		if ( isset( $input['ai_model'] ) && array_key_exists( $input['ai_model'], $ai_models ) ) {
			$sanitized['ai_model'] = sanitize_text_field( $input['ai_model'] );
		} else {
			$sanitized['ai_model'] = 'none';
		}

		// Sanitize API key (only if a ChatGPT model is selected)
		if ( isset( $input['openai_api_key'] ) ) {
			$sanitized['openai_api_key'] = sanitize_text_field( $input['openai_api_key'] );
		} else {
			$sanitized['openai_api_key'] = '';
		}

		// Sanitize sync excerpt setting
		$sanitized['sync_ai_excerpt_to_post_excerpt'] = isset( $input['sync_ai_excerpt_to_post_excerpt'] ) ? 1 : 0;

		// Sanitize excerpt word length
		if ( isset( $input['excerpt_word_length'] ) ) {
			$sanitized['excerpt_word_length'] = absint( $input['excerpt_word_length'] );
			if ( $sanitized['excerpt_word_length'] < 1 ) {
				$sanitized['excerpt_word_length'] = Admin::DEFAULT_EXCERPT_WORD_LENGTH;
			}
		} else {
			$sanitized['excerpt_word_length'] = Admin::DEFAULT_EXCERPT_WORD_LENGTH;
		}

		// Sanitize summary word length
		if ( isset( $input['summary_word_length'] ) ) {
			$sanitized['summary_word_length'] = absint( $input['summary_word_length'] );
			if ( $sanitized['summary_word_length'] < 1 ) {
				$sanitized['summary_word_length'] = Admin::DEFAULT_SUMMARY_WORD_LENGTH;
			}
		} else {
			$sanitized['summary_word_length'] = Admin::DEFAULT_SUMMARY_WORD_LENGTH;
		}

		// Sanitize prose style
		if ( isset( $input['prose_style'] ) ) {
			$sanitized['prose_style'] = sanitize_textarea_field( $input['prose_style'] );
		} else {
			$sanitized['prose_style'] = '';
		}

		/*
		 * Style settings
		 */

		// Sanitize post types
		if ( isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
			$valid_post_types = array_keys( get_post_types( array( 'public' => true ) ) );
			$sanitized['post_types'] = array_intersect( $input['post_types'], $valid_post_types );
		} else {
			$sanitized['post_types'] = array( 'post' );
		}

		// Sanitize show options
		$sanitized['show_excerpt'] = isset( $input['show_excerpt'] ) ? 1 : 0;
		$sanitized['show_summary'] = isset( $input['show_summary'] ) ? 1 : 0;

		// Sanitize style
		$valid_styles = array( 'block', 'writer', 'card', 'tab' );
		if ( isset( $input['style'] ) && in_array( $input['style'], $valid_styles, true ) ) {
			$sanitized['style'] = sanitize_text_field( $input['style'] );
		} else {
			$sanitized['style'] = 'block';
		}

		/*
		 * Block styles
		 */

		// Sanitize block margins
		if ( isset( $input['block_margins'] ) ) {
			$style_settings[ $style ]['block_margins'] = sanitize_text_field( $input['block_margins'] );
		} else {
			$style_settings[ $style ]['block_margins'] = '';
		}

		// Sanitize block foreground color (hex code)
		if ( isset( $input['block_foreground_color'] ) ) {
			$color = sanitize_text_field( $input['block_foreground_color'] );
			if ( preg_match( '/^#[0-9A-Fa-f]{6}$/', $color ) ) {
				$style_settings[ $style ]['block_foreground_color'] = $color;
			} else {
				$style_settings[ $style ]['block_foreground_color'] = '#000000';
			}
		} else {
			$style_settings[ $style ]['block_foreground_color'] = '#000000';
		}

		// Sanitize block background color (hex code)
		if ( isset( $input['block_background_color'] ) ) {
			$color = sanitize_text_field( $input['block_background_color'] );
			if ( preg_match( '/^#[0-9A-Fa-f]{6}$/', $color ) ) {
				$style_settings[ $style ]['block_background_color'] = $color;
			} else {
				$style_settings[ $style ]['block_background_color'] = '#ffffff';
			}
		} else {
			$style_settings[ $style ]['block_background_color'] = '#ffffff';
		}

		// Sanitize block excerpt title
		if ( isset( $input['block_excerpt_title'] ) ) {
			$style_settings[ $style ]['block_excerpt_title'] = sanitize_text_field( $input['block_excerpt_title'] );
		} else {
			$style_settings[ $style ]['block_excerpt_title'] = '';
		}

		// Sanitize block summary title
		if ( isset( $input['block_summary_title'] ) ) {
			$style_settings[ $style ]['block_summary_title'] = sanitize_text_field( $input['block_summary_title'] );
		} else {
			$style_settings[ $style ]['block_summary_title'] = '';
		}

		/*
		 * Writer styles
		 */

		// Sanitize writer margins
		if ( isset( $input['writer_margins'] ) ) {
			$style_settings[ $style ]['writer_margins'] = sanitize_text_field( $input['writer_margins'] );
		} else {
			$style_settings[ $style ]['writer_margins'] = '';
		}

		// Sanitize writer foreground color (hex code)
		if ( isset( $input['writer_foreground_color'] ) ) {
			$color = sanitize_text_field( $input['writer_foreground_color'] );
			if ( preg_match( '/^#[0-9A-Fa-f]{6}$/', $color ) ) {
				$style_settings[ $style ]['writer_foreground_color'] = $color;
			} else {
				$style_settings[ $style ]['writer_foreground_color'] = '#000000';
			}
		} else {
			$style_settings[ $style ]['writer_foreground_color'] = '#000000';
		}

		// Sanitize writer background color (hex code)
		if ( isset( $input['writer_background_color'] ) ) {
			$color = sanitize_text_field( $input['writer_background_color'] );
			if ( preg_match( '/^#[0-9A-Fa-f]{6}$/', $color ) ) {
				$style_settings[ $style ]['writer_background_color'] = $color;
			} else {
				$style_settings[ $style ]['writer_background_color'] = '#ffffff';
			}
		} else {
			$style_settings[ $style ]['writer_background_color'] = '#ffffff';
		}

		// Sanitize writer excerpt title
		if ( isset( $input['writer_excerpt_title'] ) ) {
			$style_settings[ $style ]['writer_excerpt_title'] = sanitize_text_field( $input['block_excerpt_title'] );
		} else {
			$style_settings[ $style ]['writer_excerpt_title'] = '';
		}

		// Sanitize writer summary title
		if ( isset( $input['writer_summary_title'] ) ) {
			$style_settings[ $style ]['writer_summary_title'] = sanitize_text_field( $input['block_summary_title'] );
		} else {
			$style_settings[ $style ]['writer_summary_title'] = '';
		}

		/*
		 * Card styles
		 */

		// Sanitize card margins
		if ( isset( $input['card_margins'] ) ) {
			$style_settings[ $style ]['card_margins'] = sanitize_text_field( $input['card_margins'] );
		} else {
			$style_settings[ $style ]['card_margins'] = '';
		}

		// Sanitize card foreground color (hex code)
		if ( isset( $input['card_foreground_color'] ) ) {
			$color = sanitize_text_field( $input['card_foreground_color'] );
			if ( preg_match( '/^#[0-9A-Fa-f]{6}$/', $color ) ) {
				$style_settings[ $style ]['card_foreground_color'] = $color;
			} else {
				$style_settings[ $style ]['card_foreground_color'] = '#000000';
			}
		} else {
			$style_settings[ $style ]['card_foreground_color'] = '#000000';
		}

		// Sanitize card background color (hex code)
		if ( isset( $input['card_background_color'] ) ) {
			$color = sanitize_text_field( $input['card_background_color'] );
			if ( preg_match( '/^#[0-9A-Fa-f]{6}$/', $color ) ) {
				$style_settings[ $style ]['card_background_color'] = $color;
			} else {
				$style_settings[ $style ]['card_background_color'] = '#ffffff';
			}
		} else {
			$style_settings[ $style ]['card_background_color'] = '#ffffff';
		}

		// Sanitize card excerpt title
		if ( isset( $input['card_excerpt_title'] ) ) {
			$style_settings[ $style ]['card_excerpt_title'] = sanitize_text_field( $input['block_excerpt_title'] );
		} else {
			$style_settings[ $style ]['card_excerpt_title'] = '';
		}

		// Sanitize card summary title
		if ( isset( $input['card_summary_title'] ) ) {
			$style_settings[ $style ]['card_summary_title'] = sanitize_text_field( $input['block_summary_title'] );
		} else {
			$style_settings[ $style ]['card_summary_title'] = '';
		}

		/*
		 * Tab styles
		 */

		// Sanitize card margins
		if ( isset( $input['tab_margins'] ) ) {
			$style_settings[ $style ]['tab_margins'] = sanitize_text_field( $input['tab_margins'] );
		} else {
			$style_settings[ $style ]['tab_margins'] = '';
		}

		// Sanitize tab foreground color (hex code)
		if ( isset( $input['tab_foreground_color'] ) ) {
			$color = sanitize_text_field( $input['tab_foreground_color'] );
			if ( preg_match( '/^#[0-9A-Fa-f]{6}$/', $color ) ) {
				$style_settings[ $style ]['tab_foreground_color'] = $color;
			} else {
				$style_settings[ $style ]['tab_foreground_color'] = '#000000';
			}
		} else {
			$style_settings[ $style ]['tab_foreground_color'] = '#000000';
		}

		// Sanitize tab background color (hex code)
		if ( isset( $input['tab_background_color'] ) ) {
			$color = sanitize_text_field( $input['tab_background_color'] );
			if ( preg_match( '/^#[0-9A-Fa-f]{6}$/', $color ) ) {
				$style_settings[ $style ]['tab_background_color'] = $color;
			} else {
				$style_settings[ $style ]['tab_background_color'] = '#ffffff';
			}
		} else {
			$style_settings[ $style ]['tab_background_color'] = '#ffffff';
		}

		// Sanitize tab excerpt title
		if ( isset( $input['tab_excerpt_title'] ) ) {
			$style_settings[ $style ]['tab_excerpt_title'] = sanitize_text_field( $input['tab_excerpt_title'] );
		} else {
			$style_settings[ $style ]['tab_excerpt_title'] = '';
		}

		// Sanitize tab summary title
		if ( isset( $input['tab_summary_title'] ) ) {
			$style_settings[ $style ]['tab_summary_title'] = sanitize_text_field( $input['tab_summary_title'] );
		} else {
			$style_settings[ $style ]['tab_summary_title'] = '';
		}

		return $sanitized;
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
		$ai_models = Settings::get_ai_models();
		$selected = $settings['ai_model'] ?? 'none';

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
		$api_key  = $settings['openai_api_key'] ?? '';

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
				<?php echo empty( $api_key ) ? 'readonly' : ''; ?>
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
		$enabled  = isset( $settings['sync_ai_excerpt_to_post_excerpt'] ) ? (bool) $settings['sync_ai_excerpt_to_post_excerpt'] : true;

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
		$length   = isset( $settings['excerpt_word_length'] ) ? absint( $settings['excerpt_word_length'] ) : Admin::DEFAULT_EXCERPT_WORD_LENGTH;

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
		$length   = isset( $settings['summary_word_length'] ) ? absint( $settings['summary_word_length'] ) : Admin::DEFAULT_SUMMARY_WORD_LENGTH;

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
		$prose_style = $settings['prose_style'] ?? '';

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
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$selected   = isset( $settings['post_types'] ) && is_array( $settings['post_types'] ) ? $settings['post_types'] : array( 'post' );

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
		$show_excerpt = isset( $settings['show_excerpt'] ) ? (bool) $settings['show_excerpt'] : false;
		$show_summary = isset( $settings['show_summary'] ) ? (bool) $settings['show_summary'] : true;

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
		$styles   = array(
			'block'  => __( 'Block', 'asc-ai-summaries' ),
			'writer' => __( 'Writer', 'asc-ai-summaries' ),
			'card'   => __( 'Card', 'asc-ai-summaries' ),
			'tab'    => __( 'Tab', 'asc-ai-summaries' ),
		);
		$selected = $settings['style'] ?? 'block';

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
	 * Render Block Margins field
	 *
	 * @return void
	 */
	public function render_block_margins_field(): void {
		$settings = Settings::get_settings();
		$margins = $settings['block_margins'] ?? '';

		?>
		<div id="asc-ais-block-margins-wrapper">
			<input
				type="text"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[block_margins]' ); ?>"
 				id="asc-ais-block-margins"
				value="<?php echo esc_attr( $margins ); ?>"
				class="regular-text"
				placeholder="<?php esc_attr_e( 'e.g., 10px 20px', 'asc-ai-summaries' ); ?>"
			/>
			<p class="description">
				<?php esc_html_e( 'Set margins for the summary display (CSS margin format: top right bottom left or top/bottom left/right).', 'asc-ai-summaries' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Block Foreground Color field
	 *
	 * @return void
	 */
	public function render_block_foreground_color_field(): void {
		$settings = Settings::get_settings();
		$color = $settings['block_foreground_color'] ?? '';

		?>
		<div id="asc-ais-block-foreground-color-wrapper">
			<input
				type="text"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[block_foreground_color]' ); ?>"
 				id="asc-ais-block-foreground-color"
				value="<?php echo esc_attr( $color ); ?>"
				class="regular-text"
				placeholder="#000000"
				pattern="^#[0-9A-Fa-f]{6}$"
				maxlength="7"
			/>
			<p class="description">
				<?php esc_html_e( 'Set the foreground (text) color using a hex code (e.g., #000000).', 'asc-ai-summaries' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Block Background Color field
	 *
	 * @return void
	 */
	public function render_block_background_color_field(): void {
		$settings = Settings::get_settings();
		$color = $settings['block_background_color'] ?? '';

		?>
		<div id="asc-ais-block-background-color-wrapper">
			<input
				type="text"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[block_background_color]' ); ?>"
 				id="asc-ais-block-background-color"
				value="<?php echo esc_attr( $color ); ?>"
				class="regular-text"
				placeholder="#ffffff"
				pattern="^#[0-9A-Fa-f]{6}$"
				maxlength="7"
			/>
			<p class="description">
				<?php esc_html_e( 'Set the background (text) color using a hex code (e.g., #ffffff).', 'asc-ai-summaries' ); ?>
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
		$title = $settings['block_excerpt_title'] ?? '';

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
		$title = $settings['block_summary_title'] ?? '';

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
	 * Render Writer Margins field
	 *
	 * @return void
	 */
	public function render_writer_margins_field(): void {
		$settings = Settings::get_settings();
		$margins = $settings['writer_margins'] ?? '';

		?>
		<div id="asc-ais-writer-margins-wrapper">
			<input
				type="text"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[writer_margins]' ); ?>"
 				id="asc-ais-writer-margins"
				value="<?php echo esc_attr( $margins ); ?>"
				class="regular-text"
				placeholder="<?php esc_attr_e( 'e.g., 10px 20px', 'asc-ai-summaries' ); ?>"
			/>
			<p class="description">
				<?php esc_html_e( 'Set margins for the summary display (CSS margin format: top right bottom left or top/bottom left/right).', 'asc-ai-summaries' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Writer Foreground Color field
	 *
	 * @return void
	 */
	public function render_writer_foreground_color_field(): void {
		$settings = Settings::get_settings();
		$color = $settings['writer_foreground_color'] ?? '';

		?>
		<div id="asc-ais-writer-foreground-color-wrapper">
			<input
				type="text"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[writer_foreground_color]' ); ?>"
 				id="asc-ais-writer-foreground-color"
				value="<?php echo esc_attr( $color ); ?>"
				class="regular-text"
				placeholder="#000000"
				pattern="^#[0-9A-Fa-f]{6}$"
				maxlength="7"
			/>
			<p class="description">
				<?php esc_html_e( 'Set the foreground (text) color using a hex code (e.g., #000000).', 'asc-ai-summaries' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Writer Background Color field
	 *
	 * @return void
	 */
	public function render_writer_background_color_field(): void {
		$settings = Settings::get_settings();
		$color = $settings['writer_background_color'] ?? '';

		?>
		<div id="asc-ais-writer-background-color-wrapper">
			<input
				type="text"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[writer_background_color]' ); ?>"
 				id="asc-ais-writer-background-color"
				value="<?php echo esc_attr( $color ); ?>"
				class="regular-text"
				placeholder="#ffffff"
				pattern="^#[0-9A-Fa-f]{6}$"
				maxlength="7"
			/>
			<p class="description">
				<?php esc_html_e( 'Set the background (text) color using a hex code (e.g., #ffffff).', 'asc-ai-summaries' ); ?>
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
		$title = $settings['writer_excerpt_title'] ?? '';

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
		$title = $settings['writer_summary_title'] ?? '';

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
	 * Render Card Margins field
	 *
	 * @return void
	 */
	public function render_card_margins_field(): void {
		$settings = Settings::get_settings();
		$margins = $settings['card_margins'] ?? '';

		?>
		<div id="asc-ais-card-margins-wrapper">
			<input
				type="text"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[card_margins]' ); ?>"
 				id="asc-ais-card-margins"
				value="<?php echo esc_attr( $margins ); ?>"
				class="regular-text"
				placeholder="<?php esc_attr_e( 'e.g., 10px 20px', 'asc-ai-summaries' ); ?>"
			/>
			<p class="description">
				<?php esc_html_e( 'Set margins for the summary display (CSS margin format: top right bottom left or top/bottom left/right).', 'asc-ai-summaries' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Card Foreground Color field
	 *
	 * @return void
	 */
	public function render_card_foreground_color_field(): void {
		$settings = Settings::get_settings();
		$color = $settings['card_foreground_color'] ?? '';

		?>
		<div id="asc-ais-card-foreground-color-wrapper">
			<input
				type="text"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[card_foreground_color]' ); ?>"
 				id="asc-ais-card-foreground-color"
				value="<?php echo esc_attr( $color ); ?>"
				class="regular-text"
				placeholder="#000000"
				pattern="^#[0-9A-Fa-f]{6}$"
				maxlength="7"
			/>
			<p class="description">
				<?php esc_html_e( 'Set the foreground (text) color using a hex code (e.g., #000000).', 'asc-ai-summaries' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Card Background Color field
	 *
	 * @return void
	 */
	public function render_card_background_color_field(): void {
		$settings = Settings::get_settings();
		$color = $settings['card_background_color'] ?? '';

		?>
		<div id="asc-ais-card-background-color-wrapper">
			<input
				type="text"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[card_background_color]' ); ?>"
 				id="asc-ais-card-background-color"
				value="<?php echo esc_attr( $color ); ?>"
				class="regular-text"
				placeholder="#ffffff"
				pattern="^#[0-9A-Fa-f]{6}$"
				maxlength="7"
			/>
			<p class="description">
				<?php esc_html_e( 'Set the background (text) color using a hex code (e.g., #ffffff).', 'asc-ai-summaries' ); ?>
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
		$title = $settings['card_excerpt_title'] ?? '';

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
		$title = $settings['card_summary_title'] ?? '';

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
	 * Render Tab Margins field
	 *
	 * @return void
	 */
	public function render_tab_margins_field(): void {
		$settings = Settings::get_settings();
		$margins = $settings['tab_margins'] ?? '';

		?>
		<div id="asc-ais-tab-margins-wrapper">
			<input
				type="text"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[tab_margins]' ); ?>"
 				id="asc-ais-tab-margins"
				value="<?php echo esc_attr( $margins ); ?>"
				class="regular-text"
				placeholder="<?php esc_attr_e( 'e.g., 10px 20px', 'asc-ai-summaries' ); ?>"
			/>
			<p class="description">
				<?php esc_html_e( 'Set margins for the summary display (CSS margin format: top right bottom left or top/bottom left/right).', 'asc-ai-summaries' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Tab Foreground Color field
	 *
	 * @return void
	 */
	public function render_tab_foreground_color_field(): void {
		$settings = Settings::get_settings();
		$color = $settings['tab_foreground_color'] ?? '';

		?>
		<div id="asc-ais-tab-foreground-color-wrapper">
			<input
				type="text"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[tab_foreground_color]' ); ?>"
 				id="asc-ais-tab-foreground-color"
				value="<?php echo esc_attr( $color ); ?>"
				class="regular-text"
				placeholder="#000000"
				pattern="^#[0-9A-Fa-f]{6}$"
				maxlength="7"
			/>
			<p class="description">
				<?php esc_html_e( 'Set the foreground (text) color using a hex code (e.g., #000000).', 'asc-ai-summaries' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Tab Background Color field
	 *
	 * @return void
	 */
	public function render_tab_background_color_field(): void {
		$settings = Settings::get_settings();
		$color = $settings['tab_background_color'] ?? '';

		?>
		<div id="asc-ais-tab-background-color-wrapper">
			<input
				type="text"
				name="<?php echo esc_attr( Admin::OPTION_NAME . '[tab_background_color]' ); ?>"
 				id="asc-ais-tab-background-color"
				value="<?php echo esc_attr( $color ); ?>"
				class="regular-text"
				placeholder="#ffffff"
				pattern="^#[0-9A-Fa-f]{6}$"
				maxlength="7"
			/>
			<p class="description">
				<?php esc_html_e( 'Set the background (text) color using a hex code (e.g., #ffffff).', 'asc-ai-summaries' ); ?>
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
		$title = $settings['tab_excerpt_title'] ?? '';

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
		$title = $settings['tab_summary_title'] ?? '';

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