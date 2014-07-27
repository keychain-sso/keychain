<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| View Storage Paths
	|--------------------------------------------------------------------------
	|
	| Most templating systems load templates from disk. Here you may specify
	| an array of paths that should be checked for your views. Of course
	| the usual Laravel view path has already been registered for you.
	|
	*/

	'paths' => array(__DIR__.'/../views'),

	/*
	|--------------------------------------------------------------------------
	| Pagination View
	|--------------------------------------------------------------------------
	|
	| This view will be used to render the pagination link output, and can
	| be easily customized here to show any view you like. A clean view
	| compatible with Twitter's Bootstrap is given to you by default.
	|
	*/

	'pagination' => 'pagination::slider-3',

	/*
	|--------------------------------------------------------------------------
	| List Length
	|--------------------------------------------------------------------------
	|
	| This dictates the maximum number of items that will be displayed on
	| one page for a list layout.
	|
	*/

	'list_length' => 15,

	/*
	|--------------------------------------------------------------------------
	| Icon Length
	|--------------------------------------------------------------------------
	|
	| This dictates the maximum number of icons that will be displayed on
	| one page for icon layout.
	|
	*/

	'icon_length' => 24,

	/*
	|--------------------------------------------------------------------------
	| Icon Size
	|--------------------------------------------------------------------------
	|
	| This specifies the maximum allowed icon width and height.
	|
	*/

	'icon_size' => 200,

);
