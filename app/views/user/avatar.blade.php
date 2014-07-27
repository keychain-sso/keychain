{{ Form::open(array('role' => 'form')) }}

<div class="modal-body">
	<nav class="navbar navbar-default navbar-static-top navbar-modal">
		<div class="container-fluid">
			<a href="{{ url("user/view/{$user->hash}") }}" class="close">&times;</a>

			<div class="text-center">
				<h3 class="spacer-none-top">{{ $title }}</h3>
			</div>
		</div>
	</nav>

	@include('common.alerts')

	<div class="text-center spacer-lg-bottom">
		<p>{{ Lang::get('user.avatar_resize') }}</p>

		<span class="thumbnail">
			<img id="avatar-resize" src="{{ asset("uploads/avatars/{$avatar}") }}" />
		</span>
	</div>

	{{ Form::hidden('screen_width') }}
	{{ Form::hidden('screen_height') }}
	{{ Form::hidden('width') }}
	{{ Form::hidden('height') }}
	{{ Form::hidden('x') }}
	{{ Form::hidden('y') }}
</div>

<div class="modal-footer">
	{{ Form::hidden('hash', $user->hash) }}
	{{ Form::submit(Lang::get('global.save'), array('class' => 'btn btn-primary')) }}

	<a href="{{ url("user/view/{$user->hash}") }}" class="btn btn-default">
		{{ Lang::get('global.close') }}
	</a>
</div>

{{ Form::close() }}

