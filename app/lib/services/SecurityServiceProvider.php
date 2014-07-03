<?php namespace Keychain\Services;

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

use Session;

use Illuminate\Support\ServiceProvider;

use Keychain\Drivers\CoupledSessionHandler;

/**
 * SecurityServiceProvider class
 *
 * Facilitates registration of the CoupledSessionHandler
 *
 * @package     Keychain
 * @subpackage  Services
 */
class SecurityServiceProvider extends ServiceProvider {

	/**
	 * Register the security service provider
	 *
	 * @access public
	 * @return void
	 */
	public function register()
	{
		Session::extend('coupled', function($app)
		{
			return new CoupledSessionHandler;
		});
	}

}
