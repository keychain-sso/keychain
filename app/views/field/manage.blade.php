@extends('common.page')

@section('body')
	<div class="row">
		<div class="col-xs-12">
			@if ($manager->field)
				<a href="{{ url('field/create') }}" class="btn btn-default pull-right">
					<span class="glyphicon glyphicon-plus"></span>
					{{ Lang::get('field.create_field') }}
				</a>
			@endif

			<h3 class="spacer-none-top">
				<a href="{{ url() }}" class="back" title="{{ Lang::get('global.return_index') }}" data-toggle="tooltip">
					<span class="glyphicon glyphicon-chevron-left"></span>
				</a>

				{{ $title }}
			</h3>

			@if ( ! isset($modal))
				@include('common.alerts')
			@endif
		</div>
	</div>

	<div class="row">
		<div class="col-md-4">
			<h4>{{ Lang::get('global.basic_info') }}</h4>

			<ul class="list-group">
				@foreach ($fields as $field)
					@if ($field->category == FieldCategories::BASIC)
						{? $basic = true ?}

						<li class="list-group-item">
							<div class="pull-right">
								<a href="{{ url("admin/field/up/{$field->id}") }}" title="{{ Lang::get('field.move_up') }}" data-toggle="tooltip">
									<span class="glyphicon glyphicon-chevron-up"></span>
								</a>

								<a href="{{ url("admin/field/down/{$field->id}") }}" title="{{ Lang::get('field.move_down') }}" data-toggle="tooltip">
									<span class="glyphicon glyphicon-chevron-down"></span>
								</a>
							</div>

							{{{ $field->name }}}
						</li>
					@endif
				@endforeach
			</ul>
		</div>
	</div>
@stop
