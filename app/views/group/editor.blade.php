{{ Form::open(array('role' => 'form')) }}

<div class="modal-body">
	@include('group.header')

	<fieldset>
		<legend>
			<span class="glyphicon glyphicon-list"></span>
			{{ Lang::get('group.general_info') }}
		</legend>

		<div class="form-group">
			{{ Form::label('name', Lang::get('group.group_name'), array('class' => 'control-label')) }}
			{{ Form::text('name', $group->name, array('class' => 'form-control')) }}
		</div>

		<div class="form-group">
			{{ Form::label('description', Lang::get('group.description'), array('class' => 'control-label')) }}

			{{
				Form::textarea('description', $group->description, array(
					'class' => 'form-control',
					'rows'  => 3,
				))
			}}
		</div>
	</fieldset>

	<fieldset>
		<legend>
			<span class="glyphicon glyphicon-bullhorn"></span>
			{{ Lang::get('group.membership_preferences') }}
		</legend>

		<div class="form-group">
			{{ Form::label('type', Lang::get('group.group_type'), array('class' => 'control-label')) }}

			<div class="radio">
				<label>
					{{ Form::radio('type', GroupTypes::OPEN, $group->type == GroupTypes::OPEN) }}
					{{ Lang::get('group.open') }}

					<span title="{{ Lang::get('group.open_exp') }}" data-toggle="tooltip" data-placement="right">
						<span class="glyphicon glyphicon-question-sign text-info"></span>
					</span>
				</label>
			</div>

			<div class="radio">
				<label>
					{{ Form::radio('type', GroupTypes::REQUEST, $group->type == GroupTypes::REQUEST) }}
					{{ Lang::get('group.request_only') }}

					<span title="{{ Lang::get('group.request_only_exp') }}" data-toggle="tooltip" data-placement="right">
						<span class="glyphicon glyphicon-question-sign text-info"></span>
					</span>
				</label>
			</div>

			<div class="radio">
				<label>
					{{ Form::radio('type', GroupTypes::CLOSED, $group->type == GroupTypes::CLOSED) }}
					{{ Lang::get('group.closed') }}

					<span title="{{ Lang::get('group.closed_exp') }}" data-toggle="tooltip" data-placement="right">
						<span class="glyphicon glyphicon-question-sign text-info"></span>
					</span>
				</label>
			</div>
		</div>

		<div class="form-group">
			{{ Form::label('type', Lang::get('group.notification'), array('class' => 'control-label')) }}

			<div class="radio">
				<label>
					{{ Form::radio('notify', Flags::YES, $group->notify == Flags::YES) }}
					{{ Lang::get('group.notify_enable') }}

					<span title="{{ Lang::get('group.notify_enable_exp') }}" data-toggle="tooltip" data-placement="right">
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

		<div class="form-group">
			{{ Form::label('type', Lang::get('group.user_compliance'), array('class' => 'control-label')) }}

			<div class="checkbox">
				<label>
					{{ Form::checkbox('auto_join', Flags::YES, $group->auto_join == Flags::YES) }}
					{{ Lang::get('group.auto_add_users') }}

					<span title="{{ Lang::get('group.auto_add_users_exp') }}" data-toggle="tooltip" data-placement="right">
						<span class="glyphicon glyphicon-question-sign text-info"></span>
					</span>
				</label>
			</div>
		</div>
	</fieldset>
</div>

<div class="modal-footer">
	{{ Form::hidden('hash', $group->hash) }}
	{{ Form::submit(Lang::get('global.save'), array('class' => 'btn btn-primary')) }}

	@if (empty($group->id))
		<a href="{{ url('group/list') }}" class="btn btn-default">
	@else
		<a href="{{ url("group/view/{$group->hash}") }}" class="btn btn-default">
	@endif
		{{ Lang::get('global.close') }}
	</a>
</div>

{{ Form::close() }}
