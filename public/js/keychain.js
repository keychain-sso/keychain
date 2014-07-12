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
 *
 * @access public
 * @return object
 */
$(init);

/**
 * Triggers all on-load functions
 *
 * @access public
 * @return void
 */
function init()
{
	// Load all bootstrap opt-in controls
	bootstrapOptIn();

	// Load search functionality
	userSearch();
}

/**
 * Initialize bootstrap extensions on the page
 *
 * @access public
 * @return void
 */
function bootstrapOptIn()
{
	// Initialize tooltips
	$('[data-toggle=tooltip]').tooltip({
		placement: 'bottom',
	});

	// Initialize popovers
	$('[data-toggle=popover]').popover({
		placement: 'right',
		trigger: 'hover',
	});

	// Date picker control
	$('[data-toggle=datepicker]').datepicker({
		format: 'yyyy-mm-dd',
	});

	// Display editor modals
	$('.modal-editor').modal({
		backdrop: 'static',
		keyboard: false,
	});
}

/**
 * Provides user search functionality
 *
 * @access public
 * @return void
 */
function userSearch()
{
	$('[data-toggle=user-search]').keyup(function(e)
	{
		// Set the user search instance
		search = $(this);

		// Clear pending search requests
		if (typeof(timer) !== 'undefined')
		{
			clearTimeout(timer);
		}

		// Create a new search request
		timer = setTimeout(function()
		{
			// Read config options from the element
			url = search.attr('data-url');
			item = search.attr('data-item');
			target = search.attr('data-target');
			empty = search.attr('data-empty');
			model = search.attr('data-model');
			loader = search.attr('data-loader');
			checkbox = search.attr('data-checkbox');
			pages = search.attr('data-pages');
			pushUrl = search.attr('data-push');

			// Read the request params
			query = search.val();
			exclude = new Array();

			// Hide the empty results box
			$(empty).addClass('hide');

			// If query is not empty, do the search
			// Otherwise, reset to original state
			if (query.length > 0)
			{
				// Set the loader as busy
				$(loader).removeClass('glyphicon-search').addClass('glyphicon-time');

				// Here, we backup the original state of the window
				if (typeof(original) == 'undefined')
				{
					original = new String(window.location);
					initial = $(target).html();

					window.history.pushState(null, null, pushUrl);
					$(pages).hide();
				}

				// Determine the users to exclude
				$(target).find('input[type=checkbox]').each(function()
				{
					if ($(this).is(':checked'))
					{
						exclude.push($(this).val());
					}
				});

				// Send the search query
				$.ajax({
					url: url,
					data: { query: query, exclude: exclude.join(), checkbox: checkbox },
					success: function(users)
					{
						// Remove existing search items that are unchecked
						$(target).children(item).each(function()
						{
							checkbox = $(this).find('input[type=checkbox]');

							if (checkbox.length == 0 || ! checkbox.is(':checked'))
							{
								$(this).remove();
							}
						});

						// Append the user icons to the target
						$(target).append(users);

						// Show empty box
						if (exclude.length == 0 && users.trim().length == 0)
						{
							$(empty).removeClass('hide');
						}

						// Set the loader as idle
						$(loader).removeClass('glyphicon-time').addClass('glyphicon-search');
					},
				})
			}
			else
			{
				// Restore the original state of the window
				window.history.pushState(null, null, original);
				$(pages).show();

				original = undefined;
				$(target).html(initial);
			}
		}, 500);
	});
}
