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
			$table->string('name', 80)->index();
			$table->string('password', 80);
			$table->string('remember_token', 60)->nullable()->index();
			$table->enum('gender', array('M', 'F', 'N'))->nullable();
			$table->timestamp('date_of_birth')->nullable();
			$table->string('timezone', 80)->default('UTC');
			$table->boolean('avatar')->default(Flags::NO);
			$table->string('title', 80)->nullable();
			$table->integer('status')->unsigned()->index();
			$table->string('hash', 8)->unique()->index();
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at')->nullable();

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
			$table->timestamp('updated_at')->nullable();

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
			$table->timestamp('updated_at')->nullable();

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
			$table->timestamp('updated_at')->nullable();

			$table->foreign('device_type')->references('id')->on('device_types')->onDelete('cascade');
		});

		// Create the field types table
		Schema::create('field_types', function($table)
		{
			$table->increments('id');
			$table->string('name', 80)->unique();
			$table->boolean('option');
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
			$table->timestamp('updated_at')->nullable();

			$table->foreign('type')->references('id')->on('field_types')->onDelete('cascade');
			$table->foreign('category')->references('id')->on('field_categories')->onDelete('cascade');
		});

		// Create the group types table
		Schema::create('group_types', function($table)
		{
			$table->increments('id');
			$table->string('name', 80)->unique();
		});

		// Create the groups table
		Schema::create('groups', function($table)
		{
			$table->increments('id');
			$table->string('name', 80)->unique()->index();
			$table->mediumText('description');
			$table->integer('type')->unsigned();
			$table->string('hash', 8)->unique()->index();
			$table->boolean('notify')->default(0);
			$table->boolean('auto_join')->default(0);
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at')->nullable();

			$table->foreign('type')->references('id')->on('group_types')->onDelete('cascade');
		});

		// Create the group requests table
		Schema::create('group_requests', function($table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index();
			$table->integer('group_id')->unsigned()->index();
			$table->mediumText('justification');
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at')->nullable();

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
		});

		// Create the mapping between users and fields
		Schema::create('user_fields', function($table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index();
			$table->integer('field_id')->unsigned()->index();
			$table->mediumText('value');
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at')->nullable();

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
			$table->timestamp('updated_at')->nullable();

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
		});

		// Create the ACL subject/object types table
		Schema::create('acl_types', function($table)
		{
			$table->increments('id');
			$table->string('name', 80)->unique();
		});

		// Create the ACL subject/object types table
		Schema::create('acl_flags', function($table)
		{
			$table->increments('id');
			$table->string('name', 80)->unique();
		});

		// Create the ACL base table
		Schema::create('acl', function($table)
		{
			$table->increments('id');
			$table->string('flag', 80);
			$table->integer('subject_id')->unsigned();
			$table->integer('subject_type')->unsigned();
			$table->integer('object_id')->unsigned()->default(0);
			$table->integer('object_type')->unsigned()->default(1);
			$table->integer('field_id')->unsigned()->default(0);
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at')->nullable();

			$table->index(array('subject_id', 'subject_type'));
			$table->foreign('flag')->references('name')->on('acl_flags')->onDelete('cascade');
			$table->foreign('subject_type')->references('id')->on('acl_types')->onDelete('cascade');
			$table->foreign('object_type')->references('id')->on('acl_types')->onDelete('cascade');
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
			$table->timestamp('updated_at')->nullable();

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

		// Insert ACL flags
		DB::table('acl_flags')->insert(array(
			array('name' => 'acl_manage'),
			array('name' => 'field_edit'),
			array('name' => 'field_manage'),
			array('name' => 'field_view'),
			array('name' => 'group_edit'),
			array('name' => 'group_manage'),
			array('name' => 'user_edit'),
			array('name' => 'user_manage'),
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

		// Insert group types
		DB::table('group_types')->insert(array(
			array('name' => 'Open'),
			array('name' => 'Request'),
			array('name' => 'Closed'),
		));

		// Insert field categories
		DB::table('field_categories')->insert(array(
			array('name' => 'Basic'),
			array('name' => 'Contact'),
			array('name' => 'Other'),
		));

		// Insert field types
		DB::table('field_types')->insert(array(
			array(
				'name'   => 'TextBox',
				'option' => Flags::NO,
			),
			array(
				'name'   => 'TextArea',
				'option' => Flags::NO,
			),
			array(
				'name'   => 'Radio',
				'option' => Flags::YES,
			),
			array(
				'name'   => 'CheckBox',
				'option' => Flags::YES,
			),
			array(
				'name'   => 'Dropdown',
				'option' => Flags::YES,
			),
			array(
				'name'   => 'DatePicker',
				'option' => Flags::NO,
			),
		));

		// Insert admin user account
		DB::table('users')->insert(array(
			'name'          => 'John Doe',
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

		// Insert the group entries
		DB::table('groups')->insert(array(
			'name'        => 'Registered users',
			'description' => 'All registered users on the website.',
			'type'        => GroupTypes::CLOSED,
			'hash'        => str_random(8),
			'auto_join'   => Flags::YES,
		));

		DB::table('groups')->insert(array(
			'name'        => 'Sysadmins',
			'description' => 'System administrators with full control over the website.',
			'type'        => GroupTypes::CLOSED,
			'hash'        => str_random(8),
		));

		// Link the admin user to the sysadmin group
		DB::table('user_groups')->insert(array(
			'user_id'  => 1,
			'group_id' => 1,
		));

		// Link admin to registered users group
		DB::table('user_groups')->insert(array(
			'user_id'  => 1,
			'group_id' => 2,
		));

		// Allow sysadmins to edit all users
		DB::table('acl')->insert(array(
			'flag'         => ACLFlags::USER_EDIT,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
		));

		// Allow registered users to edit their own profiles
		DB::table('acl')->insert(array(
			'flag'         => ACLFlags::USER_EDIT,
			'subject_id'   => 1,
			'subject_type' => ACLTypes::GROUP,
			'object_id'    => 0,
			'object_type'  => ACLTypes::SELF,
		));

		// Allow sysadmins to manage all users
		DB::table('acl')->insert(array(
			'flag'         => ACLFlags::USER_MANAGE,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
		));

		// Allow sysadmins to edit all groups
		DB::table('acl')->insert(array(
			'flag'         => ACLFlags::GROUP_EDIT,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
		));

		// Allow sysadmins to manage all groups
		DB::table('acl')->insert(array(
			'flag'         => ACLFlags::GROUP_MANAGE,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
		));

		// Allow sysadmins to manage fields
		DB::table('acl')->insert(array(
			'flag'         => ACLFlags::FIELD_MANAGE,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
		));

		// Allow sysadmins to manage ACLs
		DB::table('acl')->insert(array(
			'flag'         => ACLFlags::ACL_MANAGE,
			'subject_id'   => 2,
			'subject_type' => ACLTypes::GROUP,
			'object_id'    => 0,
			'object_type'  => ACLTypes::ALL,
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
		Schema::drop('acl_flags');
		Schema::drop('user_groups');
		Schema::drop('user_fields');
		Schema::drop('group_requests');
		Schema::drop('groups');
		Schema::drop('group_types');
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
