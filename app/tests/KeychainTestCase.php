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
 * KeychainTestCase class
 *
 * Base test case suite for Keychain
 *
 * @package     Keychain
 * @subpackage  UnitTests
 */
class KeychainTestCase extends Illuminate\Foundation\Testing\TestCase {

	/**
	 * Creates the application.
	 *
	 * @access public
	 * @return \Symfony\Component\HttpKernel\HttpKernelInterface
	 */
	public function createApplication()
	{
		$unitTesting = true;
		$testEnvironment = 'testing';

		return require __DIR__.'/../../bootstrap/start.php';
	}

	/**
	 * Set-up the unit test runs by installing data
	 *
	 * @access public
	 * @return void
	 */
	public function setUp()
	{
		parent::setUp();

		Artisan::call('migrate');
		Eloquent::unguard();
		Mail::pretend(true);
		View::flush();

		$this->seed();
	}

}
