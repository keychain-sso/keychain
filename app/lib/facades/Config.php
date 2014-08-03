<?php namespace Keychain\Facades;

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

use Cache;

/**
 * Config class
 *
 * Abstraction over \Illuminate\Support\Facades\Config
 *
 * @package     Keychain
 * @subpackage  Facades
 */
class Config extends \Illuminate\Support\Facades\Config {

	/**
	 * Get the specified configuration value
	 *
	 * @static
	 * @access public
	 * @param  string  $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	public static function get($key, $default = null)
	{
		return Cache::tags('global')->rememberForever("config.{$key}", function() use ($key, $default)
		{
			return parent::get($key, $default);
		});
	}

}
