{{ Form::open(array('role' => 'form')) }}

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
					<a href="{{ url("user/keys/{$user->hash}/remove/{$key->id}") }}" class="btn btn-xs btn-danger pull-right">
						{{ Lang::get('global.remove') }}
					</a>

					<h4 class="list-group-item-heading">{{{ $key->title }}}</h4>
					<samp class="list-group-item-text">{{ $key->fingerprint }}</samp>
				</li>
			@endforeach

			@if (count($keys) == 0)
				<li class="list-group-item">
					{{ Lang::get('user.no_ssh_keys') }}
				</li>
			@endif
		</ul>
	</fieldset>

	<fieldset>
		<legend>
			<span class="glyphicon glyphicon-export"></span>
			{{ Lang::get('user.add_ssh_key') }}
		</legend>

		<div class="form-group">
			{{ Form::label('title', Lang::get('user.title'), array('class' => 'control-label')) }}
			{{ Form::text('title', null, array('class' => 'form-control')) }}
		</div>

		<div class="form-group">
			{{ Form::label('key', Lang::get('user.public_key'), array('class' => 'control-label')) }}
			{{ Form::textarea('key', null, array('class' => 'form-control')) }}
		</div>
	</fieldset>
</div>

<div class="modal-footer">
	{{ Form::hidden('hash', $user->hash) }}
	{{ Form::submit(Lang::get('global.add'), array('class' => 'btn btn-primary')) }}

	<a href="{{ url("user/view/{$user->hash}") }}" class="btn btn-default">
		{{ Lang::get('global.close') }}
	</a>
</div>

{{ Form::close() }}
