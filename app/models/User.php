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

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

/**
 * User class
 *
 * Data model for table 'users'
 *
 * @package     Keychain
 * @subpackage  Models
 */
class User extends Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait;

	/**
	 * Relationship with the 'UserEmail' model
	 *
	 * @access public
	 * @return Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function primaryEmail()
	{
		return $this->hasMany('UserEmail')->where('primary', Flags::YES);
	}

	/**
	 * Relationship with the 'UserEmail' model
	 *
	 * @access public
	 * @return Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function emails()
	{
		return $this->hasMany('UserEmail');
	}

	/**
	 * Relationship with the 'UserKey' model
	 *
	 * @access public
	 * @return Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function keys()
	{
		return $this->hasMany('UserKey');
	}

	/**
	 * Relationship with the 'UserGroup' model
	 *
	 * @access public
	 * @return Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function groups()
	{
		return $this->hasMany('UserGroup');
	}

	/**
	 * Relationship with the 'UserField' model
	 *
	 * @access public
	 * @return Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function fields()
	{
		return $this->hasMany('UserField');
	}

	/**
	 * Searches for a specific user by name/email address
	 *
	 * @access public
	 * @param  User  $user
	 * @param  array  $filter
	 * @return User
	 */
	public function scopeSearch($user, $filter)
	{
		$filter = (object) $filter;
		$results = array();

		$query = $filter->query;
		$exclude = isset($filter->exclude) ? explode(',', $filter->exclude) : array();

		// If user is searching by email address
		if (filter_var($query, FILTER_VALIDATE_EMAIL))
		{
			// Find users by email address
			$emails = UserEmail::where('address', 'like', "{$query}%");

			// Append a '0' to have a safe where-in clause for the user lookup
			$userIds = $emails->lists('user_id');

			// Look up user by IDs
			if (count($userIds) > 0)
			{
				$user->whereIn('id', $userIds);
			}
		}

		// Searching by user's name
		else
		{
			// We do not use %query% to allow the index to be utilized
			$user->where('name', 'like', "{$query}%");
		}

		// Remove excluded users
		if (count($exclude) > 0)
		{
			$user->whereNotIn('hash', $exclude);
		}

		// Return the results of the search
		return $user->with('primaryEmail')->orderBy('name');
	}

}
