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
 * PermissionController class
 *
 * Handles all ACL management screens
 *
 * @package     Keychain
 * @subpackage  Controllers
 */
class PermissionController extends BaseController {

	/**
	 * Validates user permissions
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		Access::restrict(ACLFlags::ACL_MANAGE);
	}

	/**
	 * Displays the permissions page
	 *
	 * @access public
	 * @return View
	 */
	public function getIndex()
	{
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
			'fields' => Field::lists('name', 'id'),
			'flags'  => Lang::get('flag'),
		);

		return View::make('permission/full', 'global.modify_acl_entries', $data);
	}

	/**
	 * Handles POST events for the permissions screens
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postIndex()
	{
		$status = Access::save(Input::all());

		if ($status === true)
		{
			Session::flash('messages.success', Lang::get('permission.permission_added'));
		}
		else
		{
			Session::flash('messages.error', $status);
		}

		return Redirect::to(URL::previous())->withInput();
	}

	/**
	 * Handles removal of a specific permission
	 *
	 * @access public
	 * @param  int  $id
	 * @return Redirect
	 */
	public function getRemove($id)
	{
		Access::remove($id);

		Session::flash('messages.success', Lang::get('permission.permission_removed'));

		return Redirect::to(URL::previous());
	}

}
