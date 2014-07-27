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
 * UserController class
 *
 * Handles display and actions on the user's profile
 *
 * @package     Keychain
 * @subpackage  Controllers
 */
class UserController extends BaseController {

	/**
	 * Displays the user list
	 *
	 * @access public
	 * @return View
	 */
	public function getList()
	{
		return View::make('user/list', 'global.users', $this->getUserListData());
	}

	/**
	 * Opens the create user screen
	 *
	 * @access public
	 * @return View
	 */
	public function getCreate()
	{
		// Get the user list info
		$data = $this->getUserListData();

		// Validate manage rights
		Access::restrict(ACLFlags::USER_MANAGE);

		// Merge the list data with the view data
		$data = array_merge($data, array(
			'timezones' => Utilities::timezones(),
			'modal'     => 'user.create'
		));

		// Show the user create screen
		return View::make('user/list', 'user.create_new_user', $data);
	}

	/**
	 * Handles create user POST events
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postCreate()
	{
		// Validate manage rights
		Access::restrict(ACLFlags::USER_MANAGE);

		// Validate posted fields
		$validator = Validator::make(Input::all(), array(
			'name'     => 'required|max:80',
			'email'    => 'required|email|max:80|unique:user_emails,address',
			'password' => 'required',
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
		$user->status = UserStatus::ACTIVE;
		$user->save();

		// Insert the user's email address
		$email = new UserEmail;
		$email->user_id = $user->id;
		$email->address = Input::get('email');
		$email->primary = Flags::YES;
		$email->verified = Flags::YES;
		$email->save();

		// Redirect to the new user's profile
		return Redirect::to("user/view/{$user->hash}");
	}

	/**
	 * Displays a specific user's profile
	 *
	 * @access public
	 * @param  string  $hash
	 * @return View
	 */
	public function getView($hash)
	{
		// Fetch the user's profile
		$user = User::where('hash', $hash)->firstOrFail();
		$data = $this->getUserViewData($user);

		// Set the page title
		$title = Lang::get('user.viewing_profile', array('name' => $user->name));

		// Show the view screen
		return View::make('user/view', $title, $data);
	}

	/**
	 * Handles avatar upload operation
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postView()
	{
		// Fetch the associated user
		$hash = Input::get('hash');
		$user = User::where('hash', $hash)->firstOrFail();

		// Validate edit rights
		Access::restrict(ACLFlags::USER_EDIT, $user);

		// Build the avatar information
		$avatar = Input::file('avatar');
		$input = array('avatar' => $avatar);
		$size = Config::get('view.icon_size');

		// Validate the avatar field
		$validator = Validator::make($input, array('avatar' => 'required|image'));

		// Run the validator
		if ($validator->fails())
		{
			Session::flash('messages.error', $validator->messages()->all('<p>:message</p>'));

			return Redirect::to(URL::previous());
		}

		// Validate the image itself
		if ($avatar->isValid())
		{
			// Parse the image
			$image = Image::make($avatar->getRealPath());
			$width = $image->width();
			$height = $image->height();

			// If image size is less than $size X $size, we save it right away
			// Otherwise, we show the resize dialog
			if ($width <= $size && $height <= $size)
			{
				// Resize the avatar to $size X $size
				$image->resize($size, $size);
				$image->save();

				// Link the avatar to the user
				$user->avatar = Flags::YES;
				$user->save();

				// Move the avatar to the upload folder
				$avatar->move(public_path().'/uploads/avatars', $user->hash);

				return Redirect::to("user/view/{$user->hash}");
			}
			else
			{
				// Save the file name in session
				Session::put('user.avatar.resize', true);

				// Remove the existing avatar
				File::delete(public_path()."/uploads/avatars/{$user->hash}");

				$user->avatar = Flags::NO;
				$user->save();

				// Move the avatar to the upload folder
				$avatar->move(public_path().'/uploads/avatars/', $user->hash);

				// Take the user to the avatar resizing utility
				return Redirect::to("user/avatar/{$user->hash}");
			}
		}

		// Input file was invalid
		Session::flash('messages.error', Lang::get('user.avatar_invalid'));

		return Redirect::to("user/view/{$user->hash}");
	}

	/**
	 * Shows the avatar resize screen
	 *
	 * @access public
	 * @param  string  $hash
	 * @return View
	 */
	public function getAvatar($hash)
	{
		// Fetch the user's profile
		$user = User::where('hash', $hash)->firstOrFail();
		$data = $this->getUserViewData($user);

		// Validate edit rights
		Access::restrict(ACLFlags::USER_EDIT, $user);

		// Show the resize dialog if the file name was set in the session
		if (Session::has('user.avatar.resize'))
		{
			// Merge the user data with the view data
			$data = array_merge($data, array(
				'title' => Lang::get('user.change_avatar'),
				'modal' => 'user.avatar'
			));

			// Make the response
			return Response::view('user/view', $data);

			// We disable the browser cache to avoid displaying the old avatar
			$response->header('Cache-Control', 'nocache, no-store, max-age=0, must-revalidate');
			$response->header('Pragma', 'no-cache');
			$response->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');

			return $response;
		}

		// Illegal resource access
		App::abort(HTTPStatus::FORBIDDEN);
	}

	/**
	 * Handle POST events for the avatar dialog
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postAvatar()
	{
		// Check if the file name was set in the session
		if (Session::has('user.avatar.resize'))
		{
			// Fetch the associated user
			$hash = Input::get('hash');
			$user = User::where('hash', $hash)->firstOrFail();

			// Validate edit rights
			Access::restrict(ACLFlags::USER_EDIT, $user);

			// Validate posted fields
			$validator = Validator::make(Input::all(), array(
				'screen_width'  => 'required|integer|min:1',
				'screen_height' => 'required|integer|min:1',
				'width'         => 'required|integer|min:0',
				'height'        => 'required|integer|min:0',
				'x'             => 'required|integer|min:0',
				'y'             => 'required|integer|min:0',
			));

			// Run the validator
			if ($validator->fails())
			{
				Session::flash('messages.error', $validator->messages()->all('<p>:message</p>'));

				return Redirect::to(URL::previous())->withInput();
			}

			// Get the size and the image and load it
			$image = Image::make(public_path()."/uploads/avatars/{$user->hash}");
			$size = Config::get('view.icon_size');

			// Calculate the screen vs actual size ratio
			$ratio_width = floatval($image->width() / Input::get('screen_width'));
			$ratio_height = floatval($image->height() / Input::get('screen_height'));

			// Get the requested image dimensions
			$dim = new stdClass;
			$dim->width = intval(Input::get('width') * $ratio_width);
			$dim->height = intval(Input::get('height') * $ratio_height);
			$dim->x = intval(Input::get('x') * $ratio_width);
			$dim->y = intval(Input::get('y') * $ratio_height);

			$endx = $dim->x + $dim->width;
			$endy = $dim->y + $dim->height;

			// Verify selection falls within the X and Y bounds of the image
			if ($endx > $image->width() || $endy > $image->height())
			{
				Session::flash('messages.error', Lang::get('user.avatar_invalid'));

				return Redirect::to(URL::previous())->withInput();
			}

			// Crop and resize the image
			$image->crop($dim->width, $dim->height, $dim->x, $dim->y);
			$image->resize($size, $size);
			$image->save();

			// Link the avatar to the user
			$user->avatar = Flags::YES;
			$user->save();

			// Redirect back to the user profile
			Session::forget('user.avatar.resize');

			return Redirect::to("user/view/{$user->hash}");
		}

		// Trigger a 404 as we have nothing to do here
		App::abort(HTTPStatus::NOTFOUND);
	}

	/**
	 * Displays the edit basic profile screen for the user
	 *
	 * @access public
	 * @param  string  $hash
	 * @return View
	 */
	public function getEdit($hash)
	{
		// Fetch the user's profile
		$user = User::where('hash', $hash)->firstOrFail();
		$data = $this->getUserViewData($user);

		// Validate edit rights
		Access::restrict(ACLFlags::USER_EDIT, $user);

		// Merge the user data with editor data
		$data = array_merge($data, array(
			'fieldEdit' => FormField::getEdit($user),
			'timezones' => Utilities::timezones(),
			'modal'     => 'user.editor',
		));

		return View::make('user/view', 'user.edit_profile', $data);
	}

	/**
	 * Handles profile save functionality
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postEdit()
	{
		// Fetch the associated user
		$hash = Input::get('hash');
		$user = User::where('hash', $hash)->firstOrFail();

		// Validate edit rights
		Access::restrict(ACLFlags::USER_EDIT, $user);

		// Save the form data and show the status
		$status = FormField::save($user, Input::all());

		if ($status === true)
		{
			Session::flash('messages.success', Lang::get('user.profile_saved'));
		}
		else
		{
			Session::flash('messages.error', $status);
		}

		return Redirect::to(URL::previous())->withInput();
	}

	/**
	 * Displays the email management screen for the user
	 *
	 * @access public
	 * @param  string  $hash
	 * @param  string  $action
	 * @param  int  $id
	 * @return View
	 */
	public function getEmails($hash, $action = null, $id = 0)
	{
		// Fetch the user's profile
		$user = User::where('hash', $hash)->firstOrFail();
		$data = $this->getUserViewData($user);

		// Validate edit rights
		Access::restrict(ACLFlags::USER_EDIT, $user);

		// Get the associated email
		if ($id > 0)
		{
			$email = UserEmail::where('id', $id)->where('user_id', $user->id)->firstOrFail();
		}

		// Perform the requested action
		switch ($action)
		{
			case 'remove':

				// Primary emails may not be removed
				if ( ! $email->primary)
				{
					// Delete the email address
					$email->delete();

					// Purge the user field data cache
					Cache::tags("field.user.{$user->id}")->flush();

					// Redirect back to the previous URL
					Session::flash('messages.success', Lang::get('user.email_removed'));

					return Redirect::to(URL::previous());
				}

			case 'verify':

				// If logged in user has manager rights, we mark the email as
				// verified right away. Otherwise, we send a verification token to
				// the email address
				if (Access::check(ACLFlags::USER_MANAGE))
				{
					$email->verified = Flags::YES;
					$email->save();

					// Redirect back to the previous URL
					Session::flash('messages.success', Lang::get('user.email_verify_editor'));
				}
				else
				{
					// Send the email verification mail
					Verifier::make(TokenTypes::EMAIL, 'email.address_verify', $email);

					// Redirect back to the previous URL
					Session::flash('messages.success', Lang::get('user.email_verify'));
				}

				return Redirect::to(URL::previous());

			case 'primary':

				// Only verified emails can be marked as primary
				if ($email->verified)
				{
					$email->primary = Flags::YES;
					$email->save();

					// Now, mark the previous primary as regular
					UserEmail::where('user_id', $user->id)->where('id', '<>', $id)->update(array('primary' => Flags::NO));

					// Purge the user field data cache
					Cache::tags("field.user.{$user->id}")->flush();

					// Redirect back to the previous URL
					return Redirect::to(URL::previous());
				}

			default:

				return View::make('user/view', 'user.manage_emails', array_merge($data, array('modal' => 'user.emails')));
		}

		// If we are here, something is fishy
		App::abort(HTTPStatus::FORBIDDEN);
	}

	/**
	 * Handles email save functionality
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postEmails()
	{
		// Fetch the associated user
		$hash = Input::get('hash');
		$user = User::where('hash', $hash)->firstOrFail();

		// Validate edit rights
		Access::restrict(ACLFlags::USER_EDIT, $user);

		// Does the user have manager access?
		$manager = Access::check(ACLFlags::USER_MANAGE);

		// Validate posted fields
		$validator = Validator::make(Input::all(), array('email' => 'required|email|max:80|unique:user_emails,address'));

		// Run the validator
		if ($validator->fails())
		{
			Session::flash('messages.error', $validator->messages()->all('<p>:message</p>'));

			return Redirect::to(URL::previous())->withInput();
		}

		// Save the email address
		$email = new UserEmail;
		$email->user_id = $user->id;
		$email->address = Input::get('email');
		$email->primary = Flags::NO;
		$email->verified = $manager;
		$email->save();

		// Send the verification token for non-managers only
		if ( ! $manager)
		{
			Verifier::make(TokenTypes::EMAIL, 'email.address_add', $email);
		}

		// Purge the user field data cache
		Cache::tags("field.user.{$user->id}")->flush();

		// Redirect back to the previous URL
		Session::flash('messages.success', $manager ? Lang::get('user.email_added') : Lang::get('user.email_verify'));

		return Redirect::to(URL::previous());
	}

	/**
	 * Displays the SSH key management screen for the user
	 *
	 * @access public
	 * @param  string  $hash
	 * @param  string  $action
	 * @param  int  $id
	 * @return View|Redirect
	 */
	public function getKeys($hash, $action = null, $id = 0)
	{
		// Fetch the user's profile
		$user = User::where('hash', $hash)->firstOrFail();
		$data = $this->getUserViewData($user);

		// Validate edit rights
		Access::restrict(ACLFlags::USER_EDIT, $user);

		// Perform the requested action
		switch ($action)
		{
			case 'remove':

				// Also check for user_id to avoid injection
				UserKey::where('id', $id)->where('user_id', $user->id)->delete();

				// Redirect back to previous URL
				Session::flash('messages.success', Lang::get('user.ssh_key_removed'));

				return Redirect::to(URL::previous());


			default:

				$data = array_merge($data, array(
					'keys'  => $user->keys,
					'modal' => 'user.keys',
				));

				return View::make('user/view', 'user.manage_ssh_keys', $data);
		}
	}

	/**
	 * Handles SSH key save functionality
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postKeys()
	{
		// Fetch the associated user
		$hash = Input::get('hash');
		$user = User::where('hash', $hash)->firstOrFail();

		// Validate edit rights
		Access::restrict(ACLFlags::USER_EDIT, $user);

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
			Session::flash('messages.error', Lang::get('user.invalid_key'));

			return Redirect::to(URL::previous())->withInput();
		}

		// Save the SSH key
		$userKey = new UserKey;
		$userKey->user_id = $user->id;
		$userKey->title = Input::get('title');
		$userKey->key = $key;
		$userKey->fingerprint = $fingerprint;
		$userKey->save();

		Session::flash('messages.success', Lang::get('user.ssh_key_added'));

		return Redirect::to(URL::previous());
	}

	/**
	 * Shows the security settings screen
	 *
	 * @access public
	 * @param  string  $hash
	 * @param  string  $action
	 * @return View
	 */
	public function getSecurity($hash, $action = null)
	{
		// Fetch the user's profile
		$user = User::where('hash', $hash)->firstOrFail();
		$data = $this->getUserViewData($user);

		// Validate edit rights
		Access::restrict(ACLFlags::USER_EDIT, $user);

		// Perform the requested action
		switch ($action)
		{
			case 'killall':

				// Fetch all sessions for user
				$sessions = UserSession::where('user_id', $user->id);

				// If user is viewing their own profile, don't kill their own session
				if ($user->id == Auth::id())
				{
					$sessions->where('id', '<>', Session::getId());
				}

				$sessions->delete();

				// Regerate the remember token for this user
				Auth::refreshRememberToken($user);

				// Redirect back to the previous URL
				Session::flash('messages.success', Lang::get('user.sessions_killed'));

				return Redirect::to(URL::previous());

			default:

				$data = array_merge($data, array(
					'sessions' => UserSession::where('user_id', $user->id)->get(),
					'modal'    => 'user.security',
				));

				return View::make('user/view', 'user.security_settings', $data);
		}
	}

	/**
	 * Handles security settings save functionality
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postSecurity()
	{
		$userId = Auth::id();

		// Fetch the associated user
		$hash = Input::get('hash');
		$user = User::where('hash', $hash)->firstOrFail();
		$manager = Access::check(ACLFlags::USER_MANAGE);
		$save = false;

		// Validate edit rights
		Access::restrict(ACLFlags::USER_EDIT, $user);

		// Define the validation rules
		$rules = array(
			'new_password'     => 'min:7',
			'confirm_password' => 'required_with:new_password|same:new_password',
		);

		// Validate the status field only if it was displayed
		if ($manager && $user->id != $userId)
		{
			$rules['status'] = 'required|exists:user_status,id';
		}

		// Create the validator instance
		$validator = Validator::make(Input::all(), $rules);

		// Run the validator
		if ($validator->fails())
		{
			Session::flash('messages.error', $validator->messages()->all('<p>:message</p>'));

			return Redirect::to(URL::previous())->withInput();
		}

		// Check the old password
		if ( ! $manager && ! Hash::check(Input::get('old_password'), $user->password))
		{
			Session::flash('messages.error', Lang::get('user.old_password_invalid'));

			return Redirect::to(URL::previous())->withInput();
		}

		// Finally, we change the password
		if (Input::has('new_password'))
		{
			$user->password = Hash::make(Input::get('new_password'));

			$save = true;
		}

		// Check if logged in user has manage rights
		// If so, update the account settings as well
		if ($manager && $user->id != $userId)
		{
			$user->status = Input::get('status');

			$save = true;
		}

		if ($save)
		{
			// Save the user data
			$user->save();

			// Purge the user field data cache
			Cache::tags("field.user.{$user->id}")->flush();

			// Show a success message
			Session::flash('messages.success', Lang::get('user.security_saved'));
		}
		else
		{
			// There's nothing to save
			Session::flash('messages.info', Lang::get('user.security_no_changes'));
		}

		// Redirect back to the previous URL
		return Redirect::to(URL::previous());
	}

	/**
	 * Fetches the permissions for the user
	 *
	 * @access public
	 * @param  string  $hash
	 * @return View
	 */
	public function getPermissions($hash)
	{
		// Fetch the user's profile
		$user = User::where('hash', $hash)->firstOrFail();
		$data = $this->getUserViewData($user);

		// Validate acl_manage rights
		Access::restrict(ACLFlags::ACL_MANAGE);

		// Build the ACL query
		$query = new stdClass;
		$query->entity = $user;

		// Query the ACL for user permissions
		$acl = Access::query(QueryMethods::BY_SUBJECT, $query);

		// Set display flags
		$show = new stdClass;
		$show->site = true;
		$show->subjects = false;
		$show->objects = true;
		$show->fields = true;

		// Merge the user data with view data
		$data = array_merge($data, array(
			'acl'    => $acl,
			'show'   => $show,
			'return' => url("user/view/{$user->hash}"),
			'fields' => Field::lists('name', 'id'),
			'flags'  => Lang::get('permissions'),
			'modal'  => 'acl.modal',
			'subject' => $user,
		));

		return View::make('user/view', 'user.user_permissions', $data);
	}

	/**
	 * Deletes a specific user
	 *
	 * @access public
	 * @param  string  $hash
	 * @return Redirect
	 */
	public function getDelete($hash)
	{
		// Get the user details
		$user = User::where('hash', $hash)->firstOrFail();

		// Validate user_manage rights
		Access::restrict(ACLFlags::USER_MANAGE);

		// Delete the user
		$user->delete();

		// Redirect back to the user list
		Session::flash('messages.success', Lang::get('user.user_deleted'));

		return Redirect::to('user/list');
	}

	/**
	 * Performs user search on a query via AJAX
	 *
	 * @access public
	 * @param  string  $format
	 * @return View
	 */
	public function getSearch($format)
	{
		if (Request::ajax())
		{
			$exclude = Input::has('exclude') ? explode(',', Input::get('exclude')) : array();

			// Respond in the requested format
			switch ($format)
			{
				case 'icons':

					// Return a maximum count of icon_length users
					$max = Config::get('view.icon_length') - count($exclude);

					if ($max > 0)
					{
						$users = User::search(Input::all())->take($max)->get();

						$data = array(
							'users'    => $users,
							'checkbox' => Input::get('checkbox'),
						);

						return View::make('common/icon', null, $data);
					}

				case 'list':

					// Return a maximum count of list_length users
					$max = Config::get('view.list_length') - count($exclude);

					if ($max > 0)
					{
						$users = User::search(Input::all())->take($max)->get();

						return View::make('common/list', null, array('items' => $users));
					}
			}
		}
		else
		{
			App::abort(HTTPStatus::NOTFOUND);
		}
	}

	/**
	 * Fetches the user list
	 *
	 * @access private
	 * @return array
	 */
	private function getUserListData()
	{
		// Fetch the user list
		$length = Config::get('view.icon_length');
		$users = User::with('emails')->orderBy('name')->paginate($length);

		return array('users' => $users);
	}

	/**
	 * Fetches the user's profile data
	 *
	 * @access private
	 * @param  User  $user
	 * @return array
	 */
	private function getUserViewData($user)
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

		// Get user permissions
		$editor = Access::check(ACLFlags::USER_EDIT, $user);

		// Build the user data
		return array(
			'user'        => $user,
			'emails'      => $emails,
			'fieldView'   => FormField::getView($user),
			'memberships' => $memberships,
			'editor'      => $editor,
		);
	}

}
