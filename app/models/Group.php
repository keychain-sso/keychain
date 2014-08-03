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
 * @since       Version 1.0.0
 * @filesource
 */

/**
 * Group class
 *
 * Data model for table 'groups'
 *
 * @package     Keychain
 * @subpackage  Models
 */
class Group extends Eloquent {

	/**
	 * Searches for a specific group by name
	 *
	 * @access public
	 * @param  Group  $group
	 * @param  array  $filter
	 * @return Group
	 */
	public function scopeSearch($group, $filter)
	{
		$filter = (object) $filter;
		$results = array();

		$query = $filter->query;
		$exclude = isset($filter->exclude) ? explode(',', $filter->exclude) : array();

		// We do not use %query% to allow the index to be utilized
		$group->where('name', 'like', "{$query}%");

		// Remove excluded groups
		if (count($exclude) > 0)
		{
			$group->whereNotIn('hash', $exclude);
		}

		// Return the results of the search
		return $group->orderBy('name');
	}

}
