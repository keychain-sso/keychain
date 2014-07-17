<div class="row">
	<div class="col-xs-12">
		@if ( ! empty($info))
			<div class="alert alert-info">
				<button type="button" class="close" data-dismiss="alert">&times;</button>

				@if (is_array($info))
					@foreach ($info as $message)
						{{ $message }}
					@endforeach
				@else
					{{ $info }}
				@endif
			</div>
		@endif

		@if ( ! empty($success))
			<div class="alert alert-success">
				<button type="button" class="close" data-dismiss="alert">&times;</button>

				@if (is_array($success))
					@foreach ($success as $message)
						{{ $message }}
					@endforeach
				@else
					{{ $success }}
				@endif
			</div>
		@endif

		@if ( ! empty($error))
			<div class="alert alert-danger">
				<button type="button" class="close" data-dismiss="alert">&times;</button>

				@if (is_array($error))
					@foreach ($error as $message)
						{{ $message }}
					@endforeach
				@else
					{{ $error }}
				@endif
			</div>
		@endif
	</div>
</div>
