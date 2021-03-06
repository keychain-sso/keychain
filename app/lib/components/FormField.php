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

use ACLFlags;
use App;
use Auth;
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
use stdClass;
use Session;
use UserField;
use Validator;
use View;

/**
 * FormField class
 *
 * Provides functionalities around user fields
 *
 * @package     Keychain
 * @subpackage  Components
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
		return Cache::tags('field')->remember(Auth::user()->id, 60, function() use ($user)
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
				if (Access::check(ACLFlags::FIELD_VIEW, $user, $field))
				{
					// Parse the field for display
					if (isset($userFieldInfo[$field->id]))
					{
						$parsed = static::parse('view', $field, $userFieldInfo[$field->id]->value);

						// Assign the field to its own bucket
						if ( ! empty($parsed[FieldParser::VALUE]))
						{
							$fields->{$field->category}[$field->order] = (object) array(
								'name'  => $field->name,
								'value' => $parsed[FieldParser::VALUE],
							);
						}
					}
				}
			}

			// Sort the fields by their order
			ksort($fields->{FieldCategories::BASIC});
			ksort($fields->{FieldCategories::CONTACT});
			ksort($fields->{FieldCategories::OTHER});

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
			if (Access::check(ACLFlags::FIELD_EDIT, $user, $field))
			{
				// Parse the field for display
				$value = isset($userFieldInfo[$field->id]) ? $userFieldInfo[$field->id]->value : null;
				$parsed = static::parse('edit', $field, $value);

				$data = array(
					'name'         => $field->name,
					'machine_name' => "custom_{$field->machine_name}",
					'value'        => $parsed[FieldParser::VALUE],
					'options'      => $parsed[FieldParser::OPTIONS],
					'disabled'     => Access::check(ACLFlags::FIELD_EDIT, $user, $field) ? null : 'disabled',
				);

				$fields->{$field->category}[$field->order] = View::make("control/{$fieldTypes[$field->type]}", null, $data)->render();
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
		// Purge the user profile data session cache
		Cache::tags('field')->forget($user->id);

		// Validate basic fields
		$validator = Validator::make($data, array(
			'name'          => 'required|max:80',
			'gender'        => 'in:M,F,N',
			'date_of_birth' => 'date|before:'.date('Y-m-d', time()),
			'timezone'      => 'in:'.Utilities::timezones(true),
			'title'         => 'max:50',
		));

		// If validation fails, return the first failed message
		if ($validator->fails())
		{
			return $validator->messages()->all('<p>:message</p>');
		}

		// Convert the data to an object
		$data = (object) $data;

		// Update basic field data
		$user->name = $data->name;
		$user->gender = $data->gender;
		$user->date_of_birth = $data->date_of_birth;
		$user->timezone = $data->timezone;
		$user->title = $data->title;
		$user->save();

		// Get all available custom fields
		$fieldInfo = static::fieldInfo();

		foreach ($fieldInfo as $field)
		{
			$key = "custom_{$field->machine_name}";
			$value = isset($data->$key) ? $data->$key : '';

			// Validate if user can edit this field
			Access::restrict(ACLFlags::FIELD_EDIT, $user, $field);

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
			if (is_null($userField))
			{
				$userField = new UserField;
			}

			// Save the custom field info
			$userField->user_id = $user->id;
			$userField->field_id = $field->id;
			$userField->value = $value;
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
		return Cache::tags('field')->rememberForever('info', function()
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
		return Cache::tags('field')->rememberForever('types', function()
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

				// Build the checkbox value
				case FieldTypes::CHECKBOX:

					$flag = $value == Flags::YES ? Lang::get('global.yes') : Lang::get('global.no');
					$value = "{$field->options}: {$flag}";
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

					if ( ! empty($field->options))
					{
						$field->options = explode("\n", $field->options);
						$field->options = Utilities::arrayToSelect($field->options);
					}
					else
					{
						$field->options = array();
					}

					break;
			}
		}

		return array($field->options, $value);
	}

}
