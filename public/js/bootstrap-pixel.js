/**
 * Bootstrap Pixel Theme
 *
 * @copyright   (c) Sayak Banerjee <sayakb@kde.org>
 * @license     http://opensource.org/licenses/BSD-3-Clause
 * @since       Version 1.0.0
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

	// Load the autocomplete plugin
	autoComplete();
}

/**
 * Generates and returns a unique GUID
 *
 * @access public
 * @return string
 */
function guid()
{
	if (typeof(iterator) === 'undefined')
	{
		iterator = 1;
	}

	return 'bootstrap-pixel-id-' + iterator++;
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
 * @rerunnable
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
	$('[data-toggle=search]').on('keyup', function(e)
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

				// Determine the items to exclude
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
					success: function(results)
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

						// Append the items to the target
						$(target).append(results);

						// Set the size of the items
						$(target + ' ' + item).addClass(size);

						// Regenerate clickable icons
						clickableIcons();

						// Show empty box
						if (exclude.length > 0 || results.trim().length > 0)
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
							search.focus();
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
	$('[data-toggle=confirm]').on('click', function(e)
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
					dropdown.off('hidden.bs.dropdown').on('hidden.bs.dropdown', function(e)
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
	$('.dropdown-menu a').on('click', function(e)
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

/**
 * Provides auto complete functionality
 *
 * @access public
 * @return void
 */
function autoComplete()
{
	$('[data-toggle=autocomplete]').on('keydown', function(e)
	{
		if (e.keyCode == 13)
		{
			e.preventDefault();
		}
	});

	$('[data-toggle=autocomplete]').on('keyup', function(e)
	{
		search = $(this);
		uid = guid();
		query = search.val();
		open = typeof(menu) !== 'undefined';

		// Navigate the menu, on press of down key
		if (open && e.keyCode == 40)
		{
			active = menu.find('li[class=active]');
			menu.find('li').removeClass('active');

			if (active.length > 0)
			{
				active.parent().children().eq(active.index() + 1).addClass('active');
			}
			else
			{
				active = menu.find('li').first();
				active.addClass('active');
			}
		}

		// Navigate the menu, on press of up key
		else if (open && e.keyCode == 38)
		{
			active = menu.find('li[class=active]');
			menu.find('li').removeClass('active');
			search.val(search.val());

			if (active.length > 0 && active.index() > 0)
			{
				active.parent().children().eq(active.index() - 1).addClass('active');
			}
		}

		// Select an item
		else if (open && e.keyCode == 13)
		{
			active = menu.find('li[class=active]');

			if (active.length > 0)
			{
				active.children('a').click();
			}
		}

		// Do the item search
		else
		{
			// Clear pending search requests
			if (typeof(timer) !== 'undefined')
			{
				clearTimeout(timer);
			}

			// Create a new search request
			timer = setTimeout(function()
			{
				// Read the config option from the element
				url = search.attr('data-url');
				target = search.attr('data-target');
				icon = search.attr('data-icon');

				// Clear the target value
				if (target !== undefined)
				{
					$(target).val('');
				}

				// Do the item search
				if (query.length > 0)
				{
					// Set the icon as 'busy'
					$(icon).removeClass('glyphicon-search glyphicon-remove cursor-pointer').addClass('glyphicon-time');

					// Get the textbox position
					posLeft = search.position().left;
					width = search.outerWidth();

					// Fetch the search results
					$.ajax({
						url: url,
						data: { query: query },
						success: function(results)
						{
							// Remove an older version of the menu
							if (open)
							{
								menu.remove();
							}

							if (results.trim().length > 0)
							{
								// Append the dropdown menu
								search.parents().eq(1).append('<ul id="' + uid + '" class="dropdown-menu show"></ul>');

								// Read the newly created menu
								menu = $('#' + uid);

								// Resize the menu
								menu.css({
									left: posLeft,
									width: width,
									position: 'absolute',
								});

								// Add the results to the menu
								menu.html(results);

								// Close the dropdown on clicking a link
								menu.find('a').off('click').on('click', function(e)
								{
									// Get the selected text and value
									text = $(this).text();
									value = $(this).attr('data-value');

									// Hide the menu
									menu.remove();

									// Load the target with selected ID
									if (target !== undefined && value !== undefined)
									{
										$(target).val(value);
									}

									// Load the selected text on the search box
									search.val(text);

									e.preventDefault();
								});
							}

							// Set the icon as 'clear'
							$(icon).removeClass('glyphicon-time').addClass('glyphicon-remove cursor-pointer');

							// Clear the search box if the icon is clicked
							$(icon).off('click').on('click', function()
							{
								search.val('').keyup();
								search.focus();
							});
						},
					});
				}
				else
				{
					// Hide the menu, if open
					if (open)
					{
						menu.remove();
					}

					// Reset the icon to search
					$(icon).removeClass('glyphicon-time glyphicon-remove cursor-pointer').addClass('glyphicon-search');
				}
			}, 500);
		}
	});
}
