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
 * Various types controlled by the ACL
 */
class ACLTypes {

	const SELF = 1;
	const ALL = 2;
	const USER = 3;
	const GROUP = 4;

}

/**
 * Defines types of validation tokens
 */
class TokenTypes {

	const EMAIL = 1;
	const PASSWORD = 2;

}

/**
 * Defines types of devices
 */
class DeviceTypes {

	const COMPUTER = 1;
	const MOBILE = 2;
	const TABLET = 3;

}

/**
 * Defines all ACL permissions
 */
class Permissions {

	const FIELD_VIEW = 'field_view';
	const FIELD_EDIT = 'field_edit';
	const USER_EDIT = 'user_edit';
	const USER_STATUS = 'user_status';
	const GROUP_EDIT = 'group_edit';

}

?>
