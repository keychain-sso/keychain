@extends('common.page')

@section('body')
	<div class="row">
		<div class="col-sm-12">
			<div class="jumbotron text-center">
				@if ($type == 'success')
					<span class="glyphicon glyphicon-ok-sign text-xl text-success"></span>
				@elseif ($type == 'danger')
					<span class="glyphicon glyphicon-remove-sign text-xl text-danger"></span>
				@elseif ($type == 'info')
					<span class="glyphicon glyphicon-info-sign text-xl text-info"></span>
				@endif

				<p>{{ $message }}</p>

				@if (isset($return))
					<small>{{ $return }}</small>
				@endif
			</div>
		</div>
	</div>
@stop
