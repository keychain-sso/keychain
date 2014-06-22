<?php

/**
 * Defines various user account states
 */
class UserStatus {

	const INACTIVE = 0;
	const ACTIVE = 1;
	const BLOCKED = 2;

}

/**
 * Various types controlled by the ACL
 */
class ACLType {

	const FIELD = 0;
	const SELF = 1;
	const USER = 2;
	const GROUP = 3;

}

/**
 * Categories of profile fields
 */
class FieldCategory {

	const BASIC = 0;
	const CONTACT = 1;
	const OTHER = 2;

}

/**
 * Types of profile fields
 */
class FieldType {

	const TEXT_BOX = 0;
	const TEXT_AREA = 1;
	const RADIO_BUTTON = 2;
	const CHECK_BOX = 3;
	const DROP_DOWN_MENU = 4;
	const MULTI_SELECT = 5;

}

?>
