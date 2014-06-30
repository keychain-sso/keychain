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
 * @since       Version 1.0
 * @filesource
 */

/**
 * TokenController class
 *
 * Handles field validation using tokens
 *
 * @package     Keychain
 * @subpackage  Controllers
 */
class TokenController extends BaseController {

	/**
	 * Verifies a specific token
	 *
	 * @access public
	 * @param  string  $type
	 * @param  string  $token
	 * @return \Illuminate\Support\Facades\View
	 */
	public function getVerify($type, $token)
	{
	}

}
