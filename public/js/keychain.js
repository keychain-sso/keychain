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

$(function()
{
	// Bind to the permissions dropdown and change the search URL and state of
	// the search textbox based on the selected value of the dropdown
	$('#permissions-add .dropdown-menu a[data-value]').on('click', function(e)
	{
		searchbox = $(this).parents().eq(3).find('input[type=text]');
		url = searchbox.attr('data-url');
		value = $(this).text().toLowerCase();

		if (value != 'self' && value != 'all')
		{
			// Enable the search box and focus it
			searchbox.removeAttr('disabled').focus();

			// Set the URL for the auto complete box
			old = value != 'user' ? 'user' : 'group';
			searchbox.attr('data-url', url.replace(old, value));
		}
		else
		{
			// Disable and clear the search box
			searchbox.attr('disabled', 'disabled').val('');
		}
	});

	// Hide the object entry field if a manage permission is selected
	$('#permissions-add [name=flag]').on('change', function(e)
	{
		if ($(this).val().indexOf('manage') != -1)
		{
			$('#permission-object').addClass('hide');
		}
		else
		{
			$('#permission-object').removeClass('hide');
		}

		if ($(this).val().indexOf('field') != -1)
		{
			$('#permission-field').removeClass('hide');
		}
		else
		{
			$('#permission-field').addClass('hide');
		}
	});
});
