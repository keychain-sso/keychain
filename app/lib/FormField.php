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

use Access;
use App;
use Cache;
use Field;
use FieldCategories;
use FieldCategory;
use FieldParser;
use FieldType;
use FieldTypes;
use Flags;
use HTTPStatus;
use Lang;
use UserField;
use Utilities;
use Validator;

use stdClass;

/**
 * FormField class
 *
 * Provides functionalities around user fields
 *
 * @package     Keychain
 * @subpackage  Libraries
 */
class FormField {

	/**
	 * Fetch user's custom field data for viewing
	 *
	 * @static
	 * @access public
	 * @param  User  $user
	 * @return stdClass
	 */
	public static function getView($user)
	{
		return Cache::remember("user.field.data.{$user->id}", 43200, function() use ($user)
		{
			$userFields = UserField::where('user_id', $user->id)->get();
			$userFieldInfo = array();
			$fieldInfo = static::fieldInfo();

			$fields = new stdClass;
			$fields->{FieldCategories::BASIC} = array();
			$fields->{FieldCategories::CONTACT} = array();
			$fields->{FieldCategories::OTHER} = array();

			// Index user field by field_id
			foreach ($userFields as $userField)
			{
				$userFieldInfo[$userField->field_id] = $userField;
			}

			// Compile custom fields for display
			foreach ($fieldInfo as $field)
			{
				if (Access::check('u_field_view', $user, $field->id))
				{
					// Parse the field for display
					if (isset($userFieldInfo[$field->id]))
					{
						$parsed = static::parse('view', $field, $userFieldInfo[$field->id]->value);

						// Assign the field to its own bucket
						$fields->{$field->category}[$field->order] = (object) array(
							'name'  => $field->name,
							'value' => $parsed[FieldParser::VALUE],
						);
					}
				}
			}

			return $fields;
		});
	}

	/**
	 * Fetch user's custom field data for editing
	 *
	 * @static
	 * @access public
	 * @param  User  $user
	 * @return stdClass
	 */
	public static function getEdit($user)
	{
		$userFields = UserField::where('user_id', $user->id)->get();
		$userFieldInfo = array();

		$fieldTypes = static::fieldTypes();
		$fieldInfo = static::fieldInfo();

		$fields = new stdClass;
		$fields->{FieldCategories::BASIC} = array();
		$fields->{FieldCategories::CONTACT} = array();
		$fields->{FieldCategories::OTHER} = array();

		// Index user field by field_id
		foreach ($userFields as $userField)
		{
			$userFieldInfo[$userField->field_id] = $userField;
		}

		// Compile user field controls
		foreach ($fieldInfo as $field)
		{
			if (Access::check('u_field_edit', $user, $field->id))
			{
				// Parse the field for display
				$value = isset($userFieldInfo[$field->id]) ? $userFieldInfo[$field->id]->value : null;
				$parsed = static::parse('edit', $field, $value);

				$data = array(
					'name'         => $field->name,
					'machine_name' => "custom_{$field->machine_name}",
					'value'        => $parsed[FieldParser::VALUE],
					'options'      => $parsed[FieldParser::OPTIONS],
					'disabled'     => Access::check('u_field_edit', $user, $field->id) ? null : 'disabled',
				);

				$fields->{$field->category}[$field->order] = View::make("controls/{$fieldTypes[$field->type]}", $data)->render();
			}
		}

		return $fields;
	}

	/**
	 * Saves field data to the database
	 *
	 * @static
	 * @access public
	 * @param  User  $user
	 * @param  array  $data
	 * @return string|bool
	 */
	public static function save($user, $data)
	{
		// First, we validate if the current user has edit rights on this user
		if ( ! Access::check('u_profile_edit', $user))
		{
			App::abort(HTTPStatus::FORBIDDEN);
		}

		// Purge the profile data cache
		Cache::forget("user.field.data.{$user->id}");

		// Validate basic fields
		$validator = Validator::make($data, array(
			'first_name'    => 'required|alpha|max:80',
			'last_name'     => 'required|alpha|max:80',
			'gender'        => 'in:M,F,O',
			'date_of_birth' => 'required|date|before:'.date('Y-m-d', time()),
			'timezone'      => 'in:'.Utilities::timezones(true),
			'title'         => 'max:80',
		));

		// If validation fails, return the first failed message
		if ($validator->fails())
		{
			return $validator->messages()->all('<p>:message</p>');
		}

		// Update basic field data
		$user->first_name    = $data['first_name'];
		$user->last_name     = $data['last_name'];
		$user->gender        = $data['gender'];
		$user->date_of_birth = $data['date_of_birth'];
		$user->timezone      = $data['timezone'];
		$user->title         = $data['title'];
		$user->save();

		// Get all available custom fields
		$fieldInfo = static::fieldInfo();

		foreach ($fieldInfo as $field)
		{
			$value = isset($data['custom_'.$field->machine_name]) ? $data['custom_'.$field->machine_name] : '';

			// Validate if user can edit this field
			if ( ! Access::check('u_field_edit', $user, $field->id))
			{
				App::abort(HTTPStatus::FORBIDDEN);
			}

			// Set the initial rule
			$rules = $field->required ? 'required|' : '';

			// Build validator rules / format the value based on the field type
			switch ($field->type)
			{
				case FieldTypes::RADIO:
				case FieldTypes::DROPDOWN:

					$rules .= 'in:'.str_replace("\n", ',', $field->options);
					break;

				case FieldTypes::DATEPICKER:

					$rules .= 'date';
					break;

				case FieldTypes::SSHKEY:

					// TODO: SSH key validation logic here
					break;

			}

			// Validate field data
			$validator = Validator::make(array(
				$field->machine_name => $value,
			), array(
				$field->machine_name => trim($rules, '|'),
			));

			// If validation fails, return the failed message
			if ($validator->fails())
			{
				return $validator->messages()->all('<p>:message</p>');
			}

			// Update the custom field data
			$userField = UserField::where('user_id', $user->id)->where('field_id', $field->id)->first();

			// Field data doesn't already exist - so we insert it
			if ($userField == null)
			{
				$userField = new UserField;
			}

			// Save the custom field info
			$userField->user_id  = $user->id;
			$userField->field_id = $field->id;
			$userField->value    = $value;
			$userField->save();

		}

		// All OK!
		return true;
	}

	/**
	 * Returns list of available custom fields indexed by machine names
	 *
	 * @static
	 * @access public
	 * @return array
	 */
	public static function fieldInfo()
	{
		return Cache::rememberForever('user.field.info', function()
		{
			$info = array();

			foreach (Field::all() as $field)
			{
				$info[$field->machine_name] = $field;
			}

			return $info;
		});
	}

	/**
	 * Returns a list of field types indexed by type IDs
	 *
	 * @static
	 * @access public
	 * @return array
	 */
	public static function fieldTypes()
	{
		return Cache::rememberForever('user.field.types', function()
		{
			$types = array();

			foreach (FieldType::all() as $field)
			{
				$types[$field->id] = strtolower($field->name);
			}

			return $types;
		});
	}

	/**
	 * Parses a field for display based on its type
	 *
	 * @static
	 * @access private
	 * @param  string  $action
	 * @param  Field  $field
	 * @param  string  $value
	 * @return array
	 */
	private static function parse($action, $field, $value)
	{
		if ($action == 'view')
		{
			switch ($field->type)
			{
				// Format date for display
				case FieldTypes::DATEPICKER:

					if ( ! empty($value))
					{
						$value = date('Y-m-d', strtotime($value));
					}

					break;

				// Generate a SSH key fingerprint
				case FieldTypes::SSHKEY:

					if ( ! empty($value))
					{
						$content = explode(' ', $value, 3);
						$value = join(':', str_split(md5(base64_decode($content[1])), 2));
					}

					break;

				// Build the checkbox value
				case FieldTypes::CHECKBOX:

					$flag = $value == Flags::YES ? Lang::get('global.yes') : Lang::get('global.no');
					$value = "{$field->options}: {$flag}";
					break;

				// For everything else, convert newlines to HTML breaks
				default:

					$value = nl2br($value);
					break;
			}
		}
		else if ($action == 'edit')
		{
			switch ($field->type)
			{
				// Format date for display
				case FieldTypes::DATEPICKER:

					if ( ! empty($value))
					{
						$value = date('Y-m-d', strtotime($value));
					}

					break;

				// Format radio/dropdown options
				case FieldTypes::RADIO:
				case FieldTypes::DROPDOWN:

					if ($field->options != null)
					{
						$field->options = explode("\n", $field->options);
						$field->options = Utilities::arrayToSelect($field->options);
					}
					else
					{
						$field->options = null;
					}

					break;
			}
		}

		return array($field->options, $value);
	}

}
