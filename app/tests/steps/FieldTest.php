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
 * FieldTest class
 *
 * Unit test cases for FieldController
 *
 * @package     Keychain
 * @subpackage  UnitTests
 */
class FieldTest extends KeychainTestCase {

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
		$this->call('GET', 'field');

		$this->assertResponseOk();
		$this->assertViewHas('fields');
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
		$this->call('GET', 'field');
	}

	/**
	 * Tests the getCreate method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetCreate()
	{
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;

		$this->be($admin);
		$this->call('GET', 'field/create');

		$this->assertResponseOk();
		$this->assertViewHas('modal');
	}

	/**
	 * Tests the postCreate method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testPostCreate()
	{
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;

		$this->be($admin);

		$this->call('POST', 'field/create', array(
			'name'     => 'field name',
			'type'     => FieldTypes::TEXTAREA,
			'category' => FieldCategories::BASIC,
		));

		$this->assertRedirectedTo('field');
		$this->assertSessionHas('messages.success');
		$this->assertEquals(1, Field::where('name', 'field name')->count());
	}

	/**
	 * Tests the getEdit method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetEdit()
	{
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$field = TestHelper::createField();

		$this->be($admin);
		$this->call('GET', "field/edit/{$field->id}");

		$this->assertResponseOk();
		$this->assertViewHas('modal');
	}

	/**
	 * Tests the postEdit method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testPostEdit()
	{
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$field = TestHelper::createField();

		$this->be($admin);

		$this->call('POST', 'field/edit', array(
			'id'       => $field->id,
			'name'     => 'field name new',
			'category' => FieldCategories::BASIC,
		));

		$this->assertRedirectedTo('field');
		$this->assertSessionHas('messages.success');
		$this->assertEquals(1, Field::where('name', 'field name new')->count());
	}

	/**
	 * Tests the getDelete method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetDelete()
	{
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$field = TestHelper::createField();

		$this->be($admin);
		$this->call('GET', "field/delete/{$field->id}");

		$this->assertSessionHas('messages.success');
		$this->assertEquals(null, Field::find($field->id));
	}

	/**
	 * Tests the getMove method of the controller with direction as 'up'
	 *
	 * @access public
	 * @return void
	 */
	public function testGetMoveUp()
	{
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$fieldTop = TestHelper::createField();
		$fieldBottom = TestHelper::createField();

		$this->be($admin);
		$this->call('GET', "field/move/up/{$fieldBottom->id}");

		$this->assertEquals(1, Field::find($fieldBottom->id)->order);
		$this->assertEquals(2, Field::find($fieldTop->id)->order);
	}

	/**
	 * Tests the getMove method of the controller with direction as 'down'
	 *
	 * @access public
	 * @return void
	 */
	public function testGetMoveDown()
	{
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$fieldTop = TestHelper::createField();
		$fieldBottom = TestHelper::createField();

		$this->be($admin);
		$this->call('GET', "field/move/down/{$fieldTop->id}");

		$this->assertEquals(1, Field::find($fieldBottom->id)->order);
		$this->assertEquals(2, Field::find($fieldTop->id)->order);
	}

	/**
	 * Tests the getPermissions method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetPermissions()
	{
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$field = TestHelper::createField();

		$this->be($admin);
		$this->call('GET', "field/permissions/{$field->id}");

		$this->assertResponseOk();
		$this->assertViewHas('acl');
	}

}
