@extends('common.dialog')

@section('form')
	<div class="form-group">
		{{ Form::label('email', Lang::get('global.email_address')) }}

		{{
			Form::text('email', NULL, array(
				'class'     => 'form-control',
				'autofocus' => 'autofocus',
				'maxlength' => 80,
			))
		}}
	</div>

	<div class="form-group">
		{{ Form::label('password', Lang::get('global.password')) }}
		{{ Form::password('password', array('class' => 'form-control')) }}
	</div>

	<div class="checkbox">
		<label>
			{{ Form::checkbox('remember', NULL, NULL, array('id' => 'remember')) }}
			{{ Lang::get('auth.remember') }}
		</label>
	</div>

	<a href="{{ url('auth/register') }}" class="btn btn-link pull-right" tabindex="-1">
		{{ Lang::get('auth.create_account') }}
	</a>

	{{ Form::submit(Lang::get('auth.login'), array('class' => 'btn btn-primary')) }}

	<a href="{{ url('auth/forgot') }}" class="btn btn-link">
		{{ Lang::get('auth.forgot_password') }}
	</a>
@stop
