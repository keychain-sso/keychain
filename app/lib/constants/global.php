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
 * HTTP status codes
 */
class HTTPStatus {

	const OK = 200;
	const UNAUTHORIZED = 401;
	const FORBIDDEN = 403;
	const NOTFOUND = 404;

}

/**
 * A general yes/no flag
 */
class Flags {

	const YES = 1;
	const NO = 0;

}

/**
 * Defines types of notices
 */
class NoticeTypes {

	const SUCCESS = 1;
	const ERROR = 2;
	const INFORMATION = 3;

}

/**
 * Defines types of devices
 */
class DeviceTypes {

	const COMPUTER = 1;
	const MOBILE = 2;
	const TABLET = 3;

}

?>
