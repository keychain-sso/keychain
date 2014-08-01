@extends('common.page')

@section('body')
	<h3 class="spacer-none-top">
		<a href="{{ url() }}" class="back" title="{{ Lang::get('global.return_index') }}" data-toggle="tooltip">
			<span class="glyphicon glyphicon-chevron-left"></span>
		</a>

		{{ $title }}
	</h3>

	{{
		Form::open(array(
			'action' => 'AdminController@postPermission',
			'role'   => 'form'
		))
	}}

	@include('common.alerts')

	<fieldset id="permission-add">
		<legend>
			<span class="glyphicon glyphicon-import"></span>
			{{ Lang::get('permission.add_permissions') }}
		</legend>

		<div class="row">
			<div id="permission-subject" class="col-md-6 col-lg-4">
				<div class="form-group has-feedback has-justification">
					{? $type = isset($subject) ? strtolower(get_class($subject)) : 'user' ?}

					{{ Form::label('subject', Lang::get('permission.user_group'), array('class' => 'control-label')) }}

					<div class="input-group">
						<div class="input-group-btn">
							<button type="button" class="btn btn-default dropdown-toggle" @if(isset($subject)) disabled="disabled" @endif
									data-toggle="dropdown" data-select="[name=subject_type]" data-focus="[name=subject]">
								{{ Lang::get("global.{$type}") }}
								<span class="caret"></span>
							</button>

							<ul class="dropdown-menu">
								<li><a href="#" data-value="3">{{ Lang::get('global.user') }}</a></li>
								<li><a href="#" data-value="4">{{ Lang::get('global.group') }}</a></li>
							</ul>
						</div>

						{{
							Form::text('subject', isset($subject) ? $subject->name : null, array(
								'class'       => 'form-control',
								'disabled'    => isset($subject) ? 'disabled' : null,
								'data-toggle'  => 'autocomplete',
								'data-icon'    => '#search-icon-subject',
								'data-target'  => '[name=subject_id]',
								'data-url'     => url('user/search/list'),
								'autocomplete' => 'off',
							))
						}}
					</div>

					<span id="search-icon-subject" class="glyphicon glyphicon-search text-muted form-control-feedback"></span>

					{{ Form::hidden('subject_id', isset($subject) ? $subject->id : null) }}
					{{ Form::hidden('subject_type', isset($subject) && get_class($subject) == 'Group' ? ACLTypes::GROUP : ACLTypes::USER) }}
				</div>
			</div>

			<div class="col-md-6 col-lg-4">
				<div class="form-group">
					{{ Form::label('flag', Lang::get('permission.permission')) }}
					{{ Form::select('flag', $flags, null, array('class' => 'form-control')) }}
				</div>
			</div>

			@if (isset($fields))
				<div id="permission-field" class="col-md-6 col-lg-4 @if ( ! str_contains(array_keys($flags)[0], 'field')) hide @endif">
					<div class="form-group">
						{{ Form::label('field', Lang::get('permission.field')) }}

						{{
							Form::select('field', $fields, isset($field) ? $field->id : null, array(
								'class'    => 'form-control',
								'disabled' => isset($field) ? 'disabled' : null,
							))
						}}
					</div>
				</div>
			@endif

			<div id="permission-object" class="col-md-6 col-lg-4 hide">
				<div class="form-group has-feedback has-justification">
					{{ Form::label('object', Lang::get('permission.scope'), array('class' => 'control-label')) }}

					<div class="input-group">
						<div class="input-group-btn">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" data-select="[name=object_type]"
									data-focus="[name=object]">
								{{ Lang::get('global.user') }}
								<span class="caret"></span>
							</button>

							<ul class="dropdown-menu">
								<li><a href="#" data-value="1">{{ Lang::get('permission.self') }}</a></li>
								<li><a href="#" data-value="2">{{ Lang::get('permission.global') }}</a></li>
								<li><a href="#" data-value="3">{{ Lang::get('global.user') }}</a></li>
								<li><a href="#" data-value="4">{{ Lang::get('global.group') }}</a></li>
							</ul>
						</div>

						{{
							Form::text('object', null, array(
								'class'        => 'form-control',
								'data-toggle'  => 'autocomplete',
								'data-icon'    => '#search-icon-object',
								'data-target'  => '[name=object_id]',
								'data-url'     => url('user/search/list'),
								'autocomplete' => 'off',
							))
						}}
					</div>

					<span id="search-icon-object" class="glyphicon glyphicon-search text-muted form-control-feedback"></span>

					{{ Form::hidden('object_id', null) }}
					{{ Form::hidden('object_type', ACLTypes::USER) }}
				</div>
			</div>
		</div>

		{{ Form::submit(Lang::get('global.submit'), array('class' => 'btn btn-primary spacer-lg-bottom')) }}
	</fieldset>

	@include('permission.list')

	{{ Form::close() }}
@stop
