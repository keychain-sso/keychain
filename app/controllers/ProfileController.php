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

		// Get custom profile fields
		$userFields = UserField::where('user_id', $user->id)->with('field')->get();

		$fields = new stdClass;
		$fields->{FieldCategory::BASIC} = array();
		$fields->{FieldCategory::CONTACT} = array();
		$fields->{FieldCategory::OTHER} = array();

		foreach ($userFields as $item)
		{
			if (Access::check('u_field_view', $user, $item->field->id))
			{
				$fields->{$item->field->category}[] = (object) array(
					'id'    => $item->field->id,
					'name'  => $item->field->name,
					'value' => nl2br($item->value),
				);
			}
		}

		// Get user-group data
		$memberships = UserGroup::where('user_id', $user->id)->with('group')->get();

		// Assign the view data
		$data = array(
			'emails'      => $emails,
			'fields'      => $fields,
			'memberships' => $memberships,
		);

		return View::make('profile/view', $data);
	}

	/**
	 * Displays the edit profile screen for the user
	 *
	 * @access public
	 * @param  string $category
	 * @return \Illuminate\Support\Facades\View
	 */
	public function getEdit()
	{
		return 'Coming soon';
	}

}
