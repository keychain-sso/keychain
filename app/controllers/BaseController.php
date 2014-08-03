<?php

/**
 * Keychain
 *
 * SSO login provider for enterprise.
 *
 * @package     Keychain
 * @copyright   (c) Keychain Developers
 * @license     http://opensource.org/licenses/BSD-3-Clause
 * @link        https://github.com/keychain-sso/keychain
 * @since       Version 1.0.0
 * @filesource
 */

/**
 * BaseController class
 *
 * Parent class for all controllers
 *
 * @package     Keychain
 * @subpackage  Controllers
 */

class BaseController extends Controller {

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		// Create the layout
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}

		// Process input text to make it safe for display
		$input = Input::all();

		// Trim leading and trailing whitespace and remove HTML tags
		foreach ($input as $key => $value)
		{
			if (is_string($value))
			{
				$input[$key] = strip_tags(trim($value));
			}
		}

		// Merge it back to the input data
		Input::merge($input);
	}

}
