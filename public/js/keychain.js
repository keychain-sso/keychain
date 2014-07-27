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
	// Handle change avatar button click
	$('#change-avatar').on('click', function(e)
	{
		$('[name=avatar]').click();
	});

	// Handle avatar upload control change event
	$('[name=avatar]').on('change', function(e)
	{
		$(this).parent().submit();
	});

	// Bind to the permissions dropdown and change the search URL and state of
	// the search textbox based on the selected value of the dropdown
	$('#permission-add .dropdown-menu a[data-value]').on('click', function(e)
	{
		searchbox = $(this).parents().eq(3).find('input[type=text]');
		url = searchbox.attr('data-url');
		value = $(this).text().toLowerCase();

		if (value != 'self' && value != 'global')
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

	// Show/hide fields on the permissions screens
	$('#permission-add [name=flag]').on('change', function(e)
	{
		// Hide the object box if a manage permission is selected
		if ($(this).val().indexOf('manage') != -1)
		{
			$('#permission-object').addClass('hide');
		}
		else
		{
			$('#permission-object').removeClass('hide');
		}

		// Show the field box if a field permission is selected
		if ($(this).val().indexOf('field') != -1)
		{
			$('#permission-field').removeClass('hide');
		}
		else
		{
			$('#permission-field').addClass('hide');
		}

		// Hide 'self' and 'user' objects if group_edit permission is selected
		if ($(this).val() == 'group_edit')
		{
			$('#permission-object .dropdown-menu').find('[data-value=1],[data-value=3]').hide();
			$('#permission-object .dropdown-menu a[data-value=4]').click();
		}
	});

	// Retain the dropdown state based on the hidden field data
	subjectType = $('[name=subject_type]');
	objectType = $('[name=object_type]');

	if (subjectType.length == 1 && objectType.length == 1)
	{
		$('#permission-add [name=flag]').change();

		if ( ! $('#permission-subject [name=subject]').is(':disabled'))
		{
			$('#permission-subject .dropdown-menu a[data-value=' + subjectType.val() + ']').click();
		}

		$('#permission-object .dropdown-menu a[data-value=' + objectType.val() + ']').click();
	}
});
