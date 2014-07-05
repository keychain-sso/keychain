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
 * ProfileController class
 *
 * Handles display and actions on the user's profile
 *
 * @package     Keychain
 * @subpackage  Controllers
 */
class ProfileController extends BaseController {

	/**
	 * Redirects to the logged in user's profile
	 *
	 * @access public
	 * @return \Illuminate\Support\Facades\Redirect
	 */
	public function getIndex()
	{
		return Redirect::to('profile/view/'.Auth::user()->hash);
	}

	/**
	 * Displays a specific user's profile
	 *
	 * @access public
	 * @param  string  $hash
	 * @return \Illuminate\Support\Facades\View
	 */
	public function getView($hash)
	{
		// Fetch the user's profile
		$user = User::where('hash', $hash)->firstOrFail();
		$data = $this->getProfileData($user);

		// Set the page title
		$title = Lang::get('profile.viewing_profile', array(
			'first_name' => $user->first_name,
			'last_name'  => $user->last_name,
		));

		return View::make('profile/view', $title, $data);
	}

	/**
	 * Displays the edit basic profile screen for the user
	 *
	 * @access public
	 * @param  string  $hash
	 * @return \Illuminate\Support\Facades\View
	 */
	public function getEdit($hash)
	{
		// Fetch the user's profile
		$user = User::where('hash', $hash)->firstOrFail();
		$data = $this->getProfileData($user);

		// Validate edit rights
		Access::restrict('user.edit', $user);

		// Merge the profile data with editor data
		$data = array_merge($data, array(
			'fieldEdit' => FormField::getEdit($user),
			'timezones' => Utilities::timezones(),
			'modal'     => 'edit',
		));

		return View::make('profile/view', 'profile.edit_profile', $data);
	}

	/**
	 * Handles profile save functionality
	 *
	 * @access public
	 * @return \Illuminate\Support\Facades\Redirect
	 */
	public function postEdit()
	{
		if (Input::has('_save'))
		{
			// Fetch the associated user
			$hash = Input::get('hash');
			$user = User::where('hash', $hash)->firstOrFail();

			// Validate edit rights
			Access::restrict('user.edit', $user);

			// Save the form data and show the status
			$status = FormField::save($user, Input::all());

			if ($status === true)
			{
				Session::flash('messages.success', Lang::get('profile.profile_saved'));
			}
			else
			{
				Session::flash('messages.error', $status);
			}
		}

		return Redirect::to(URL::previous())->withInput();
	}

	/**
	 * Displays the email management screen for the user
	 *
	 * @access public
	 * @param  string  $hash
	 * @param  string  $action
	 * @param  int  $email
	 * @return \Illuminate\Support\Facades\View
	 */
	public function getEmails($hash, $action = null, $email = 0)
	{
		// Fetch the user's profile
		$user = User::where('hash', $hash)->firstOrFail();
		$data = $this->getProfileData($user);

		// Validate edit rights
		Access::restrict('user.edit', $user);

		// Perform the requested action
		switch ($action)
		{
			case 'remove':

				// Check for user_id as well, to avoid injection
				// Primary emails may not be removed
				UserEmail::where('id', $email)->where('user_id', $user->id)->where('primary', 0)->delete();

				// Purge the user field data cache
				Cache::tags("fields.{$user->id}")->flush();

				// Redirect back to the previous URL
				Session::flash('messages.success', Lang::get('profile.email_removed'));

				return Redirect::to(URL::previous());

			case 'verify':

				// Send the verification email
				Verifier::make('email_add', $email);

				// Redirect back to the previous URL
				Session::flash('messages.success', Lang::get('profile.email_verify'));

				return Redirect::to(URL::previous());

			case 'primary':

				// Check for user_id as well, to avoid injection
				// Only verified emails can be marked as primary
				$userEmail = UserEmail::where('id', $email)->where('user_id', $user->id)->where('verified', 1)->firstOrFail();
				$userEmail->primary = 1;
				$userEmail->save();

				// Now, mark the previous primary as regular
				UserEmail::where('user_id', $user->id)->where('id', '<>', $email)->update(array('primary' => 0));

				// Purge the user field cache
				Cache::forget("user.field.data.{$user->id}");

				// Redirect back to the previous URL
				return Redirect::to(URL::previous());

			default:

				return View::make('profile/view', 'profile.manage_email_addresses', array_merge($data, array('modal' => 'emails')));
		}
	}

	/**
	 * Handles email save functionality
	 *
	 * @access public
	 * @return \Illuminate\Support\Facades\Redirect
	 */
	public function postEmails()
	{
		if (Input::has('_add'))
		{
			// Fetch the associated user
			$hash = Input::get('hash');
			$user = User::where('hash', $hash)->firstOrFail();

			// Validate edit rights
			Access::restrict('user.edit', $user);

			// Validate posted fields
			$validator = Validator::make(Input::all(), array(
				'email' => 'required|email|max:80|unique:user_emails,address',
			));

			// Run the validator
			if ($validator->fails())
			{
				Session::flash('messages.error', $validator->messages()->all('<p>:message</p>'));

				return Redirect::to(URL::previous())->withInput();
			}

			// Save the email address
			$email = new UserEmail;
			$email->user_id  = $user->id;
			$email->address  = Input::get('email');
			$email->primary  = Flags::NO;
			$email->verified = Flags::NO;
			$email->save();

			// Send the verification token
			Verifier::make('email_add', $email->id);

			// Purge the user field data cache
			Cache::tags("fields.{$user->id}")->flush();

			// Redirect back to the previous URL
			Session::flash('messages.success', Lang::get('profile.email_verify'));

			return Redirect::to(URL::previous());
		}
	}

	/**
	 * Displays the SSH key management screen for the user
	 *
	 * @access public
	 * @param  string  $hash
	 * @param  string  $action
	 * @param  int  $key
	 * @return \Illuminate\Support\Facades\View|\Illuminate\Support\Facades\Redirect
	 */
	public function getKeys($hash, $action = null, $key = 0)
	{
		// Fetch the user's profile
		$user = User::where('hash', $hash)->firstOrFail();
		$data = $this->getProfileData($user);

		// Validate edit rights
		Access::restrict('user.edit', $user);

		// Perform the requested action
		switch ($action)
		{
			case 'remove':

				// Also check for user_id to avoid injection
				UserKey::where('id', $key)->where('user_id', $user->id)->delete();

				// Redirect back to previous URL
				Session::flash('messages.success', Lang::get('profile.ssh_key_removed'));

				return Redirect::to(URL::previous());


			default:

				$data = array_merge($data, array(
					'keys'  => $user->keys,
					'modal' => 'keys',
				));

				return View::make('profile/view', 'profile.manage_ssh_keys', $data);
		}
	}

	/**
	 * Handles SSH key save functionality
	 *
	 * @access public
	 * @return \Illuminate\Support\Facades\Redirect
	 */
	public function postKeys()
	{
		if (Input::has('_add'))
		{
			// Fetch the associated user
			$hash = Input::get('hash');
			$user = User::where('hash', $hash)->firstOrFail();

			// Validate edit rights
			Access::restrict('user.edit', $user);

			// Validate posted fields
			$validator = Validator::make(Input::all(), array(
				'title' => 'required|max:30',
				'key'   => 'required',
			));

			// Run the validator
			if ($validator->fails())
			{
				Session::flash('messages.error', $validator->messages()->all('<p>:message</p>'));

				return Redirect::to(URL::previous())->withInput();
			}

			// Generate the fingerprint
			$key = Input::get('key');

			// Validate the fingerprint
			if (is_null($fingerprint = Utilities::fingerprint($key)))
			{
				Session::flash('messages.error', Lang::get('profile.invalid_key'));

				return Redirect::to(URL::previous())->withInput();
			}

			// Save the SSH key
			$userKey = new UserKey;
			$userKey->user_id     = $user->id;
			$userKey->title       = Input::get('title');
			$userKey->key         = $key;
			$userKey->fingerprint = $fingerprint;
			$userKey->save();

			Session::flash('messages.success', Lang::get('profile.ssh_key_added'));

			return Redirect::to(URL::previous());
		}
	}

	/**
	 * Shows the security settings screen
	 *
	 * @access public
	 * @param  string  $hash
	 * @param  string  $action
	 * @return \Illuminate\Support\Facades\View
	 */
	public function getSecurity($hash, $action = null)
	{
		// Fetch the user's profile
		$user = User::where('hash', $hash)->firstOrFail();
		$data = $this->getProfileData($user);

		// Validate edit rights
		Access::restrict('user.edit', $user);

		// Perform the requested action
		switch ($action)
		{
			case 'killall':

				// Kill the specified session
				UserSession::where('id', '<>', Session::getId())->where('user_id', $user->id)->delete();

				// Re-authenticate the current session to regenerate the remember token
				Auth::refresh(Session::get('security.remember'));

				// Redirect back to the previous URL
				Session::flash('messages.success', Lang::get('profile.sessions_killed'));

				return Redirect::to(URL::previous());

			default:

				$data = array_merge($data, array(
					'sessions' => UserSession::where('user_id', $user->id)->get(),
					'modal'    => 'security',
				));

				return View::make('profile/view', 'profile.security_settings', $data);
		}
	}

	/**
	 * Handles security settings save functionality
	 *
	 * @access public
	 * @return \Illuminate\Support\Facades\Redirect
	 */
	public function postSecurity()
	{
		if (Input::has('_save'))
		{
			// Fetch the associated user
			$hash = Input::get('hash');
			$user = User::where('hash', $hash)->firstOrFail();
			$manage = Access::check('user.manage', $user);

			// Validate edit rights
			Access::restrict('user.edit', $user);

			// Define the validation rules
			$validator = Validator::make(Input::all(), array(
				'new_password'     => 'min:7',
				'confirm_password' => 'required_with:new_password|same:new_password',
				'status'           => 'exists:user_status,id',
			));

			// Run the validator
			if ($validator->fails())
			{
				Session::flash('messages.error', $validator->messages()->all('<p>:message</p>'));

				return Redirect::to(URL::previous())->withInput();
			}

			// Check the old password
			if ( ! $manage && ! Hash::check(Input::get('old_password'), $user->password))
			{
				Session::flash('messages.error', Lang::get('profile.old_password_invalid'));

				return Redirect::to(URL::previous())->withInput();
			}

			// Finally, we change the password
			$user->password = Hash::make(Input::get('new_password'));

			// Check if logged in user has manage rights
			// If so, update the account settings as well
			if ($manage)
			{
				$user->status = Input::get('status');
			}

			$user->save();

			// Purge the user field data cache
			Cache::tags("fields.{$user->id}")->flush();

			// Redirect back to the previous URL
			Session::flash('messages.success', Lang::get('profile.security_saved'));

			return Redirect::to(URL::previous());
		}
	}

	/**
	 * Fetches the user's profile data
	 *
	 * @access private
	 * @param  User  $user
	 * @return array
	 */
	private function getProfileData($user)
	{
		return Cache::tags("fields.{$user->id}")->remember(Auth::id(), 60, function() use ($user)
		{
			// Parse user's email addresses as primary and other
			$emails = new stdClass;

			foreach ($user->emails as $email)
			{
				if ($email->primary)
				{
					$emails->primary = $email;
				}
				else
				{
					$emails->other[] = $email;
				}
			}

			// Get user-group data
			$memberships = UserGroup::where('user_id', $user->id)->with('group')->get();

			// Build the profile data
			$profile = array(
				'user'        => $user,
				'emails'      => $emails,
				'fieldView'   => FormField::getView($user),
				'memberships' => $memberships,
				'modal'       => false,
			);

			return $profile;
		});
	}

}
