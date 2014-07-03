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
 * @since       Version 1.0
 * @filesource
 */

use Auth;
use DB;
use SessionHandlerInterface;

/**
 * CoupledSessionHandler class
 *
 * Custom session driver for handling sessions tied to users
 *
 * @package     Keychain
 * @subpackage  Drivers
 */
class CoupledSessionHandler implements SessionHandlerInterface {

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
		// This method is a stub
	}

	/**
	 * Implementation of SessionHandlerInterface::close
	 *
	 * @access public
	 * @return bool
	 */
	public function close()
	{
		// This method is a stub
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
		if ($session != null)
		{
			return $session->payload;
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
			'payload'    => $data,
			'updated_at' => time(),
		);

		// If user is logged in, set the user_id as well
		if (Auth::check())
		{
			$session['user_id'] = Auth::user()->id;
		}

		// Insert the session data
		$instance = DB::table('user_sessions')->where('id', $sessionId);

		if ($instance->count() == 0)
		{
			DB::table('user_sessions')->insert($session);
		}
		else
		{
			$instance->update($session);
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
		DB::table('user_sessions')->where('updated_at', '<', time() - $lifetime)->delete();
	}

}
