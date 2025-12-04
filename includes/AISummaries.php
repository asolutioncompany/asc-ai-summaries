<?php
/**
 * AI Summaries Main Class
 *
 * Main plugin class that handles initialization and lifecycle hooks.
 *
 * @package: asc-ai-summaries
 * @since: 0.1.0
 */

declare( strict_types = 1 );

namespace ASolutionCompany\AISummaries;

/**
 * Main AI Summaries Plugin Class
 */
class AISummaries {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '0.1.0';

	/**
	 * Available AI models.
	 *
	 * @var array
	 */
	private static array $ai_models = array(
		'none' => array(
			'label' => 'None (Manual)',
			'provider' => 'none',
			'model' => '',
		),
		'huggingface-open-ai-gpt-oss-20b' => array(
			'label' => 'Hugging Face (openai/gpt-oss-20b)',
			'provider' => 'huggingface',
			'model' => 'openai/gpt-oss-20b',
		),
		'huggingface-open-ai-gpt-oss-120b' => array(
			'label' => 'Hugging Face (openai/gpt-oss-120b)',
			'provider' => 'huggingface',
			'model' => 'openai/gpt-oss-120b',
		),
		'openai-gpt-5-nano' => array(
			'label' => 'OpenAI (gpt-5-nano)',
			'provider' => 'openai',
			'model' => 'gpt-5-nano',
		),
		'openai-gpt-5-mini' => array(
			'label' => 'OpenAI (gpt-5-mini)',
			'provider' => 'openai',
			'model' => 'gpt-5-mini',
		),
		'openai-gpt-5' => array(
			'label' => 'OpenAI (gpt-5)',
			'provider' => 'openai',
			'model' => 'gpt-5',
		),
		'anthropic-haiku-3' => array(
			'label' => 'Anthropic (claude-haiku-3)',
			'provider' => 'openai',
			'model' => 'haiku-3',
		),
		'anthropic-haiku-3.5' => array(
			'label' => 'Anthropic (claude-haiku-3.5)',
			'provider' => 'openai',
			'model' => 'haiku-3.5',
		),
		'anthropic-haiku-4.5' => array(
			'label' => 'Anthropic (claude-haiku-4.5)',
			'provider' => 'openai',
			'model' => 'haiku-4.5',
		),
		'anthropic-sonnet-4.5' => array(
			'label' => 'Anthropic (claude-sonnet-4.5)',
			'provider' => 'openai',
			'model' => 'sonnet-4',
		),
		'google-gemma-3' => array(
			'label' => 'Google (gemma-3)',
			'provider' => 'google',
			'model' => 'gemma-3',
		),
		'google-gemini-2.5-flash-lite' => array(
			'label' => 'Google (gemini-2.5-flash-lite)',
			'provider' => 'google',
			'model' => 'gemini-2.5-flash-lite',
		),
		'google-gemini-2.5-flash' => array(
			'label' => 'Google (gemini-2.5-flash)',
			'provider' => 'google',
			'model' => 'gemini-2.5-flash',
		),
		'google-gemini-2.5-pro' => array(
			'label' => 'Google (gemini-2.5-pro)',
			'provider' => 'google',
			'model' => 'gemini-2.5-pro',
		),
	);

	/**
	 * Default settings.
	 *
	 * @var array
	 */
	private static array $default_settings = array(
		'ai_model' => 'none',
		'huggingface_api_key' => '',
		'openai_api_key' => '',
		'anthropic_api_key' => '',
		'google_api_key' => '',
		'sync_ai_excerpt_to_post_excerpt' => 1,
		'excerpt_prompt' => 'Write 30 word excerpt of this article:',
		'summary_prompt' => 'Write 90 word summary of this article:',
		'post_types' => array( 'post' ),
		'show_excerpt' => 1,
		'show_summary' => 1,
		'show_on_non_singular' => 0,
		'style' => 'block',
		'block_css' => '',
		'block_excerpt_title' => '',
		'block_summary_title' => 'Summary',
		'writer_css' => '',
		'writer_excerpt_title' => '',
		'writer_summary_title' => 'Summary',
		'card_css' => '',
		'card_excerpt_title' => '',
		'card_summary_title' => 'Summary',
		'tab_css' => '',
		'tab_excerpt_title' => 'Short Summary',
		'tab_summary_title' => 'Long Summary',
	);

	/**
	 * Plugin instance.
	 *
	 * @var AISummaries|null
	 */
	private static ?AISummaries $instance = null;

	/**
	 * Get plugin instance.
	 *
	 * @return AISummaries
	 */
	public static function get_instance(): AISummaries {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get plugin settings.
	 *
	 * @return array
	 */
	public static function get_settings(): array {
		$settings = get_option( Admin\Admin::OPTION_NAME, array() );
		$settings = wp_parse_args( $settings, self::$default_settings );

		return $settings;
	}

	/**
	 * Get available AI models.
	 *
	 * @return array Array of model slugs => model data objects.
	 */
	public static function get_ai_models(): array {
		return self::$ai_models;
	}

	/**
	 * Get AI models as a simple key => label array for dropdowns.
	 *
	 * @return array Array of model slugs => labels.
	 */
	public static function get_ai_models_labels(): array {
		$labels = array();
		foreach ( self::$ai_models as $slug => $model_data ) {
			$labels[ $slug ] = $model_data['label'];
		}
		return $labels;
	}

	/**
	 * Get model data by slug.
	 *
	 * @param string $slug The model slug.
	 * @return array|null Model data or null if not found.
	 */
	public static function get_model_data( string $slug ): ?array {
		return self::$ai_models[ $slug ] ?? null;
	}

	/**
	 * Get default settings.
	 *
	 * @return array
	 */
	public static function get_default_settings(): array {
		return self::$default_settings;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	private function init(): void {
		// Load text domain for translations
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		// Initialize admin functionality
		if ( is_admin() ) {
			$this->init_admin();
		}

		// Initialize front functionality
		$this->init_front();
	}

	/**
	 * Initialize admin functionality.
	 *
	 * @return void
	 */
	private function init_admin(): void {
		// Enqueue admin assets
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Initialize Admin class
		new Admin\Admin();
	}

	/**
	 * Initialize front functionality.
	 *
	 * @return void
	 */
	private function init_front(): void {
		// Enqueue front assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );

		// Initialize Front class
		new Front\Front();
	}

	/**
	 * Load plugin text domain.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'asc-ai-summaries',
			false,
			dirname( plugin_basename( __FILE__ ), 2 ) . '/languages'
		);
	}

	/**
	 * Get the plugin URL.
	 *
	 * @return string
	 */
	private function get_plugin_url(): string {
		return plugin_dir_url( dirname( __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	private function get_plugin_path(): string {
		return plugin_dir_path( dirname( __FILE__ ) );
	}

	/**
	 * Enqueue admin assets (CSS and JavaScript).
	 *
	 * @return void
	 */
	public function enqueue_admin_assets(): void {
		$plugin_url = $this->get_plugin_url();
		$version    = self::VERSION;

		// Enqueue admin CSS
		wp_enqueue_style(
			'asc_ais_admin',
			$plugin_url . 'assets/css/admin.css',
			array(),
			$version
		);

		// Enqueue admin JavaScript with jQuery as dependency
		wp_enqueue_script(
			'asc_ais_admin',
			$plugin_url . 'assets/js/admin.js',
			array( 'jquery' ),
			$version,
			true
		);

		// Localize script for AJAX
		wp_localize_script(
			'asc_ais_admin',
			'ascAISummaries',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'asc_ais_generate_summaries' ),
				'i18n' => array(
					'generating' => __( 'Generating...', 'asc-ai-summaries' ),
					'success' => __( 'Success!', 'asc-ai-summaries' ),
					'error' => __( 'An error occurred.', 'asc-ai-summaries' ),
				),
			)
		);
	}

	/**
	 * Enqueue public assets (CSS and JavaScript).
	 *
	 * @return void
	 */
	public function enqueue_public_assets(): void {
		$plugin_url = $this->get_plugin_url();
		$plugin_path = $this->get_plugin_path();
		$version    = self::VERSION;

		// Enqueue front CSS
		wp_enqueue_style(
			'asc_ais_public',
			$plugin_url . 'assets/css/front.css',
			array(),
			$version
		);

		// Enqueue style-specific CSS files
		$styles = array( 'block', 'writer', 'card', 'tab' );

		foreach ( $styles as $style ) {
			$css_file = $plugin_path . 'assets/css/' . $style . '.css';
			$default_css_file = $plugin_path . 'assets/css/' . $style . '-default.css';

			if ( file_exists( $css_file ) ) {
				// Enqueue user CSS file if it exists
				wp_enqueue_style(
					'asc_ais_' . $style . '_style',
					$plugin_url . 'assets/css/' . $style . '.css',
					array(),
					filemtime( $css_file )
				);
			} else {
				// Enqueue default CSS file if user file doesn't exist
				if ( file_exists( $default_css_file ) ) {
					wp_enqueue_style(
						'asc_ais_' . $style . '_default_style',
						$plugin_url . 'assets/css/' . $style . '-default.css',
						array(),
						filemtime( $default_css_file )
					);
				}
			}
		}

		// Enqueue front JavaScript with jQuery as dependency
		wp_enqueue_script(
			'asc_ais_public',
			$plugin_url . 'assets/js/front.js',
			array( 'jquery' ),
			$version,
			true
		);
	}

	/**
	 * Activation hook callback.
	 *
	 * @return void
	 */
	public static function activate(): void {
		// Flush rewrite rules if needed
		flush_rewrite_rules();

		// Set default options if needed
		add_option( 'asc_ais_version', self::VERSION );
	}

	/**
	 * Deactivation hook callback.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		// Flush rewrite rules if needed
		flush_rewrite_rules();

		// Clean up temporary data if needed
	}

	/**
	 * Uninstall hook callback.
	 *
	 * @return void
	 */
	public static function uninstall(): void {
		// Delete options
		delete_option( 'asc_ais_version' );
		delete_option( 'asc_ais_settings' );

		// Clean up any other data
	}
}
