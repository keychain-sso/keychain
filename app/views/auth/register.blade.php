@extends('common.page')

@section('body')
	{{
		Form::open(array(
			'role'         => 'form',
			'autocomplete' => 'off',
		))
	}}

	<div class="row">
		<div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
			<div class="text-center spacer-lg-bottom">
				<h1>
					<img src="{{ asset('img/logo.png') }}" alt="" />
					{{ Config::get('app.title') }}
				</h1>
			</div>

			@include('common.alerts')

			<div class="well">
				<fieldset>
					<div class="form-group">
						{{ Form::label('name', Lang::get('global.name')) }}

						{{
							Form::text('name', NULL, array(
								'class'     => 'form-control',
								'autofocus' => 'autofocus',
								'maxlength' => 80,
							))
						}}
					</div>

					<div class="form-group">
						{{ Form::label('email', Lang::get('global.email_address')) }}

						{{
							Form::text('email', NULL, array(
								'class'     => 'form-control',
								'maxlength' => 80,
							))
						}}
					</div>

					<div class="form-group">
						{{ Form::label('password', Lang::get('global.password')) }}
						{{ Form::password('password', array('class' => 'form-control')) }}
					</div>

					<div class="form-group">
						{{ Form::label('confirm_password', Lang::get('auth.confirm_password')) }}
						{{ Form::password('confirm_password', array('class' => 'form-control')) }}
					</div>

					<a href="{{ url('auth/login') }}" class="btn btn-link pull-right" tabindex="-1">
						{{ Lang::get('auth.already_have_account') }}
					</a>

					{{
						Form::submit(Lang::get('auth.register'), array(
							'name'  => '_register',
							'class' => 'btn btn-primary'
						))
					}}
				</fieldset>
			</div>
		</div>
	</div>

	{{ Form::close() }}
@stop
