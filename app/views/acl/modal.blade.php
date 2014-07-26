{{
	Form::open(array(
		'action' => 'AdminController@postPermissions',
		'role'   => 'form'
	))
}}

<div class="modal-body">
	<nav class="navbar navbar-default navbar-static-top navbar-modal">
		<div class="container-fluid">
			<a href="{{ $return }}" class="close">&times;</a>

			<div class="text-center">
				<h3 class="spacer-none-top">{{ $title }}</h3>
			</div>
		</div>
	</nav>

	@include('common.alerts')

	<fieldset id="permission-add">
		<legend>
			<span class="glyphicon glyphicon-import"></span>
			{{ Lang::get('global.add_permissions') }}
		</legend>

		<div id="permission-subject" class="form-group has-feedback">
			{? $type = isset($subject) ? strtolower(get_class($subject)) : 'user' ?}

			{{ Form::label('subject', Lang::get('global.user_group'), array('class' => 'control-label')) }}

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
			{{ Form::hidden('subject_type', get_class($subject) == 'Group' ? ACLTypes::GROUP : ACLTypes::USER) }}
		</div>

		<div class="form-group">
			{{ Form::label('flag', Lang::get('global.permission')) }}
			{{ Form::select('flag', $flags, null, array('class' => 'form-control')) }}
		</div>

		@if (isset($fields))
			<div id="permission-field" class="form-group @if ( ! str_contains(array_keys($flags)[0], 'field')) hide @endif">
				{{ Form::label('field', Lang::get('global.field')) }}

				{{
					Form::select('field', $fields, isset($field) ? $field->id : null, array(
						'class'    => 'form-control',
						'disabled' => isset($field) ? 'disabled' : null,
					))
				}}
			</div>
		@endif

		<div id="permission-object" class="form-group has-feedback hide">
			{{ Form::label('object', Lang::get('global.scope'), array('class' => 'control-label')) }}

			<div class="input-group">
				<div class="input-group-btn">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" data-select="[name=object_type]"
					        data-focus="[name=object]">
						{{ Lang::get('global.user') }}
						<span class="caret"></span>
					</button>

					<ul class="dropdown-menu">
						<li><a href="#" data-value="1">{{ Lang::get('global.self') }}</a></li>
						<li><a href="#" data-value="2">{{ Lang::get('global.global') }}</a></li>
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
	</fieldset>

	{{
		Form::submit(Lang::get('global.submit'), array(
			'name'  => '_submit',
			'class' => 'btn btn-primary',
		))
	}}

	@include('acl.list')
</div>

<div class="modal-footer">
	{{ $token }}

	{{
		Form::submit(Lang::get('global.save'), array(
			'name'     => '_save',
			'class'    => 'btn btn-primary',
		))
	}}

	<a href="{{ $return }}" class="btn btn-default">
		{{ Lang::get('global.close') }}
	</a>
</div>

{{ Form::close() }}
