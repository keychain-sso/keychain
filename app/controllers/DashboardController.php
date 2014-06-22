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
 * DashboardController class
 *
 * Handles display and actions on the user's dashboard
 *
 * @package     Keychain
 * @subpackage  Controllers
 */
class DashboardController extends BaseController {

	/**
	 * Displays the user dash
	 *
	 * @access public
	 * @return \Illuminate\Support\Facades\View
	 */
	public function getIndex()
	{
		$user = Auth::user();

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
			$fields->{$item->field->category}[] = (object) array(
				'name'  => $item->field->name,
				'value' => nl2br($item->value),
			);
		}

		// Get user-group data
		$memberships = UserGroup::where('user_id', $user->id)->with('group')->get();

		// Assign the view data
		$data = array(
			'emails'      => $emails,
			'fields'      => $fields,
			'memberships' => $memberships,
		);

		return View::make('dashboard/index', $data);
	}

}
