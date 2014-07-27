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
 * AdminController class
 *
 * Handles all admin screen related operations
 *
 * @package     Keychain
 * @subpackage  Controllers
 */
class AdminController extends BaseController {

	/**
	 * Displays the permissions page, and handles
	 * delete operations
	 *
	 * @access public
	 * @param  string  $action
	 * @param  int  $id
	 * @return View
	 */
	public function getPermissions($action = null, $id = 0)
	{
		// Perform the requested action
		switch ($action)
		{
			case 'remove':

				// Remove the selected permission
				Access::remove($id);

				Session::flash('messages.success', Lang::get('global.permission_removed'));

				return Redirect::to(URL::previous());

			default:

				// Not implemented yet!
				App::abort(HTTPStatus::NOTFOUND);
		}
	}

	/**
	 * Handles POST events for the permissions screens
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postPermissions()
	{
		// Validate manage rights
		Access::restrict(ACLFlags::ACL_MANAGE);

		// Save the ACL data and show the status
		$status = Access::save(Input::all());

		if ($status === true)
		{
			Session::flash('messages.success', Lang::get('global.permission_added'));
		}
		else
		{
			Session::flash('messages.error', $status);
		}

		return Redirect::to(URL::previous())->withInput();
	}

}
