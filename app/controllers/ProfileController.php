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
		$user = User::where('hash', $hash)->firstOrFail();

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

		// Assign the view data
		$data = array(
			'user'        => $user,
			'emails'      => $emails,
			'fields'      => FormField::show($user),
			'memberships' => $memberships,
			'preview'     => Session::get('user.profile.preview'),
		);

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
		if (Request::ajax())
		{
			$user = User::where('hash', $hash)->firstOrFail();

			// Assign the view data
			$data = array(
				'user'      => $user,
				'fields'    => FormField::edit($user),
				'timezones' => Utilities::timezones(),
			);

			return View::make('profile/edit', $data);
		}
		else
		{
			Session::flash('user.profile.preview', 'edit');

			return Redirect::to("profile/view/{$hash}");
		}
	}

	/**
	 * Handles profile save functionality
	 *
	 * @access public
	 * @return \Illuminate\Support\Facades\Redirect
	 */
	public function postEdit()
	{

	}

}
