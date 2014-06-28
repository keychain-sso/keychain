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
		// Fetch the associated user
		$hash = Input::get('hash');
		$user = User::where('hash', $hash)->firstOrFail();

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

		return Redirect::to("profile/edit/{$hash}")->withInput();
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
	}

}
