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
	// Bootstrap tooltips
	$('[data-toggle=tooltip]').tooltip({
		placement: 'bottom',
	});

	// Bootstrap popovers
	$('[data-toggle=popover]').popover({
		placement: 'right',
		trigger: 'hover',
	});

	// Date picker control
	$('[data-toggle=datepicker]').datepicker({
		format: 'yyyy-mm-dd',
	});

	// Show editor modals
	$('.modal-editor').modal({
		backdrop: 'static',
		keyboard: false,
	});
});
