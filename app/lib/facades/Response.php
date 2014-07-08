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

/**
 * Response class
 *
 * Abstraction over \Illuminate\Support\Facades\Response
 *
 * @package     Keychain
 * @subpackage  Facades
 */
class Response extends \Illuminate\Support\Facades\Response {

	/**
	 * This abstraction over the base method injects default view data.
	 *
	 * @static
	 * @access public
	 * @param  string  $view
	 * @param  array  $data
	 * @param  int  $status
	 * @param  array  $headers
	 * @return View
	 */
	public static function view($view, $data = array(), $status = 200, array $headers = array())
	{
		$data = array_merge(View::defaults(), $data);

		return parent::view($view, $data, $status, $headers);
	}

}
