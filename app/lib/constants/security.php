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
 * Defines all ACL permissions
 */
class ACLFlags {

	// Global permissions
	const ACL_MANAGE = 'acl_manage';
	const FIELD_MANAGE = 'field_manage';
	const USER_MANAGE = 'user_manage';
	const GROUP_MANAGE = 'group_manage';

	// Object based permissions
	const FIELD_VIEW = 'field_view';
	const FIELD_EDIT = 'field_edit';
	const USER_EDIT = 'user_edit';
	const GROUP_EDIT = 'group_edit';

}

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
 * Defines acl query methods
 */
class QueryMethods {

	const BY_SUBJECT = 1;
	const BY_OBJECT = 2;

}

?>
