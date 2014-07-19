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
				@yield('form')
			</div>
	</div>

	{{ Form::close() }}
@stop
