@extends('common.dialog')

@section('form')
	<div class="form-group">
		{{ Form::label('password', Lang::get('auth.new_password')) }}

		{{
			Form::password('new_password', array(
				'class'     => 'form-control',
				'autofocus' => 'autofocus',
			))
		}}
	</div>

	<div class="form-group">
		{{ Form::label('confirm_password', Lang::get('auth.confirm_password')) }}
		{{ Form::password('confirm_password', array('class' => 'form-control')) }}
	</div>

	{{ Form::submit(Lang::get('auth.reset_password'), array('class' => 'btn btn-primary')) }}
@stop
