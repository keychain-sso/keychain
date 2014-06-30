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
	return Redirect::to('profile');
});

// User profile route
Route::controller('profile', 'ProfileController');

// Token validation route
Route::controller('token', 'TokenController');

// Authentication route
Route::controller('auth', 'AuthController');

// Global authentication check
Route::when('*', 'auth');

// CSRF protection for all forms
Route::when('*', 'csrf', array('post'));
