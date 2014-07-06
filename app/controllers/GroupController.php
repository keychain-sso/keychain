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
 * GroupController class
 *
 * Handles all user group related actions
 *
 * @package     Keychain
 * @subpackage  Controllers
 */
class GroupController extends BaseController {

	/**
	 * Displays the group list
	 *
	 * @access public
	 * @return \Illuminate\Support\Facades\View
	 */
	public function getList()
	{
		$length = Config::get('view.list_length');
		$userGroups = UserGroup::where('user_id', Auth::id())->get();
		$membership = array();

		// Cache group membership data
		foreach ($userGroups as $userGroup)
		{
			$membership[$userGroup->group_id] = true;
		}

		// Build the view data
		$data = array(
			'groups'     => Group::paginate($length),
			'membership' => $membership,
		);

		return View::make('group/list', 'global.groups', $data);
	}

}
