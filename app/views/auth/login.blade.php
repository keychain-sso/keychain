@extends('common.page')

@section('body')
	<section id="login">
		{{
			Form::open(array(
				'autocomplete'   => 'off',
				'role'           => 'form'
			))
		}}

		<div class="row">
			<div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
				<fieldset>
					<legend>
						{{
							Lang::get('auth.log_into', array(
								'site' => Config::get('app.title'),
							))
						}}
					</legend>

					@include('common.alerts')

					<div class="form-group">
						{{ Form::label('email', Lang::get('global.email_address')) }}

						{{
							Form::text('email', NULL, array(
								'class'     => 'form-control',
								'maxlength' => 50
							))
						}}
					</div>

					<div class="form-group">
						{{ Form::label('password', Lang::get('global.password')) }}

						{{
							Form::password('password', array(
								'class' => 'form-control'
							))
						}}
					</div>

					<div class="checkbox">
						<label>
							{{
								Form::checkbox('remember', NULL, NULL, array(
									'id' => 'remember'
								))
							}}

							{{ Lang::get('auth.remember') }}
						</label>
					</div>

					{{
						Form::submit(Lang::get('auth.login'), array(
							'name'  => '_login',
							'class' => 'btn btn-primary'
						))
					}}
				</fieldset>
			</div>
		</div>

		{{ Form::close() }}
@stop
