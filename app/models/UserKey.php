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
 * UserKey class
 *
 * Data model for table 'user_keys'
 *
 * @package     Keychain
 * @subpackage  Models
 */
class UserKey extends Eloquent {

	/**
	 * Relationship with the 'User' model
	 *
	 * @access public
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user()
	{
		return $this->belongsTo('User');
	}

}
