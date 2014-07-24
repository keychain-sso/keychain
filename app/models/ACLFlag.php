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
 * ACLFlag class
 *
 * Data model for table 'acl_flags'
 *
 * @package     Keychain
 * @subpackage  Models
 */
class ACLFlag extends Eloquent {

	/**
	 * Table name for this model
	 *
	 * @var string
	 */
	protected $table = 'acl_flags';

}
