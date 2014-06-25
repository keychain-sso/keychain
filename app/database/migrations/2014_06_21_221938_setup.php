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

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Setup class
 *
 * Defines the raw database schema
 *
 * @package     Keychain
 * @subpackage  Migrations
 */
class Setup extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Create the user status table
		Schema::create('user_status', function($table)
		{
			$table->increments('id');
			$table->string('name', 80);
		});

		// Create the main user table
		Schema::create('users', function($table)
		{
			$table->increments('id');
			$table->string('first_name', 80)->index();
			$table->string('last_name', 80)->index();
			$table->string('password', 80);
			$table->string('remember_token', 60)->nullable()->index();
			$table->enum('gender', array('M', 'F', 'O'))->nullable();
			$table->timestamp('date_of_birth');
			$table->string('timezone', 80);
			$table->string('avatar', 15)->nullable();
			$table->string('title', 80)->nullable();
			$table->integer('status')->unsigned()->index();
			$table->string('hash', 8)->index();
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at');

			$table->foreign('status')->references('id')->on('user_status')->onDelete('cascade');
		});

		// Create the emails table
		Schema::create('user_emails', function( $table )
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->string('email', 80)->index();
			$table->boolean('primary');
			$table->boolean('verified')->index();
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at');

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		});

		// Create the field types table
		Schema::create('field_types', function($table)
		{
			$table->increments('id');
			$table->string('name', 80);
		});

		// Create the field categories table
		Schema::create('field_categories', function($table)
		{
			$table->increments('id');
			$table->string('name', 80);
		});

		// Create the field definitions table
		Schema::create('fields', function($table)
		{
			$table->increments('id');
			$table->string('name', 80);
			$table->string('machine_name', 80);
			$table->integer('type')->unsigned();
			$table->integer('category')->unsigned();
			$table->mediumText('options')->nullable();
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at');

			$table->foreign('type')->references('id')->on('field_types')->onDelete('cascade');
			$table->foreign('category')->references('id')->on('field_categories')->onDelete('cascade');
		});

		// Create the groups table
		Schema::create('groups', function( $table )
		{
			$table->increments('id');
			$table->string('name', 80)->index();
			$table->mediumText('description');
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at');
		});

		// Create the mapping between users and fields
		Schema::create('user_fields', function($table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index();
			$table->integer('field_id')->unsigned()->index();
			$table->mediumText('value');
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at');

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('field_id')->references('id')->on('fields')->onDelete('cascade');
		});

		// Create the mapping between users and groups
		Schema::create('user_groups', function($table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index();
			$table->integer('group_id')->unsigned()->index();
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at');

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
		});

		// Create the ACL subject types table
		Schema::create('acl_types', function($table)
		{
			$table->increments('id');
			$table->string('name', 80);
		});

		// Create the ACL base table
		Schema::create('acl', function($table)
		{
			$table->increments('id');
			$table->integer('object_id')->unsigned()->default(0);
			$table->integer('object_type')->unsigned()->default(1);
			$table->integer('subject_id')->unsigned();
			$table->integer('subject_type')->unsigned();
			$table->integer('field_id')->unsigned()->default(0);
			$table->string('access', 20);
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at');

			$table->index(array('subject_id', 'subject_type'));
			$table->foreign('object_type')->references('id')->on('acl_types')->onDelete('cascade');
			$table->foreign('subject_type')->references('id')->on('acl_types')->onDelete('cascade');
			$table->foreign('field_id')->references('id')->on('fields')->onDelete('cascade');
		});

		// Create the sessions table
		Schema::create('sessions', function($table)
		{
			$table->string('id')->unique()->index();
			$table->text('payload');
			$table->integer('last_activity');
		});

		// Create the token holder
		Schema::create('tokens', function( $table )
		{
			$table->increments('id');
			$table->string('token', 20)->index();
			$table->integer('permits_id');
			$table->string('permits_type', 20);
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at');
		});

		// Insert ACL types
		DB::table('acl_types')->insert(array(
			array('name' => 'Self'),
			array('name' => 'All'),
			array('name' => 'User'),
			array('name' => 'Group'),
		));

		// Insert field categories
		DB::table('field_categories')->insert(array(
			array('name' => 'Basic'),
			array('name' => 'Contact'),
			array('name' => 'Other'),
		));

		// Insert field types
		DB::table('field_types')->insert(array(
			array('name' => 'TextBox'),
			array('name' => 'TextArea'),
			array('name' => 'Radio'),
			array('name' => 'CheckBox'),
			array('name' => 'Dropdown'),
			array('name' => 'DatePicker'),
		));

		// Insert the user status values
		DB::table('user_status')->insert(array(
			array('name' => 'Inactive'),
			array('name' => 'Active'),
			array('name' => 'Blocked'),
		));

		// Insert admin user account
		DB::table('users')->insert(array(
			'first_name'    => 'John',
			'last_name'     => 'Doe',
			'gender'        => 'M',
			'date_of_birth' => '1980-07-01',
			'timezone'      => 'America/Chicago',
			'password'      => Hash::make('password'),
			'title'         => 'Site administrator',
			'hash'          => str_random(8),
			'status'        => UserStatus::ACTIVE,
		));

		// Insert admin user emails
		DB::table('user_emails')->insert(array(
			'user_id'  => 1,
			'email'    => 'admin@keychain.sso',
			'primary'  => Flags::YES,
			'verified' => Flags::YES,
		));

		DB::table('user_emails')->insert(array(
			'user_id'  => 1,
			'email'    => 'alternate@keychain.sso',
			'primary'  => Flags::NO,
			'verified' => Flags::YES,
		));

		// Add an address field
		DB::table('fields')->insert(array(
			'name'         => 'Address',
			'machine_name' => 'address',
			'type'         => FieldTypes::TEXTAREA,
			'category'     => FieldCategories::CONTACT,
		));

		// Add a SSH ket size field
		DB::table('fields')->insert(array(
			'name'         => 'SSH key',
			'machine_name' => 'ssh_key',
			'type'         => FieldTypes::TEXTAREA,
			'category'     => FieldCategories::OTHER,
		));

		// Add a SSH ket size field
		DB::table('fields')->insert(array(
			'name'         => 'Developer username',
			'machine_name' => 'dev_username',
			'type'         => FieldTypes::TEXTBOX,
			'category'     => FieldCategories::BASIC,
		));

		// Add the admin's address
		DB::table('user_fields')->insert(array(
			'user_id'  => 1,
			'field_id' => 1,
			'value'    => "2400 Hudson Dr. #200\nAustin TX 75234\nUnited States",
		));

		// Add the admin's T-Shirt size!
		DB::table('user_fields')->insert(array(
			'user_id'  => 1,
			'field_id' => 2,
			'value'    => str_random(128).'==',
		));

		// Add the admin's dev username
		DB::table('user_fields')->insert(array(
			'user_id'  => 1,
			'field_id' => 3,
			'value'    => 'johndoe',
		));

		// Insert the group entries
		DB::table('groups')->insert(array(
			'name'        => 'Registered users',
			'description' => 'All registered users on the website.',
		));

		DB::table('groups')->insert(array(
			'name'        => 'Sysadmins',
			'description' => 'System administrators with full control over the website.',
		));

		// Link the admin user to the sysadmin group
		DB::table('user_groups')->insert(array(
			'user_id'  => 1,
			'group_id' => 1,
		));

		DB::table('user_groups')->insert(array(
			'user_id'  => 1,
			'group_id' => 2,
		));

		// Give sysadmins access to everything
		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'field_id'     => 1,
			'access'       => 'u_field_view',
		));

		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'field_id'     => 1,
			'access'       => 'u_field_edit',
		));

		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'field_id'     => 2,
			'access'       => 'u_field_view',
		));

		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'field_id'     => 2,
			'access'       => 'u_field_edit',
		));

		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'field_id'     => 3,
			'access'       => 'u_field_view',
		));

		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'field_id'     => 3,
			'access'       => 'u_field_edit',
		));
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tokens');
		Schema::drop('sessions');
		Schema::drop('acl');
		Schema::drop('acl_types');
		Schema::drop('user_groups');
		Schema::drop('user_fields');
		Schema::drop('groups');
		Schema::drop('fields');
		Schema::drop('field_categories');
		Schema::drop('field_types');
		Schema::drop('user_emails');
		Schema::drop('users');
		Schema::drop('user_status');
	}

}
