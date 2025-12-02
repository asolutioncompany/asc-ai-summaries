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
		'none' => 'None (Manual)',
		'gpt-5-mini' => 'ChatGPT 5 Mini (gpt-5-mini)',
		'gpt-5-nano' => 'ChatGPT 5 Nano (gpt-5-nano)',
	);

	/**
	 * Default settings.
	 *
	 * @var array
	 */
	private static array $default_settings = array(
		'ai_model' => 'none',
		'openai_api_key' => '',
		'sync_ai_excerpt_to_post_excerpt' => 1,
		'excerpt_word_length' => Admin\Admin::DEFAULT_EXCERPT_WORD_LENGTH,
		'summary_word_length' => Admin\Admin::DEFAULT_SUMMARY_WORD_LENGTH,
		'prose_style' => 'Write the summary in the style of the article.',
		'post_types' => array( 'post' ),
		'show_excerpt' => 1,
		'show_summary' => 1,
		'style' => 'block',
		'block_css' => '',
		'block_excerpt_title' => '',
		'block_summary_title' => 'Summary',
		'writer_css' => '',
		'writer_excerpt_title' => '',
		'writer_summary_title' => '',
		'card_css' => '',
		'card_excerpt_title' => '',
		'card_summary_title' => 'Summary',
		'tab_css' => '',
		'tab_excerpt_title' => 'Excerpt',
		'tab_summary_title' => 'Summary',
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
	 * @return array
	 */
	public static function get_ai_models(): array {
		return self::$ai_models;
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

		// Initialize public functionality
		$this->init_public();
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
	 * Initialize public functionality.
	 *
	 * @return void
	 */
	private function init_public(): void {
		// Enqueue public assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );

		// Initialize Public class
		new Public\Front();
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

		// Enqueue public CSS
		wp_enqueue_style(
			'asc_ais_public',
			$plugin_url . 'assets/css/public.css',
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

		// Enqueue public JavaScript with jQuery as dependency
		wp_enqueue_script(
			'asc_ais_public',
			$plugin_url . 'assets/js/public.js',
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
