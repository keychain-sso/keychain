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
		// Clear the cache
		Cache::flush();

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
		Schema::create('user_emails', function($table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index();
			$table->string('address', 80)->index();
			$table->boolean('primary');
			$table->boolean('verified')->index();
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at');

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		});

		// Create the keys table
		Schema::create('user_keys', function($table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index();
			$table->string('title', 30);
			$table->mediumText('key');
			$table->string('fingerprint', 48);
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at');

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		});

		// Create the device types table
		Schema::create('device_types', function($table)
		{
			$table->increments('id');
			$table->string('name', 80)->unique();
		});

		// Create the sessions table
		Schema::create('user_sessions', function($table)
		{
			$table->string('id')->unique()->index();
			$table->integer('user_id')->unsigned()->index();
			$table->text('payload');
			$table->string('ip_address', 45);
			$table->integer('device_type')->unsigned();
			$table->boolean('killed')->default(0);
			$table->timestamp('updated_at');

			$table->foreign('device_type')->references('id')->on('device_types')->onDelete('cascade');
		});

		// Create the field types table
		Schema::create('field_types', function($table)
		{
			$table->increments('id');
			$table->string('name', 80)->unique();
		});

		// Create the field categories table
		Schema::create('field_categories', function($table)
		{
			$table->increments('id');
			$table->string('name', 80)->unique();
		});

		// Create the field definitions table
		Schema::create('fields', function($table)
		{
			$table->increments('id');
			$table->string('name', 80);
			$table->string('machine_name', 80)->unique();
			$table->integer('type')->unsigned();
			$table->integer('category')->unsigned();
			$table->mediumText('options')->nullable();
			$table->boolean('required')->default(0);
			$table->integer('order');
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at');

			$table->foreign('type')->references('id')->on('field_types')->onDelete('cascade');
			$table->foreign('category')->references('id')->on('field_categories')->onDelete('cascade');
		});

		// Create the groups table
		Schema::create('groups', function($table)
		{
			$table->increments('id');
			$table->string('name', 80)->unique()->index();
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
			$table->string('name', 80)->unique();
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
		});

		// Create the token types table
		Schema::create('token_types', function($table)
		{
			$table->increments('id');
			$table->string('name', 80)->unique();
		});

		// Create the token holder
		Schema::create('tokens', function($table)
		{
			$table->increments('id');
			$table->string('token', 32)->unique()->index();
			$table->integer('permits_id')->unsigned();
			$table->integer('permits_type')->unsigned();
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at');

			$table->index(array('permits_id', 'permits_type'));
			$table->foreign('permits_type')->references('id')->on('token_types')->onDelete('cascade');
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

		// Insert token types
		DB::table('token_types')->insert(array(
			array('name' => 'Email'),
			array('name' => 'Password'),
		));

		// Insert device types
		DB::table('device_types')->insert(array(
			array('name' => 'Computer'),
			array('name' => 'Mobile'),
			array('name' => 'Tablet'),
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

		// Insert admin email addresses
		DB::table('user_emails')->insert(array(
			'user_id'  => 1,
			'address'  => 'admin@keychain.sso',
			'primary'  => Flags::YES,
			'verified' => Flags::YES,
		));

		DB::table('user_emails')->insert(array(
			'user_id'  => 1,
			'address'  => 'alternate@keychain.sso',
			'primary'  => Flags::NO,
			'verified' => Flags::YES,
		));

		// Insert a DSA key
		DB::table('user_keys')->insert(array(
			'user_id'     => 1,
			'title'       => 'DSA key',
			'fingerprint' => '54:c6:38:32:20:3d:9d:a1:b7:33:51:dc:aa:67:06:76',
			'key'         => 'ssh-dss AAAAB3NzaC1kc3MAAACBAMVDh+OGdTuMLDj5fiQ6P6XX'.
			                 'c6vRTU8IPWW28wGyUdsHtkAxWmlAoa+86P5NnU1zRH6Wp4GYISu/'.
			                 'NBK9AAYVdxqVeUau3+yZhTOMgKLSXTJ4dRjZgwBU1nqNnNuZs+Xa'.
			                 'URWBBdIPdRWah9LexvVZ6f4ke5t7d8A+ii0WsumZVOiRAAAAFQDK'.
			                 '2TMi352pA4JMNVJGVZIi4uT+9wAAAIANTti7ZvazqAvauY08112G'.
			                 'FX8ukmo6FXaYUt3sbwVGb/x8QkEu2uVSB+o7loDVQNB0+ONbSxQa'.
			                 'xD7Uo4IXn/tf4fXZ24HMWl4h+rTR8Z90bZeNZqOglO0JehKOYRVK'.
			                 'WeR/x5+OD9+bq5tLgmTGN6LonZoqNOuJyrp9FNJZhHwVAgAAAIEA'.
			                 'pA7OeurzH2vGiS2oCJjqB1WlWZ9jlHXNlt9bVrZe3WW8BwRu3Nn+'.
			                 'yAtmOnqIxAVi02oXXfM6X10uTb4ZmCJhuaoM4Bo1yEToVPzea1Xp'.
			                 'cnvXCMsa0hHmNBzy1pYtag3wVMMflj0KrwO1FuG78fuViiIC5GHW'.
			                 'SnTK4Nnti3cWYvI= admin@keychain',
		));

		// Insert a RSA key
		DB::table('user_keys')->insert(array(
			'user_id'     => 1,
			'title'       => 'RSA key',
			'fingerprint' => '65:ca:1b:88:12:e2:35:69:5c:2a:63:48:65:db:75:0e',
			'key'         => 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDGSNchtn5ME3xZ'.
			                 'af+KsxGtH3cgbe5Av9Nz+RogDIkA764IB789+QNJOT5uQMN3nJnG'.
			                 '25/o6YZ9UQqsQhIMPYC5bTv1ZsUc/WrgJRabOI8hU3JML9C9SJue'.
			                 'ZIQ1jUF4O7u63eVoTCl8YiITXzmf+Lo1VbqOuEe8k9lpL+E2xzKm'.
			                 'fs92Lk0XcnXbu741XkYEBHAGl86Das/ZbGzY6Gj5yUv5Eap7vBQH'.
			                 'ffAx2/cQR1Q4IYQuABGU+xg2T/FLRTl3lL1OIhwDAoL+BdIsInXL'.
			                 '17Kxz5nokUbNrh/EfFSMnahjLQtz1GOIs/h4GbsIKsBTezs4me6v'.
			                 'gEmfT2dskW9y0lAX admin@keychain',
		));

		// Insert an ECDSA key
		DB::table('user_keys')->insert(array(
			'user_id'     => 1,
			'title'       => 'ECDSA key',
			'fingerprint' => '6d:c9:0b:33:9c:35:1d:d5:92:f9:cc:da:03:24:8d:68',
			'key'         => 'ecdsa-sha2-nistp256 AAAAE2VjZHNhLXNoYTItbmlzdHAyNTYA'.
			                 'AAAIbmlzdHAyNTYAAABBBCu97bcm7MlMaYNXC6Llu/plSj+UorvV'.
			                 'YpXiZ6MoijSXKRnxkgzsuv80FlERaPRayc15fi4mEeDeiwnqi45D'.
			                 'vag= admin@keychain',
		));

		// Add an address field
		DB::table('fields')->insert(array(
			'name'         => 'Address',
			'machine_name' => 'address',
			'type'         => FieldTypes::TEXTAREA,
			'category'     => FieldCategories::CONTACT,
			'order'        => 1,
		));

		// Add a dev username field
		DB::table('fields')->insert(array(
			'name'         => 'Developer username',
			'machine_name' => 'dev_username',
			'type'         => FieldTypes::TEXTBOX,
			'category'     => FieldCategories::BASIC,
			'order'        => 2,
		));

		// Add a honor field
		DB::table('fields')->insert(array(
			'name'         => 'Honor',
			'machine_name' => 'honor',
			'type'         => FieldTypes::RADIO,
			'category'     => FieldCategories::BASIC,
			'options'      => "Mr.\nMs.\nNone",
			'order'        => 1,
		));

		// Add a 'beverage preference' field
		DB::table('fields')->insert(array(
			'name'         => 'Beverage preference',
			'machine_name' => 'beverage_preference',
			'type'         => FieldTypes::DROPDOWN,
			'category'     => FieldCategories::OTHER,
			'options'      => "\nI like tea\nI like coffee",
			'order'        => 2,
			'required'     => 1,
		));

		// Add a 'linux user since' field
		DB::table('fields')->insert(array(
			'name'         => 'Linux user since',
			'machine_name' => 'linux_user_since',
			'type'         => FieldTypes::DATEPICKER,
			'category'     => FieldCategories::OTHER,
			'order'        => 3,
		));

		// Add a 'can contact' field
		DB::table('fields')->insert(array(
			'name'         => 'Available on phone?',
			'machine_name' => 'available_on_phone',
			'type'         => FieldTypes::CHECKBOX,
			'category'     => FieldCategories::CONTACT,
			'options'      => 'Calls accepted',
			'order'        => 2,
		));

		// Add the admin's address
		DB::table('user_fields')->insert(array(
			'user_id'  => 1,
			'field_id' => 1,
			'value'    => "2400 Hudson Dr. #200\nAustin TX 75234\nUnited States",
		));

		// Add the admin's dev username
		DB::table('user_fields')->insert(array(
			'user_id'  => 1,
			'field_id' => 2,
			'value'    => 'johndoe',
		));

		// Set the admin's honoritic
		DB::table('user_fields')->insert(array(
			'user_id'  => 1,
			'field_id' => 3,
			'value'    => 'Mr.',
		));

		// Set the admin's beverage preference
		DB::table('user_fields')->insert(array(
			'user_id'  => 1,
			'field_id' => 4,
			'value'    => 'I like coffee',
		));

		// Set the admin's linux user field
		DB::table('user_fields')->insert(array(
			'user_id'  => 1,
			'field_id' => 5,
			'value'    => '2004-04-02',
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
			'access'       => 'field.view',
		));

		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'field_id'     => 1,
			'access'       => 'field.edit',
		));

		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'field_id'     => 2,
			'access'       => 'field.view',
		));

		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'field_id'     => 2,
			'access'       => 'field.edit',
		));

		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'field_id'     => 3,
			'access'       => 'field.view',
		));

		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'field_id'     => 3,
			'access'       => 'field.edit',
		));

		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'field_id'     => 4,
			'access'       => 'field.view',
		));

		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'field_id'     => 4,
			'access'       => 'field.edit',
		));

		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'field_id'     => 5,
			'access'       => 'field.view',
		));

		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'field_id'     => 5,
			'access'       => 'field.edit',
		));

		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'field_id'     => 6,
			'access'       => 'field.view',
		));

		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'field_id'     => 6,
			'access'       => 'field.edit',
		));

		// Allow sysadmins to edit all profiles
		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'access'       => 'user.edit',
		));

		// Allow sysadmins to administrate all users
		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'access'       => 'user.manage',
		));

		// Allow sysadmins to administrate all groups
		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'access'       => 'group.manage',
		));

		// Allow registered users to edit their own profiles
		DB::table('acl')->insert(array(
			'object_id'    => 0,
			'object_type'  => ACLTypes::SELF,
			'subject_id'   => 1,
			'subject_type' => ACLTypes::GROUP,
			'access'       => 'user.edit',
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
		Schema::drop('token_types');
		Schema::drop('acl');
		Schema::drop('acl_types');
		Schema::drop('user_groups');
		Schema::drop('user_fields');
		Schema::drop('groups');
		Schema::drop('fields');
		Schema::drop('field_categories');
		Schema::drop('field_types');
		Schema::drop('user_sessions');
		Schema::drop('device_types');
		Schema::drop('user_keys');
		Schema::drop('user_emails');
		Schema::drop('users');
		Schema::drop('user_status');
	}

}
