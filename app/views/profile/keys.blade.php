{{
	Form::open(array(
		'action' => 'ProfileController@postKeys',
		'role'   => 'form',
	))
}}

<div class="modal-body">
	<nav class="navbar navbar-default navbar-static-top navbar-lg navbar-modal">
		<div class="container-fluid">
			<a href="{{ url('profile/view/'.$user->hash) }}" class="close">&times;</a>

			<div class="text-center">
				<ul class="nav nav-icons">
					<li>
						<a href="{{ url('profile/edit/'.$user->hash) }}" title="{{ Lang::get('profile.edit_profile') }}" data-toggle="tooltip">
							<span class="glyphicon glyphicon-pencil"></span>
						</a>
					</li>

					<li>
						<a href="{{ url('profile/emails/'.$user->hash) }}" title="{{ Lang::get('profile.manage_email_addresses') }}" data-toggle="tooltip">
							<span class="glyphicon glyphicon-envelope"></span>
						</a>
					</li>

					<li class="active">
						<a title="{{ Lang::get('profile.manage_ssh_keys') }}" data-toggle="tooltip">
							<span class="glyphicon glyphicon-briefcase"></span>
						</a>
					</li>

					<li>
						<a href="{{ url('profile/security/'.$user->hash) }}" title="{{ Lang::get('profile.security_settings') }}" data-toggle="tooltip">
							<span class="glyphicon glyphicon-lock"></span>
						</a>
					</li>
				</ul>

				<h3>{{ Lang::get('profile.manage_ssh_keys') }}</h3>
			</div>
		</div>
	</nav>

	@include('common.alerts')

	<fieldset>
		<legend>
			<span class="glyphicon glyphicon-briefcase"></span>
			{{ Lang::get('profile.ssh_keys') }}
		</legend>

		<ul class="list-group">
			@foreach ($keys as $key)
				<li class="list-group-item">
					<a href="{{ url('profile/keys/'.$user->hash.'/remove/'.$key->id) }}" class="btn btn-xs btn-danger pull-right">
						{{ Lang::get('global.remove') }}
					</a>

					<h4 class="list-group-item-heading">{{{ $key->title }}}</h4>
					<samp class="list-group-item-text">{{ $key->fingerprint }}</samp>
				</li>
			@endforeach
		</ul>

	</fieldset>

	<fieldset>
		<legend>
			<span class="glyphicon glyphicon-export"></span>
			{{ Lang::get('profile.add_ssh_key') }}
		</legend>

		<div class="form-group">
			{{
				Form::label('title', Lang::get('profile.title'), array(
					'class' => 'control-label'
				))
			}}

			{{
				Form::text('title', null, array(
					'class' => 'form-control',
				))
			}}
		</div>

		<div class="form-group">
			{{
				Form::label('key', Lang::get('profile.public_key'), array(
					'class' => 'control-label'
				))
			}}

			{{
				Form::textarea('key', null, array(
					'class' => 'form-control',
				))
			}}
		</div>
	</fieldset>
</div>

<div class="modal-footer">
	{{ Form::hidden('hash', $user->hash) }}

	{{
		Form::submit(Lang::get('global.add'), array(
			'name'     => '_add',
			'class'    => 'btn btn-primary',
		))
	}}

	{{
		link_to("profile/view/{$user->hash}", Lang::get('global.close'), array(
			'class' => 'btn btn-default',
		))
	}}
</div>

{{ Form::close() }}
