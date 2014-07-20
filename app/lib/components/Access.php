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
use HTTPStatus;
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
	 * @param  int  $field
	 * @return bool
	 */
	public static function check($flag, $object = null, $field = 0)
	{
		// Fetch all data related to the subject, which is always
		// the currently logged in user
		$subjectUser = Auth::user();
		$subjectGroups = Auth::groups();

		// Fetch all privileges tied to the subject and store them in the session
		$acl = Cache::tags("security.user.{$subjectUser->id}")->remember('access.check', 60, function()
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
				if ($object->id == $subjectUser->id && isset($acl[ACLTypes::SELF.".{$field}.{$flag}"]))
				{
					return true;
				}

				// Does the subject have access to all users?
				if (isset($acl[ACLTypes::ALL.".{$field}.{$flag}"]))
				{
					return true;
				}

				// Does the subject have access to this specific object user?
				if (isset($acl["{$object->id}.".ACLTypes::USER.".{$field}.{$flag}"]))
				{
					return true;
				}

				// Does the subject have access to any of the object user's groups?
				foreach ($object->groups as $group)
				{
					if (isset($acl["{$group->group_id}.".ACLTypes::GROUP.".{$field}.{$flag}"]))
					{
						return true;
					}
				}

				break;

			case 'Group':

				// Does the subject have access to all groups?
				if (isset($acl[ACLTypes::ALL.".{$field}.{$flag}"]))
				{
					return true;
				}

				// Does the subject have access directly to the group?
				if (isset($acl["{$object->id}.".ACLTypes::GROUP.".{$field}.{$flag}"]))
				{
					return true;
				}

				break;

			default:

				// Does the subject have access to all objects?
				if (isset($acl[ACLTypes::ALL.".{$field}.{$flag}"]))
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
	 * @param  int  $field
	 */
	public static function restrict($flag, $object = null, $field = 0)
	{
		if ( ! static::check($flag, $object, $field))
		{
			App::abort(HTTPStatus::FORBIDDEN);
		}
	}

	/**
	 * Fetches a list of users and groups who have permissions on a
	 * specific object
	 *
	 * @static
	 * @access public
	 * @param  string  $flag
	 * @param  User|Group  $object
	 * @param  int  $field
	 * @param  bool  $expand
	 * @return array
	 */
	public static function lists($flag, $object = null, $field = 0, $expand = false)
	{
		return Cache::tags("security.global")->remember('access.lists', 60, function() use ($flag, $object, $field, $expand)
		{
			$acl['users'] = array();
			$acl['groups'] = array();

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

			// Query all subjects that have access to this object
			// We also fetch the subjects who have access to all objects against this flag
			$list = ACL::where('access', $flag)->where('field_id', $field)->where(function($outer) use ($object, $type)
			{
				$outer->where(function($inner) use ($object, $type)
				{
					$inner->where('object_id', $object->id)->where('object_type', $type);
				})->orWhere(function($inner)
				{
					$inner->where('object_id', 0)->where('object_type', ACLTypes::ALL);
				});
			})->get();

			// Populate the subject IDs
			foreach ($list as $item)
			{
				switch ($item->subject_type)
				{
					case ACLTypes::USER:

						$acl['users'][] = $item->subject_id;

						break;

					case ACLTypes::GROUP:

						if ($expand)
						{
							$users = UserGroup::where('group_id', $item->subject_id)->lists('user_id');
							$acl['users'] = array_merge($acl['users'], $users);
						}
						else
						{
							$acl['groups'][] = $item->subject_id;
						}

						break;
				}
			}

			// Remove duplicate entries
			$acl['users'] = array_unique($acl['users']);
			$acl['groups'] = array_unique($acl['groups']);

			return $acl;
		});
	}

}
