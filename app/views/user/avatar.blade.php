{{ Form::open(array('role' => 'form')) }}

<div class="modal-body">
	<nav class="navbar navbar-default navbar-static-top navbar-modal">
		<div class="container-fluid">
			<a href="{{ url('user/list') }}" class="close">&times;</a>

			<div class="text-center">
				<h3 class="spacer-none-top">{{ $title }}</h3>
			</div>
		</div>
	</nav>

	@include('common.alerts')

	<img src="{{ asset("uploads/avatars/{$avatar}") }} class="img-thumbnail" />

	{{ Form::hidden('width') }}
	{{ Form::hidden('height') }}
	{{ Form::hidden('x') }}
	{{ Form::hidden('y') }}
</div>

<div class="modal-footer">
	{{ Form::submit(Lang::get('global.save'), array('class' => 'btn btn-primary')) }}

	<a href="{{ url("user/view/{$user->hash}") }}" class="btn btn-default">
		{{ Lang::get('global.close') }}
	</a>
</div>

{{ Form::close() }}

