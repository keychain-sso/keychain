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
 * V100Seeder class
 *
 * Database seeder for v1.0.0
 *
 * @package     Keychain
 * @subpackage  Seeds
 */
class V100Seeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		// Insert ACL types
		DB::table('acl_types')->insert(array(
			array('id' => 1, 'name' => 'Self'),
			array('id' => 2, 'name' => 'All'),
			array('id' => 3, 'name' => 'User'),
			array('id' => 4, 'name' => 'Group'),
		));

		// Insert ACL flags
		DB::table('acl_flags')->insert(array(
			array('id' => 1, 'name' => 'acl_manage'),
			array('id' => 2, 'name' => 'field_edit'),
			array('id' => 3, 'name' => 'field_manage'),
			array('id' => 4, 'name' => 'field_view'),
			array('id' => 5, 'name' => 'group_edit'),
			array('id' => 6, 'name' => 'group_manage'),
			array('id' => 7, 'name' => 'user_edit'),
			array('id' => 8, 'name' => 'user_manage'),
		));

		// Insert the user status values
		DB::table('user_status')->insert(array(
			array('id' => 1, 'name' => 'Inactive'),
			array('id' => 2, 'name' => 'Active'),
			array('id' => 3, 'name' => 'Blocked'),
		));

		// Insert token types
		DB::table('token_types')->insert(array(
			array('id' => 1, 'name' => 'Email'),
			array('id' => 2, 'name' => 'Password'),
		));

		// Insert device types
		DB::table('device_types')->insert(array(
			array('id' => 1, 'name' => 'Computer'),
			array('id' => 2, 'name' => 'Mobile'),
			array('id' => 3, 'name' => 'Tablet'),
		));

		// Insert group types
		DB::table('group_types')->insert(array(
			array('id' => 1, 'name' => 'Open'),
			array('id' => 2, 'name' => 'Request'),
			array('id' => 3, 'name' => 'Closed'),
		));

		// Insert field categories
		DB::table('field_categories')->insert(array(
			array('id' => 1, 'name' => 'Basic'),
			array('id' => 2, 'name' => 'Contact'),
			array('id' => 3, 'name' => 'Other'),
		));

		// Insert field types
		DB::table('field_types')->insert(array(
			array('id' => 1, 'name' => 'TextBox', 'option' => Flags::NO),
			array('id' => 2, 'name' => 'TextArea', 'option' => Flags::NO),
			array('id' => 3, 'name' => 'Radio', 'option' => Flags::YES),
			array('id' => 4, 'name' => 'CheckBox', 'option' => Flags::YES),
			array('id' => 5, 'name' => 'Dropdown', 'option' => Flags::YES),
			array('id' => 6, 'name' => 'DatePicker', 'option' => Flags::NO),
		));

		// Insert admin user account
		DB::table('users')->insert(array(
			'name'          => 'John Doe',
			'gender'        => 'M',
			'date_of_birth' => '1980-07-01',
			'timezone'      => 'America/Chicago',
			'password'      => Hash::make('password'),
			'title'         => 'Site administrator',
			'hash'          => str_random(8),
			'status'        => UserStatus::ACTIVE,
		));

		// Insert admin email addresses
		DB::table('user_emails')->insert(array(
			'user_id'  => 1,
			'address'  => 'admin@keychain.sso',
			'primary'  => Flags::YES,
			'verified' => Flags::YES,
		));

		// Insert the group entries
		DB::table('groups')->insert(array(
			'name'        => 'Registered users',
			'description' => 'All registered users on the website.',
			'type'        => GroupTypes::CLOSED,
			'hash'        => str_random(8),
			'auto_join'   => Flags::YES,
		));

		DB::table('groups')->insert(array(
			'name'        => 'Sysadmins',
			'description' => 'System administrators with full control over the website.',
			'type'        => GroupTypes::CLOSED,
			'hash'        => str_random(8),
		));

		// Link the user to registered users group
		DB::table('user_groups')->insert(array(
			'user_id'  => 1,
			'group_id' => 1,
		));

		// Link user to the sysadmin group
		DB::table('user_groups')->insert(array(
			'user_id'  => 1,
			'group_id' => 2,
		));

		// Allow sysadmins to edit all users
		DB::table('acl')->insert(array(
			'flag'         => ACLFlags::USER_EDIT,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
		));

		// Allow registered users to edit their own profiles
		DB::table('acl')->insert(array(
			'flag'         => ACLFlags::USER_EDIT,
			'subject_id'   => 1,
			'subject_type' => ACLTypes::GROUP,
			'object_id'    => 0,
			'object_type'  => ACLTypes::SELF,
		));

		// Allow sysadmins to manage all users
		DB::table('acl')->insert(array(
			'flag'         => ACLFlags::USER_MANAGE,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
		));

		// Allow sysadmins to edit all groups
		DB::table('acl')->insert(array(
			'flag'         => ACLFlags::GROUP_EDIT,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
		));

		// Allow sysadmins to manage all groups
		DB::table('acl')->insert(array(
			'flag'         => ACLFlags::GROUP_MANAGE,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
		));

		// Allow sysadmins to manage fields
		DB::table('acl')->insert(array(
			'flag'         => ACLFlags::FIELD_MANAGE,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
		));

		// Allow sysadmins to manage ACLs
		DB::table('acl')->insert(array(
			'flag'         => ACLFlags::ACL_MANAGE,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
		));
	}

}
