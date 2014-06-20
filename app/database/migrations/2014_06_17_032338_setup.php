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
			$table->integer('status')->index();
			$table->timestamps();
		});

		// Create the emails table
		Schema::create('user_emails', function( $table )
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->string('email', 80)->index();
			$table->boolean('primary');
			$table->boolean('verified')->index();
			$table->timestamps();

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		});

		// Create the field types table
		Schema::create('field_types', function($table)
		{
			$table->increments('id');
			$table->string('name', 80);
		});

		// Create the ACL object types table
		Schema::create('object_types', function($table)
		{
			$table->increments('id');
			$table->string('name', 80);
		});

		// Create the ACL subject types table
		Schema::create('subject_types', function($table)
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
			$table->timestamps();

			$table->foreign('type')->references('id')->on('field_types')->onDelete('cascade');
		});

		// Create the groups table
		Schema::create('groups', function( $table )
		{
			$table->increments('id');
			$table->string('name', 80)->index();
			$table->mediumText('description');
			$table->timestamps();
		});

		// Create the mapping between users and fields
		Schema::create('user_fields', function($table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index();
			$table->integer('field_id')->unsigned()->index();
			$table->mediumText('value');
			$table->timestamps();

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('field_id')->references('id')->on('fields')->onDelete('cascade');
		});

		// Create the mapping between users and groups
		Schema::create('user_groups', function($table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index();
			$table->integer('group_id')->unsigned()->index();
			$table->timestamps();

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
		});

		// Create the ACL base table
		Schema::create('acl', function($table)
		{
			$table->increments('id');
			$table->integer('object_id')->unsigned();
			$table->integer('object_type')->unsigned();
			$table->integer('subject_id')->unsigned();
			$table->integer('subject_type')->unsigned();
			$table->integer('field')->unsigned()->nullable();
			$table->string('access', 20);
			$table->timestamps();

			$table->index(array('object_id', 'object_type', 'subject_id', 'subject_type'));
			$table->foreign('object_type')->references('id')->on('object_types')->onDelete('cascade');
			$table->foreign('subject_type')->references('id')->on('subject_types')->onDelete('cascade');
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
			$table->timestamps();
		});

		// Insert admin user account
		$user = new User;

		$user->first_name = 'Site';
		$user->last_name  = 'Administrator';
		$user->password   = Hash::make('password');
		$user->status     = UserStatus::ACTIVE;

		$user->save();

		// Insert admin user email
		$email = new UserEmail;

		$email->user_id   = 1;
		$email->email     = 'admin@keychain.sso';
		$email->primary   = 1;
		$email->verified  = 1;

		$email->save();
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
		Schema::drop('user_groups');
		Schema::drop('user_fields');
		Schema::drop('groups');
		Schema::drop('fields');
		Schema::drop('subject_types');
		Schema::drop('object_types');
		Schema::drop('field_types');
		Schema::drop('user_emails');
		Schema::drop('users');
	}

}
