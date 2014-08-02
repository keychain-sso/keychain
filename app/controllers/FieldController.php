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
 * FieldController class
 *
 * Handles profile field related operations
 *
 * @package     Keychain
 * @subpackage  Controllers
 */
class FieldController extends BaseController {

	/**
	 * Validates user permissions
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		Access::restrict(ACLFlags::FIELD_MANAGE);
	}

	/**
	 * Displays the field management screen
	 *
	 * @access public
	 * @return View
	 */
	public function getIndex()
	{
		return View::make('field/manage', 'global.manage_fields', $this->getFieldData());
	}

	/**
	 * Shows the create field modal
	 *
	 * @access public
	 * @return View
	 */
	public function getCreate()
	{
		// Get the field management data
		$data = $this->getFieldData();

		// Merge the view data with the field data
		$data = array_merge($data, array(
			'field'  => new Field,
			'modal'  => 'field.editor',
		));

		// Show the field editor view
		return View::make('field/manage', 'field.create_field', $data);
	}

	/**
	 * Handles POST events for the create field screen
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postCreate()
	{
		// Create the validation rules
		$rules = array(
			'name'     => 'required|alpha_space|max:80|unique:fields,name',
			'type'     => 'required|exists:field_types,id',
			'category' => 'required|exists:field_categories,id',
		);

		// Based on the field type, determine if we need options
		if (Input::has('type'))
		{
			$id = Input::get('type');
			$type = FieldType::find($id);

			if ( ! is_null($type) && $type->option)
			{
				$rules['options'] = 'required|alpha_newline';
			}
		}

		// Build the validator
		$validator = Validator::make(Input::all(), $rules);

		// Run the validator
		if ($validator->fails())
		{
			Session::flash('messages.error', $validator->messages()->all('<p>:message</p>'));

			return Redirect::to(URL::previous())->withInput();
		}

		// Calculate field properties
		$name = Input::get('name');
		$category = Input::get('category');

		$machineName = str_replace(' ', '', snake_case($name));
		$order = Field::where('category', $category)->max('order') + 1;

		// Create the new field
		$field = new Field;
		$field->name = $name;
		$field->machine_name = $machineName;
		$field->type = Input::get('type');
		$field->category = $category;
		$field->options = Input::get('options');
		$field->required = Input::has('required');
		$field->order = $order;
		$field->save();

		// Clear the field cache
		Cache::tags('field')->flush();

		// Build the 'field created' message
		$link = link_to("field/permission/{$field->id}", Lang::get('field.manage_permissions'));

		Session::flash('messages.success', Lang::get('field.field_created', array('link' => $link)));

		// Redirect to the field management page
		return Redirect::to('field');
	}

	/**
	 * Shows the field editor for a specific field
	 *
	 * @access public
	 * @param  int  $id
	 * @return View
	 */
	public function getEdit($id)
	{
		// Get the field management data
		$data = $this->getFieldData();

		// Merge the view data with the field data
		$data = array_merge($data, array(
			'field'  => Field::findOrFail($id),
			'modal'  => 'field.editor',
		));

		// Show the field editor view
		return View::make('field/manage', 'field.edit_field', $data);
	}

	/**
	 * Handles POST events for the update field screen
	 *
	 * @access public
	 * @return Redirect
	 */
	public function postEdit()
	{
		// Fetch the associated field
		$id = Input::get('id');
		$field = Field::findOrFail($id);

		// Create the validation rules
		$rules = array(
			'name'     => "required|alpha_space|max:80|unique:fields,name,{$field->id}",
			'category' => 'required|exists:field_categories,id',
		);

		// Based on the field type, determine if we need options
		$type = FieldType::find($field->type);

		if ($type->option)
		{
			$rules['options'] = 'required|alpha_newline';
		}

		// Build the validator
		$validator = Validator::make(Input::all(), $rules);

		// Run the validator
		if ($validator->fails())
		{
			Session::flash('messages.error', $validator->messages()->all('<p>:message</p>'));

			return Redirect::to(URL::previous())->withInput();
		}

		// Calculate field properties
		$category = Input::get('category');
		$order = Field::where('category', $category)->max('order') + 1;

		// Create the new field
		$field->name = Input::get('name');
		$field->category = $category;
		$field->options = Input::get('options');
		$field->required = Input::has('required');
		$field->order = $order;
		$field->save();

		// Clear the field cache
		Cache::tags('field')->flush();

		// Redirect to the field management page
		Session::flash('messages.success', Lang::get('field.field_updated'));

		return Redirect::to('field');
	}

	/**
	 * Deletes a specific field
	 *
	 * @access public
	 * @param  int  $id
	 * @return Redirect
	 */
	public function getDelete($id)
	{
		// Delete the field
		Field::findOrFail($id)->delete();

		// Delete all field permissions
		ACL::where('field_id', $id)->delete();

		// Clear the field cache
		Cache::tags('field')->flush();

		// Redirect to the previous page
		Session::flash('messages.success', Lang::get('field.field_deleted'));

		return Redirect::to(URL::previous());
	}

	/**
	 * Moves a field up or down in its category
	 *
	 * @access public
	 * @param  string  $direction
	 * @param  int  $id
	 * @return Redirect
	 */
	public function getMove($direction, $id)
	{
		// Get the field being moved
		$field = Field::findOrFail($id);
		$other = null;

		// Get the other field with which we will swap the order
		// This other field is usually the closest in proximity in the
		// field's category
		switch ($direction)
		{
			case 'up':

				$other = Field::where('category', $field->category)->where('order', '<', $field->order)->orderBy('order', 'desc')->first();

				break;

			case 'down':

				$other = Field::where('category', $field->category)->where('order', '>', $field->order)->orderBy('order')->first();

				break;
		}

		if ( ! is_null($other))
		{
			// Swap the field orders
			list($field->order, $other->order) = array($other->order, $field->order);

			// Save both the fields
			$field->save();
			$other->save();

			// Clear the field cache
			Cache::tags('field')->flush();
		}

		// Redirect to the previous page
		return Redirect::to(URL::previous());
	}

	/**
	 * Displays the field permissions page
	 *
	 * @access public
	 * @param  int  $id
	 * @return View
	 */
	public function getPermission($id)
	{
		$field = Field::findOrFail($id);

		// Build the ACL query
		$query = new stdClass;
		$query->field = $field;

		// Query the ACL for user permissions
		$acl = Access::query(QueryMethods::BY_OBJECT, $query);

		// Set display flags
		$show = new stdClass;
		$show->site = false;
		$show->subjects = true;
		$show->objects = true;
		$show->fields = false;

		// Get the flags to display
		$filter = array(ACLFlags::FIELD_EDIT => true, ACLFlags::FIELD_VIEW => true);
		$flags = array_intersect_key(Lang::get('flag'), $filter);

		// Build the view data
		$data = array(
			'acl'    => $acl,
			'show'   => $show,
			'field'  => $field,
			'flags'  => $flags,
			'fields' => Field::lists('name', 'id'),
			'return' => url('field'),
		);

		return View::make('permission/full', 'field.manage_permissions', $data);
	}

	/**
	 * Returns field management data
	 *
	 * @access private
	 * @return array
	 */
	private function getFieldData()
	{
		$fields = Field::orderBy('order')->get();
		$types = FieldType::lists('name', 'id');
		$categories = FieldCategory::all();

		// Set default values of min and max
		foreach ($categories as $category)
		{
			$min[$category->id] = PHP_INT_MAX;
			$max[$category->id] = 0;
		}

		// Determine the min and max for each category
		foreach ($fields as $field)
		{
			if ($field->order < $min[$field->category])
			{
				$min[$field->category] = $field->order;
			}

			if ($field->order > $max[$field->category])
			{
				$max[$field->category] = $field->order;
			}
		}

		// Return the field data
		return array(
			'fields' => $fields,
			'types'  => $types,
			'min'    => $min,
			'max'    => $max,
		);
	}

}
