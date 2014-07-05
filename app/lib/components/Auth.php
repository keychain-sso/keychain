<?php namespace Keychain\Components;

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

use Cookie;
use Session;
use User;

/**
 * Auth class
 *
 * Abstraction over \Illuminate\Support\Facades\Auth
 *
 * @package     Keychain
 * @subpackage  Components
 */
class Auth extends \Illuminate\Support\Facades\Auth {

	/**
	 * Returns the group memberships of the logged in user
	 *
	 * @static
	 * @access public
	 * @return Illuminate\Database\Eloquent\Model
	 */
	public static function groups()
	{
		if ( ! Session::has('security.groups'))
		{
			Session::put('security.groups', parent::user()->groups);
		}

		return Session::get('security.groups');
	}

	/**
	 * Log the user out of the application.
	 *
	 * @static
	 * @access public
	 * @return void
	 */
	public static function logout()
	{
		parent::logout();

		Session::flush();
	}

}
