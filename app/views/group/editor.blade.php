{{
	Form::open(array(
		'action' => 'GroupController@postEdit',
		'role'   => 'form',
	))
}}

<div class="modal-body">
	@include('group.header')

	<div class="form-group">
		{{
			Form::label('name', Lang::get('group.group_name'), array(
				'class' => 'control-label'
			))
		}}

		{{
			Form::text('name', $group->name, array(
				'class' => 'form-control',
			))
		}}
	</div>

	<div class="form-group">
		{{
			Form::label('description', Lang::get('group.description'), array(
				'class' => 'control-label'
			))
		}}

		{{
			Form::textarea('description', $group->description, array(
				'class' => 'form-control',
				'rows'  => 3,
			))
		}}
	</div>

	<div class="form-group">
		{{
			Form::label('type', Lang::get('group.group_type'), array(
				'class' => 'control-label'
			))
		}}

		<div class="radio">
			<label>
				{{ Form::radio('type', GroupTypes::OPEN, $group->type == GroupTypes::OPEN) }}
				{{ Lang::get('group.open') }}

				<span data-toggle="popover" data-content="{{ Lang::get('group.open_exp') }}">
					<span class="glyphicon glyphicon-question-sign text-info"></span>
				</span>
			</label>
		</div>

		<div class="radio">
			<label>
				{{ Form::radio('type', GroupTypes::REQUEST, $group->type == GroupTypes::REQUEST) }}
				{{ Lang::get('group.request_only') }}

				<span data-toggle="popover" data-content="{{ Lang::get('group.request_only_exp') }}">
					<span class="glyphicon glyphicon-question-sign text-info"></span>
				</span>
			</label>
		</div>

		<div class="radio">
			<label>
				{{ Form::radio('type', GroupTypes::CLOSED, $group->type == GroupTypes::CLOSED) }}
				{{ Lang::get('group.closed') }}

				<span data-toggle="popover" data-content="{{ Lang::get('group.closed_exp') }}">
					<span class="glyphicon glyphicon-question-sign text-info"></span>
				</span>
			</label>
		</div>
	</div>

	<div class="form-group">
		{{
			Form::label('type', Lang::get('group.notification'), array(
				'class' => 'control-label'
			))
		}}

		<div class="radio">
			<label>
				{{ Form::radio('notify', Flags::YES, $group->notify == Flags::YES) }}
				{{ Lang::get('group.notify_enable') }}

				<span data-toggle="popover" data-content="{{ Lang::get('group.notify_enable_exp') }}">
					<span class="glyphicon glyphicon-question-sign text-info"></span>
				</span>
			</label>
		</div>

		<div class="radio">
			<label>
				{{ Form::radio('notify', Flags::NO, $group->notify == Flags::NO) }}
				{{ Lang::get('group.notify_disable') }}
			</label>
		</div>
	</div>
</div>

<div class="modal-footer">
	{{ Form::hidden('hash', $group->hash) }}

	{{
		Form::submit(Lang::get('global.save'), array(
			'name'     => '_save',
			'class'    => 'btn btn-primary',
		))
	}}

	<a href="{{ url('group/view/'.$group->hash) }}" class="btn btn-default">
		{{ Lang::get('global.close') }}
	</a>
</div>

{{ Form::close() }}
