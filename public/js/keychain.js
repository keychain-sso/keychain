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

	// For the objects filter box, if the user selects 'self' or 'global', we
	// disable the textbox
	$('#permission-object .input-group').on('hidden.bs.dropdown', function(e)
	{
		value = $('[name=object_type]').val();

		if (value == 'self' || value == 'all')
		{
			$('#permission-object input[type=text]')
				.attr('disabled', 'disabled')
				.val('');
		}
		else
		{
			$('#permission-object input[type=text]')
				.removeAttr('disabled')
				.focus();
		}
	});
});
