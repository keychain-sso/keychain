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

		return View::make('profile/view', $data);
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
		if ( ! Access::check('u_profile_edit', $user))
		{
			App::abort(HTTPStatus::FORBIDDEN);
		}

		// Merge the profile data with editor data
		$data = array_merge($data, array(
			'fieldEdit' => FormField::getEdit($user),
			'timezones' => Utilities::timezones(),
			'modal'     => 'edit',
		));

		return View::make('profile/view', $data);
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
			if ( ! Access::check('u_profile_edit', $user))
			{
				App::abort(HTTPStatus::FORBIDDEN);
			}

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
	 * @return \Illuminate\Support\Facades\View
	 */
	public function getEmails($hash, $action = '')
	{
		// Fetch the user's profile
		$user = User::where('hash', $hash)->firstOrFail();
		$data = $this->getProfileData($user);

		// Validate edit rights
		if ( ! Access::check('u_profile_edit', $user))
		{
			App::abort(HTTPStatus::FORBIDDEN);
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
		if ( ! Access::check('u_profile_edit', $user))
		{
			App::abort(HTTPStatus::FORBIDDEN);
		}

		if (is_null($action))
		{
			// Merge the profile data with editor data
			$data = array_merge($data, array(
				'keys'  => $user->keys,
				'modal' => 'keys',
			));

			return View::make('profile/view', $data);
		}
		else if ($action == 'remove')
		{
			UserKey::findOrFail($key)->delete();

			Session::flash('messages.success', Lang::get('profile.ssh_key_removed'));

			return Redirect::to(URL::previous());
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
			if ( ! Access::check('u_profile_edit', $user))
			{
				App::abort(HTTPStatus::FORBIDDEN);
			}

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
			$userKey              = new UserKey;
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
	 * Fetches the user's profile data
	 *
	 * @access private
	 * @param  User  $user
	 * @return array
	 */
	private function getProfileData($user)
	{
		return Cache::remember("user.field.data.{$user->id}", 1440, function() use ($user)
		{
			// Parse user's email addresses as primary and other
			$emails = new stdClass;

			foreach ($user->emails as $addr)
			{
				if ($addr->primary)
				{
					$emails->primary = $addr->email;
				}
				else
				{
					$emails->other[] = $addr->email;
				}
			}

			// Get user-group data
			$memberships = UserGroup::where('user_id', $user->id)->with('group')->get();

			// Return the user's profile data
			return array(
				'user'        => $user,
				'emails'      => $emails,
				'fieldView'   => FormField::getView($user),
				'memberships' => $memberships,
				'modal'       => false,
			);
		});
	}

}
