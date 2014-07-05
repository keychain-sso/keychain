<?php

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
 * Defines various user account states
 */
class UserStatus {

	const INACTIVE = 1;
	const ACTIVE = 2;
	const BLOCKED = 3;

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
 * Categories of profile fields
 */
class FieldCategories {

	const BASIC = 1;
	const CONTACT = 2;
	const OTHER = 3;

}

/**
 * Types of profile fields
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

?>
