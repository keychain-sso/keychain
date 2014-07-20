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

use ACL;
use ACLTypes;
use App;
use Cache;
use Field;
use Group;
use HTTPStatus;
use User;
use UserGroup;

use Keychain\Facades\Auth;

/**
 * Access class
 *
 * Handles access restrictions over various resources across
 * the application
 *
 * @package     Keychain
 * @subpackage  Components
 */
class Access {

	/**
	 * Queries the ACL for permission flags on an object (user/group)
	 *
	 * @static
	 * @access public
	 * @param  string  $flag
	 * @param  User|Group  $object
	 * @param  Field  $field
	 * @return bool
	 */
	public static function check($flag, $object = null, $field = null)
	{
		// Fetch all data related to the subject, which is always
		// the currently logged in user
		$subjectUser = Auth::user();
		$subjectGroups = Auth::groups();

		// Set field ID to 0 if no field was passed
		if (is_null($field))
		{
			$field = (object) array('id' => 0);
		}

		// Fetch all privileges tied to the subject and store them in the session
		$acl = Cache::tags("security.user.{$subjectUser->id}")->remember('acl', 60, function()
		{
			$acl = array();

			// Get the current user and group memberships
			$user = Auth::user();
			$groups = $user->groups->lists('group_id');

			// Query the ACL and look up all flags set for the user, or the
			// group memberships the user has
			$list = ACL::query();

			$list = $list->where(function($query) use ($user)
			{
				$query->where('subject_id', $user->id)->where('subject_type', ACLTypes::USER);
			});

			if (count($groups) > 0)
			{
				$list = $list->orWhere(function($query) use ($groups)
				{
					$query->whereIn('subject_id', $groups)->where('subject_type', ACLTypes::GROUP);
				});
			}

			// Iterate through the list and build the access data
			foreach ($list->get() as $item)
			{
				// Object ID will be 0 if type is [self] or [all]
				if ($item->object_id > 0)
				{
					$acl["{$item->object_id}.{$item->object_type}.{$item->field_id}.{$item->access}"] = true;
				}
				else
				{
					$acl["{$item->object_type}.{$item->field_id}.{$item->access}"] = true;
				}
			}

			return $acl;
		});

		// Get the type of the object
		$type = get_class($object);

		switch ($type)
		{
			case 'User':

				// Does the user have access to his/her own field?
				if ($object->id == $subjectUser->id && isset($acl[ACLTypes::SELF.".{$field->id}.{$flag}"]))
				{
					return true;
				}

				// Does the subject have access to all users?
				if (isset($acl[ACLTypes::ALL.".{$field->id}.{$flag}"]))
				{
					return true;
				}

				// Does the subject have access to this specific object user?
				if (isset($acl["{$object->id}.".ACLTypes::USER.".{$field->id}.{$flag}"]))
				{
					return true;
				}

				// Does the subject have access to any of the object user's groups?
				foreach ($object->groups as $group)
				{
					if (isset($acl["{$group->group_id}.".ACLTypes::GROUP.".{$field->id}.{$flag}"]))
					{
						return true;
					}
				}

				break;

			case 'Group':

				// Does the subject have access to all groups?
				if (isset($acl[ACLTypes::ALL.".{$field->id}.{$flag}"]))
				{
					return true;
				}

				// Does the subject have access directly to the group?
				if (isset($acl["{$object->id}.".ACLTypes::GROUP.".{$field->id}.{$flag}"]))
				{
					return true;
				}

				break;

			default:

				// Does the subject have access to all objects?
				if (isset($acl[ACLTypes::ALL.".{$field->id}.{$flag}"]))
				{
					return true;
				}

				break;
		}

		// No access!
		return false;
	}

	/**
	 * Restricts access to a resource if an ACL query fails
	 *
	 * @static
	 * @access public
	 * @param  string  $flag
	 * @param  User|Group  $object
	 * @param  Field  $field
	 */
	public static function restrict($flag, $object = null, $field = null)
	{
		if ( ! static::check($flag, $object, $field))
		{
			App::abort(HTTPStatus::FORBIDDEN);
		}
	}

	/**
	 * Fetches all subjects who have permissions on a specific object
	 *
	 * @static
	 * @access public
	 * @param  string  $flag
	 * @param  User|Group  $object
	 * @param  Field  $field
	 * @param  bool  $expand
	 * @return object
	 */
	public static function getByObject($flag, $object, $field = null, $expand = false)
	{
		// Determine object type
		$class = get_class($object);

		switch ($class)
		{
			case 'User':

				$type = ACLTypes::USER;

				break;

			case 'Group':

				$type = ACLTypes::GROUP;

				break;

			default:

				return array();
		}

		// Set field ID to 0 if no field was passed
		if (is_null($field))
		{
			$field = (object) array('id' => 0);
		}

		// Query all subjects that have access to this object
		// We also fetch the subjects who have access to all objects against this flag
		$list = ACL::where('access', $flag)->where('field_id', $field->id)->where(function($outer) use ($object, $type)
		{
			$outer->where(function($inner) use ($object, $type)
			{
				$inner->where('object_id', $object->id)->where('object_type', $type);
			})->orWhere(function($inner)
			{
				$inner->where('object_id', 0)->where('object_type', ACLTypes::ALL);
			});
		})->get();

		// Get the user_ids and group_ids
		$userIds = $list->filter(function($item)
		{
			return $item->subject_type == ACLTypes::USER;
		})->lists('subject_id');

		$groupIds = $list->filter(function($item)
		{
			return $item->subject_type == ACLTypes::GROUP;
		})->lists('subject_id');

		// If set to expand, get the user's against the groupIds
		if ($expand)
		{
			$userIds = array_merge($userIds, UserGroup::whereIn('group_id', $groupIds)->lists('user_id'));
			$groupIds = array();
		}

		// Finally, get the list of users and groups
		if (count($userIds) > 0)
		{
			$acl['users'] = User::whereIn('id', $userIds)->with('primaryEmail')->get();
		}

		if (count($groupIds) > 0)
		{
			$acl['groups'] = Group::whereIn('id', $groupIds)->get();
		}

		return (object) $acl;
	}

	/**
	 * Fetches all objects on which a specific subject has access to
	 *
	 * @static
	 * @access public
	 * @param  string  $flag
	 * @param  User|Group  $subject
	 * @return object
	 */
	public static function getBySubject($flag, $subject)
	{
	}

}
