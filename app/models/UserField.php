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
 * UserField class
 *
 * Data model for table 'user_fields'
 *
 * @package     Keychain
 * @subpackage  Models
 */
class UserField extends Eloquent {

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

	/**
	 * Relationship with the 'Field' model
	 *
	 * @access public
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function field()
	{
		return $this->belongsTo('Field');
	}

}
