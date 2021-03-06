<?php

/*
|--------------------------------------------------------------------------
| Register The Laravel Class Loader
|--------------------------------------------------------------------------
|
| In addition to using Composer, you may use the Laravel class loader to
| load your controllers and models. This is useful for keeping all of
| your classes in the "global" namespace without Composer updating.
|
*/

ClassLoader::addDirectories(array(

	app_path().'/commands',
	app_path().'/controllers',
	app_path().'/models',
	app_path().'/database/seeds',

));

/*
|--------------------------------------------------------------------------
| Application Error Logger
|--------------------------------------------------------------------------
|
| Here we will configure the error logger setup for the application which
| is built on top of the wonderful Monolog library. By default we will
| build a basic log file setup which creates a single file for logs.
|
*/

Log::useFiles(storage_path().'/logs/laravel.log');

/*
|--------------------------------------------------------------------------
| Application Error Handler
|--------------------------------------------------------------------------
|
| Here you may handle any errors that occur in your application, including
| logging them or displaying custom views for specific errors. You may
| even register several error handlers to handle different types of
| exceptions. If nothing is returned, the default error view is
| shown, which includes a detailed stack trace during debug.
|
*/

App::error(function(Exception $exception, $code)
{
	Log::error($exception);
});

/*
|--------------------------------------------------------------------------
| Maintenance Mode Handler
|--------------------------------------------------------------------------
|
| The "down" Artisan command gives you the ability to put an application
| into maintenance mode. Here, you will define what is displayed back
| to the user if maintenance mode is in effect for the application.
|
*/

App::down(function()
{
	return Response::make("Be right back!", 503);
});

/*
|--------------------------------------------------------------------------
| Require The Filters File
|--------------------------------------------------------------------------
|
| Next we will load the filters file for the application. This gives us
| a nice separate location to store our route and application filter
| definitions instead of putting them all in the main routes file.
|
*/

require app_path().'/filters.php';

/*
|--------------------------------------------------------------------------
| Multi-Factor Authentication Driver
|--------------------------------------------------------------------------
|
| Handles primary and multi-factor authentication for users.
|
*/

Auth::extend('multifactor', function()
{
	return new Guard(new MultiFactorUserProvider(), App::make('session.store'));
});

/*
|--------------------------------------------------------------------------
| Blade Code Tags
|--------------------------------------------------------------------------
|
| Define the custom blade tags to handle code such as assignment.
|
*/

Blade::extend(function($value)
{
	return preg_replace('/\{\?(.+)\?\}/', '<?php ${1} ?>', $value);
});

/*
|--------------------------------------------------------------------------
| Validator Alpha Space Rule
|--------------------------------------------------------------------------
|
| Defines a custom validation rule to allow alphabets and spaces only.
|
*/

Validator::extend('alpha_space', function($attribute, $value)
{
	return preg_match('/^[\pL\s]+$/u', $value);
});

/*
|--------------------------------------------------------------------------
| Validator Alpha Newline Rule
|--------------------------------------------------------------------------
|
| Defines a custom validation rule to allow alphabets, spaces and newlines
| only.
|
*/

Validator::extend('alpha_newline', function($attribute, $value)
{
	return preg_match('/^[\pL\s\r\n]+$/u', $value);
});

/*
|--------------------------------------------------------------------------
| Handle application errors
|--------------------------------------------------------------------------
|
| Shows custom screens for app errors. This is mainly done to show a
| friendly error message and to throw errors with ease from the view.
|
*/

App::error(function($exception, $code)
{
	// Get the exception instance
	$type = get_class($exception);

	// Set code based on exception
	switch ($type)
	{
		case 'Illuminate\Session\TokenMismatchException':

			$code = 403;

			break;

		case 'Illuminate\Database\Eloquent\ModelNotFoundException':
		case 'InvalidArgumentException':

			$code = 404;

			break;
	}

	// Show an error page
	if ( ! Config::get('app.debug'))
	{
		// Log the exception details
		Log::error($exception);

		// Check if the language file has a friendly text for the error
		// If not, fall back to generic verbiage
		$key = Lang::has("error.{$code}") ? $code : 'default';

		// Build the view data
		$data = array(
			'type'    => NoticeTypes::ERROR,
			'message' => Lang::get("error.{$key}"),
		);

		// Return the notice to the user
		return Response::layout('common/notice', 'global.error', $data, $code);
	}
});

