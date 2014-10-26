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
	 * @return stdClass
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

		// Create a user key
		$key = UserKey::create(array(
			'user_id'     => $user->id,
			'title'       => 'Primary SSH Key',
			'key'         => 'keyhash',
			'fingerprint' => 'fingerprint',
		));

		// Add a primary email address
		$emailPrimary = UserEmail::create(array(
			'user_id'  => $user->id,
			'address'  => 'primary@unittest.sso',
			'primary'  => Flags::YES,
			'verified' => $verified,
		));

		// Add an alternate email address
		$emailAlternate = UserEmail::create(array(
			'user_id'  => $user->id,
			'address'  => 'alternate@unittest.sso',
			'primary'  => Flags::NO,
			'verified' => $verified,
		));

		// Add the user to the registered users group
		$groupRegistered = UserGroup::create(array(
			'user_id'  => $user->id,
			'group_id' => 1,
		));

		// Add the user to the admin group
		if ($admin)
		{
			$groupAdmin = UserGroup::create(array(
				'user_id'  => $user->id,
				'group_id' => 2,
			));
		}
		else
		{
			$groupAdmin = null;
		}

		// Return all relevant data
		return (object) array(
			'user'            => $user,
			'key'             => $key,
			'emailPrimary'    => $emailPrimary,
			'emailAlternate'  => $emailAlternate,
			'groupRegistered' => $groupRegistered,
			'groupAdmin'      => $groupAdmin,
		);
	}

	/**
	 * Creates a group in the test database
	 *
	 * @static
	 * @access public
	 * @param  int  $type
	 * @param  User  $user
	 * @param  bool  $request
	 * @return stdClass
	 */
	public static function createGroup($type = GroupTypes::OPEN, $user = null, $request = false)
	{
		$group = Group::create(array(
			'name'        => str_random(20),
			'description' => 'group description',
			'type'        => $type,
			'hash'        => str_random(8),
			'notify'      => Flags::YES,
			'auto_join'   => Flags::NO,
		));

		if ( ! is_null($user))
		{
			if ($request)
			{
				$userGroup = null;

				$groupRequest = GroupRequest::create(array(
					'user_id'       => $user->id,
					'group_id'      => $group->id,
					'justification' => 'request justification',
				));
			}
			else
			{
				$groupRequest = null;

				$userGroup = UserGroup::create(array(
					'user_id'  => $user->id,
					'group_id' => $group->id,
				));
			}
		}
		else
		{
			$userGroup = null;
			$groupRequest = null;
		}

		return (object) array(
			'group'        => $group,
			'userGroup'    => $userGroup,
			'groupRequest' => $groupRequest,
		);
	}

	/**
	 * Creates a field in the test database
	 *
	 * @static
	 * @access public
	 * @return Field
	 */
	public static function createField()
	{
		$order = Field::where('category', FieldCategories::BASIC)->max('order') + 1;

		$field = Field::create(array(
			'name'         => 'unit test field',
			'machine_name' => str_random(10),
			'type'         => FieldTypes::TEXTBOX,
			'category'     => FieldCategories::BASIC,
			'required'     => false,
			'order'        => $order,
		));

		return $field;
	}

	/**
	 * Creates a token in the test database
	 *
	 * @static
	 * @access public
	 * @param  int  $type
	 * @param  UserEmail  $email
	 * @return Token
	 */
	public static function createToken($type, $email)
	{
		$token = Token::create(array(
			'token'        => str_random(10),
			'permits_id'   => $email->id,
			'permits_type' => $type,
		));

		return $token;
	}

	/**
	 * Creates an ACL entry in the test database
	 *
	 * @static
	 * @access public
	 * @param  int  $subjectType
	 * @param  int  $subjectId
	 * @return ACL
	 */
	public static function createPermission($subjectType, $subjectId)
	{
		$acl = ACL::create(array(
			'flag'         => 'user_manage',
			'subject_type' => $subjectType,
			'subject_id'   => $subjectId,
			'object_type'  => ACLTypes::ALL,
			'object_id'    => 0,
			'field_id'     => 0,
		));

		return $acl;
	}

}

?>
