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
	 * @return View
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
	 * @return View
	 */
	public function getView($hash)
	{
		// Get the group information
		$group = Group::where('hash', $hash)->firstOrFail();
		$data = $this->getGroupData($group);

		return View::make('group/view', 'group.members', $data);
	}

	/**
	 * Handles post events for the view group page
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postView()
	{
		if (Input::has('_remove'))
		{
			// Fetch the associated group
			$hash = Input::get('hash');
			$group = Group::where('hash', $hash)->firstOrFail();

			// Validate edit rights
			Access::restrict(Permissions::GROUP_EDIT, $group);

			// Check if users were selected
			if (Input::has('users'))
			{
				// Populate a list of user_ids to delete
				$users = User::whereIn('hash', Input::get('users'))->get();
				$userIds = array();

				foreach ($users as $user)
				{
					// Queue the user ID
					$userIds[] = $user->id;

					// Clear the ACL cache for this user
					Cache::tags("user.{$user->id}.security")->flush();
				}

				// Remove these users from the group
				UserGroup::whereIn('user_id', $userIds)->where('group_id', $group->id)->delete();

				Session::flash('messages.success', Lang::get('group.users_removed'));
			}
			else
			{
				Session::flash('messages.error', Lang::get('group.users_not_selected'));
			}

			return Redirect::to(URL::previous());
		}
	}

	/**
	 * Opens the group details editor
	 *
	 * @access public
	 * @param  string  $hash
	 * @return View
	 */
	public function getEdit($hash)
	{
		// Fetch the group information
		$group = Group::where('hash', $hash)->firstOrFail();
		$data = $this->getGroupData($group);

		// Validate edit rights
		Access::restrict(Permissions::GROUP_EDIT, $group);

		return View::make('group/view', 'group.edit_group', array_merge($data, array('modal' => 'editor')));
	}

	/**
	 * Handles post events for the group details editor
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postEdit()
	{
		if (Input::has('_save'))
		{
			// Fetch the associated group
			$hash = Input::get('hash');
			$group = Group::where('hash', $hash)->firstOrFail();

			// Validate edit rights
			Access::restrict(Permissions::GROUP_EDIT, $group);

			// Validate posted fields
			$validator = Validator::make(Input::all(), array(
				'name'        => 'required|alpha_space|max:80',
				'description' => 'required',
				'type'        => 'required|exists:group_types,id',
				'notify'      => 'required|in:0,1',
			));

			// Run the validator
			if ($validator->fails())
			{
				Session::flash('messages.error', $validator->messages()->all('<p>:message</p>'));

				return Redirect::to(URL::previous())->withInput();
			}

			// If group type is being change to open or closed, and there are open requests,
			// disallow user to change
			if (Input::get('type') != GroupTypes::REQUEST && GroupRequest::where('group_id', $group->id)->count() > 0)
			{
				Session::flash('messages.error', Lang::get('group.open_requests'));

				return Redirect::to(URL::previous())->withInput();
			}

			// Update the group information
			$group->name        = Input::get('name');
			$group->description = Input::get('description');
			$group->type        = Input::get('type');
			$group->notify      = Input::get('notify');
			$group->save();

			// Redirect back to the previous URL
			Session::flash('messages.success', Lang::get('group.info_saved'));

			return Redirect::to(URL::previous());
		}
	}

	/**
	 * Adds the current user to the group
	 *
	 * @access public
	 * @param  string  $hash
	 * @return Redirect|View
	 */
	public function getJoin($hash)
	{
		$userId = Auth::id();

		// Get the group details
		$group = Group::where('hash', $hash)->firstOrFail();

		// Check if user is already a member of this group
		$member = UserGroup::where('user_id', $userId)->where('group_id', $group->id)->count() > 0;

		// Does the user have group_edit rights?
		$editor = Access::check(Permissions::GROUP_EDIT, $group);

		// Only non-member can join open and request-only groups
		// Users with group_edit rights can join any group
		if ( ! $member && ($editor || $group->type != GroupTypes::CLOSED))
		{
			// This is an open group (or the user has group_edit rights), add the user right away
			if ($editor || $group->type == GroupTypes::OPEN)
			{
				$userGroup = new UserGroup;
				$userGroup->user_id = $userId;
				$userGroup->group_id = $group->id;
				$userGroup->save();

				// Clear the ACL cache for current user
				Cache::tags("user.{$userId}.security")->flush();

				// Redirect to previous URL
				Session::flash('messages.success', Lang::get('group.group_joined'));

				return Redirect::to(URL::previous());
			}

			// This is a request only group, show the request modal
			else if ($group->type == GroupTypes::REQUEST)
			{
				$data = $this->getGroupData($group);

				return View::make('group/view', 'group.join_group', array_merge($data, array('modal' => 'join')));
			}
		}
		else
		{
			App::abort(HTTPStatus::FORBIDDEN);
		}
	}

	/**
	 * Handles post events for the join group modal
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postJoin()
	{
		if (Input::has('_submit'))
		{
			$userId = Auth::id();

			// Fetch the associated group
			$hash = Input::get('hash');
			$group = Group::where('hash', $hash)->firstOrFail();

			// Check if user is already a member of this group
			$member = UserGroup::where('user_id', $userId)->where('group_id', $group->id)->count() > 0;

			// Only a non-member can request access to a request-only group
			if ( ! $member && $group->type == GroupTypes::REQUEST)
			{
				// Validate posted fields
				$validator = Validator::make(Input::all(), array(
					'justification' => 'required',
				));

				// Run the validator
				if ($validator->fails())
				{
					Session::flash('messages.error', $validator->messages()->all('<p>:message</p>'));

					return Redirect::to(URL::previous())->withInput();
				}

				// Insert the group request
				$request = new GroupRequest;
				$request->user_id = $userId;
				$request->group_id = $group->id;
				$request->justification = Input::get('justification');
				$request->save();

				// Redirect back to the previous URL
				Session::flash('messages.success', Lang::get('group.join_request_submitted'));

				return Redirect::to("group/view/{$group->hash}");
			}
			else
			{
				App::abort(HTTPStatus::FORBIDDEN);
			}
		}
	}

	/**
	 * Removes the current user from the group
	 *
	 * @access public
	 * @param  string  $hash
	 * @return Redirect
	 */
	public function getLeave($hash)
	{
		$userId = Auth::id();

		// Get the group details
		$group = Group::where('hash', $hash)->firstOrFail();

		// Check if user is already a member of this group
		$member = UserGroup::where('user_id', $userId)->where('group_id', $group->id)->count() > 0;

		// Does the user have group_edit rights?
		$editor = Access::check(Permissions::GROUP_EDIT, $group);

		// Only members may leave an open group
		// Users with group_edit rights can leave any type of group
		if ($member && ($editor || $group->type == GroupTypes::OPEN))
		{
			// Remove the user from the group
			UserGroup::where('user_id', $userId)->where('group_id', $group->id)->delete();

			// Clear the ACL cache for current user
			Cache::tags("user.{$userId}.security")->flush();

			// Redirect to previous URL
			Session::flash('messages.success', Lang::get('group.group_left'));

			return Redirect::to(URL::previous());
		}
		else
		{
			App::abort(HTTPStatus::FORBIDDEN);
		}
	}

	/**
	 * Withdraws group membership application for the user
	 *
	 * @access public
	 * @param  string  $hash
	 * @return Redirect
	 */
	public function getWithdraw($hash)
	{
		$userId = Auth::id();

		// Get the group details
		$group = Group::where('hash', $hash)->firstOrFail();

		// Membership requests can be removed for request groups only
		if ($group->type == GroupTypes::REQUEST)
		{
			// Delete the membership request
			GroupRequest::where('user_id', $userId)->where('group_id', $group->id)->delete();

			// Redirect to previous URL
			Session::flash('messages.success', Lang::get('group.membership_withdrawn'));

			return Redirect::to(URL::previous());
		}
		else
		{
			App::abort(HTTPStatus::FORBIDDEN);
		}
	}

	/**
	 * Displays membership requests for a group
	 *
	 * @access public
	 * @param  string  $hash
	 * @param  string  $action
	 * @param  int  $request
	 * @return Redirect
	 */
	public function getRequests($hash, $action = null, $request = 0)
	{
		// Get the group and request details
		$group = Group::where('hash', $hash)->firstOrFail();
		$request = GroupRequest::find($request);

		// Validate edit rights
		Access::restrict(Permissions::GROUP_EDIT, $group);

		// Perform the requested action
		switch ($action)
		{
			case 'approve':

				// Add the user to the group
				$userGroup = new UserGroup;
				$userGroup->user_id = $request->user_id;
				$userGroup->group_id = $request->group_id;
				$userGroup->save();

				// Clear the ACL cache for current user
				Cache::tags("user.{$request->user_id}.security")->flush();

				// Remove the group request
				$request->delete();

				// Redirect to previous URL
				Session::flash('messages.success', Lang::get('group.request_approved'));

				return Redirect::to(URL::previous());

			case 'reject':

				// Remove the group request
				$request->delete();

				// Redirect to previous URL
				Session::flash('messages.success', Lang::get('group.request_rejected'));

				return Redirect::to(URL::previous());

			default:

				$data = $this->getGroupData($group);
				$requests = GroupRequest::where('group_id', $group->id)->with('user')->get();

				// Merge the view data with group info
				$data = array_merge($data, array(
					'requests' => $requests,
					'modal'    => 'requests',
				));

				return View::make('group/view', 'group.membership_requests', $data);
		}
	}

	/**
	 * Fetches the group's details
	 *
	 * @access private
	 * @param  Group  $group
	 * @return array
	 */
	private function getGroupData($group)
	{
		$userId = Auth::id();
		$length = Config::get('view.icon_length');

		// Get the group members
		$userGroups = UserGroup::where('group_id', $group->id)->with(array('user', 'emails'))->paginate($length);

		// Check if current user is a member
		$member = UserGroup::where('user_id', $userId)->where('group_id', $group->id)->count() > 0;

		// Get pending join requests for the group
		$requestCount = GroupRequest::where('group_id', $group->id)->count();

		// Check if current user has a pending join request
		$pending = GroupRequest::where('user_id', $userId)->where('group_id', $group->id)->count();

		// Determine if the group actions bar should be displayed
		$actions = false;

		// Display if user can edit the group
		if ($editor = Access::check(Permissions::GROUP_EDIT, $group))
		{
			$actions = true;
		}

		// Display if user can manage the group
		if ($manager = Access::check(Permissions::GROUP_MANAGE, $group))
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

		// Return the group information
		return array(
			'group'        => $group,
			'userGroups'   => $userGroups,
			'member'       => $member,
			'actions'      => $actions,
			'editor'       => $editor,
			'manager'      => $manager,
			'requestCount' => $requestCount,
			'pending'      => $pending,
			'remove'       => count($userGroups) > 0,
			'modal'        => false,
		);
	}

}
