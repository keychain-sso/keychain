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
 * TestHelper class
 *
 * Exposes helper methods that assist with unit testing
 *
 * @package     Keychain
 * @subpackage  UnitTests
 */
class TestHelper {

	/**
	 * Creates a user in the test database
	 *
	 * @static
	 * @access public
	 * @param  UserStatus  $status
	 * @param  bool  $admin
	 * @param  bool  $verified
	 * @return User
	 */
	public static function createUser($status = UserStatus::ACTIVE, $admin = false, $verified = true)
	{
		// Create the user
		$user = User::create(array(
			'name'          => 'Unit Test',
			'password'      => Hash::make('unittest'),
			'date_of_birth' => '1980-07-01',
			'status'        => $status,
			'hash'          => str_random(8),
		));

		// Add a primary email address
		$emailPrimary = UserEmail::create(array(
			'user_id'  => 1,
			'address'  => 'primary@unittest.sso',
			'primary'  => Flags::YES,
			'verified' => $verified,
		));

		// Add an alternate email address
		$emailAlternate = UserEmail::create(array(
			'user_id'  => 1,
			'address'  => 'alternate@unittest.sso',
			'primary'  => Flags::NO,
			'verified' => $verified,
		));

		// Add the user to the registered users group
		$groupRegistered = UserGroup::create(array(
			'user_id'  => 1,
			'group_id' => 1,
		));

		// Add the user to the admin group
		if ($admin)
		{
			$groupAdmin = UserGroup::create(array(
				'user_id'  => 1,
				'group_id' => 1,
			));
		}
		else
		{
			$groupAdmin = null;
		}

		// Return all relevant data
		return (object) array(
			'user'            => $user,
			'emailPrimary'    => $emailPrimary,
			'emailAlternate'  => $emailAlternate,
			'groupRegistered' => $groupRegistered,
			'groupAdmin'      => $groupAdmin,
		);
	}

}

?>
