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
use ACLFlag;
use ACLFlags;
use ACLTypes;
use App;
use Auth;
use Cache;
use Field;
use Group;
use HTTPStatus;
use Lang;
use QueryMethods;
use stdClass;
use User;
use UserGroup;
use Validator;

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
	 * Filter and return ACL flags
	 *
	 * @static
	 * @access public
	 * @param  object  $filter
	 * @return array
	 */
	public static function flags($filter = null)
	{
		$flags = ACLFlag::query();

		if ( ! is_null($filter))
		{
			// Apply site-wide permission filters
			if ( ! $filter->site)
			{
				$flags->where('name', 'not like', '%manage%');
			}

			// Apply field permission filters
			if ( ! $filter->fields)
			{
				$flags->where('name', 'not like', '%field%');
			}
		}

		// Fetch the flags that match the filter
		$filtered = $flags->lists('name', 'name');

		// Get the complete list of flags and descriptions
		$all = Lang::get('permissions');

		// Filter and return the flags
		return array_intersect_key($all, $filtered);
	}

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
					$acl["{$item->object_id}.{$item->object_type}.{$item->field_id}.{$item->flag}"] = true;
				}
				else
				{
					$acl["{$item->object_type}.{$item->field_id}.{$item->flag}"] = true;
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
	 * Returns all manager flags for the user
	 *
	 * @static
	 * @access public
	 * @return bool
	 */
	public static function manager()
	{
		return Cache::tags('security.user.'.Auth::id())->remember('admin', 60, function()
		{
			return (object) array(
				'acl' => static::check(ACLFlags::ACL_MANAGE),
				'field' => static::check(ACLFlags::FIELD_MANAGE),
				'group' => static::check(ACLFlags::GROUP_MANAGE),
				'user' => static::check(ACLFlags::USER_MANAGE),
			);
		});
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

		// Get the user IDs
		$subjectUserIds = array_merge(array(0), $list->filter(function($item)
		{
			return $item->subject_type == ACLTypes::USER;
		})->lists('subject_id'));

		$objectUserIds = array_merge(array(0), $list->filter(function($item)
		{
			return $item->object_type == ACLTypes::USER;
		})->lists('object_id'));

		// Get the group IDs
		$subjectGroupIds = array_merge(array(0), $list->filter(function($item)
		{
			return $item->subject_type == ACLTypes::GROUP;
		})->lists('subject_id'));

		$objectGroupIds = array_merge(array(0), $list->filter(function($item)
		{
			return $item->object_type == ACLTypes::GROUP;
		})->lists('object_id'));

		// Get the field IDs
		$fieldIds = array_merge(array(0), $list->filter(function($item) {
			return $item->field_id > 0;
		})->lists('field_id'));

		// If set to expand, get the users against the groupIds
		if ($expand)
		{
			$subjectUserIds = array_merge($subjectUserIds, UserGroup::whereIn('group_id', $subjectGroupIds)->lists('user_id'));
			$objectUserIds = array_merge($objectUserIds, UserGroup::whereIn('group_id', $objectGroupIds)->lists('user_id'));
		}

		// Build the raw ACL data
		$acl = new stdClass;
		$acl->users = new stdClass;
		$acl->groups = new stdClass();

		// Split the ACL into global and scope-based permissions
		$acl->site = $list->filter(function($item)
		{
			return str_contains($item->flag, 'manage');
		});

		$acl->scope = $list->filter(function($item)
		{
			return ! str_contains($item->flag, 'manage');
		});

		// Set the user data
		$acl->users->subjects = User::whereIn('id', $subjectUserIds)->with('primaryEmail')->get();
		$acl->users->objects = User::whereIn('id', $objectUserIds)->with('primaryEmail')->get();

		// Set the group data
		$acl->groups->subjects = Group::whereIn('id', $subjectGroupIds)->get();
		$acl->groups->objects = Group::whereIn('id', $objectGroupIds)->get();

		// Set the field data
		$acl->fields = Field::whereIn('id', $fieldIds)->get();

		return $acl;
	}

	/**
	 * Creates an entry in the ACL based on the posted data
	 *
	 * @static
	 * @access public
	 * @param  array  $data
	 * @return string|bool
	 */
	public static function save($data)
	{
		$entry = (object) $data;

		// Set up the validation rules
		$rules = array(
			'subject_type' => 'required',
			'field'        => 'required|exists:fields,id',
			'flag'         => 'required|exists:acl_flags,name',
		);

		// Determine the subject lookup rules
		if (isset($entry->subject_type))
		{
			switch ($entry->subject_type)
			{
				case ACLTypes::USER:

					$rules['subject_id'] = 'required|exists:users,id';

					break;

				case ACLTypes::GROUP:

					$rules['subject_id'] = 'required|exists:groups,id';

					break;

				default:

					return Lang::get('global.invalid_subject');
			}
		}

		// Based on the flag, determine whether we need the object
		if (isset($entry->flag))
		{
			// We need the object_type for all view/edit permissions
			if (str_contains($entry->flag, 'edit') || str_contains($entry->flag, 'view'))
			{
				$rules['object_type'] = 'required|exists:acl_types,id';

				// Determine the object lookup rules
				if (isset($entry->object_type))
				{
					switch ($entry->object_type)
					{
						case ACLTypes::USER:

							$rules['object_id'] = 'required|exists:users,id';

							break;

						case ACLTypes::GROUP:

							$rules['object_id'] = 'required|exists:groups,id';

							break;
					}
				}
			}
		}

		// Create the validator
		$validator = Validator::make($data, $rules);

		// Run the validator
		if ($validator->fails())
		{
			return $validator->messages()->all('<p>:message</p>');
		}

		// Set field to 0 for non-field permissions
		if ( ! str_contains($entry->flag, 'field'))
		{
			$entry->field = 0;
		}

		// Set field to 0 and object to 'all' for manage permissions
		if (str_contains($entry->flag, 'manage'))
		{
			$entry->field = 0;
			$entry->object_id = 0;
			$entry->object_type = ACLTypes::ALL;
		}

		// Check if an existing entry already exists
		$acl = ACL::where('flag', $entry->flag)
		          ->where('subject_id', $entry->subject_id)
		          ->where('subject_type', $entry->subject_type)
		          ->where('object_id', $entry->object_id)
		          ->where('object_type', $entry->object_type)
		          ->where('field_id', $entry->field)
		          ->first();

		if ($acl == null)
		{
			$acl = new ACL;
			$acl->flag = $entry->flag;
			$acl->subject_id = $entry->subject_id;
			$acl->subject_type = $entry->subject_type;
			$acl->object_id = $entry->object_id;
			$acl->object_type = $entry->object_type;
			$acl->field_id = $entry->field;
			$acl->save();
		}

		// Clear the ACL cache for this entity
		static::refresh($entry->subject_id, $entry->subject_type);

		// All OK!
		return true;
	}

	/**
	 * Removes a specific ACL entry
	 *
	 * @static
	 * @access public
	 * @param  int  $id
	 * @return void
	 */
	public static function remove($id)
	{
		// Fetch the ACL entry
		$acl = ACL::findOrFail($id);

		// Clear cached against the subject
		static::refresh($acl->subject_id, $acl->subject_type);

		// Remove the ACL entry
		$acl->delete();
	}

	/**
	 * Refreshes the cache for a certain ACL entity
	 *
	 * @static
	 * @access private
	 * @param  int  $id
	 * @param  int  $type
	 * @return void
	 */
	private static function refresh($id, $type)
	{
		switch ($type)
		{
			case ACLTypes::USER:

				Cache::tags("security.user.{$id}")->flush();

				break;

			case ACLTypes::GROUP:

				$users = UserGroup::where('group_id', $id)->lists('user_id');

				foreach ($users as $user)
				{
					Cache::tags("security.user.{$user}")->flush();
				}

				break;
		}
	}

}
