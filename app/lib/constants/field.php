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
 * Categories of user profile fields
 */
class FieldCategories {

	const BASIC = 1;
	const CONTACT = 2;
	const OTHER = 3;

}

/**
 * Types of user profile fields
 */
class FieldTypes {

	const TEXTBOX = 1;
	const TEXTAREA = 2;
	const RADIO = 3;
	const CHECKBOX = 4;
	const DROPDOWN = 5;
	const DATEPICKER = 6;

}

/**
 * Holds the field parser indices
 */
class FieldParser {

	const OPTIONS = 0;
	const VALUE = 1;

}

?>
