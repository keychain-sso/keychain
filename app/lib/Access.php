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
use Auth;
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
	 * Queries the ACL for permission flags on an object for a specific
	 * or current subject
	 *
	 * @static
	 * @access public
	 * @param  string  $flag
	 * @param  int  $objectId
	 * @param  int  $objectType
	 * @param  int  $field
	 * @return bool
	 */
	public static function check($flag, $objectId, $objectType, $field = null)
	{
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
				$acl["{$item->object_id}.{$item->object_type}.{$item->field}.{$item->access}"] = true;
			}

			// Finally, save the ACL flagset to the session
			Session::put('security.acl', $acl);
		}

		// Get the ACL from session
		$acl = Session::get('security.acl');

		// Check if the flag against the object is set
		return isset($acl["{$objectId}.{$objectType}.{$field}.{$flag}"]);
	}

}
