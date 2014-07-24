<div class="modal-body">
	<nav class="navbar navbar-default navbar-static-top navbar-modal">
		<div class="container-fluid">
			<a href="{{ $return }}" class="close">&times;</a>

			<div class="text-center">
				<h3 class="spacer-none-top">{{ $title }}</h3>
			</div>
		</div>
	</nav>

	{{ Form::open(array('role' => 'form')) }}

	<fieldset>
		<legend>
			<span class="glyphicon glyphicon-import"></span>
			{{ Lang::get('global.add_permissions') }}
		</legend>

		<div class="form-group">
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
						<li><a href="#" data-value="user">{{ Lang::get('global.user') }}</a></li>
						<li><a href="#" data-value="group">{{ Lang::get('global.group') }}</a></li>
					</ul>
				</div>

				{{
					Form::text('subject', isset($subject) ? $subject->name : null, array(
						'class'    => 'form-control',
						'disabled' => isset($subject) ? 'disabled' : null,
					))
				}}
			</div>

			{{ Form::hidden('subject_id', isset($subject) ? $subject->id : null) }}
			{{ Form::hidden('subject_type', $type) }}
		</div>

		<div class="form-group">
			{{ Form::label('flag', Lang::get('global.permission')) }}
			{{ Form::select('flag', $flags, null, array('class' => 'form-control')) }}
		</div>

		@if (isset($fields))
			<div class="form-group">
				{{ Form::label('field', Lang::get('global.field')) }}

				{{
					Form::select('field', $fields, isset($field) ? $field->id : null, array(
						'class'    => 'form-control',
						'disabled' => isset($field) ? 'disabled' : null,
					))
				}}
			</div>
		@endif

		<div class="form-group">
			{{ Form::label('object', Lang::get('global.scope'), array('class' => 'control-label')) }}

			<div class="input-group">
				<div class="input-group-btn">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" data-select="[name=object_type]"
					        data-focus="[name=object]">
						{{ Lang::get('global.user') }}
						<span class="caret"></span>
					</button>

					<ul class="dropdown-menu">
						<li><a href="#" data-value="user">{{ Lang::get('global.user') }}</a></li>
						<li><a href="#" data-value="group">{{ Lang::get('global.group') }}</a></li>
					</ul>
				</div>

				{{ Form::text('object', null, array('class' => 'form-control')) }}
			</div>

			{{ Form::hidden('object_id', null) }}
			{{ Form::hidden('object_type', 'user') }}
		</div>
	</fieldset>

	{{
		Form::submit(Lang::get('global.submit'), array(
			'name'  => '_submit',
			'class' => 'btn btn-primary',
		))
	}}

	@include('acl.list')

	{{ Form::close() }}
</div>
