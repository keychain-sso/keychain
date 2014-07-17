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
 * @since       Version 1.0
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
	 * Transforms a 1D array to a laravel select worthy array
	 *
	 * @static
	 * @access public
	 * @param  string  $action
	 * @param  int  $id
	 * @return void
	 */
	public static function make($action, $id)
	{
		// Generate a unique hash
		$hash = md5(microtime().$action.$id);

		// Based on the type, determine all data
		switch ($action)
		{
			case 'email_add':
			case 'email_verify':
			case 'register':

				// Classify into a broader bucket
				$type = TokenTypes::EMAIL;
				$verify = 'email';

				// Resolve the user and email address
				$email = UserEmail::findOrFail($id);
				$user = User::find($email->user_id);
				$to = $email->address;
				break;

			case 'forgot':

				// Classify into a broader bucket
				$type = TokenTypes::PASSWORD;
				$verify = 'password';

				// Resolve the user and email address
				$user = User::find($id);
				$to = UserEmail::where('user_id', $id)->where('primary', Flags::YES)->firstOrFail()->address;
				break;

			default:

				App::abort(HTTPStatus::NOTFOUND);
				break;
		}

		// Delete any previous token for this permit
		Token::where('permits_id', $id)->where('permits_type', $type)->delete();

		// Create the token entry
		$token = new Token;
		$token->token = $hash;
		$token->permits_id = $id;
		$token->permits_type = $type;
		$token->save();

		// Build the email template
		$data = array(
			'user'   => $user,
			'action' => Lang::get("email.action_{$action}"),
			'token'  => url("token/verify/{$verify}/{$hash}"),
		);

		// Finally, we send the email
		Mail::queue('emails/verify', $data, function($message) use ($to)
		{
			$message->to($to)->subject(Lang::get('email.mail_subject'));
		});
	}

	/**
	 * Validates a token based on its type
	 *
	 * @static
	 * @access public
	 * @param  string  $type
	 * @param  string  $hash
	 * @return bool
	 */
	public static function check($type, $hash)
	{
		// Perform validation based on token type
		switch ($type)
		{
			case 'email':

				// Fetch the token
				$token = Token::where('permits_type', TokenTypes::EMAIL)->where('token', $hash)->firstOrFail();

				// Get the associated user and email
				$userEmail = UserEmail::find($token->permits_id);
				$user = User::find($userEmail->user_id);

				// Verify the associated email
				$userEmail->verified = Flags::YES;
				$userEmail->save();

				// If the user is inactive, activate their account
				// We also process auto-join groups at this point
				// As a security check, we do it only if the primary email is
				// being activated
				if ($user->status == UserStatus::INACTIVE && $userEmail->primary)
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
				Cache::tags("user.{$userEmail->user_id}.field")->flush();

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

			case 'password':

				// Fetch the token
				$token = Token::where('permits_type', TokenTypes::PASSWORD)->where('token', $hash)->firstOrFail();

				// Set the session flag to indicate successful validation
				Session::set('security.token.validated', true);

				// Delete the token
				$token->delete();

				return Redirect::to('auth/reset');
		}
	}

}
