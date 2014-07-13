<nav class="navbar navbar-default navbar-static-top navbar-lg navbar-modal">
	<div class="container-fluid">
		<a href="{{ url("user/view/{$user->hash}") }}" class="close">&times;</a>

		<div class="text-center">
			<ul class="nav nav-icons">
				<li @if ($modal == 'editor') class="active" @endif>
					<a href="{{ url("user/edit/{$user->hash}") }}" title="{{ Lang::get('user.edit_profile') }}"
					   data-toggle="tooltip" data-placement="bottom">
						<span class="glyphicon glyphicon-pencil"></span>
					</a>
				</li>

				<li @if ($modal == 'emails') class="active" @endif>
					<a href="{{ url("user/emails/{$user->hash}") }}" title="{{ Lang::get('user.manage_email_addresses') }}"
					   data-toggle="tooltip" data-placement="bottom">
						<span class="glyphicon glyphicon-envelope"></span>
					</a>
				</li>

				<li @if ($modal == 'keys') class="active" @endif>
					<a href="{{ url("user/keys/{$user->hash}") }}" title="{{ Lang::get('user.manage_ssh_keys') }}"
					   data-toggle="tooltip" data-placement="bottom">
						<span class="glyphicon glyphicon-briefcase"></span>
					</a>
				</li>

				<li @if ($modal == 'security') class="active" @endif>
					<a href="{{ url("user/security/{$user->hash}") }}" title="{{ Lang::get('user.security_settings') }}"
					   data-toggle="tooltip" data-placement="bottom">
						<span class="glyphicon glyphicon-lock"></span>
					</a>
				</li>
			</ul>

			<h3 class="spacer-none-top">{{ $title }}</h3>
		</div>
	</div>
</nav>

@include('common.alerts')
