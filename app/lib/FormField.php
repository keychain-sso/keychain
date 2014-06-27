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
use Cache;
use FieldCategories;
use FieldCategory;
use FieldParserActions;
use FieldType;
use FieldTypes;
use UserField;

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
	 * Fetch field data for a specific user
	 *
	 * @static
	 * @access public
	 * @param  User  $user
	 * @return stdClass
	 */
	public static function show($user)
	{
		$userFields = UserField::where('user_id', $user->id)->with('field')->get();

		$fields = new stdClass;
		$fields->{FieldCategories::BASIC} = array();
		$fields->{FieldCategories::CONTACT} = array();
		$fields->{FieldCategories::OTHER} = array();

		// Compile user fields for display
		foreach ($userFields as $item)
		{
			if (Access::check('u_field_view', $user, $item->field->id))
			{
				$value = static::parse(FieldParserActions::SHOW, $item->field->type, $item->value);

				$fields->{$item->field->category}[] = (object) array(
					'name'  => $item->field->name,
					'value' => $value,
				);
			}
		}

		return $fields;
	}

	/**
	 * Builds the HTML markup for form fields
	 *
	 * @static
	 * @access public
	 * @param  User  $user
	 * @return stdClass
	 */
	public static function edit($user)
	{
		$userFields = UserField::where('user_id', $user->id)->with('field')->get();
		$fieldTypes = static::types();

		$fields = new stdClass;
		$fields->{FieldCategories::BASIC} = array();
		$fields->{FieldCategories::CONTACT} = array();
		$fields->{FieldCategories::OTHER} = array();

		// Compile user field controls
		foreach ($userFields as $item)
		{
			if (Access::check('u_field_view', $user, $item->field->id))
			{
				$value = static::parse(FieldParserActions::EDIT, $item->field->type, $item->value);

				$data = array(
					'name'         => $item->field->name,
					'machine_name' => $item->field->machine_name,
					'options'      => $item->field->options != null ? explode("\n", $item->field->options) : null,
					'value'        => $value,
					'disabled'     => Access::check('u_field_edit', $user, $item->field->id) ? null : 'disabled',
				);

				$fields->{$item->field->category}[] = View::make("controls/{$fieldTypes[$item->field->type]}", $data)->render();
			}
		}

		return $fields;
	}

	/**
	 * Returns a list of field types
	 *
	 * @static
	 * @access public
	 * @return array
	 */
	public static function types()
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
	 * @param  int  $action
	 * @param  string  $type
	 * @param  string  $value
	 * @return string
	 */
	private static function parse($action, $type, $value)
	{
		switch ($type)
		{
			// Format date for display
			case FieldTypes::DATEPICKER:

				$value = date('Y-m-d', strtotime($value));
				break;

			// Generate a SSH key fingerprint
			case FieldTypes::SSHKEY:

				if ($action == FieldParserActions::SHOW)
				{
					$content = explode(' ', $value, 3);
					$value = join(':', str_split(md5(base64_decode($content[1])), 2));
				}

				break;

			// For everything else, convert newlines to HTML breaks
			default:

				if ($action == FieldParserActions::SHOW)
				{
					$value = nl2br($value);
				}

				break;
		}

		return $value;
	}

}
