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
use QueryMethods;
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
	 * Queries the ACL based on the following parameters:
	 *
	 *  - entity : The entity being searched by (subject / object)
	 *  - field  : The field associated with the object
	 *  - flag   : The access flag for the ACL entry
	 *
	 * @static
	 * @access public
	 * @param  int  $method
	 * @param  object  $query
	 * @param  bool  $expand
	 * @return object
	 */
	public static function query($method = null, $query = array(), $expand = false)
	{
		$query = (object) $query;
		$acl = ACL::query();

		if (isset($method))
		{
			if (isset($query->entity))
			{
				$class = get_class($query->entity);

				// Determine the entity type
				switch ($class)
				{
					case 'User':

						$type = ACLTypes::USER;

						break;

					case 'Group':

						$type = ACLTypes::GROUP;

						break;
				}
			}

			// Based on the query method, add various filter criteria
			switch ($method)
			{
				case QueryMethods::BY_SUBJECT:

					// Set the subject filter
					if (isset($query->entity))
					{
						$acl->where('subject_id', $query->entity->id)->where('subject_type', $type);
					}

					break;

				case QueryMethods::BY_OBJECT:

					// Set the object filter
					if (isset($query->entity))
					{
						$acl->where(function($outer) use ($query, $type)
						{
							// Include direct object filter
							$outer->where(function($inner) use ($query, $type)
							{
								$inner->where('object_id', $query->entity->id)->where('object_type', $type);
							});

							// Include global object targets
							$outer->orWhere(function($inner)
							{
								$inner->where('object_id', 0)->where('object_type', ACLTypes::ALL);
							});

							// Include self object targets
							$outer->orWhere(function($inner)
							{
								$inner->where('object_id', 0)->where('object_type', ACLTypes::SELF);
							});
						});
					}

					// Set the field filter
					if (isset($query->field))
					{
						$acl->where('field_id', $query->field->id);
					}
					else
					{
						$acl->where('field_id', 0);
					}

					break;
			}

			// Set the flag filter
			if (isset($query->flag))
			{
				$acl->where('access', $query->flag);
			}
		}

		// Fetch the permission list
		$list = $acl->get();

		// Get the user IDs and group IDs
		switch ($method)
		{
			case QueryMethods::BY_SUBJECT:

				$userIds = $list->filter(function($item)
				{
					return $item->object_type == ACLTypes::USER;
				})->lists('object_id');

				$groupIds = $list->filter(function($item)
				{
					return $item->object_type == ACLTypes::GROUP;
				})->lists('object_id');

				break;

			case QueryMethods::BY_OBJECT:

				$userIds = $list->filter(function($item)
				{
					return $item->subject_type == ACLTypes::USER;
				})->lists('subject_id');

				$groupIds = $list->filter(function($item)
				{
					return $item->subject_type == ACLTypes::GROUP;
				})->lists('subject_id');

				break;
		}

		// Get the field IDs
		$fieldIds = $list->filter(function($item) { return $item->field_id > 0; })->lists('field_id');

		// If set to expand, get the users against the groupIds
		if ($expand)
		{
			$userIds = array_merge($userIds, UserGroup::whereIn('group_id', $groupIds)->lists('user_id'));
			$groupIds = array();
		}

		// Build the raw ACL data
		$acl = new stdClass;

		// Split the ACL into global and scope-based permissions
		$acl->site = $list->filter(function($item)
		{
			return str_contains($item->access, 'manage');
		});

		$acl->scope = $list->filter(function($item)
		{
			return ! str_contains($item->access, 'manage');
		});

		// Set the user, group and field data
		$acl->users = count($userIds) > 0 ? User::whereIn('id', $userIds)->with('primaryEmail')->get() : array();
		$acl->groups = count($groupIds) > 0 ? Group::whereIn('id', $groupIds)->get() : array();
		$acl->fields = count($fieldIds) > 0 ? Field::whereIn('id', $fieldIds)->get() : array();

		return $acl;
	}

}
