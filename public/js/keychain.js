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
	// Bind to the permissions dropdown and change the search URL of the textbox
	// based on the selected value on the dropdown
	$('#permissions-add .dropdown-menu a[data-value]').on('click', function(e)
	{
		searchbox = $(this).parents().eq(3).find('input[type=text]');
		url = searchbox.attr('data-url');

		from = value == 'user' ? 'group' : 'user';
		to = $(this).attr('data-value');

		searchbox.attr('data-url', url.replace(from, to));
	});

	// Hide the object entry field if a manage permission is selected
	$('#permissions-add [name=flag]').on('change', function(e)
	{
		if ($(this).val().indexOf('manage') != -1)
		{
			$('#search-object').addClass('hide');
		}
		else
		{
			$('#search-object').removeClass('hide');
		}
	});
});
