<?php namespace Keychain;

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

use Session;

/**
 * View class
 *
 * Abstraction over \Illuminate\Support\Facades\View to enable skin support
 *
 * @package     Keychain
 * @subpackage  Libraries
 */
class View extends \Illuminate\Support\Facades\View {

	/**
	 * Cache for default view data
	 *
	 * @static
	 * @var array
	 */
	private static $viewDefaults = NULL;

	/**
	 * Returns default variables for a view
	 *
	 * @static
	 * @access public
	 * @return array
	 */
	public static function defaults()
	{
		if (is_null(static::$viewDefaults))
		{
			static::$viewDefaults = array(
				'error'      => Session::get('messages.error'),
				'success'    => Session::get('messages.success'),
				'global'     => Session::get('messages.global'),
			);
		}

		return static::$viewDefaults;
	}

	/**
	 * This abstraction over the base method injects the skin name
	 * and default view data.
	 *
	 * @param  string  $view
	 * @param  array  $data
	 * @param  bool  $inject
	 * @return \Illuminate\View\View
	 */
	public static function make($view, $data = array(), $inject = true)
	{
		return parent::make($view, $data, static::defaults());
	}

}
