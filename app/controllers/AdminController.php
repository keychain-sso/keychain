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
		// Validate acl_manage rights
		Access::restrict(ACLFlags::ACL_MANAGE);

		// Perform the requested action
		switch ($action)
		{
			case 'remove':

				// Remove the selected permission
				Access::remove($id);

				Session::flash('messages.success', Lang::get('global.permission_removed'));

				return Redirect::to(URL::previous());

			default:

				// Query the ACL for all permissions
				$acl = Access::query();

				// Set display flags
				$show = new stdClass;
				$show->site = true;
				$show->subjects = true;
				$show->objects = true;
				$show->fields = true;

				// Build the view data
				$data = array(
					'acl'    => $acl,
					'show'   => $show,
					'return' => url(),
					'fields' => Field::lists('name', 'id'),
					'flags'  => Lang::get('permissions'),
				);

				return View::make('acl/full', 'global.modify_acl_entries', $data);
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
