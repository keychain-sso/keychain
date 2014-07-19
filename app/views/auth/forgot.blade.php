@extends('common.page')

@section('body')
	{{ Form::open(array('role' => 'form')) }}

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
				</fieldset>
			</div>
		</div>
	</div>

	{{ Form::close() }}
@stop
