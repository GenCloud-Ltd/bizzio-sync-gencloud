(function ($) {
	'use strict';

	$(document).ready(function () {
		// Test Connection AJAX
		$('#bizzio-test-connection').on('click', function (e) {
			e.preventDefault();

			var data = {
				'action': 'bizzio_test_connection',
				'security': bizzio_sync_gencloud_ajax.test_connection_nonce
			};

			$.post(bizzio_sync_gencloud_ajax.ajax_url, data, function (response) {
				var resultDiv = $('#bizzio-test-connection-result');
				if (response.success) {
					resultDiv.html('<p style="color: green;">' + response.data.message + '</p>');
				} else {
					resultDiv.html('<p style="color: red;">' + response.data.message + '</p>');
				}
				console.log(response);
			});
		});

		// Product Import AJAX
		$('#bizzio-import-products').on('click', function (e) {
			e.preventDefault();

			var $button = $(this);
			$button.prop('disabled', true).text('Please wait, don\'t close the page...');
			$('#bizzio-product-import-status').html('<p>Initiating product import...</p>');
			$('#bizzio-product-import-progress-bar').css('width', '0%').text('0%');

			var data = {
				'action': 'bizzio_import_products',
				'security': bizzio_sync_gencloud_ajax.import_products_nonce
			};
			if (typeof BIZZIO_SYNC_GENCLOUD_DEBUG_LOG !== 'undefined' && BIZZIO_SYNC_GENCLOUD_DEBUG_LOG) {
				console.log('[BIZZIO DEBUG] Import Products AJAX: action=' + data.action + ', nonce=' + data.security);
			}
			$.post(bizzio_sync_gencloud_ajax.ajax_url, data, function (response) {
				if (response.success) {
					$('#bizzio-product-import-status').html('<p style="color: green;">' + response.data.message + '</p>');
					// Start processing batches
					processProductBatch();
					// Start polling for progress
					setInterval(getImportProgress, 3000); // Poll every 3 seconds
				} else {
					$('#bizzio-product-import-status').html('<p style="color: red;">' + response.data.message + '</p>');
					$button.prop('disabled', false).text('Import Products');
				}
				console.log(response);
			});
		});

		function processProductBatch() {
			var data = {
				'action': 'bizzio_process_product_batch',
				'security': bizzio_sync_gencloud_ajax.import_products_nonce
			};
			if (typeof BIZZIO_SYNC_GENCLOUD_DEBUG_LOG !== 'undefined' && BIZZIO_SYNC_GENCLOUD_DEBUG_LOG) {
				console.log('[BIZZIO DEBUG] Process Product Batch AJAX: action=' + data.action + ', nonce=' + data.security);
			}
			$.post(bizzio_sync_gencloud_ajax.ajax_url, data, function (response) {
				console.log('Batch processing response:', response);
				if (response.success) {
					if (response.data.status === 'in_progress') {
						// Continue processing next batch
						setTimeout(processProductBatch, 1000); // Wait 1 second before next batch
					} else if (response.data.status === 'completed') {
						$('#bizzio-product-import-status').html('<p style="color: green;">' + response.data.message + '</p>');
						$('#bizzio-import-products').prop('disabled', false).text('Import Products');
					}
				} else {
					$('#bizzio-product-import-status').html('<p style="color: red;">Error processing batch: ' + response.data.message + '</p>');
					$('#bizzio-import-products').prop('disabled', false).text('Import Products');
				}
			});
		}

		function getImportProgress() {
			var data = {
				'action': 'bizzio_get_import_progress',
				'security': bizzio_sync_gencloud_ajax.import_products_nonce
			};
			if (typeof BIZZIO_SYNC_GENCLOUD_DEBUG_LOG !== 'undefined' && BIZZIO_SYNC_GENCLOUD_DEBUG_LOG) {
				console.log('[BIZZIO DEBUG] Get Import Progress AJAX: action=' + data.action + ', nonce=' + data.security);
			}
			$.post(bizzio_sync_gencloud_ajax.ajax_url, data, function (response) {
				if (response.success) {
					var progress = response.data.progress;
					var total_articles = response.data.total_articles;
					var imported = response.data.imported;
					var failed = response.data.failed;
					var status = response.data.status;

					var percentage = (total_articles > 0) ? Math.round((progress / total_articles) * 100) : 0;

					$('#bizzio-product-import-progress-bar').css('width', percentage + '%').text(percentage + '%');
					$('#bizzio-product-import-status').html(
						'<p>Status: ' + status + '</p>' +
						'<p>Processed: ' + progress + ' / ' + total_articles + '</p>' +
						'<p>Imported: ' + imported + '</p>' +
						'<p>Failed: ' + failed + '</p>'
					);

					if (status === 'completed' || status === 'idle') {
						// Stop polling if import is complete or idle
						clearInterval(this); // 'this' refers to the interval ID
					}
				}
			});
		}

		// Category Import AJAX
		$('#bizzio-import-categories').on('click', function (e) {
			e.preventDefault();

			var $button = $(this);
			$button.prop('disabled', true).text('Importing , please don\'t close the page...');
			$('#bizzio-category-import-status').html('<p>Initiating category import...</p>');
			$('#bizzio-category-import-progress-bar').css('width', '0%').text('0%');

			var data = {
				'action': 'bizzio_import_categories',
				'security': bizzio_sync_gencloud_ajax.import_categories_nonce
			};
			if (typeof BIZZIO_SYNC_GENCLOUD_DEBUG_LOG !== 'undefined' && BIZZIO_SYNC_GENCLOUD_DEBUG_LOG) {
				console.log('[BIZZIO DEBUG] Import Categories AJAX: action=' + data.action + ', nonce=' + data.security);
			}
			$.post(bizzio_sync_gencloud_ajax.ajax_url, data, function (response) {
				if (response.success) {
					$('#bizzio-category-import-status').html('<p style="color: green;">' + response.data.message + '</p>');
					// Start processing batches
					processCategoryBatch();
					// Start polling for progress
					setInterval(getCategoryImportProgress, 3000); // Poll every 3 seconds
				} else {
					$('#bizzio-category-import-status').html('<p style="color: red;">' + response.data.message + '</p>');
					$button.prop('disabled', false).text('Import Categories');
				}
				console.log(response);
			});
		});

		function processCategoryBatch() {
			var data = {
				'action': 'bizzio_process_category_batch',
				'security': bizzio_sync_gencloud_ajax.import_categories_nonce
			};
			if (typeof BIZZIO_SYNC_GENCLOUD_DEBUG_LOG !== 'undefined' && BIZZIO_SYNC_GENCLOUD_DEBUG_LOG) {
				console.log('[BIZZIO DEBUG] Process Category Batch AJAX: action=' + data.action + ', nonce=' + data.security);
			}
			$.post(bizzio_sync_gencloud_ajax.ajax_url, data, function (response) {
				console.log('Category batch processing response:', response);
				if (response.success) {
					if (response.data.status === 'in_progress') {
						// Continue processing next batch
						setTimeout(processCategoryBatch, 1000); // Wait 1 second before next batch
					} else if (response.data.status === 'completed') {
						$('#bizzio-category-import-status').html('<p style="color: green;">' + response.data.message + '</p>');
						$('#bizzio-import-categories').prop('disabled', false).text('Import Categories');
					}
				} else {
					$('#bizzio-category-import-status').html('<p style="color: red;">Error processing batch: ' + response.data.message + '</p>');
					$('#bizzio-import-categories').prop('disabled', false).text('Import Categories');
				}
			});
		}

		function getCategoryImportProgress() {
			var data = {
				'action': 'bizzio_get_category_import_progress',
				'security': bizzio_sync_gencloud_ajax.import_categories_nonce
			};
			if (typeof BIZZIO_SYNC_GENCLOUD_DEBUG_LOG !== 'undefined' && BIZZIO_SYNC_GENCLOUD_DEBUG_LOG) {
				console.log('[BIZZIO DEBUG] Get Category Import Progress AJAX: action=' + data.action + ', nonce=' + data.security);
			}
			$.post(bizzio_sync_gencloud_ajax.ajax_url, data, function (response) {
				if (response.success) {
					var progress = response.data.progress;
					var total_categories = response.data.total_categories;
					var imported = response.data.imported;
					var failed = response.data.failed;
					var status = response.data.status;

					var percentage = (total_categories > 0) ? Math.round((progress / total_categories) * 100) : 0;

					$('#bizzio-category-import-progress-bar').css('width', percentage + '%').text(percentage + '%');
					$('#bizzio-category-import-status').html(
						'<p>Status: ' + status + '</p>' +
						'<p>Processed: ' + progress + ' / ' + total_categories + '</p>' +
						'<p>Imported: ' + imported + '</p>' +
						'<p>Failed: ' + failed + '</p>'
					);

					if (status === 'completed' || status === 'idle') {
						// Stop polling if import is complete or idle
						clearInterval(this); // 'this' refers to the interval ID
					}
				}
			});
		}

	});

})(jQuery);