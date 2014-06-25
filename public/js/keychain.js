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
	// Load bootstrap
	loadBootstrapUtils();

	// Initialize AJAX modals
	setupAjaxModals();
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
	$('[data-nav=ajax-modal]').click(function(e)
	{
		var href = $(this).attr('href');
		var modal = $(this).attr('data-target');
		var loader = $('#modal.loader').html();

		$(modal).modal();
		$(modal + ' .modal-content').html(loader);

		$.get(href, function(data)
		{
			$(modal + ' .modal-content').html(data);

			loadBootstrapUtils();
		});

		e.preventDefault();
	});
}
