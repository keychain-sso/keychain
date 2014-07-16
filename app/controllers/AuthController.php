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
	 * @return View
	 */
	public function getLogin()
	{
		return View::make('auth/login', 'auth.login');
	}

	/**
	 * Handles POST events for the login screen
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postLogin()
	{
		if (Input::has('_login'))
		{
			// Define validation rules
			$validator = Validator::make(Input::all(), array(
				'email'    => 'required|email|max:80',
				'password' => 'required'
			));

			// Run the validator
			if ($validator->fails())
			{
				Session::flash('messages.error', $validator->messages()->all('<p>:message</p>'));

				return Redirect::to(URL::previous())->withInput();
			}

			// Get the remember token
			$remember = Input::has('remember');

			// Authenticate the user
			$success = Auth::attempt(array(
				'email'    => Input::get('email'),
				'password' => Input::get('password')
			), $remember);

			// If auth is successful, redirect to the requested page
			if ($success)
			{
				Session::put('security.remember', $remember);

				return Redirect::intended('/');
			}
			else
			{
				Session::flash('messages.error', Lang::get('auth.login_failed'));

				return Redirect::to(URL::previous())->withInput();
			}
		}
	}

	/**
	 * Displays the registration page
	 *
	 * @access public
	 * @return View
	 */
	public function getRegister()
	{
		return View::make('auth/register', 'auth.register');
	}

	/**
	 * Handles POST events for the registration screen
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postRegister()
	{
		if (Input::has('_register'))
		{
			// Define validation rules
			$validator = Validator::make(Input::all(), array(
				'name'             => 'required|max:80',
				'email'            => 'required|email|max:80|unique:user_emails,address',
				'password'         => 'required|min:5|same:confirm_password',
				'confirm_password' => 'required',
			));

			// Run the validator
			if ($validator->fails())
			{
				Session::flash('messages.error', $validator->messages()->all('<p>:message</p>'));

				return Redirect::to(URL::previous())->withInput();
			}

			// Create the new user
			$user = new User;
			$user->name = Input::get('name');
			$user->password = Hash::make(Input::get('password'));
			$user->hash = Utilities::hash($user);
			$user->status = UserStatus::INACTIVE;
			$user->save();

			// Insert the user's email address
			$userEmail = new UserEmail;
			$userEmail->user_id = $user->id;
			$userEmail->address = Input::get('email');
			$userEmail->primary = Flags::YES;
			$userEmail->verified = Flags::NO;
			$userEmail->save();

			// Send the email verification mail
			Verifier::make('register', $userEmail->id);

			// Show registration success message
			Session::flash('messages.success', Lang::get('auth.register_email'));

			return Redirect::to(URL::previous());
		}
	}

	/**
	 * Handles user logout
	 *
	 * @access public
	 * @return Redirect
	 */
	public function getLogout()
	{
		Auth::logout();

		return Redirect::to('auth/login');
	}

}
