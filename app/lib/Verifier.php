<?php namespace Keychain;

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
use Cache;
use DateTimeZone;
use HTTPStatus;
use Lang;
use Mail;
use Token;
use TokenTypes;
use User;
use UserEmail;

/**
 * Verifier class
 *
 * Verification service that uses tokens to validate specific fields
 *
 * @package     Keychain
 * @subpackage  Libraries
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
				$to = UserEmail::where('user_id', $id)->firstOrFail()->address;
				break;

			default:

				App::abort(HTTPStatus::NOTFOUND);
				break;
		}

		// Delete any previous token for this permit
		Token::where('permits_id', $id)->where('permits_type', $type)->delete();

		// Create the token entry
		$token = new Token;
		$token->token        = $hash;
		$token->permits_id   = $id;
		$token->permits_type = $type;
		$token->save();

		// Build the email template
		$data = array(
			'user'   => $user,
			'action' => Lang::get("email.action_{$action}"),
			'token'  => url("token/verify/{$verify}/{$hash}"),
		);

		// Finally, we send the email
		Mail::queue('emails/default', $data, function($message) use ($to)
		{
			$message->to($to)->subject(Lang::get('email.mail_subject'));
		});
	}

}
