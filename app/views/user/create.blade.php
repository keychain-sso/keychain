{{
	Form::open(array(
		'role'         => 'form',
		'autocomplete' => 'off',
	))
}}

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

	<div class="form-group">
		{{ Form::label('name', Lang::get('global.name'), array('class' => 'control-label')) }}
		{{ Form::text('name', null, array('class' => 'form-control')) }}
	</div>

	<div class="form-group">
		{{ Form::label('email', Lang::get('global.email_address'), array('class' => 'control-label')) }}
		{{ Form::text('email', null, array('class' => 'form-control')) }}
	</div>

	<div class="form-group">
		{{ Form::label('password', Lang::get('global.password'), array('class' => 'control-label')) }}
		{{ Form::password('password', array('class' => 'form-control')) }}
	</div>
</div>

<div class="modal-footer">
	{{ Form::submit(Lang::get('global.save'), array('class' => 'btn btn-primary')) }}

	<a href="{{ url('user/list') }}" class="btn btn-default">
		{{ Lang::get('global.close') }}
	</a>
</div>

{{ Form::close() }}
