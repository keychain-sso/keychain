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
							{{ Form::label('email', Lang::get('global.email_address')) }}

							{{
								Form::text('email', NULL, array(
									'class'     => 'form-control',
									'autofocus' => 'autofocus',
									'maxlength' => 50,
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

						<a href="{{ url('auth/register') }}" class="btn btn-link pull-right">
							{{ Lang::get('auth.create_account') }}
						</a>

						{{
							Form::submit(Lang::get('auth.login'), array(
								'name'  => '_login',
								'class' => 'btn btn-primary'
							))
						}}

						<a href="{{ url('auth/forgot') }}" class="btn btn-link">
							{{ Lang::get('auth.forgot_password') }}
						</a>
					</fieldset>
				</div>
			</div>
		</div>

		{{ Form::close() }}
@stop
