<!DOCTYPE html>

<html lang="{{ $appconfig['locale'] }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ Lang::get('global.keychain') }}</title>

	<link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" />
	<link href="{{ asset('css/keychain.css') }}" rel="stylesheet" />
	<link href="{{ asset('css/datepicker.css') }}" rel="stylesheet" />
</head>

<body>
	<nav class="navbar navbar-default navbar-static-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>

				<a class="navbar-brand" href="{{ url() }}">{{ Lang::get('global.keychain') }}</a>
			</div>

			<div class="collapse navbar-collapse navbar-ex1-collapse">
				<ul class="nav navbar-nav navbar-right">
					<!-- Add navigation links here -->
				</ul>
			</div>
		</div>
	</nav>

	<div class="container">
		@yield('body')
	</div>

	<footer class="text-center">
		&copy; <a href="https://github.com/keychain-sso">Keychain Developers</a>
	</footer>

	<div id="modal-loader" class="hide">
		@include('common.loader')
	</div>

	<script type="text/javascript" src="{{ asset('js/jquery.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/jquery.cookie.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/jquery.scrollto.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/bootstrap.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/bootstrap-datepicker.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/keychain.js') }}"></script>

	@if ($appconfig['locale'] != 'en')
		<script type="text/javascript" src="{{ asset('js/locales/bootstrap-datepicker.'.$appconfig['locale'].'.js') }}"></script>
	@endif
</body>

</html>
