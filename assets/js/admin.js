(function ($) { // jQuery Encapsulation
	'use strict';

	$(document).ready(function () {
		/*
		 * Tab Switcher
		 *
		 * Show/hide tabs for each tab.
		 */
		$('.asc-ais-tabs .nav-tab').on('click', function (e) {
			e.preventDefault();

			const targetTab = $(this).attr('data-tab');
			const targetClass = '.asc-ais-' + targetTab + '-tab';

			// Set active tab
			$('.asc-ais-tabs .nav-tab').removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active');

			// Show/hide active tab content
			$('.asc-ais-tab-content').hide();
			$(targetClass).show();
		});

		/*
		 * Style Panel Switcher
		 *
		 * Show/hide style panels for each style.
		 */
		$('#asc-ais-style').on('change', function() {
			const targetStyle = $(this).val();
			const targetClass = '.asc-ais-tr-' + targetStyle;

			// Show/hide active style content
			$('.asc-ais-tr-style-row').hide();
			$(targetClass).show();
		});

		/*
		 * AI Model API keys
		 *
		 * Block password manager interference.
		 *
		 * Toggle API key visibility button.
		 */
		const aiModel = $('#asc-ais-ai-model');
		const openaiApiKey = $('#asc-ais-openai-api-key');
		const toggleApiKey = $('#asc-ais-toggle-api-key');
		const keyField = document.getElementById('asc-ais-openai-api-key');

		if (aiModel.length) {
			// Remove readonly attribute on focus to prevent password manager interference
			if (keyField) {
				keyField.addEventListener('focus', function () {
					this.removeAttribute('readonly');
					this.type = 'text';
				});

				keyField.addEventListener('blur', function () {
					if (this.value === '') {
						this.setAttribute('readonly', 'readonly');
					}
					this.type = 'password';
				});
			}

			// Toggle API key visibility button
			toggleApiKey.on('click', function () {
				if (openaiApiKey.attr('type') === 'password') {
					openaiApiKey.attr('type', 'text');
					openaiApiKey.removeAttr('readonly');
					$(this).text('Hide');
				} else {
					openaiApiKey.attr('type', 'password');
					$(this).text('Show');
				}
			});
		}
	});
})(jQuery);