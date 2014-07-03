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

use Session;

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

}
