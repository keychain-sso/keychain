<nav class="navbar navbar-default navbar-static-top navbar-lg navbar-modal">
	<div class="container-fluid">
		<a href="{{ url('profile/view/'.$user->hash) }}" class="close">&times;</a>

		<div class="text-center">
			<ul class="nav nav-icons">
				<li @if ($modal == 'edit') class="active" @endif>
					<a href="{{ url('profile/edit/'.$user->hash) }}" title="{{ Lang::get('profile.edit_profile') }}" data-toggle="tooltip">
						<span class="glyphicon glyphicon-pencil"></span>
					</a>
				</li>

				<li @if ($modal == 'emails') class="active" @endif>
					<a href="{{ url('profile/emails/'.$user->hash) }}" title="{{ Lang::get('profile.manage_email_addresses') }}" data-toggle="tooltip">
						<span class="glyphicon glyphicon-envelope"></span>
					</a>
				</li>

				<li @if ($modal == 'keys') class="active" @endif>
					<a href="{{ url('profile/keys/'.$user->hash) }}" title="{{ Lang::get('profile.manage_ssh_keys') }}" data-toggle="tooltip">
						<span class="glyphicon glyphicon-briefcase"></span>
					</a>
				</li>

				<li @if ($modal == 'security') class="active" @endif>
					<a href="{{ url('profile/security/'.$user->hash) }}" title="{{ Lang::get('profile.security_settings') }}" data-toggle="tooltip">
						<span class="glyphicon glyphicon-lock"></span>
					</a>
				</li>
			</ul>

			<h3>{{ $title }}</h3>
		</div>
	</div>
</nav>

@include('common.alerts')
