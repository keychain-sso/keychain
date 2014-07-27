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
		return View::make('group/list', 'global.groups', $this->getGroupListData());
	}

	/**
	 * Opens the create group screen
	 *
	 * @access public
	 * @return View
	 */
	public function getCreate()
	{
		// Get the group list info
		$data = $this->getGroupListData();

		// Validate manage rights
		Access::restrict(ACLFlags::GROUP_MANAGE);

		// Merge the list data with view data
		$data = array_merge($data, array(
			'group'  => new Group,
			'modal'  => 'group.editor',
		));

		return View::make('group/list', 'group.create_new_group', $data);
	}

	/**
	 * Handles create group POST events
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postCreate()
	{
		// Validate manage rights
		Access::restrict(ACLFlags::GROUP_MANAGE);

		// Validate posted fields
		$validator = Validator::make(Input::all(), array(
			'name'        => 'required|alpha_space|max:80|unique:groups,name',
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

		// Update the group information
		$group = new Group;
		$group->name = Input::get('name');
		$group->description = Input::get('description');
		$group->type = Input::get('type');
		$group->notify = Input::get('notify');
		$group->hash = Utilities::hash($group);
		$group->auto_join = Input::has('auto_join');
		$group->save();

		// Redirect back to the group list
		Session::flash('messages.success', Lang::get('group.group_created'));

		return Redirect::to('group/list');
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
		$data = $this->getGroupViewData($group);

		return View::make('group/view', 'group.members', $data);
	}

	/**
	 * Handles POST events for the view group page
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postView()
	{
		// Fetch the associated group
		$hash = Input::get('hash');
		$group = Group::where('hash', $hash)->firstOrFail();

		// Validate edit rights
		Access::restrict(ACLFlags::GROUP_EDIT, $group);

		// Check if users were selected
		if (Input::has('users'))
		{
			// Populate a list of user_ids to delete
			$users = User::whereIn('hash', Input::get('users'))->get();
			$userIds = $users->lists('id');

			// Clear the ACL cache for the selected users
			foreach ($users as $user)
			{
				Cache::tags("security.user.{$user->id}")->flush();
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
		$data = $this->getGroupViewData($group);

		// Validate edit rights
		Access::restrict(ACLFlags::GROUP_EDIT, $group);

		return View::make('group/view', 'group.edit_group', array_merge($data, array('modal'  => 'group.editor')));
	}

	/**
	 * Handles POST events for the group details editor
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postEdit()
	{
		// Fetch the associated group
		$hash = Input::get('hash');
		$group = Group::where('hash', $hash)->firstOrFail();

		// Validate edit rights
		Access::restrict(ACLFlags::GROUP_EDIT, $group);

		// Validate posted fields
		$validator = Validator::make(Input::all(), array(
			'name'        => 'required|alpha_space|max:80|unique:groups,name,'.$group->id,
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
		$group->name = Input::get('name');
		$group->description = Input::get('description');
		$group->type = Input::get('type');
		$group->notify = Input::get('notify');
		$group->auto_join = Input::has('auto_join');
		$group->save();

		// Redirect back to the previous URL
		Session::flash('messages.success', Lang::get('group.info_saved'));

		return Redirect::to(URL::previous());
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
		$editor = Access::check(ACLFlags::GROUP_EDIT, $group);

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
				Cache::tags("security.user.{$userId}")->flush();

				// Redirect to previous URL
				Session::flash('messages.success', Lang::get('group.group_joined'));

				return Redirect::to(URL::previous());
			}

			// This is a request only group, show the request modal
			else if ($group->type == GroupTypes::REQUEST)
			{
				$data = $this->getGroupViewData($group);

				return View::make('group/view', 'group.join_group', array_merge($data, array('modal' => 'group.join')));
			}
		}
		else
		{
			App::abort(HTTPStatus::FORBIDDEN);
		}
	}

	/**
	 * Handles POST events for the join group modal
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postJoin()
	{
		$userId = Auth::id();
		$userName = Auth::user()->name;

		// Fetch the associated group
		$hash = Input::get('hash');
		$group = Group::where('hash', $hash)->firstOrFail();

		// Check if user is already a member of this group
		$member = UserGroup::where('user_id', $userId)->where('group_id', $group->id)->count() > 0;

		// Only a non-member can request access to a request-only group
		if ( ! $member && $group->type == GroupTypes::REQUEST)
		{
			// Validate posted fields
			$validator = Validator::make(Input::all(), array('justification' => 'required'));

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

			// Build the ACL query
			$query = new stdClass;
			$query->entity = $group;
			$query->flag = ACLFlags::GROUP_EDIT;

			// Get a list of users who has group_edit rights
			$editors = Access::query(QueryMethods::BY_OBJECT, $query, true)->users;

			// Send the join notification to each editor
			foreach ($editors as $editor)
			{
				$action = Lang::get('email.join_request', array(
					'user'  => $userName,
					'group' => $group->name,
				));

				$data = array(
					'action'        => $action,
					'justification' => Input::get('justification'),
					'user'          => $editor,
					'group'         => $group,
				);

				Mail::queue('emails/group', $data, function($message) use ($editor)
				{
					$message->to($editor->primaryEmail[0]->address)->subject(Lang::get('email.subject_group'));
				});
			}

			// Redirect back to the previous URL
			Session::flash('messages.success', Lang::get('group.join_request_submitted'));

			return Redirect::to("group/view/{$group->hash}");
		}
		else
		{
			App::abort(HTTPStatus::FORBIDDEN);
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
		$editor = Access::check(ACLFlags::GROUP_EDIT, $group);

		// Only members may leave an open group
		// Users with group_edit rights can leave any type of group
		if ($member && ($editor || $group->type == GroupTypes::OPEN))
		{
			// Remove the user from the group
			UserGroup::where('user_id', $userId)->where('group_id', $group->id)->delete();

			// Clear the ACL cache for current user
			Cache::tags("security.user.{$userId}")->flush();

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
	 * @param  int  $id
	 * @return Redirect
	 */
	public function getRequests($hash, $action = null, $id = 0)
	{
		// Get the group and request details
		$group = Group::where('hash', $hash)->firstOrFail();
		$request = GroupRequest::find($id);

		// Validate edit rights
		Access::restrict(ACLFlags::GROUP_EDIT, $group);

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
				Cache::tags("security.user.{$request->user_id}")->flush();

				// Remove the group request
				$request->delete();

				// Send approval notification to user
				$data = array(
					'action' => Lang::get('email.request_approved', array('group' => $group->name)),
					'user'   => $request->user,
					'group'  => $group,
				);

				Mail::queue('emails/group', $data, function($message) use ($request)
				{
					$message->to($request->user->primaryEmail[0]->address)->subject(Lang::get('email.subject_group'));
				});

				// Redirect to previous URL
				Session::flash('messages.success', Lang::get('group.request_approved'));

				return Redirect::to(URL::previous());

			case 'reject':

				// Remove the group request
				$request->delete();

				// Send rejection notification to user
				$data = array(
					'action' => Lang::get('email.request_rejected', array('group' => $group->name)),
					'user'   => $request->user,
					'group'  => $group,
				);

				Mail::queue('emails/group', $data, function($message) use ($request)
				{
					$message->to($request->user->primaryEmail[0]->address)->subject(Lang::get('email.subject_group'));
				});

				// Redirect to previous URL
				Session::flash('messages.success', Lang::get('group.request_rejected'));

				return Redirect::to(URL::previous());

			default:

				$data = $this->getGroupViewData($group);
				$requests = GroupRequest::where('group_id', $group->id)->with('user')->get();

				// Merge the view data with group info
				$data = array_merge($data, array(
					'requests' => $requests,
					'modal'    => 'group.requests',
				));

				return View::make('group/view', 'group.membership_requests', $data);
		}
	}

	/**
	 * Shows the add user screen
	 *
	 * @access public
	 * @param  string  $hash
	 * @return View
	 */
	public function getAddUser($hash)
	{
		// Fetch the group information
		$group = Group::where('hash', $hash)->firstOrFail();
		$data = $this->getGroupViewData($group);

		// Validate edit rights
		Access::restrict(ACLFlags::GROUP_EDIT, $group);

		// Get users that are not already members
		$length = Config::get('view.icon_length');
		$members = UserGroup::where('group_id', $group->id)->lists('user_id');
		$users = User::whereNotIn('id', $members)->paginate($length);

		// Merge the group data with view data
		$data = array_merge($data, array(
			'users'  => $users,
			'modal'  => 'group.users',
		));

		return View::make('group/view', 'group.add_users', $data);
	}

	/**
	 * Handles POST events for the add user screen
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postAddUser()
	{
		// Fetch the associated group
		$hash = Input::get('hash');
		$group = Group::where('hash', $hash)->firstOrFail();

		// Validate edit rights
		Access::restrict(ACLFlags::GROUP_EDIT, $group);

		// Check if users were selected
		if (Input::has('users'))
		{
			// Populate a list of user_ids to add
			$users = User::whereIn('hash', Input::get('users'))->get();
			$userIds = $users->lists('id');
			$userGroups = array();

			foreach ($users as $user)
			{
				// Build the row to be inserted into the table
				$userGroups[] = array(
					'user_id'  => $user->id,
					'group_id' => $group->id,
				);

				// Clear the ACL cache for the selected users
				Cache::tags("security.user.{$user->id}")->flush();
			}

			// Remove these users from the group to avoid duplicate entries
			UserGroup::whereIn('user_id', $userIds)->where('group_id', $group->id)->delete();

			// Add the users to the group
			UserGroup::insert($userGroups);

			// Redirect back to the group
			Session::flash('messages.success', Lang::get('group.users_added'));

			return Redirect::to("group/view/{$group->hash}");
		}
		else
		{
			Session::flash('messages.error', Lang::get('group.users_not_selected'));

			return Redirect::to(URL::previous());
		}
	}

	/**
	 * Fetches the permissions for the group
	 *
	 * @access public
	 * @param  string  $hash
	 * @return View
	 */
	public function getPermissions($hash)
	{
		// Fetch the group information
		$group = Group::where('hash', $hash)->firstOrFail();
		$data = $this->getGroupViewData($group);

		// Validate acl_manage rights
		Access::restrict(ACLFlags::ACL_MANAGE);

		// Build the ACL query
		$query = new stdClass;
		$query->entity = $group;

		// Query the ACL for group permissions
		$acl = Access::query(QueryMethods::BY_SUBJECT, $query);

		// Set display flags
		$show = new stdClass;
		$show->site = true;
		$show->subjects = false;
		$show->objects = true;
		$show->fields = false;

		// Merge the group data with view data
		$data = array_merge($data, array(
			'acl'    => $acl,
			'show'   => $show,
			'return' => url("group/view/{$group->hash}"),
			'fields' => Field::lists('name', 'id'),
			'flags'  => Access::flags(),
			'modal'  => 'acl.modal',
			'subject' => $group,
		));

		return View::make('group/view', 'group.group_permissions', $data);
	}

	/**
	 * Deletes a specific group
	 *
	 * @access public
	 * @param  string  $hash
	 * @return View
	 */
	public function getDelete($hash)
	{
		// Get the group details
		$group = Group::where('hash', $hash)->firstOrFail();
		$members = UserGroup::where('group_id', $group->id)->lists('user_id');

		// Validate group_manage rights
		Access::restrict(ACLFlags::GROUP_MANAGE);

		// Clear the ACL cache for all members of the group
		foreach ($members as $member)
		{
			Cache::tags("security.user.{$member}")->flush();
		}

		// Delete the group
		$group->delete();

		// Redirect back to the group list
		Session::flash('messages.success', Lang::get('group.group_deleted'));

		return Redirect::to('group/list');
	}

	/**
	 * Performs group search on a query via AJAX
	 *
	 * @access public
	 * @return View
	 */
	public function getSearch()
	{
		if (Request::ajax())
		{
			// Get the search criteria
			$exclude = Input::has('exclude') ? explode(',', Input::get('exclude')) : array();
			$max = Config::get('view.list_length') - count($exclude);

			// Search the group and return the results
			if ($max > 0)
			{
				$groups = Group::search(Input::all())->take($max)->get();

				return View::make('common/list', null, array('items' => $groups));
			}
		}
		else
		{
			App::abort(HTTPStatus::NOTFOUND);
		}
	}

	/**
	 * Performs member search on a query via AJAX
	 *
	 * @access public
	 * @param  string  $hash
	 * @return View
	 */
	public function getMemberSearch($hash)
	{
		if (Request::ajax())
		{
			// Get the search criteria
			$exclude = Input::has('exclude') ? explode(',', Input::get('exclude')) : array();
			$max = Config::get('view.icon_length') - count($exclude);

			// Do the search only if we need to return any users
			if ($max > 0)
			{
				$users = User::search(Input::all());

				// Apply group membership filter
				$group = Group::where('hash', $hash)->firstOrFail();
				$members = UserGroup::where('group_id', $group->id)->lists('user_id');

				if (Input::get('member'))
				{
					$users->whereIn('id', $members);
				}
				else
				{
					$users->whereNotIn('id', $members);
				}

				// Get the results of the search
				$users = $users->take($max)->get();

				// Build the view data
				$data = array(
					'users'    => $users,
					'checkbox' => Input::get('checkbox'),
				);

				// Return the icon set
				return View::make('common/icon', null, $data);
			}
		}
		else
		{
			App::abort(HTTPStatus::NOTFOUND);
		}
	}

	/**
	 * Fetches the group list
	 *
	 * @access private
	 * @param  Group  $group
	 * @return array
	 */
	private function getGroupListData()
	{
		$length = Config::get('view.list_length');

		// Get a list of all groups
		$groupItems = Group::paginate($length);

		// Get a list of current user's memberships
		$userGroups = UserGroup::where('user_id', Auth::id())->lists('group_id');

		// Return the list data
		return array(
			'groupItems' => $groupItems,
			'userGroups' => $userGroups,
		);
	}

	/**
	 * Fetches a specific group's details
	 *
	 * @access private
	 * @param  Group  $group
	 * @return array
	 */
	private function getGroupViewData($group)
	{
		$userId = Auth::id();
		$length = Config::get('view.icon_length');

		// Get the group members
		$userGroups = UserGroup::where('group_id', $group->id)->with(array('user', 'emails'))->paginate($length);

		// Check if current user is a member
		$member = UserGroup::where('user_id', $userId)->where('group_id', $group->id)->count() > 0;

		// Check if we need to show the add member link
		if (count($members = $userGroups->lists('user_id')) > 0)
		{
			$canAdd = User::whereNotIn('id', $members)->count() > 0;
		}
		else
		{
			$canAdd = true;
		}

		// Get pending join requests for the group
		$requests = GroupRequest::where('group_id', $group->id);
		$requestCount = $requests->count();

		// Check if current user has a pending join request
		$pending = in_array($userId, $requests->lists('user_id'));

		// Determine if the group actions bar should be displayed
		$actions = false;

		// Display if user can modify the ACL
		if (Access::check(ACLFlags::ACL_MANAGE))
		{
			$actions = true;
		}

		// Display if user can manage the group
		if (Access::check(ACLFlags::GROUP_MANAGE))
		{
			$actions = true;
		}

		// Display if user can edit the group
		if ($editor = Access::check(ACLFlags::GROUP_EDIT, $group))
		{
			$actions = true;
		}

		// Display if user is a member of an open group
		else if ($member && $group->type == GroupTypes::OPEN)
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
			'canAdd'       => $canAdd,
			'actions'      => $actions,
			'editor'       => $editor,
			'requestCount' => $requestCount,
			'pending'      => $pending,
			'remove'       => count($userGroups) > 0,
		);
	}

}
