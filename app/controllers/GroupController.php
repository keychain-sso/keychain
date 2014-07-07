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
		$groups = Group::paginate($length);
		$userGroups = UserGroup::where('user_id', Auth::id())->get();
		$membership = array();

		// Cache group membership data
		foreach ($userGroups as $userGroup)
		{
			$membership[$userGroup->group_id] = true;
		}

		// Build the view data
		$data = array(
			'groups'     => $groups,
			'membership' => $membership,
		);

		return View::make('group/list', 'global.groups', $data);
	}

	/**
	 * Displays group details and members
	 *
	 * @access public
	 * @param  string  $hash
	 * @return \Illuminate\Support\Facades\View
	 */
	public function getView($hash)
	{
		$length = Config::get('view.list_length');
		$group = Group::where('hash', $hash)->firstOrFail();
		$userGroups = UserGroup::where('group_id', $group->id)->with('user')->paginate($length);
		$member = UserGroup::where('user_id', Auth::id())->where('group_id', $group->id)->count() == 1;

		// Determine if the group actions bar should be displayed
		$actions = false;

		// Display if user can edit the group
		if ($manager = Access::check(Permissions::GROUP_EDIT, $group))
		{
			$actions = true;
		}

		// Display if user is a member of the group
		else if ($member)
		{
			$actions = true;
		}

		// Display if the group is an open or request group
		else if ($group->type == GroupTypes::OPEN || $group->type == GroupTypes::REQUEST)
		{
			$actions = true;
		}

		// Build the view data
		$data = array(
			'group'      => $group,
			'userGroups' => $userGroups,
			'member'     => $member,
			'actions'    => $actions,
			'manager'    => $manager,
		);

		return View::make('group/view', 'group.members', $data);
	}

}
