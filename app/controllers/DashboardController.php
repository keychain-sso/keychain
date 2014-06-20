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
 * DashboardController class
 *
 * Handles display and actions on the user's dashboard
 *
 * @package     Keychain
 * @subpackage  Controllers
 */
class DashboardController extends BaseController {

	/**
	 * Displays the user dash
	 *
	 * @access public
	 * @return \Illuminate\Support\Facades\View
	 */
	public function getIndex()
	{
		return View::make('dashboard/index');
	}

}
