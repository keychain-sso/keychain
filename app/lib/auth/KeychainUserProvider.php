<?php namespace Keychain\Auth;

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

use Config;
use Hash;
use User;
use UserEmail;

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\UserProviderInterface;
use Illuminate\Database\Connection;
use Illuminate\Hashing\HasherInterface;

/**
 * KeychainUserProvider class
 *
 * Handles primary and multi-factor authentication for users
 *
 * @package     Keychain
 * @subpackage  Drivers
 */
class KeychainUserProvider implements UserProviderInterface {

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @access public
	 * @param  mixed  $identifier
	 * @return \Illuminate\Auth\UserInterface|null
	 */
	public function retrieveById($identifier)
	{
		return User::find($identifier);
	}

	/**
	 * Retrieve a user by by their unique identifier and "remember me" token.
	 *
	 * @access public
	 * @param  mixed  $identifier
	 * @param  string  $token
	 * @return \Illuminate\Auth\UserInterface|null
	 */
	public function retrieveByToken($identifier, $token)
	{
		return User::where('id', $identifier)->where('remember_token', $token)->first();
	}

	/**
	 * Update the "remember me" token for the given user in storage.
	 *
	 * @access public
	 * @param  \Illuminate\Auth\UserInterface  $user
	 * @param  string  $token
	 * @return void
	 */
	public function updateRememberToken(UserInterface $user, $token)
	{
		$user->setAttribute('remember_token', $token);

		$user->save();
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @access public
	 * @param  array  $credentials
	 * @return \Illuminate\Auth\UserInterface|null
	 */
	public function retrieveByCredentials(array $credentials)
	{
		// First, we fetch a matching verified email address
		$email = UserEmail::where('email', $credentials['email'])->where('verified', 1)->first();

		// If an email address match is found, return the corresponding user
		if ($email != null)
		{
			return $email->user;
		}
	}

	/**
	 * Validate a user against the given credentials.
	 *
	 * @access public
	 * @param  \Illuminate\Auth\UserInterface  $user
	 * @param  array  $credentials
	 * @return bool
	 */
	public function validateCredentials(UserInterface $user, array $credentials)
	{
		$plain = $credentials['password'];

		return Hash::check($plain, $user->getAuthPassword());
	}

}
