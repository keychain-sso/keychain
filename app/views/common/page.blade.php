<!DOCTYPE html>

<html lang="{{ $appconfig['locale'] }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ $title }}</title>

	<link href="{{ asset('img/favicon.ico') }}" rel="icon" />
	<link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" />
	<link href="{{ asset('css/bootstrap-pixel.css') }}" rel="stylesheet" />
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

				<a class="navbar-brand" href="{{ url() }}">{{ Config::get('app.title') }}</a>
			</div>

			<div class="collapse navbar-collapse navbar-ex1-collapse">
				<ul class="nav navbar-nav navbar-right">
					<li>
						<a href="{{ url('user/list') }}">
							<span class="glyphicon glyphicon-user"></span>
							{{ Lang::get('global.users') }}
						</a>
					</li>

					<li>
						<a href="{{ url('group/list') }}">
							<span class="glyphicon glyphicon-th-large"></span>
							{{ Lang::get('global.groups') }}
						</a>
					</li>

					@if (Auth::check())
						<li>
							<a href="{{ url('auth/logout') }}">
								<span class="glyphicon glyphicon-log-out"></span>
								{{ Lang::get('global.logout') }}
							</a>
						</li>
					@endif
				</ul>
			</div>
		</div>
	</nav>

	<div class="container">
		@yield('body')
	</div>

	@if (isset($modal))
		<div class="modal modal-editor">
			<div class="modal-dialog">
				<div class="modal-content">
					@include($modal)
				</div>
			</div>
		</div>
	@endif

	<footer class="text-center">
		&copy; <a href="https://github.com/keychain-sso">Keychain Developers</a>
	</footer>

	<script type="text/javascript" src="{{ asset('js/jquery.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/jquery.cookie.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/jquery.scrollto.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/bootstrap.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/bootstrap-datepicker.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/bootstrap-pixel.js') }}"></script>

	@if ($appconfig['locale'] != 'en')
		<script type="text/javascript" src="{{ asset("js/locales/bootstrap-datepicker.{$appconfig['locale']}.js") }}"></script>
	@endif
</body>

</html>
