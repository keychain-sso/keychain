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

	<a href="{{ url('auth/login') }}" class="btn btn-link pull-right" tabindex="-1">
		{{ Lang::get('auth.return_login') }}
	</a>

	{{
		Form::submit(Lang::get('auth.send_confirmation_code'), array(
			'name'  => '_send',
			'class' => 'btn btn-primary'
		))
	}}
@stop
