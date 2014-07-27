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

		<span class="img-thumbnail">
			<img id="avatar-resize" src="{{ asset("uploads/avatars/{$user->hash}") }}" class="stretch" />
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
	<a href="{{ url("user/view/{$user->hash}") }}" class="btn btn-danger pull-left">
		{{ Lang::get('user.remove_avatar') }}
	</a>

	{{ Form::hidden('hash', $user->hash) }}
	{{ Form::submit(Lang::get('global.save'), array('class' => 'btn btn-primary')) }}
</div>

{{ Form::close() }}

