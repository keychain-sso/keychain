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

// The dashboard is the homepage
Route::get('/', function()
{
	return Redirect::to('dashboard');
});

// User homepage route
Route::controller('dashboard', 'DashboardController');

// Authentication route
Route::controller('auth', 'AuthController');

// Global authentication check
Route::when('*', 'auth');

// CSRF protection for all forms
Route::when('*', 'csrf', array('post'));
