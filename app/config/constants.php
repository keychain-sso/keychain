<?php

/**
 * A general yes/no flag
 */
class Flag {

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
class ACLType {

	const SELF = 1;
	const ALL = 2;
	const USER = 3;
	const GROUP = 4;

}

/**
 * Categories of profile fields
 */
class FieldCategory {

	const BASIC = 1;
	const CONTACT = 2;
	const OTHER = 3;

}

/**
 * Types of profile fields
 */
class FieldType {

	const TEXT_BOX = 1;
	const TEXT_AREA = 2;
	const RADIO_BUTTON = 3;
	const CHECK_BOX = 4;
	const DROP_DOWN_MENU = 5;
	const MULTI_SELECT = 6;

}

?>
