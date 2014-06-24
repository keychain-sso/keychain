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
use FieldCategory;
use FieldType;
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
	public static function get($user)
	{
		$userFields = UserField::where('user_id', $user->id)->with('field')->get();

		$fields = new stdClass;
		$fields->{FieldCategory::BASIC} = array();
		$fields->{FieldCategory::CONTACT} = array();
		$fields->{FieldCategory::OTHER} = array();

		// Compile user fields for display
		foreach ($userFields as $item)
		{
			if (Access::check('u_field_view', $user, $item->field->id))
			{
				$fields->{$item->field->category}[] = (object) array(
					'name'  => $item->field->name,
					'value' => nl2br($item->value),
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
	public static function build($user)
	{
		$userFields = UserField::where('user_id', $user->id)->with('field')->get();
		$fieldTypes = static::types();

		$fields = new stdClass;
		$fields->{FieldCategory::BASIC} = array();
		$fields->{FieldCategory::CONTACT} = array();
		$fields->{FieldCategory::OTHER} = array();

		// Compile user field controls
		foreach ($userFields as $item)
		{
			if (Access::check('u_field_view', $user, $item->field->id))
			{
				$data = array(
					'name'     => $item->field->name,
					'value'    => $item->value,
					'options'  => $item->field->options != null ? explode("\n", $item->field->options) : null,
					'disabled' => Access::check('u_field_edit', $user, $item->field->id) ? null : 'disabled',
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

}
