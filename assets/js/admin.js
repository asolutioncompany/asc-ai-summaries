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
	 * Generate AI Summaries Button
	 *
	 * Handle the generate summaries button click.
	 */
	const generateButton = $('#asc-ais-generate-button');
	const generateStatus = $('#asc-ais-generate-status');
	const excerptField = $('#asc_ais_excerpt');
	const summaryField = $('#asc_ais_summary');

	if (generateButton.length) {
		generateButton.on('click', function () {
			const postId = $(this).data('post-id');
			if (!postId) {
				return;
			}

			// Disable button and show loading
			generateButton.prop('disabled', true);
			generateStatus.html('<span style="color: #2271b1;">' + ascAISummaries.i18n.generating + '</span>');

			// Make AJAX request
			$.ajax({
				url: ascAISummaries.ajaxUrl,
				type: 'POST',
				data: {
					action: 'asc_ais_generate_summaries',
					nonce: ascAISummaries.nonce,
					post_id: postId,
				},
				success: function (response) {
					if (response.success) {
						if (response.data.excerpt) {
							excerptField.val(response.data.excerpt);
						}
						if (response.data.summary) {
							summaryField.val(response.data.summary);
						}
						generateStatus.html('<span style="color: #00a32a;">' + ascAISummaries.i18n.success + '</span>');
						setTimeout(function () {
							generateStatus.html('');
						}, 3000);
					} else {
						generateStatus.html('<span style="color: #d63638;">' + (response.data.message || ascAISummaries.i18n.error) + '</span>');
					}
					generateButton.prop('disabled', false);
				},
				error: function () {
					generateStatus.html('<span style="color: #d63638;">' + ascAISummaries.i18n.error + '</span>');
					generateButton.prop('disabled', false);
				},
			});
		});
	}
});
})(jQuery);