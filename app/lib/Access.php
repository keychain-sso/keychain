<?php namespace Keychain;

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
use ACLType;
use Session;

/**
 * Access class
 *
 * Handles access restrictions over various resources across
 * the application
 *
 * @package     Keychain
 * @subpackage  Libraries
 */
class Access {

	/**
	 * Queries the ACL for permission flags on an object (user/group)
	 *
	 * @static
	 * @access public
	 * @param  string  $flag
	 * @param  int  $object
	 * @param  int  $field
	 * @return bool
	 */
	public static function check($flag, $object, $field = 0)
	{
		// Fetch all data related to the subject, which is always
		// the currently logged in user
		$subjectUser = Auth::user();
		$subjectGroups = Auth::groups();

		// Fetch all privileges tied to the subject and store them in the session
		if ( ! Session::has('security.acl'))
		{
			$acl = array();

			// Get the current user and group memberships
			$user = Auth::user();
			$groups = array();

			// Build a one-dimensional array of groups
			foreach ($user->groups as $item)
			{
				$groups[] = $item['group_id'];
			}

			// Query the ACL and look up all flags set for the user, or the
			// group memberships the user has
			$list = ACL::query();

			$list = $list->where(function($query) use ($user)
			{
				$query->where('subject_id', $user->id)->where('subject_type', ACLType::USER);
			});

			$list = $list->orWhere(function($query) use ($groups)
			{
				$query->whereIn('subject_id', $groups)->where('subject_type', ACLType::GROUP);
			});

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

			// Finally, save the ACL flagset to the session
			Session::put('security.acl', $acl);
		}
		else
		{
			$acl = Session::get('security.acl');
		}

		// Get the type of the object
		$type = get_class($object);

		switch ($type)
		{
			case 'User':

				// Does the user have access to his/her own field?
				if ($object->id == $subjectUser->id && isset($acl[ACLType::SELF.".{$field}.{$flag}"]))
				{
					return true;
				}

				// Does the subject have access to all users?
				if (isset($acl[ACLType::ALL.".{$field}.{$flag}"]))
				{
					return true;
				}

				// Does the subject have access to this specific object user?
				if (isset($acl["{$object->id}.".ACLType::USER.".{$field}.{$flag}"]))
				{
					return true;
				}

				// Does the subject have access to any of the object user's groups?
				foreach ($object->groups as $group)
				{
					if (isset($acl["{$group->group_id}.".ACLType::GROUP.".{$field}.{$flag}"]))
					{
						return true;
					}
				}

				break;

			case 'Group':

				if (isset($acl["{$object->id}.".ACLType::GROUP.".{$field}.{$flag}"]))
				{
					return true;
				}

				break;
		}

		// No access!
		return false;
	}

}
