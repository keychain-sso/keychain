{{ Form::open(array('role'   => 'form')) }}

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

	<fieldset>
		<legend>
			<span class="glyphicon glyphicon-globe"></span>
			{{ Lang::get('global.global_permissions') }}
		</legend>

		<table class="table table-bordered table-striped">
			<colgroup>
				<col />
				<col width="30" />
			</colgroup>

			<thead>
				<tr>
					<th>{{ Lang::get('global.permission') }}</th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				@foreach ($acl->permissions->global as $permission)
					<tr>
						<td>{{ Lang::get("permissions.{$permission->access}") }}</td>

						<td>
							<a href="{{ url("group/{$group->hash}/permissions/remove/{$permission->id}") }}"
							   title="{{ Lang::get('global.remove') }}" data-toggle="tooltip">
								<span class="glyphicon glyphicon-remove text-danger"></span>
							</a>
						</td>
					</tr>
				@endforeach

				@if (count($acl->permissions->global) == 0)
					<tr>
						<td colspan="2">{{ Lang::get('global.no_permissions') }}</td>
					</tr>
				@endif
			</tbody>
		</table>
	</fieldset>

	<fieldset>
		<legend>
			<span class="glyphicon glyphicon-record"></span>
			{{ Lang::get('global.scope_permissions') }}
		</legend>

		<table class="table table-bordered table-striped">
			<colgroup>
				<col />
				<col />
				<col width="30" />
			</colgroup>

			<thead>
				<tr>
					<th>{{ Lang::get('global.permission') }}</th>
					<th>{{ Lang::get('global.scope') }}</th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				@foreach ($acl->permissions->scope as $permission)
					<tr>
						@if ($permission->field_id > 0)
							<td>
								{{
									Lang::get("permissions.{$permission->access}", array(
										'field' => $acl->objects->fields->find($permission->field_id)->name,
									))
								}}
							</td>
						@else
							<td>{{ Lang::get("permissions.{$permission->access}") }}</td>
						@endif

						<td>
							@if ($permission->object_type == ACLTypes::ALL)
								<span class="glyphicon glyphicon-asterisk"></span>
								{{ Lang::get('global.global') }}
							@elseif ($permission->object_type == ACLTypes::SELF)
								<span class="glyphicon glyphicon-bookmark"></span>
								{{ Lang::get('global.self') }}
							@elseif ($permission->object_type == ACLTypes::USER)
								{? $object = $acl->objects->users->find($permission->object_id) ?}

								<span class="glyphicon glyphicon-user"></span>
								<a href="{{ url("user/view/{$object->hash}") }}">{{{ $object->name }}}</a>
							@elseif ($permission->object_type == ACLTypes::GROUP)
								{? $object = $acl->objects->groups->find($permission->object_id) ?}

								<span class="glyphicon glyphicon-th-large"></span>
								<a href="{{ url("group/view/{$object->hash}") }}">{{ $object->name }}</a>
							@endif
						</td>

						<td>
							<a href="{{ url("group/{$group->hash}/permissions/remove/{$permission->id}") }}"
							   title="{{ Lang::get('global.remove') }}" data-toggle="tooltip">
								<span class="glyphicon glyphicon-remove text-danger"></span>
							</a>
						</td>
					</tr>
				@endforeach

				@if (count($acl->permissions->scope) == 0)
					<tr>
						<td colspan="3">{{ Lang::get('global.no_permissions') }}</td>
					</tr>
				@endif
			</tbody>
		</table>
	</fieldset>
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
