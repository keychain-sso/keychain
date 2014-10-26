@extends('common.page')

@section('body')
	<div class="row">
		<div class="col-xs-12">
			@if ($manager->field)
				<a href="{{ url('field/create') }}" class="btn btn-default pull-right spacer-lg-bottom">
					<span class="glyphicon glyphicon-plus"></span>
					{{ Lang::get('field.create_field') }}
				</a>
			@endif

			<h3>
				<a href="{{ url() }}" class="back" title="{{ Lang::get('global.return_index') }}" data-toggle="tooltip">
					<span class="glyphicon glyphicon-chevron-left"></span>
				</a>

				{{ $title }}
			</h3>
		</div>
	</div>

	@if ( ! isset($modal))
		@include('common.alerts')
	@endif

	<div class="row">
		<div class="col-md-4">
			<h4>{{ Lang::get('global.basic_info') }}</h4>

			<ul class="list-group">
				@foreach ($fields as $fieldBasic)
					@if ($fieldBasic->category == FieldCategories::BASIC)
						{? $basic = true ?}

						<li class="list-group-item">
							<div class="pull-right">
								@if ($fieldBasic->order != $min[FieldCategories::BASIC])
									<a href="{{ url("field/move/up/{$fieldBasic->id}") }}" title="{{ Lang::get('field.move_up') }}"
									   data-toggle="tooltip"><span class="glyphicon glyphicon-chevron-up"></span></a>
								@else
									<span class="glyphicon glyphicon-chevron-up text-muted"></span>
								@endif

								@if ($fieldBasic->order != $max[FieldCategories::BASIC])
									<a href="{{ url("field/move/down/{$fieldBasic->id}") }}" title="{{ Lang::get('field.move_down') }}"
									   data-toggle="tooltip"><span class="glyphicon glyphicon-chevron-down"></span></a>
								@else
									<span class="glyphicon glyphicon-chevron-down text-muted"></span>
								@endif

								<a href="#" data-toggle="dropdown"><span class="glyphicon glyphicon-cog"></span></a>

								<ul class="dropdown-menu">
									<li>
										<a href="{{ url("field/permissions/{$fieldBasic->id}") }}">
											{{ Lang::get('global.permissions') }}
										</a>
									</li>

									<li>
										<a href="{{ url("field/edit/{$fieldBasic->id}") }}">
											{{ Lang::get('field.edit') }}
										</a>
									</li>

									<li>
										<a href="#" data-toggle="confirm" data-href="{{ url("field/delete/{$fieldBasic->id}") }}"
										   data-prompt="{{ Lang::get('global.click_again') }}"
										   data-wait="{{ Lang::get('global.please_wait') }}">
											{{ Lang::get('field.delete') }}
										</a>
									</li>
								</ul>
							</div>

							{{{ $fieldBasic->name }}}
						</li>
					@endif
				@endforeach

				@if ( ! isset($basic))
					<li class="list-group-item">{{ Lang::get('field.no_fields') }}</li>
				@endif
			</ul>
		</div>

		<div class="col-md-4">
			<h4>{{ Lang::get('global.contact_info') }}</h4>

			<ul class="list-group">
				@foreach ($fields as $fieldContact)
					@if ($fieldContact->category == FieldCategories::CONTACT)
						{? $contact = true ?}

						<li class="list-group-item">
							<div class="pull-right">
								@if ($fieldContact->order != $min[FieldCategories::CONTACT])
									<a href="{{ url("field/move/up/{$fieldContact->id}") }}" title="{{ Lang::get('field.move_up') }}"
									   data-toggle="tooltip"><span class="glyphicon glyphicon-chevron-up"></span></a>
								@else
									<span class="glyphicon glyphicon-chevron-up text-muted"></span>
								@endif

								@if ($fieldContact->order != $max[FieldCategories::CONTACT])
									<a href="{{ url("field/move/down/{$fieldContact->id}") }}" title="{{ Lang::get('field.move_down') }}"
									   data-toggle="tooltip"><span class="glyphicon glyphicon-chevron-down"></span></a>
								@else
									<span class="glyphicon glyphicon-chevron-down text-muted"></span>
								@endif

								<a href="#" data-toggle="dropdown"><span class="glyphicon glyphicon-cog"></span></a>

								<ul class="dropdown-menu">
									<li>
										<a href="{{ url("field/permissions/{$fieldContact->id}") }}">
											{{ Lang::get('global.permissions') }}
										</a>
									</li>

									<li>
										<a href="{{ url("field/edit/{$fieldContact->id}") }}">
											{{ Lang::get('field.edit') }}
										</a>
									</li>

									<li>
										<a href="#" data-toggle="confirm" data-href="{{ url("field/delete/{$fieldContact->id}") }}"
										   data-prompt="{{ Lang::get('global.click_again') }}"
										   data-wait="{{ Lang::get('global.please_wait') }}">
											{{ Lang::get('field.delete') }}
										</a>
									</li>
								</ul>
							</div>

							{{{ $fieldContact->name }}}
						</li>
					@endif
				@endforeach

				@if ( ! isset($contact))
					<li class="list-group-item">{{ Lang::get('field.no_fields') }}</li>
				@endif
			</ul>
		</div>

		<div class="col-md-4">
			<h4>{{ Lang::get('global.other_details') }}</h4>

			<ul class="list-group">
				@foreach ($fields as $fieldOther)
					@if ($fieldOther->category == FieldCategories::OTHER)
						{? $other = true ?}

						<li class="list-group-item">
							<div class="pull-right">
								@if ($fieldOther->order != $min[FieldCategories::OTHER])
									<a href="{{ url("field/move/up/{$fieldOther->id}") }}" title="{{ Lang::get('field.move_up') }}"
									   data-toggle="tooltip"><span class="glyphicon glyphicon-chevron-up"></span></a>
								@else
									<span class="glyphicon glyphicon-chevron-up text-muted"></span>
								@endif

								@if ($fieldOther->order != $max[FieldCategories::OTHER])
									<a href="{{ url("field/move/down/{$fieldOther->id}") }}" title="{{ Lang::get('field.move_down') }}"
									   data-toggle="tooltip"><span class="glyphicon glyphicon-chevron-down"></span></a>
								@else
									<span class="glyphicon glyphicon-chevron-down text-muted"></span>
								@endif

								<a href="#" data-toggle="dropdown"><span class="glyphicon glyphicon-cog"></span></a>

								<ul class="dropdown-menu">
									<li>
										<a href="{{ url("field/permissions/{$fieldOther->id}") }}">
											{{ Lang::get('global.permissions') }}
										</a>
									</li>

									<li>
										<a href="{{ url("field/edit/{$fieldOther->id}") }}">
											{{ Lang::get('field.edit') }}
										</a>
									</li>

									<li>
										<a href="#" data-toggle="confirm" data-href="{{ url("field/delete/{$fieldOther->id}") }}"
										   data-prompt="{{ Lang::get('global.click_again') }}"
										   data-wait="{{ Lang::get('global.please_wait') }}">
											{{ Lang::get('field.delete') }}
										</a>
									</li>
								</ul>
							</div>

							{{{ $fieldOther->name }}}
						</li>
					@endif
				@endforeach

				@if ( ! isset($other))
					<li class="list-group-item">{{ Lang::get('field.no_fields') }}</li>
				@endif
			</ul>
		</div>
	</div>
@stop
