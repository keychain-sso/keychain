<?php namespace Keychain\Facades;

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

use Lang;
use Session;

/**
 * View class
 *
 * Abstraction over \Illuminate\Support\Facades\View
 *
 * @package     Keychain
 * @subpackage  Facades
 */
class View extends \Illuminate\Support\Facades\View {

	/**
	 * Cache for default view data
	 *
	 * @static
	 * @access protected
	 * @var array
	 */
	protected static $defaults = null;

	/**
	 * Returns default variables for a view
	 *
	 * @static
	 * @access public
	 * @return array
	 */
	public static function defaults()
	{
		if (is_null(static::$defaults))
		{
			static::$defaults = array(
				'error'     => Session::get('messages.error'),
				'success'   => Session::get('messages.success'),
				'global'    => Session::get('messages.global'),
				'appconfig' => Config::get('app'),
				'auth'      => Auth::user(),
				'title'     => null,
			);
		}

		return static::$defaults;
	}

	/**
	 * This abstraction over the base method injects the page title
	 * and default view data.
	 *
	 * @static
	 * @access public
	 * @param  string  $view
	 * @param  string  $title
	 * @param  array  $data
	 * @return View
	 */
	public static function make($view, $title = null, $data = array())
	{
		$data['title'] = Lang::get($title);

		return parent::make($view, $data, static::defaults());
	}

}
