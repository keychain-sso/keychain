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
 * Utilities class
 *
 * Provider helper functionalities across the board
 *
 * @package     Keychain
 * @subpackage  Libraries
 */
class Utilities {

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
			$identifiers = DateTimeZone::listIdentifiers();

			return static::arrayToSelect($identifiers);
		});
	}

	/**
	 * Transforms a 1D array to a laravel select worthy array
	 *
	 * @static
	 * @access public
	 * @param  array  $array
	 * @return array
	 */
	public static function arrayToSelect($array)
	{
		$select = array();

		foreach ($array as $item)
		{
			$select[$item] = $item;
		}

		return $select;
	}

}
