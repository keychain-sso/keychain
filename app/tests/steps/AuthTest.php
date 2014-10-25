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
 * AuthTest class
 *
 * Unit test cases for AuthController
 *
 * @package     Keychain
 * @subpackage  UnitTests
 */
class AuthTest extends KeychainTestCase {

	/**
	 * Tests the getLogin method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetLogin()
	{
		$this->call('GET', 'auth/login');

		$this->assertResponseOk();
	}

	/**
	 * Tests the postLogin method of the controller with an active primary
	 * email and password
	 *
	 * @access public
	 * @return void
	 */
	public function testPostLoginPrimaryActive()
	{
		$data = TestHelper::createUser();

		$this->call('POST', 'auth/login', array(
			'email'    => $data->emailPrimary->address,
			'password' => 'unittest',
			'remember' => 'on',
		));

		$this->assertTrue(Auth::check());
		$this->assertRedirectedTo('/');
	}

	/**
	 * Tests the postLogin method of the controller with an active primary
	 * email and password
	 *
	 * @access public
	 * @return void
	 */
	public function testPostLoginAlternateActive()
	{
		$data = TestHelper::createUser();

		$this->call('POST', 'auth/login', array(
			'email'    => $data->emailAlternate->address,
			'password' => 'unittest',
			'remember' => 'on',
		));

		$this->assertTrue(Auth::check());
		$this->assertRedirectedTo('/');
	}

	/**
	 * Tests the postLogin method of the controller with an unverified
	 * email and password
	 *
	 * @access public
	 * @return void
	 */
	public function testPostLoginUnverified()
	{
		$data = TestHelper::createUser(UserStatus::ACTIVE, false, false);

		$this->call('POST', 'auth/login', array(
			'email'    => $data->emailPrimary->address,
			'password' => 'unittest',
			'remember' => 'on',
		));

		$this->assertFalse(Auth::check());
		$this->assertEquals(Session::get('messages.error'), Lang::get('auth.account_inactive'));
	}

	/**
	 * Tests the postLogin method of the controller with an inactive
	 * email and password
	 *
	 * @access public
	 * @return void
	 */
	public function testPostLoginInactive()
	{
		$data = TestHelper::createUser(UserStatus::INACTIVE);

		$this->call('POST', 'auth/login', array(
			'email'    => $data->emailPrimary->address,
			'password' => 'unittest',
			'remember' => 'on',
		));

		$this->assertFalse(Auth::check());
		$this->assertEquals(Session::get('messages.error'), Lang::get('auth.account_inactive'));
	}

	/**
	 * Tests the postLogin method of the controller with a blocked
	 * email and password
	 *
	 * @access public
	 * @return void
	 */
	public function testPostLoginBlocked()
	{
		$data = TestHelper::createUser(UserStatus::BLOCKED);

		$this->call('POST', 'auth/login', array(
			'email'    => $data->emailPrimary->address,
			'password' => 'unittest',
			'remember' => 'on',
		));

		$this->assertFalse(Auth::check());
		$this->assertEquals(Session::get('messages.error'), Lang::get('auth.account_blocked'));
	}

	/**
	 * Tests the postLogin method of the controller with an incorrect password
	 *
	 * @access public
	 * @return void
	 */
	public function testPostLoginIncorrect()
	{
		$data = TestHelper::createUser();

		$this->call('POST', 'auth/login', array(
			'email'    => $data->emailPrimary->address,
			'password' => 'unittestinc',
			'remember' => 'on',
		));

		$this->assertFalse(Auth::check());
		$this->assertEquals(Session::get('messages.error'), Lang::get('auth.login_failed'));
	}

}
