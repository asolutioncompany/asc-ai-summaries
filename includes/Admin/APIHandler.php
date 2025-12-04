<?php
/**
 * API Handler Class
 *
 * Handles API calls to different AI model providers.
 *
 * @package: asc-ai-summaries
 * @since: 0.1.0
 */

declare( strict_types = 1 );

namespace ASolutionCompany\AISummaries\Admin;

use ASolutionCompany\AISummaries\AISummaries as Settings;

/**
 * API Handler Class
 */
class APIHandler {

	/**
	 * Generate text using the specified model.
	 *
	 * @param string $model The model identifier.
	 * @param string $content The post content to summarize.
	 * @param string $prompt_template The prompt template to use.
	 * @param string $type The type of generation ('excerpt' or 'summary').
	 * @return string|\WP_Error The generated text or error.
	 */
	public function generate_text( string $model, string $content, string $prompt_template, string $type ): string|\WP_Error {
		// Clean content
		$cleaned_content = $this->extract_content( $content );

		// Build prompt from template
		$prompt = $this->build_prompt( $cleaned_content, $prompt_template );

		// Get model data to determine provider
		$model_data = Settings::get_model_data( $model );
		if ( ! $model_data ) {
			return new \WP_Error( 'unknown_model', __( 'Unknown AI model.', 'asc-ai-summaries' ) );
		}

		$provider = $model_data['provider'];

		if ( 'huggingface' === $provider ) {
			return $this->call_huggingface_api( $model, $prompt, $cleaned_content );
		}

		if ( 'openai' === $provider ) {
			return $this->call_openai_api( $model, $prompt );
		}

		if ( 'anthropic' === $provider ) {
			return $this->call_anthropic_api( $model, $prompt );
		}

		if ( 'google' === $provider ) {
			return $this->call_google_api( $model, $prompt );
		}

		return new \WP_Error( 'unknown_model', __( 'Unknown AI model.', 'asc-ai-summaries' ) );
	}

	/**
	 * Build the prompt for text generation.
	 *
	 * @param string $content The post content.
	 * @param string $prompt_template The prompt template.
	 * @return string The built prompt.
	 */
	private function build_prompt( string $content, string $prompt_template ): string {
		$prompt = $prompt_template . ' ' . $content;
		return $prompt;
	}

	/**
	 * Extract content from post content (strip HTML, etc.).
	 *
	 * @param string $content The post content.
	 * @return string The cleaned content.
	 */
	private function extract_content( string $content ): string {
		// Strip HTML tags and decode entities
		$content = wp_strip_all_tags( $content );
		$content = html_entity_decode( $content, ENT_QUOTES, 'UTF-8' );
		return trim( $content );
	}

	/**
	 * Call Hugging Face API.
	 *
	 * @param string $model The model identifier.
	 * @param string $prompt The prompt.
	 * @param string $content The cleaned content.
	 * @return string|\WP_Error The generated text or error.
	 */
	private function call_huggingface_api( string $model, string $prompt, string $content ): string|\WP_Error {
		$settings = Settings::get_settings();
		$api_key = $settings['huggingface_api_key'] ?? '';

		if ( empty( $api_key ) ) {
			return new \WP_Error( 'missing_api_key', __( 'Hugging Face API key is required.', 'asc-ai-summaries' ) );
		}

		// Get model data from settings
		$model_data = Settings::get_model_data( $model );
		if ( ! $model_data || 'huggingface' !== $model_data['provider'] ) {
			return new \WP_Error( 'unknown_model', __( 'Unknown Hugging Face model.', 'asc-ai-summaries' ) );
		}

		$hf_model = $model_data['model'];
		if ( empty( $hf_model ) ) {
			return new \WP_Error( 'invalid_model', __( 'Invalid Hugging Face model configuration.', 'asc-ai-summaries' ) );
		}

		// Use the full prompt for all models
		$request_body = array(
			'inputs' => $prompt,
			'parameters' => array(
				'max_new_tokens' => 250,
				'temperature' => 0.7,
			),
		);

		// Call Hugging Face Inference API
		$response = wp_remote_post(
			'https://router.huggingface.co/models/' . $hf_model,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type' => 'application/json',
				),
				'body' => wp_json_encode( $request_body ),
				'timeout' => 60,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['error'] ) ) {
			$error_message = is_array( $data['error'] ) ? wp_json_encode( $data['error'] ) : $data['error'];
			return new \WP_Error( 'api_error', $error_message );
		}

		// Extract generated text from response
		// Hugging Face API can return different formats depending on the model
		$generated_text = '';

		// Handle array response (most common format)
		if ( is_array( $data ) ) {
			// Check if first element is an array (nested structure)
			if ( isset( $data[0] ) && is_array( $data[0] ) ) {
				// Try generated_text (for text generation models)
				if ( isset( $data[0]['generated_text'] ) ) {
					$generated_text = $data[0]['generated_text'];
				}
			} else {
				// Response might be a direct array of strings
				if ( isset( $data[0] ) && is_string( $data[0] ) ) {
					$generated_text = $data[0];
				}
			}
		}

		// Handle direct string response
		if ( empty( $generated_text ) && is_string( $data ) ) {
			$generated_text = $data;
		}

		// Handle object response with generated_text at root level
		if ( empty( $generated_text ) && is_array( $data ) && isset( $data['generated_text'] ) ) {
			$generated_text = $data['generated_text'];
		}

		// Handle models that might return text directly in the array
		if ( empty( $generated_text ) && is_array( $data ) ) {
			// Check all array elements for text
			foreach ( $data as $item ) {
				if ( is_string( $item ) && ! empty( $item ) ) {
					$generated_text = $item;
					break;
				}
				if ( is_array( $item ) ) {
					// Check nested arrays
					if ( isset( $item['generated_text'] ) ) {
						$generated_text = $item['generated_text'];
						break;
					}
				}
			}
		}

		if ( empty( $generated_text ) ) {
			// Log the response for debugging
			error_log( 'Hugging Face API Response for ' . $hf_model . ': ' . wp_json_encode( $data ) );
			$error_msg = __( 'No text generated. Response format may be unexpected.', 'asc-ai-summaries' );
			$error_msg .= ' Response: ' . substr( wp_json_encode( $data ), 0, 200 );
			return new \WP_Error( 'api_error', $error_msg );
		}

		return trim( $generated_text );
	}

	/**
	 * Call OpenAI API.
	 *
	 * @param string $model The model identifier.
	 * @param string $prompt The prompt.
	 * @return string|\WP_Error The generated text or error.
	 */
	private function call_openai_api( string $model, string $prompt ): string|\WP_Error {
		$settings = Settings::get_settings();
		$api_key = $settings['openai_api_key'] ?? '';

		if ( empty( $api_key ) ) {
			return new \WP_Error( 'missing_api_key', __( 'OpenAI API key is required.', 'asc-ai-summaries' ) );
		}

		// Get model data from settings
		$model_data = Settings::get_model_data( $model );
		if ( ! $model_data || 'openai' !== $model_data['provider'] ) {
			return new \WP_Error( 'unknown_model', __( 'Unknown OpenAI model.', 'asc-ai-summaries' ) );
		}

		$openai_model = $model_data['model'] ?? 'gpt-4o-mini';

		// Call OpenAI API
		$response = wp_remote_post(
			'https://api.openai.com/v1/chat/completions',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type' => 'application/json',
				),
				'body' => wp_json_encode( array(
					'model' => $openai_model,
					'messages' => array(
						array(
							'role' => 'user',
							'content' => $prompt,
						),
					),
					'temperature' => 0.7,
				) ),
				'timeout' => 60,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['error'] ) ) {
			return new \WP_Error( 'api_error', $data['error']['message'] ?? __( 'OpenAI API error.', 'asc-ai-summaries' ) );
		}

		$generated_text = $data['choices'][0]['message']['content'] ?? '';

		if ( empty( $generated_text ) ) {
			return new \WP_Error( 'api_error', __( 'No text generated.', 'asc-ai-summaries' ) );
		}

		return trim( $generated_text );
	}

	/**
	 * Call Anthropic API.
	 *
	 * @param string $model The model identifier.
	 * @param string $prompt The prompt.
	 * @return string|\WP_Error The generated text or error.
	 */
	private function call_anthropic_api( string $model, string $prompt ): string|\WP_Error {
		$settings = Settings::get_settings();
		$api_key = $settings['anthropic_api_key'] ?? '';

		if ( empty( $api_key ) ) {
			return new \WP_Error( 'missing_api_key', __( 'Anthropic API key is required.', 'asc-ai-summaries' ) );
		}

		// Get model data from settings
		$model_data = Settings::get_model_data( $model );
		if ( ! $model_data || 'anthropic' !== $model_data['provider'] ) {
			return new \WP_Error( 'unknown_model', __( 'Unknown Anthropic model.', 'asc-ai-summaries' ) );
		}

		$anthropic_model = $model_data['model'] ?? 'claude-3-5-sonnet-20241022';

		// Call Anthropic API
		$response = wp_remote_post(
			'https://api.anthropic.com/v1/messages',
			array(
				'headers' => array(
					'x-api-key' => $api_key,
					'anthropic-version' => '2023-06-01',
					'Content-Type' => 'application/json',
				),
				'body' => wp_json_encode( array(
					'model' => $anthropic_model,
					'max_tokens' => 1024,
					'messages' => array(
						array(
							'role' => 'user',
							'content' => $prompt,
						),
					),
				) ),
				'timeout' => 60,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['error'] ) ) {
			return new \WP_Error( 'api_error', $data['error']['message'] ?? __( 'Anthropic API error.', 'asc-ai-summaries' ) );
		}

		$generated_text = $data['content'][0]['text'] ?? '';

		if ( empty( $generated_text ) ) {
			return new \WP_Error( 'api_error', __( 'No text generated.', 'asc-ai-summaries' ) );
		}

		return trim( $generated_text );
	}

	/**
	 * Call Google API.
	 *
	 * @param string $model The model identifier.
	 * @param string $prompt The prompt.
	 * @return string|\WP_Error The generated text or error.
	 */
	private function call_google_api( string $model, string $prompt ): string|\WP_Error {
		$settings = Settings::get_settings();
		$api_key = $settings['google_api_key'] ?? '';

		if ( empty( $api_key ) ) {
			return new \WP_Error( 'missing_api_key', __( 'Google API key is required.', 'asc-ai-summaries' ) );
		}

		// Get model data from settings
		$model_data = Settings::get_model_data( $model );
		if ( ! $model_data || 'google' !== $model_data['provider'] ) {
			return new \WP_Error( 'unknown_model', __( 'Unknown Google model.', 'asc-ai-summaries' ) );
		}

		$google_model = $model_data['model'] ?? 'gemini-pro';

		// Call Google API
		$response = wp_remote_post(
			'https://generativelanguage.googleapis.com/v1beta/models/' . $google_model . ':generateContent?key=' . urlencode( $api_key ),
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body' => wp_json_encode( array(
					'contents' => array(
						array(
							'parts' => array(
								array(
									'text' => $prompt,
								),
							),
						),
					),
				) ),
				'timeout' => 60,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['error'] ) ) {
			return new \WP_Error( 'api_error', $data['error']['message'] ?? __( 'Google API error.', 'asc-ai-summaries' ) );
		}

		$generated_text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

		if ( empty( $generated_text ) ) {
			return new \WP_Error( 'api_error', __( 'No text generated.', 'asc-ai-summaries' ) );
		}

		return trim( $generated_text );
	}
}
