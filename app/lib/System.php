<?php namespace Keychain;

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

use Cache;
use DateTimeZone;

/**
 * System class
 *
 * Handles system level functionalities such as config management
 *
 * @package     Keychain
 * @subpackage  Libraries
 */
class System {

	/**
	 * Returns a list of timezones supported by the server
	 *
	 * @static
	 * @access public
	 * @return array
	 */
	public static function timezones()
	{
		return Cache::rememberForever('system.timezones', function()
		{
			$timezones = array();
			$identifiers = DateTimeZone::listIdentifiers();

			// Set both the key and value as the timezone name
			foreach ($identifiers as $identifier)
			{
				$timezones[$identifier] = $identifier;
			}

			return $timezones;
		});
	}

}
