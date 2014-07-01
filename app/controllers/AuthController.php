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
 * AuthController class
 *
 * Handles all primary authentication actions
 *
 * @package     Keychain
 * @subpackage  Controllers
 */
class AuthController extends BaseController {

	/**
	 * Displays the login page
	 *
	 * @access public
	 * @return \Illuminate\Support\Facades\View
	 */
	public function getLogin()
	{
		return View::make('auth/login', 'auth.login');
	}

	/**
	 * Handles POST events for the login screen
	 *
	 * @access public
	 * @return \Illuminate\Support\Facades\Redirect
	 */
	public function postLogin()
	{
		// Define validation rules
		$validator = Validator::make(Input::all(), array(
			'email'    => 'required',
			'password' => 'required'
		));

		// Run the validator
		if ($validator->passes())
		{
			$remember = Input::has('remember');

			$success = Auth::attempt(array(
				'email'    => Input::get('email'),
				'password' => Input::get('password')
			), $remember);

			if ($success)
			{
				// Auth successful, redirect to the requested page
				return Redirect::intended('/');
			}
			else
			{
				// Auth failed, show error message
				Session::flash('messages.error', Lang::get('auth.login_failed'));
			}
		}
		else
		{
			// Set the error message as flashdata
			Session::flash('messages.error', $validator->messages()->all('<p>:message</p>'));
		}

		return Redirect::to('auth/login')->withInput();
	}

	/**
	 * Handles user logout
	 *
	 * @access public
	 * @return \Illuminate\Support\Facades\Redirect
	 */
	public function getLogout()
	{
		Auth::logout();

		return Redirect::to('auth/login');
	}

}
