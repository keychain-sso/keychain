@extends('common.page')

@section('body')
	<div class="row">
		<div class="col-xs-12">
			<div class="jumbotron text-center">
				@if ($type == NoticeTypes::SUCCESS)
					<span class="glyphicon glyphicon-ok-sign text-xl text-success"></span>
				@elseif ($type == NoticeTypes::ERROR)
					<span class="glyphicon glyphicon-remove-sign text-xl text-danger"></span>
				@elseif ($type == NoticeTypes::INFORMATION)
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
