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
	public function emails()
	{
		return $this->hasMany('UserEmail');
	}

}
