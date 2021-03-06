<?php namespace Keychain\Components;

/**
 * Keychain
 *
 * SSO login provider for enterprise.
 *
 * @package     Keychain
 * @copyright   (c) Keychain Developers
 * @license     http://opensource.org/licenses/BSD-3-Clause
 * @link        https://github.com/keychain-sso/keychain
 * @since       Version 1.0.0
 * @filesource
 */

use App;
use Auth;
use Cache;
use DateTimeZone;
use Flags;
use Group;
use HTTPStatus;
use Lang;
use Mail;
use NoticeTypes;
use Redirect;
use Session;
use Token;
use TokenTypes;
use User;
use UserEmail;
use UserGroup;
use UserStatus;
use View;

/**
 * Verifier class
 *
 * Verification service that uses tokens to validate specific fields
 *
 * @package     Keychain
 * @subpackage  Components
 */
class Verifier {

	/**
	 * Creates a validation token and sends out the email containing
	 * that token
	 *
	 * @static
	 * @access public
	 * @param  int  $type
	 * @param  string  $action
	 * @param  UserEmail  $email
	 * @return void
	 */
	public static function make($type, $action, $email)
	{
		// Get the associated user
		$user = User::find($email->user_id);

		// Delete any previous token for this permit
		Token::where('permits_id', $email->id)->where('permits_type', $type)->delete();

		// Create the token entry
		$token = new Token;
		$token->token = md5(microtime().$action.$email->id);
		$token->permits_id = $email->id;
		$token->permits_type = $type;
		$token->save();

		// Build the email template
		$data = array(
			'user'   => $user,
			'action' => Lang::get($action),
			'token'  => url("token/verify/{$token->token}"),
		);

		// Finally, we send the email to the user
		Mail::queue('email/verify', $data, function($message) use ($email)
		{
			$message->to($email->address)->subject(Lang::get('email.subject_verify'));
		});
	}

	/**
	 * Validates an email/password token based
	 *
	 * @static
	 * @access public
	 * @param  string  $type
	 * @param  string  $hash
	 * @return bool
	 */
	public static function check($token)
	{
		// Fetch the token
		$token = Token::where('token', $token)->firstOrFail();

		// Get the associated user and email
		$email = UserEmail::findOrFail($token->permits_id);
		$user = User::findOrFail($email->user_id);

		// Perform validation based on token type
		switch ($token->permits_type)
		{
			case TokenTypes::EMAIL:

				// Verify the associated email
				$email->verified = Flags::YES;
				$email->save();

				// If the user is inactive, activate their account
				// We also process auto-join groups at this point
				// As a security check, we do it only if the primary email is
				// being activated
				if ($user->status == UserStatus::INACTIVE && $email->primary)
				{
					// Activate the user
					$user->status = UserStatus::ACTIVE;
					$user->save();

					// Get all auto-join groups
					$autoGroups = Group::where('auto_join', Flags::YES)->lists('id');

					if (count($autoGroups) > 0)
					{
						// Remove all memberships for auto join groups for this user
						UserGroup::where('user_id', $user->id)->whereIn('group_id', $autoGroups)->delete();

						// Build group memberships
						foreach ($autoGroups as $group)
						{
							$userGroups[] = array(
								'user_id'  => $user->id,
								'group_id' => $group,
							);
						}

						// Insert the group membership info
						UserGroup::insert($userGroups);
					}
				}

				// Purge the user field data cache
				Cache::tags('field')->forget($email->user_id);

				// Delete the token
				$token->delete();

				// Show a success notice
				$data = array(
					'type'    => NoticeTypes::SUCCESS,
					'message' => Lang::get('global.email_verified'),
				);

				if (Auth::check())
				{
					$data['return'] = link_to('/', Lang::get('global.return_profile'));
				}
				else
				{
					$data['return'] = link_to('auth/login', Lang::get('global.return_login'));
				}

				return View::make('common/notice', 'global.information', $data);

			case TokenTypes::PASSWORD:

				// Set the session flag to indicate successful validation
				Session::put('security.reset.account', $email);

				// Delete the token
				$token->delete();

				return Redirect::to('auth/reset');
		}
	}

}
