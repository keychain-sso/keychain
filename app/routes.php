<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This file defines all MVC routes for Keychain. Any HTTP request
| that doesn't match these routes will be blocked.
|
*/

// The user profile is the homepage
Route::get('/', function()
{
	return Redirect::to('user/view/'.Auth::user()->hash);
});

// User profile route
Route::controller('user', 'UserController');

// User group route
Route::controller('group', 'GroupController');

// Token validation route
Route::controller('token', 'TokenController');

// Authentication route
Route::controller('auth', 'AuthController');

// Field management route
Route::controller('field', 'FieldController');

// ACL management route
Route::controller('permission', 'PermissionController');

// Global authentication check
Route::when('*', 'auth');

// CSRF protection for all forms
Route::when('*', 'csrf', array('post'));
