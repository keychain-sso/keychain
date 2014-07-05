{{
	Form::open(array(
		'action' => 'UserController@postKeys',
		'role'   => 'form',
	))
}}

<div class="modal-body">
	@include('user.header')

	<fieldset>
		<legend>
			<span class="glyphicon glyphicon-briefcase"></span>
			{{ Lang::get('user.ssh_keys') }}
		</legend>

		<ul class="list-group">
			@foreach ($keys as $key)
				<li class="list-group-item">
					<a href="{{ url('user/keys/'.$user->hash.'/remove/'.$key->id) }}" class="btn btn-xs btn-danger pull-right">
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
			{{ Lang::get('user.add_ssh_key') }}
		</legend>

		<div class="form-group">
			{{
				Form::label('title', Lang::get('user.title'), array(
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
				Form::label('key', Lang::get('user.public_key'), array(
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
		link_to("user/view/{$user->hash}", Lang::get('global.close'), array(
			'class' => 'btn btn-default',
		))
	}}
</div>

{{ Form::close() }}
