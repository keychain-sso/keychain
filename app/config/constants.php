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

?>
