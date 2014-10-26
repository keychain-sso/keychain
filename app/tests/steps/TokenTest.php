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
 * TokenTest class
 *
 * Unit test cases for TokenController
 *
 * @package     Keychain
 * @subpackage  UnitTests
 */
class TokenTest extends KeychainTestCase {

	/**
	 * Tests the getVerify method of the controller with an email token
	 *
	 * @access public
	 * @return void
	 */
	public function testGetVerifyEmail()
	{
		$email = TestHelper::createUser(UserStatus::ACTIVE, true, false)->emailPrimary;
		$token = TestHelper::createToken(TokenTypes::EMAIL, $email);

		$this->call('GET', "token/verify/{$token->token}");

		$this->assertResponseOk();
		$this->assertViewHas('return');
		$this->assertEquals(Flags::YES, UserEmail::find($email->id)->verified);
		$this->assertEquals(null, Token::find($token->id));
	}

	/**
	 * Tests the getVerify method of the controller with a password token
	 *
	 * @access public
	 * @return void
	 */
	public function testGetVerifyPassword()
	{
		$email = TestHelper::createUser(UserStatus::ACTIVE, true, false)->emailPrimary;
		$token = TestHelper::createToken(TokenTypes::PASSWORD, $email);

		$this->call('GET', "token/verify/{$token->token}");

		$this->assertRedirectedTo('auth/reset');
		$this->assertSessionHas('security.reset.account');
		$this->assertEquals(null, Token::find($token->id));
	}

}
