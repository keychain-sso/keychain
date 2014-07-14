<nav class="navbar navbar-default navbar-static-top @if ($modal != 'user.create') navbar-lg @endif navbar-modal">
	<div class="container-fluid">
		@if ($modal != 'user.create')
			<a href="{{ url("user/view/{$user->hash}") }}" class="close">&times;</a>
		@else
			<a href="{{ url('user/list') }}" class="close">&times;</a>
		@endif

		<div class="text-center">
			@if ($modal != 'user.create')
				<ul class="nav nav-icons">
					<li @if ($modal == 'user.editor') class="active" @endif>
						<a href="{{ url("user/edit/{$user->hash}") }}" title="{{ Lang::get('user.edit_profile') }}"
						   data-toggle="tooltip" data-placement="bottom">
							<span class="glyphicon glyphicon-pencil"></span>
						</a>
					</li>

					<li @if ($modal == 'user.emails') class="active" @endif>
						<a href="{{ url("user/emails/{$user->hash}") }}" title="{{ Lang::get('user.manage_emails') }}"
						   data-toggle="tooltip" data-placement="bottom">
							<span class="glyphicon glyphicon-envelope"></span>
						</a>
					</li>

					<li @if ($modal == 'user.keys') class="active" @endif>
						<a href="{{ url("user/keys/{$user->hash}") }}" title="{{ Lang::get('user.manage_ssh_keys') }}"
						   data-toggle="tooltip" data-placement="bottom">
							<span class="glyphicon glyphicon-briefcase"></span>
						</a>
					</li>

					<li @if ($modal == 'user.security') class="active" @endif>
						<a href="{{ url("user/security/{$user->hash}") }}" title="{{ Lang::get('user.security_settings') }}"
						   data-toggle="tooltip" data-placement="bottom">
							<span class="glyphicon glyphicon-lock"></span>
						</a>
					</li>
				</ul>
			@endif

			<h3 class="spacer-none-top">{{ $title }}</h3>
		</div>
	</div>
</nav>

@include('common.alerts')
