@if ($show->site)
	<fieldset>
		<legend>
			<span class="glyphicon glyphicon-retweet"></span>
			{{ Lang::get('permission.site_permissions') }}
		</legend>

		<table class="table table-bordered table-striped">
			<thead>
				<tr>
					@if ($show->subjects)
						<th>{{ Lang::get('permission.user_group') }}</th>
					@endif

					<th>{{ Lang::get('permission.permission') }}</th>
					<th width="30"></th>
				</tr>
			</thead>

			<tbody>
				@foreach ($acl->site as $permission)
					<tr>
						@if ($show->subjects)
							<td>
								@if ($permission->subject_type == ACLTypes::USER)
									{? $subject = $acl->users->subjects->find($permission->subject_id) ?}

									<span class="glyphicon glyphicon-user"></span>
									<a href="{{ url("user/view/{$subject->hash}") }}">{{{ $subject->name }}}</a>
								@elseif ($permission->subject_type == ACLTypes::GROUP)
									{? $subject = $acl->groups->subjects->find($permission->subject_id) ?}

									<span class="glyphicon glyphicon-th-large"></span>
									<a href="{{ url("group/view/{$subject->hash}") }}">{{ $subject->name }}</a>
								@endif
							</td>
						@endif

						<td>{{ Lang::get("flag.{$permission->flag}") }}</td>

						<td>
							<a href="{{ url("admin/permissions/remove/{$permission->id}") }}"
							   title="{{ Lang::get('global.remove') }}" data-toggle="tooltip">
								<span class="glyphicon glyphicon-remove text-danger"></span>
							</a>
						</td>
					</tr>
				@endforeach

				@if (count($acl->site) == 0)
					<tr>
						<td colspan="3">{{ Lang::get('permission.no_permissions') }}</td>
					</tr>
				@endif
			</tbody>
		</table>
	</fieldset>
@endif

<fieldset>
	<legend>
		@if ($show->site)
			<span class="glyphicon glyphicon-record"></span>
			{{ Lang::get('permission.scope_permissions') }}
		@else
			<span class="glyphicon glyphicon-list"></span>
			{{ Lang::get('permission.permissions') }}
		@endif
	</legend>

	<table class="table table-bordered table-striped">
		<thead>
			<tr>
				@if ($show->subjects)
					<th>{{ Lang::get('permission.user_group') }}</th>
				@endif

				<th>{{ Lang::get('permission.permission') }}</th>

				@if ($show->objects)
					<th>{{ Lang::get('permission.scope') }}</th>
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
								{? $subject = $acl->users->subjects->find($permission->subject_id) ?}

								<span class="glyphicon glyphicon-user"></span>
								<a href="{{ url("user/view/{$subject->hash}") }}">{{{ $subject->name }}}</a>
							@elseif ($permission->subject_type == ACLTypes::GROUP)
								{? $subject = $acl->groups->subjects->find($permission->subject_id) ?}

								<span class="glyphicon glyphicon-th-large"></span>
								<a href="{{ url("group/view/{$subject->hash}") }}">{{ $subject->name }}</a>
							@endif
						</td>
					@endif

					<td>
						{{ Lang::get("flag.{$permission->flag}") }}

						@if ($permission->field_id > 0)
							@if ($show->fields)
								&rarr; <em>{{ $acl->fields->find($permission->field_id)->name }}</em>
							@else
								{{ Lang::get('global.data') }}
							@endif
						@endif
					</td>

					@if ($show->objects)
						<td>
							@if ($permission->object_type == ACLTypes::ALL)
								<span class="glyphicon glyphicon-globe"></span>
								{{ Lang::get('permission.global') }}
							@elseif ($permission->object_type == ACLTypes::SELF)
								<span class="glyphicon glyphicon-bookmark"></span>
								{{ Lang::get('permission.self') }}
							@elseif ($permission->object_type == ACLTypes::USER)
								{? $object = $acl->users->objects->find($permission->object_id) ?}

								<span class="glyphicon glyphicon-user"></span>
								<a href="{{ url("user/view/{$object->hash}") }}">{{{ $object->name }}}</a>
							@elseif ($permission->object_type == ACLTypes::GROUP)
								{? $object = $acl->groups->objects->find($permission->object_id) ?}

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
					<td colspan="3">{{ Lang::get('permission.no_permissions') }}</td>
				</tr>
			@endif
		</tbody>
	</table>
</fieldset>
