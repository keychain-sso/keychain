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
	 * @return Group
	 */
	public function scopeSearch($groups)
	{
		$query = Input::get('query');
		$exclude = Input::has('exclude') ? explode(',', Input::get('exclude')) : array();
		$results = array();

		// We do not use %query% to allow the index to be utilized
		$groups->where('name', 'like', "{$query}%");

		// Remove excluded groups
		if (count($exclude) > 0)
		{
			$groups->whereNotIn('hash', $exclude);
		}

		// Return the results of the search
		return $groups->orderBy('name');
	}

}
