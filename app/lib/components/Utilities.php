<?php namespace Keychain\Components;

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
use Lang;

/**
 * Utilities class
 *
 * Provider helper functionalities across the board
 *
 * @package     Keychain
 * @subpackage  Components
 */
class Utilities {

	/**
	 * Generates a hash for a specific model
	 *
	 * @static
	 * @access public
	 * @param  Eloquent  $model
	 * @param  string  $column
	 * @return string
	 */
	public static function hash($model, $column = 'hash')
	{
		while (true)
		{
			$hash = str_random(8);

			if ($model->where($column, $hash)->count() == 0)
			{
				return $hash;
			}
		}
	}

	/**
	 * Returns a list of timezones supported by the server
	 *
	 * @static
	 * @access public
	 * @param  bool  $csv
	 * @return array|string
	 */
	public static function timezones($csv = false)
	{
		return Cache::tags('global')->rememberForever("timezones.{$csv}", function() use ($csv)
		{
			$timezones = DateTimeZone::listIdentifiers();

			// If not requesting CSV, we return a nested array where we group the timezones
			// based on the geographical region
			if ( ! $csv)
			{
				foreach ($timezones as $timezone)
				{
					if (str_contains($timezone, '/'))
					{
						list($region, $zone) = explode('/', $timezone);
						$zone = str_replace('_', ' ', $zone);

						$regions[$region][$timezone] = $zone;
					}
					else
					{
						$regions[Lang::get('global.others')][$timezone] = $timezone;
					}
				}

				return $regions;
			}

			// For CSV, just return a regular array of possible values
			else
			{
				return implode(',', $timezones);
			}
		});
	}

	/**
	 * Generates fingerprints for SSH keys
	 *
	 * @static
	 * @access public
	 * @param  string  $key
	 * @return string|null
	 */
	public static function fingerprint($key)
	{
		$content = explode(' ', $key, 3);

		if (count($content) > 1)
		{
			return join(':', str_split(md5(base64_decode($content[1])), 2));
		}
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
