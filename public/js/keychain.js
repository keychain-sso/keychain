/**
 * Keychain
 *
 * SSO login provider for enterprise.
 *
 * @package     Keychain
 * @copyright   (c) Keychain Developers
 * @license     http://opensource.org/licenses/BSD-3-Clause
 * @link        https://github.com/keychain-sso/keychain
 * @since       Version 1.0
 * @filesource
 */

/**
 * Constructor
 */
$(function()
{
	loadBootstrapUtils();
	setupAjaxModals();
	setupAutoNavigation();
});

/**
 * Load bootstrap components
 */
function loadBootstrapUtils()
{
	// Bootstrap tooltips
	$('[data-toggle=tooltip]').tooltip({
		placement: 'bottom',
	});

	// Date picker control
	$('[data-toggle=datepicker]').datepicker({
		format: 'yyyy-mm-dd',
	})
}

/**
 * Sets up AJAX modal popups
 */
function setupAjaxModals()
{
	// Handle click event for AJAX enabled links
	$('[data-nav=ajax-modal]')
		.off('click')
		.on('click', function(e)
		{
			var href = $(this).attr('href');
			var modal = $(this).attr('data-target');

			navigateAjaxModals(href, modal);
			e.preventDefault();
		});
}

/**
 * Sets up automatic navigation controls
 */
function setupAutoNavigation()
{
	// Auto load preview links
	var preview = $('[data-nav=ajax-modal][data-auto=true]');

	if (preview.length == 1)
	{
		var href = preview.attr('href');
		var modal = preview.attr('data-target');

		navigateAjaxModals(href, modal);
	}
}

/**
 * Handles the navigation part for AJAX modals
 *
 * @param  string  href
 * @param  object  modal
 */
function navigateAjaxModals(href, modal)
{
	var loader = $('#modal-loader').html();
	var error = $('#modal-error').html();

	// Push the current URL to history
	window.history.pushState(null, null, href);

	// Set the modal up for events
	$(modal)
		.modal()
		.on('hidden.bs.modal', function()
		{
			window.history.back();
		});

	// Show the loader as a placeholder
	$(modal + ' .modal-content').html(loader);

	// Load the target page on the modal
	$.ajax({
		url: href,

		// AJAX callback
		success: function(data)
		{
			$(modal + ' .modal-content').html(data);

			// Reload active components
			loadBootstrapUtils();
			setupAjaxModals();
		},

		// Error handler
		error: function()
		{
			$(modal + ' .modal-content').html(error);
		}
	});
}
