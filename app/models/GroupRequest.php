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
 * GroupRequest class
 *
 * Data model for table 'group_requests'
 *
 * @package     Keychain
 * @subpackage  Models
 */
class GroupRequest extends Eloquent {

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
	 * Relationship with the 'Group' model
	 *
	 * @access public
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function group()
	{
		return $this->belongsTo('Group');
	}

}
