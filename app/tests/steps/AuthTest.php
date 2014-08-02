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
	 */
	public function testGetLogin()
	{
		$this->call('GET', 'auth/login');

		$this->assertResponseOk();
	}

}
