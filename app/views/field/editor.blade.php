{{ Form::open(array('role' => 'form')) }}

<div class="modal-body">
	<nav class="navbar navbar-default navbar-static-top navbar-modal">
		<div class="container-fluid">
			<a href="{{ url('user/list') }}" class="close">&times;</a>

			<div class="text-center">
				<h3>{{ $title }}</h3>
			</div>
		</div>
	</nav>

	@include('common.alerts')

	<div class="form-group">
		{{ Form::label('name', Lang::get('global.name'), array('class' => 'control-label')) }}
		{{ Form::text('name', $field->name, array('class' => 'form-control')) }}
	</div>

	<div class="form-group">
		{{ Form::label('type', Lang::get('field.control_type'), array('class' => 'control-label')) }}

		{{
			Form::select('type', $types, $field->type, array(
				'class'    => 'form-control',
				'disabled' => empty($field->id) ? null : 'disabled',
			))
		}}
	</div>

	<div class="form-group">
		{{ Form::label('category', Lang::get('field.category'), array('class' => 'control-label')) }}

		<div class="radio">
			<label>
				{{ Form::radio('category', FieldCategories::BASIC, $field->category == FieldCategories::BASIC) }}
				{{ Lang::get('global.basic_info') }}
			</label>
		</div>

		<div class="radio">
			<label>
				{{ Form::radio('category', FieldCategories::CONTACT, $field->category == FieldCategories::CONTACT) }}
				{{ Lang::get('global.contact_info') }}
			</label>
		</div>

		<div class="radio">
			<label>
				{{ Form::radio('category', FieldCategories::OTHER, $field->category == FieldCategories::OTHER) }}
				{{ Lang::get('global.other_details') }}
			</label>
		</div>
	</div>

	<div class="form-group">
		{{ Form::label('options', Lang::get('field.options'), array('class' => 'control-label')) }}

		<span title="{{ Lang::get('field.options_exp') }}" data-toggle="tooltip" data-placement="right">
			<span class="glyphicon glyphicon-question-sign text-info"></span>
		</span>

		{{
			Form::textarea('options', $field->options, array(
				'class'    => 'form-control',
				'rows'     => 5,
				'disabled' => empty($field->id) || ! empty($field->options) ? null : 'disabled',
			))
		}}
	</div>

	<div class="form-group">
		{{ Form::label('required', Lang::get('field.required'), array('class' => 'control-label')) }}

		<div class="radio">
			<label>
				{{ Form::radio('required', Flags::YES, $field->required == Flags::YES) }}
				{{ Lang::get('global.yes') }}
			</label>
		</div>

		<div class="radio">
			<label>
				{{ Form::radio('required', Flags::NO, $field->required == Flags::NO) }}
				{{ Lang::get('global.no') }}
			</label>
		</div>
	</div>
</div>

<div class="modal-footer">
	{{ Form::hidden('id', $field->id) }}
	{{ Form::submit(Lang::get('global.save'), array('class' => 'btn btn-primary')) }}

	<a href="{{ url('field') }}" class="btn btn-default">
		{{ Lang::get('global.close') }}
	</a>
</div>

{{ Form::close() }}
