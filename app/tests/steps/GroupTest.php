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
 * GroupTest class
 *
 * Unit test cases for GroupController
 *
 * @package     Keychain
 * @subpackage  UnitTests
 */
class GroupTest extends KeychainTestCase {

	/**
	 * Tests the getList method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetList()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;

		$this->be($user);
		$this->call('GET', 'group/list');

		$this->assertResponseOk();
		$this->assertViewHas('groupItems');
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
		$this->call('GET', 'group/create');

		$this->assertResponseOk();
		$this->assertViewHas('groupItems');
	}

	/**
	 * Tests the getCreate method of the controller when user does not
	 * have permissions
	 *
	 * @access public
	 * @return void
	 * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
	 */
	public function testGetCreateNoPermissions()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;

		$this->be($user);
		$this->call('GET', 'group/create');

		$this->assertResponseOk();
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

		$this->call('POST', 'group/create', array(
			'name'        => 'MyGroup',
			'description' => 'Lorem ipsum',
			'type'        => GroupTypes::OPEN,
			'notify'      => Flags::YES,
		));

		$this->assertRedirectedTo('group/list');
		$this->assertSessionHas('messages.success');
		$this->assertEquals(1, Group::where('name', 'MyGroup')->count());
	}

	/**
	 * Tests the postCreate method of the controller when user does not
	 * have permissions
	 *
	 * @access public
	 * @return void
	 * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
	 */
	public function testPostCreateNoPermissions()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;

		$this->be($user);

		$this->call('POST', 'group/create', array(
			'name'        => 'MyGroup',
			'description' => 'group description',
			'type'        => GroupTypes::OPEN,
			'notify'      => Flags::YES,
		));
	}

	/**
	 * Tests the getView method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetView()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$group = TestHelper::createGroup()->group;

		$this->be($user);
		$this->call('GET', "group/view/{$group->hash}");

		$this->assertResponseOk();
		$this->assertViewHas('group');
	}

	/**
	 * Tests the postView method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testPostView()
	{
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$group = TestHelper::createGroup(GroupTypes::OPEN, $admin)->group;

		$this->be($admin);

		$this->call('POST', 'group/view', array(
			'hash'  => $group->hash,
			'users' => array($admin->hash),
		));

		$this->assertSessionHas('messages.success');
		$this->assertEquals(0, UserGroup::where('group_id', $group->id)->count());
	}

	/**
	 * Tests the postView method of the controller when user does not
	 * have permissions
	 *
	 * @access public
	 * @return void
	 * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
	 */
	public function testPostViewNoPermissions()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$group = TestHelper::createGroup(GroupTypes::OPEN, $user)->group;

		$this->be($user);

		$this->call('POST', 'group/view', array(
			'hash'  => $group->hash,
			'users' => array($user->hash),
		));
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
		$group = TestHelper::createGroup()->group;

		$this->be($admin);
		$this->call('GET', "group/edit/{$group->hash}");

		$this->assertResponseOk();
		$this->assertViewHas('group');
		$this->assertViewHas('modal');
	}

	/**
	 * Tests the getEdit method of the controller when user does not
	 * have permissions
	 *
	 * @access public
	 * @return void
	 * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
	 */
	public function testGetEditNoPermissions()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$group = TestHelper::createGroup()->group;

		$this->be($user);
		$this->call('GET', "group/edit/{$group->hash}");
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
		$group = TestHelper::createGroup()->group;

		$this->be($admin);

		$this->call('POST', 'group/edit', array(
			'hash'        => $group->hash,
			'name'        => 'MyGroup New',
			'description' => 'group description',
			'type'        => GroupTypes::OPEN,
			'notify'      => Flags::YES,
			'auto_join'   => Flags::NO,
		));

		$this->assertSessionHas('messages.success');
		$this->assertEquals('MyGroup New', Group::find($group->id)->name);
	}

	/**
	 * Tests the postEdit method of the controller when user does not
	 * have permissions
	 *
	 * @access public
	 * @return void
	 * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
	 */
	public function testPostEditNoPermissions()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$group = TestHelper::createGroup()->group;

		$this->be($user);

		$this->call('POST', 'group/edit', array(
			'hash'        => $group->hash,
			'name'        => 'MyGroup New',
			'description' => 'group description',
			'type'        => GroupTypes::OPEN,
			'notify'      => Flags::YES,
			'auto_join'   => Flags::NO,
		));
	}

	/**
	 * Tests the getJoin method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetJoin()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$group = TestHelper::createGroup()->group;

		$this->be($user);
		$this->call('GET', "group/join/{$group->hash}");

		$this->assertSessionHas('messages.success');
		$this->assertEquals(1, UserGroup::where('user_id', $user->id)->where('group_id', $group->id)->count());
	}

	/**
	 * Tests the getJoin method of the controller for a request only group
	 *
	 * @access public
	 * @return void
	 */
	public function testGetJoinRequest()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$group = TestHelper::createGroup(GroupTypes::REQUEST)->group;

		$this->be($user);
		$this->call('GET', "group/join/{$group->hash}");

		$this->assertResponseOk();
		$this->assertViewHas('modal');
	}

	/**
	 * Tests the postJoin method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testPostJoin()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$group = TestHelper::createGroup(GroupTypes::REQUEST)->group;

		$this->be($user);

		$this->call('POST', 'group/join', array(
			'hash'          => $group->hash,
			'justification' => 'add me',
		));

		$this->assertSessionHas('messages.success');
		$this->assertRedirectedTo("group/view/{$group->hash}");
		$this->assertEquals(1, GroupRequest::where('group_id', $group->id)->count());
	}

	/**
	 * Tests the getLeave method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetLeave()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$group = TestHelper::createGroup(GroupTypes::OPEN, $user)->group;

		$this->be($user);
		$this->call('GET', "group/leave/{$group->hash}");

		$this->assertSessionHas('messages.success');
		$this->assertEquals(0, UserGroup::where('user_id', $user->id)->where('group_id', $group->id)->count());
	}

	/**
	 * Tests the getWithdraw method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetWithdraw()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$group = TestHelper::createGroup(GroupTypes::REQUEST, $user, true)->group;

		$this->be($user);
		$this->call('GET', "group/withdraw/{$group->hash}");

		$this->assertSessionHas('messages.success');
		$this->assertEquals(0, UserGroup::where('user_id', $user->id)->where('group_id', $group->id)->count());
	}

	/**
	 * Tests the getRequests method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetRequests()
	{
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$group = TestHelper::createGroup(GroupTypes::REQUEST, $user, true)->group;

		$this->be($admin);
		$this->call('GET', "group/requests/{$group->hash}");

		$this->assertResponseOk();
		$this->assertViewHas('modal');
		$this->assertViewHas('requests');
	}

	/**
	 * Tests the getRequests method of the controller when user does not
	 * have permissions
	 *
	 * @access public
	 * @return void
	 * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
	 */
	public function testGetRequestsNoPermissions()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$group = TestHelper::createGroup(GroupTypes::REQUEST)->group;

		$this->be($user);
		$this->call('GET', "group/requests/{$group->hash}");
	}

	/**
	 * Tests the getRequests method of the controller with the 'approve' action
	 *
	 * @access public
	 * @return void
	 */
	public function testGetRequestsApprove()
	{
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$data = TestHelper::createGroup(GroupTypes::REQUEST, $user, true);

		$this->be($admin);
		$this->call('GET', "group/requests/{$data->group->hash}/approve/{$data->groupRequest->id}");

		$this->assertSessionHas('messages.success');
		$this->assertEquals(0, GroupRequest::where('group_id', $data->group->id)->count());
		$this->assertEquals(1, UserGroup::where('user_id', $user->id)->where('group_id', $data->group->id)->count());
	}

	/**
	 * Tests the getRequests method of the controller with the 'reject' action
	 *
	 * @access public
	 * @return void
	 */
	public function testGetRequestsReject()
	{
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$data = TestHelper::createGroup(GroupTypes::REQUEST, $user, true);

		$this->be($admin);
		$this->call('GET', "group/requests/{$data->group->hash}/reject/{$data->groupRequest->id}");

		$this->assertSessionHas('messages.success');
		$this->assertEquals(0, GroupRequest::where('group_id', $data->group->id)->count());
		$this->assertEquals(0, UserGroup::where('user_id', $user->id)->where('group_id', $data->group->id)->count());
	}

	/**
	 * Tests the getAddUser method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetAddUser()
	{
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$group = TestHelper::createGroup(GroupTypes::CLOSED)->group;

		$this->be($admin);
		$this->call('GET', "group/add-user/{$group->hash}");

		$this->assertResponseOk();
		$this->assertViewHas('modal');
		$this->assertViewHas('users');
	}

	/**
	 * Tests the getAddUser method of the controller when user does not
	 * have permissions
	 *
	 * @access public
	 * @return void
	 * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
	 */
	public function testGetAddUserNoPermissions()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$group = TestHelper::createGroup(GroupTypes::CLOSED)->group;

		$this->be($user);
		$this->call('GET', "group/add-user/{$group->hash}");
	}

	/**
	 * Tests the postAddUser method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testPostAddUser()
	{
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$group = TestHelper::createGroup(GroupTypes::REQUEST)->group;

		$this->be($admin);

		$this->call('POST', 'group/add-user', array(
			'hash'  => $group->hash,
			'users' => array($user->hash),
		));

		$this->assertSessionHas('messages.success');
		$this->assertRedirectedTo("group/view/{$group->hash}");
		$this->assertEquals(1, UserGroup::where('user_id', $user->id)->where('group_id', $group->id)->count());
	}

	/**
	 * Tests the postAddUser method of the controller when user does not
	 * have permissions
	 *
	 * @access public
	 * @return void
	 * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
	 */
	public function testPostAddUserNoPermissions()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$group = TestHelper::createGroup(GroupTypes::REQUEST)->group;

		$this->be($user);

		$this->call('POST', 'group/add-user', array(
			'hash'  => $group->hash,
			'users' => array(),
		));
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
		$group = TestHelper::createGroup(GroupTypes::CLOSED)->group;

		$this->be($admin);
		$this->call('GET', "group/permissions/{$group->hash}");

		$this->assertResponseOk();
		$this->assertViewHas('modal');
		$this->assertViewHas('acl');
	}

	/**
	 * Tests the getPermissions method of the controller when user does not
	 * have permissions
	 *
	 * @access public
	 * @return void
	 * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
	 */
	public function testGetPermissionsNoPermissions()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$group = TestHelper::createGroup(GroupTypes::CLOSED)->group;

		$this->be($user);
		$this->call('GET', "group/permissions/{$group->hash}");
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
		$group = TestHelper::createGroup(GroupTypes::CLOSED)->group;

		$this->be($admin);
		$this->call('GET', "group/delete/{$group->hash}");

		$this->assertRedirectedTo('group/list');
		$this->assertSessionHas('messages.success');
		$this->assertEquals(null, Group::find($group->id));
	}

	/**
	 * Tests the getDelete method of the controller when the user
	 * does not have access
	 *
	 * @access public
	 * @return void
	 * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
	 */
	public function testGetDeleteNoPermissions()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$group = TestHelper::createGroup(GroupTypes::CLOSED)->group;

		$this->be($user);
		$this->call('GET', "group/delete/{$group->hash}");
	}

	/**
	 * Tests the getSearch method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetSearch()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;

		$this->be($user);
		$this->call('GET', 'group/search', array('query' => 'Registered Users'), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest'));

		$this->assertResponseOk();
		$this->assertViewHas('items');
	}

	/**
	 * Tests the getMemberSearch method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetMemberSearch()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$group = TestHelper::createGroup(GroupTypes::OPEN, $user)->group;

		$this->be($user);
		$this->call('GET', "group/member-search/{$group->hash}", array('query' => 'Unit Test'), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest'));

		$this->assertResponseOk();
		$this->assertViewHas('users');
	}

}
