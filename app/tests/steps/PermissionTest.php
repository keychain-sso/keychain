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
 * PermissionTest class
 *
 * Unit test cases for PermissionController
 *
 * @package     Keychain
 * @subpackage  UnitTests
 */
class PermissionTest extends KeychainTestCase {

	/**
	 * Tests the getIndex method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetIndex()
	{
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;

		$this->be($admin);
		$this->call('GET', 'permission');

		$this->assertResponseOk();
		$this->assertViewHas('acl');
	}

	/**
	 * Tests the getIndex method of the controller when user does not
	 * have permissions
	 *
	 * @access public
	 * @return void
	 * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
	 */
	public function testGetIndexNoPermissions()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;

		$this->be($user);
		$this->call('GET', 'permission');
	}

	/**
	 * Tests the postIndex method of the controller for a global permission
	 *
	 * @access public
	 * @return void
	 */
	public function testPostIndexGlobal()
	{
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;

		$this->be($admin);

		$this->call('POST', 'permission/index', array(
			'subject_type' => ACLTypes::USER,
			'subject_id'   => $user->id,
			'flag'         => ACLFlags::USER_MANAGE,
		));

		$this->be($user);
		$this->assertSessionHas('messages.success');
		$this->assertTrue(Access::check(ACLFlags::USER_MANAGE));
	}

	/**
	 * Tests the postIndex method of the controller for an object-based
	 * permission
	 *
	 * @access public
	 * @return void
	 */
	public function testPostIndexObject()
	{
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$subject = TestHelper::createGroup(GroupTypes::OPEN, $user)->group;
		$object = TestHelper::createGroup()->group;

		$this->be($admin);

		$this->call('POST', 'permission/index', array(
			'subject_type' => ACLTypes::GROUP,
			'subject_id'   => $subject->id,
			'object_type'  => ACLTypes::GROUP,
			'object_id'    => $object->id,
			'flag'         => ACLFlags::GROUP_EDIT,
		));

		$this->be($user);
		$this->assertSessionHas('messages.success');
		$this->assertTrue(Access::check(ACLFlags::GROUP_EDIT, $object));
	}

	/**
	 * Tests the postIndex method of the controller for a field-based
	 * permission
	 *
	 * @access public
	 * @return void
	 */
	public function testPostIndexField()
	{
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$object = TestHelper::createGroup(GroupTypes::OPEN, $admin)->group;
		$field = TestHelper::createField();

		$this->be($admin);

		$this->call('POST', 'permission/index', array(
			'subject_type' => ACLTypes::USER,
			'subject_id'   => $user->id,
			'object_type'  => ACLTypes::GROUP,
			'object_id'    => $object->id,
			'flag'         => ACLFlags::FIELD_EDIT,
			'field'        => $field->id,
		));

		$this->be($user);
		$this->assertSessionHas('messages.success');
		$this->assertTrue(Access::check(ACLFlags::FIELD_EDIT, $admin, $field));
	}

	/**
	 * Tests the getRemove method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetRemove()
	{
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$permission = ACL::where('flag', ACLFlags::USER_MANAGE)->first();

		$this->be($admin);
		$this->call('GET', "permission/remove/{$permission->id}");

		$this->assertSessionHas('messages.success');
		$this->assertEquals(null, ACL::find($permission->id));
		$this->assertFalse(Access::check(ACLFlags::USER_MANAGE));
	}

}
