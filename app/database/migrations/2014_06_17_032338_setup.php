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
			$table->timestamp('date_of_birth')->nullable();
			$table->string('timezone', 80)->nullable();
			$table->integer('status')->unsigned()->index();
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

		// Create the field definitions table
		Schema::create('fields', function($table)
		{
			$table->increments('id');
			$table->string('name', 80);
			$table->string('machine_name', 80);
			$table->integer('type')->unsigned();
			$table->mediumText('options')->nullable();
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at');

			$table->foreign('type')->references('id')->on('field_types')->onDelete('cascade');
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
			$table->integer('field')->unsigned()->default(0);
			$table->string('access', 20);
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at');

			$table->index(array('subject_id', 'subject_type'));
			$table->foreign('object_type')->references('id')->on('acl_types')->onDelete('cascade');
			$table->foreign('subject_type')->references('id')->on('acl_types')->onDelete('cascade');
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

		// Insert the user status values
		DB::table('user_status')->insert(array(
			array('name' => 'Inactive'),
			array('name' => 'Active'),
			array('name' => 'Blocked'),
		));

		// Insert admin user account
		DB::table('users')->insert(array(
			'first_name' => 'Site',
			'last_name'  => 'Admin',
			'password'   => Hash::make('password'),
			'status'     => UserStatus::ACTIVE,
		));

		// Insert admin user email
		DB::table('user_emails')->insert(array(
			'user_id'  => 1,
			'email'    => 'admin@keychain.sso',
			'primary'  => 1,
			'verified' => 1,
		));

		// Insert the sysadmin group
		DB::table('groups')->insert(array(
			'name'        => 'Sysadmins',
			'description' => 'System administrators with full control over the website.',
		));

		// Link the admin user to the sysadmin group
		DB::table('user_groups')->insert(array(
			'user_id'  => 1,
			'group_id' => 1,
		));

		// Insert ACL types
		DB::table('acl_types')->insert(array(
			array('name' => 'Field'),
			array('name' => 'Self'),
			array('name' => 'User'),
			array('name' => 'Group'),
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
		Schema::drop('field_types');
		Schema::drop('user_emails');
		Schema::drop('users');
		Schema::drop('user_status');
	}

}
