/**
 * Bootstrap Pixel Theme
 *
 * @copyright   (c) Sayak Banerjee <sayakb@kde.org>
 * @license     http://opensource.org/licenses/BSD-3-Clause
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

	// Make icons with checkbox clickable
	clickableIcons();

	// Load search functionality
	itemSearch();

	// Generate confirmation actions
	confirmPrompts();

	// Load bootstrap dropdowns
	bootstrapDropdowns();
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
	$('[data-toggle=tooltip]').tooltip();

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
 * Makes icons with checkboxes clickable
 *
 * @access public
 * @return void
 */
function clickableIcons()
{
	$('[data-toggle=clickable] .thumbnail').off('click').on('click', function(e)
	{
		parent = $(this).parents('[data-toggle=clickable]').first();
		checkbox = parent.find('input[type=checkbox]');

		if (checkbox.length > 0)
		{
			checkbox.prop('checked', ! checkbox.is(':checked'));
		}

		e.preventDefault();
	});
}

/**
 * Provides item search functionality
 *
 * @access public
 * @return void
 */
function itemSearch()
{
	$('[data-toggle=user-search]').off('keyup').on('keyup', function(e)
	{
		// Set the search instance
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
			size = search.attr('data-size');
			empty = search.attr('data-empty');
			icon = search.attr('data-icon');
			checkbox = search.attr('data-checkbox');
			pages = search.attr('data-pages');
			pushUrl = search.attr('data-push');

			// Read the request params
			query = search.val();
			exclude = new Array();

			// If query is not empty, do the search
			// Otherwise, reset to original state
			if (query.length > 0)
			{
				// Set the icon as 'busy'
				$(icon).removeClass('glyphicon-search glyphicon-remove cursor-pointer').addClass('glyphicon-time');

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

						// Set the size of the items
						$(target + ' ' + item).addClass(size);

						// Regenerate clickable icons
						clickableIcons();

						// Show empty box
						if (exclude.length > 0 || users.trim().length > 0)
						{
							$(empty).addClass('hide');
						}
						else
						{
							$(empty).removeClass('hide');
						}

						// Set the icon as 'clear'
						$(icon).removeClass('glyphicon-time').addClass('glyphicon-remove cursor-pointer');

						// Clear the search box if the icon is clicked
						$(icon).off('click').on('click', function()
						{
							search.val('').keyup();
						});
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
				$(empty).addClass('hide');

				// Set the icon as 'search'
				$(icon).removeClass('glyphicon-time glyphicon-remove cursor-pointer').addClass('glyphicon-search');
			}
		}, 500);
	});
}

/**
 * Generates confirmation prompts
 *
 * @access public
 * @return void
 */
function confirmPrompts()
{
	$('[data-toggle=confirm]').off('click').on('click', function(e)
	{
		// Set the link instance
		link = $(this);

		// Read config options from the element
		href = link.attr('data-href');
		prompt = link.attr('data-prompt');
		wait = link.attr('data-wait');
		clicked = link.attr('data-clicked');
		text = link.html();

		// Check if the link isn't clicked yet
		if (clicked === undefined)
		{
			// Are we waiting? Be patient!
			if (text.indexOf(wait) == -1)
			{
				// Save the original text
				original = text;

				// Show the wait text
				link.html(wait);

				// Show the tooltip
				link.tooltip({
						title: prompt,
						placement: 'left',
						trigger: 'manual'
					});

				link.tooltip('show');

				// Mark item as clicked
				setTimeout(function()
				{
					link.html(original);
					link.attr('data-clicked', true);
				}, 500);

				// Reset the click state in 5 seconds
				setTimeout(function()
				{
					link.removeAttr('data-clicked');
					link.tooltip('hide');
				}, 5000);

				// If link is within a dropdown, reset when dropdown is closed
				dropdown = link.parents('.dropdown');

				if (dropdown.count != 0)
				{
					dropdown.off('hidden.bs.dropdown').on('hidden.bs.dropdown', function()
					{
						link.removeAttr('data-clicked');
						link.tooltip('hide');
					});
				}
			}
		}

		// Link was clicked, navigate to target location
		else
		{
			window.location = href;
		}

		e.preventDefault();
		e.stopPropagation();
	});
}

/**
 * Converts bootstrap dropdowns to actual dropdowns
 *
 * @access public
 * @return void
 */
function bootstrapDropdowns()
{
	$('.dropdown-menu a').off('click').on('click', function(e)
	{
		button = $(this).parents().eq(2).find('[data-toggle=dropdown]');

		if (button.length == 1)
		{
			target = button.attr('data-select');
			focus = button.attr('data-focus');
			text = $(this).text();
			value = $(this).attr('data-value');

			// Check if the dropdown has enabled selection
			if (target !== undefined)
			{
				// Default the value, if one is not set it
				// to the link's text
				if (value === undefined)
				{
					value = text;
				}

				// Select the clicked value
				$(target).val(value);
				$(button).html(text + ' <span class="caret"></span>');

				// Focus the post-selection control
				if (focus !== undefined)
				{
					$(focus).focus();
				}

				e.preventDefault();
			}
		}
	});
}
