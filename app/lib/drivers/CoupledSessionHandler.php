<?php namespace Keychain\Drivers;

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

use Agent;
use Auth;
use Config;
use DB;
use DeviceTypes;
use Request;
use SessionHandlerInterface;

use Illuminate\Session\ExistenceAwareInterface;

/**
 * CoupledSessionHandler class
 *
 * Custom session driver for handling sessions tied to users
 *
 * @package     Keychain
 * @subpackage  Drivers
 */
class CoupledSessionHandler implements SessionHandlerInterface, ExistenceAwareInterface {

	/**
	 * Indicates if a session key exists
	 */
	protected $exists = false;

	/**
	 * Implementation of SessionHandlerInterface::open
	 *
	 * @access public
	 * @param  string  $savePath
	 * @param  string  $sessionName
	 * @return object
	 */
	public function open($savePath, $sessionName)
	{
		return true;
	}

	/**
	 * Implementation of SessionHandlerInterface::close
	 *
	 * @access public
	 * @return bool
	 */
	public function close()
	{
		return true;
	}

	/**
	 * Returns a value from the user's session
	 *
	 * @access public
	 * @param  string  $sessionId
	 * @return string
	 */
	public function read($sessionId)
	{
		// Retrieve the session data
		$session = DB::table('user_sessions')->where('id', $sessionId)->first();

		// If data was returned, we return the payload
		if (isset($session->payload))
		{
			$this->exists = true;

			return base64_decode($session->payload);
		}
	}

	/**
	 * Saves data to the user's session
	 *
	 * @access public
	 * @param  string  $sessionId
	 * @param  string  $data
	 * @return void
	 */
	public function write($sessionId, $data)
	{
		// Build the payload
		$session = array(
			'id'         => $sessionId,
			'payload'    => base64_encode($data),
			'user_id'    => 0,
			'ip_address' => Request::getClientIp(),
			'updated_at' => date('Y-m-d H:i:s'),
		);

		// If user is logged in, set the user_id as well
		if (Auth::check())
		{
			$session['user_id'] = Auth::id();
		}

		// Save the session data
		if ( ! $this->exists)
		{
			// Insert the device type
			if (Agent::isMobile())
			{
				$session['device_type'] = DeviceTypes::MOBILE;
			}
			else if (Agent::isTablet())
			{
				$session['device_type'] = DeviceTypes::TABLET;
			}
			else
			{
				$session['device_type'] = DeviceTypes::COMPUTER;
			}

			// Insert the session record
			DB::table('user_sessions')->insert($session);

			// Purge stale sessions
			$this->gc(Config::get('session.lifetime'));
		}
		else
		{
			// Update the exsting session record
			DB::table('user_sessions')->where('id', $sessionId)->update($session);
		}
	}

	/**
	 * Invalidates a specific user session
	 *
	 * @access public
	 * @param  string  $sessionId
	 * @return void
	 */
	public function destroy($sessionId)
	{
		DB::table('user_sessions')->where('id', $sessionId)->delete();
	}

	/**
	 * Performs garbage collection on sessions
	 *
	 * @access public
	 * @param  string  $sessionId
	 * @return void
	 */
	public function gc($lifetime)
	{
		$threshold = date('Y-m-d H:i:s', time() - $lifetime * 60);

		DB::table('user_sessions')->where('updated_at', '<', $threshold)->delete();
	}

	/**
	 * Set the existence state for the session.
	 *
	 * @access public
	 * @param  bool  $value
	 * @return SessionHandlerInterface
	 */
	public function setExists($value)
	{
		$this->exists = $value;

		return $this;
	}

}
