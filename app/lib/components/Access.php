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
use Auth;
use Cache;
use Field;
use Group;
use HTTPStatus;
use stdClass;
use User;
use UserGroup;

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
	 * @param  User|Group  $object
	 * @param  string  $flag
	 * @param  Field  $field
	 * @param  bool  $expand
	 * @return object
	 */
	public static function getByObject($object, $flag = null, $field = null, $expand = false)
	{
		$acl = ACL::query();
		$class = get_class($object);

		// Determine object type
		switch ($class)
		{
			case 'User':

				$type = ACLTypes::USER;
				$self = $object->id == Auth::id();

				break;

			case 'Group':

				$type = ACLTypes::GROUP;
				$self = false;

				break;

			default:

				return array();
		}

		// Set the flag filter
		if ( ! is_null($flag))
		{
			$acl->where('access', $flag);
		}

		// Set field filter, if one was passed
		if ( ! is_null($field))
		{
			$acl->where('field_id', $field->id);
		}
		else
		{
			$acl->where('field_id', 0);
		}

		// Query for subjects
		$list = $acl->where(function($outer) use ($object, $type, $self)
		{
			// Get all subjects with direct access on this object
			$outer->where(function($inner) use ($object, $type)
			{
				$inner->where('object_id', $object->id)->where('object_type', $type);
			});

			// Get all subjects with access to all objects
			$outer->orWhere(function($inner)
			{
				$inner->where('object_id', 0)->where('object_type', ACLTypes::ALL);
			});

			// Get all subjects with access to itself, if applicable
			if ($self)
			{
				$outer->orWhere(function($inner)
				{
					$inner->where('object_id', 0)->where('object_type', ACLTypes::SELF);
				});
			}
		})->get();

		// Get the user IDs and group IDs
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

		// Split the ACL into global and local permissions
		$global = $list->filter(function($item)
		{
			$item->object_id == 0;
		});

		$local = $list->filter(function($item)
		{
			$item->object_id > 0;
		});

		// Add the raw ACL data
		$acl = new stdClass;

		$acl->permissions = new stdClass;
		$acl->permissions->global = $global;
		$acl->permissions->local = $local;

		$acl->subjects = new stdClass;
		$acl->subjects->users = array();
		$acl->subjects->groups = array();

		// Finally, get the list of users and groups
		if (count($userIds) > 0)
		{
			$acl->subjects->users = User::whereIn('id', $userIds)->with('primaryEmail')->get();
		}

		if (count($groupIds) > 0)
		{
			$acl->subjects->groups = Group::whereIn('id', $groupIds)->get();
		}

		return $acl;
	}

	/**
	 * Fetches all objects on which a specific subject has access to
	 *
	 * @static
	 * @access public
	 * @param  User|Group  $subject
	 * @param  string  $flag
	 * @return object
	 */
	public static function getBySubject($subject, $flag = null)
	{
		$acl = ACL::query();
		$class = get_class($subject);

		// Determine subject type
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

		// Set the flag filter
		if ( ! is_null($flag))
		{
			$acl->where('access', $flag);
		}

		// Query all objects to which this subject has access to
		$list = $acl->where('subject_id', $subject->id)->where('subject_type', $type)->get();

		// Get the user IDs, group IDs and field IDs
		$userIds = $list->filter(function($item)
		{
			return $item->object_type == ACLTypes::USER && $item->field_id == 0;
		})->lists('object_id');

		$groupIds = $list->filter(function($item)
		{
			return $item->object_type == ACLTypes::GROUP && $item->field_id == 0;
		})->lists('object_id');

		$fieldIds = $list->filter(function($item)
		{
			return $item->field_id > 0;
		})->lists('field_id');

		// Split the ACL into global and local permissions
		$global = $list->filter(function($item)
		{
			$item->object_id == 0;
		});

		$local = $list->filter(function($item)
		{
			$item->object_id > 0;
		});

		// Add the raw ACL data
		$acl = new stdClass;

		$acl->permissions = new stdClass;
		$acl->permissions->global = $global;
		$acl->permissions->local = $local;

		$acl->objects = new stdClass;
		$acl->objects->users = array();
		$acl->objects->groups = array();
		$acl->objects->fields = array();

		// Finally, get the list of users, groups and fields
		if (count($userIds) > 0)
		{
			$acl->objects->users = User::whereIn('id', $userIds)->with('primaryEmail')->get();
		}

		if (count($groupIds) > 0)
		{
			$acl->objects->groups = Group::whereIn('id', $groupIds)->get();
		}

		if (count($fieldIds) > 0)
		{
			$acl->objects->fields = Field::whereIn('id', $fieldIds)->get();
		}

		return $acl;
	}

}
