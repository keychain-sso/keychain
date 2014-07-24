@extends('common.page')

@section('body')
	<h3 class="spacer-none-top">
		<a href="{{ URL::previous() }}" class="back" title="{{ Lang::get('global.return_previous') }}" data-toggle="tooltip">
			<span class="glyphicon glyphicon-chevron-left"></span>
		</a>

		{{ $title }}
	</h3>

	{{ Form::open(array('role' => 'form')) }}

	@include('acl.editor')

	{{ Form::close() }}
@stop
