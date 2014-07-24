@include('common.alerts')

@if ($show->site && count($acl->site) > 0)
	<fieldset>
		<legend>
			<span class="glyphicon glyphicon-comment"></span>
			{{ Lang::get('global.site_permissions') }}
		</legend>

		<table class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>{{ Lang::get('global.permission') }}</th>
					<th width="30"></th>
				</tr>
			</thead>

			<tbody>
				@foreach ($acl->site as $permission)
					<tr>
						<td>{{ Lang::get("permissions.{$permission->access}") }}</td>

						<td>
							<a href="{{ url("admin/permissions/remove/{$permission->id}") }}"
							   title="{{ Lang::get('global.remove') }}" data-toggle="tooltip">
								<span class="glyphicon glyphicon-remove text-danger"></span>
							</a>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</fieldset>
@endif

<fieldset>
	<legend>
		@if ($show->site)
			<span class="glyphicon glyphicon-record"></span>
			{{ Lang::get('global.scope_permissions') }}
		@else
			<span class="glyphicon glyphicon-list"></span>
			{{ Lang::get('global.permissions') }}
		@endif
	</legend>

	<table class="table table-bordered table-striped">
		<thead>
			<tr>
				@if ($show->subjects)
					<th>{{ Lang::get('global.user_group') }}</th>
				@endif

				<th>{{ Lang::get('global.permission') }}</th>

				@if ($show->objects)
					<th>{{ Lang::get('global.scope') }}</th>
				@endif

				<th width="30"></th>
			</tr>
		</thead>

		<tbody>
			@foreach ($acl->scope as $permission)
				<tr>
					@if ($show->subjects)
						<td>
							@if ($permission->subject_type == ACLTypes::USER)
								{? $subject = $acl->users->find($permission->subject_id) ?}

								<span class="glyphicon glyphicon-user"></span>
								<a href="{{ url("user/view/{$subject->hash}") }}">{{{ $subject->name }}}</a>
							@elseif ($permission->subject_type == ACLTypes::GROUP)
								{? $subject = $acl->groups->find($permission->subject_id) ?}

								<span class="glyphicon glyphicon-th-large"></span>
								<a href="{{ url("group/view/{$subject->hash}") }}">{{ $subject->name }}</a>
							@endif
						</td>
					@endif

					@if ($permission->field_id > 0)
						<td>
							@if ($show->fields)
								{{
									Lang::get("permissions.{$permission->access}", array(
										'field' => ": <em>".$acl->fields->find($permission->field_id)->name.'</em>',
									))
								}}
							@else
								{{
									Lang::get("permissions.{$permission->access}", array(
										'field' => Lang::get('global.space_data'),
									))
								}}
							@endif
						</td>
					@else
						<td>{{ Lang::get("permissions.{$permission->access}") }}</td>
					@endif

					@if ($show->objects)
						<td>
							@if ($permission->object_type == ACLTypes::ALL)
								<span class="glyphicon glyphicon-asterisk"></span>
								{{ Lang::get('global.global') }}
							@elseif ($permission->object_type == ACLTypes::SELF)
								<span class="glyphicon glyphicon-bookmark"></span>
								{{ Lang::get('global.self') }}
							@elseif ($permission->object_type == ACLTypes::USER)
								{? $object = $acl->users->find($permission->object_id) ?}

								<span class="glyphicon glyphicon-user"></span>
								<a href="{{ url("user/view/{$object->hash}") }}">{{{ $object->name }}}</a>
							@elseif ($permission->object_type == ACLTypes::GROUP)
								{? $object = $acl->groups->find($permission->object_id) ?}

								<span class="glyphicon glyphicon-th-large"></span>
								<a href="{{ url("group/view/{$object->hash}") }}">{{ $object->name }}</a>
							@endif
						</td>
					@endif

					<td>
						<a href="{{ url("admin/permissions/remove/{$permission->id}") }}"
						   title="{{ Lang::get('global.remove') }}" data-toggle="tooltip">
							<span class="glyphicon glyphicon-remove text-danger"></span>
						</a>
					</td>
				</tr>
			@endforeach

			@if (count($acl->scope) == 0)
				<tr>
					<td colspan="3">{{ Lang::get('global.no_permissions') }}</td>
				</tr>
			@endif
		</tbody>
	</table>
</fieldset>

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
