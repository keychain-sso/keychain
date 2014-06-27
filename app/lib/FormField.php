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
use Flags;
use Lang;
use UserField;
use Utilities;
use Session;

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
		if ( ! Session::has('user.field.data'))
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
					$item = static::parse(FieldParserActions::SHOW, $item);

					$fields->{$item->field->category}[$item->field->order] = (object) array(
						'name'  => $item->field->name,
						'value' => $item->value,
					);
				}
			}

			Session::put('user.fields.view', $fields);
		}
		else
		{
			$fields = Session::get('user.field.data');
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
				$item = static::parse(FieldParserActions::EDIT, $item);

				$data = array(
					'name'         => $item->field->name,
					'machine_name' => $item->field->machine_name,
					'value'        => $item->value,
					'options'      => $item->field->options,
					'disabled'     => Access::check('u_field_edit', $user, $item->field->id) ? null : 'disabled',
				);

				$fields->{$item->field->category}[$item->field->order] = View::make("controls/{$fieldTypes[$item->field->type]}", $data)->render();
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
	 * @param  string  $item
	 * @return string
	 */
	private static function parse($action, $item)
	{
		switch ($item->field->type)
		{
			// Format date for display
			case FieldTypes::DATEPICKER:

				$item->value = date('Y-m-d', strtotime($item->value));
				break;

			// Generate a SSH key fingerprint
			case FieldTypes::SSHKEY:

				if ($action == FieldParserActions::SHOW)
				{
					$content = explode(' ', $item->value, 3);
					$item->value = join(':', str_split(md5(base64_decode($content[1])), 2));
				}

				break;

			// Build the checkbox value
			case FieldTypes::CHECKBOX:

				if ($action == FieldParserActions::SHOW)
				{
					$flag = $item->value == Flags::YES ? Lang::get('global.yes') : Lang::get('global.no');
					$item->value = "{$item->field->options}: {$flag}";
				}

				break;

			// Format radio/dropdown options
			case FieldTypes::RADIO:
			case FieldTypes::DROPDOWN:

				if ($action == FieldParserActions::EDIT)
				{
					if ($item->field->options != null)
					{
						$item->field->options = explode("\n", $item->field->options);
						$item->field->options = Utilities::arrayToSelect($item->field->options);
					}
					else
					{
						$item->field->options = null;
					}
				}

				break;

			// For everything else, convert newlines to HTML breaks
			default:

				if ($action == FieldParserActions::SHOW)
				{
					$item->value = nl2br($item->value);
				}

				break;
		}

		return $item;
	}

}
