{{
	Form::open(array(
		'action' => 'UserController@postSecurity',
		'role'   => 'form',
	))
}}

<div class="modal-body">
	@include('user.header')

	<fieldset>
		<legend>
			<span class="glyphicon glyphicon-lock"></span>
			{{ Lang::get('user.change_password') }}
		</legend>

		@if (Auth::id() == $user->id)
			<div class="form-group">
				{{
					Form::label('old_password', Lang::get('user.old_password'), array(
						'class' => 'control-label'
					))
				}}

				{{
					Form::password('old_password', array(
						'class' => 'form-control',
					))
				}}
			</div>
		@endif

		<div class="form-group">
			{{
				Form::label('new_password', Lang::get('user.new_password'), array(
					'class' => 'control-label'
				))
			}}

			{{
				Form::password('new_password', array(
					'class' => 'form-control',
				))
			}}
		</div>

		<div class="form-group">
			{{
				Form::label('confirm_password', Lang::get('user.confirm_new_password'), array(
					'class' => 'control-label'
				))
			}}

			{{
				Form::password('confirm_password', array(
					'class' => 'form-control',
				))
			}}
		</div>
	</fieldset>

	<fieldset>
		<legend>
			<span class="glyphicon glyphicon-tasks"></span>
			{{ Lang::get('user.active_sessions') }}
		</legend>

		<ul class="list-group">
			@foreach ($sessions as $session)
				<li class="list-group-item">
					@if ($session->device_type == DeviceTypes::MOBILE)
						{? $asset = 'img/mobile.png' ?}
					@elseif ($session->device_type == DeviceTypes::TABLET)
						{? $asset = 'img/tablet.png' ?}
					@else
						{? $asset = 'img/computer.png' ?}
					@endif

					<img src="{{ asset($asset) }}" class="list-group-icon pull-left" alt="" />

					@if ($session->id == Session::getId())
						<span class="pull-right">
							<span class="glyphicon glyphicon-flag text-success"></span>
							<small class="text-muted">{{ Lang::get('user.current_session') }}</small>
						</span>
					@endif

					<h4 class="list-group-item-heading">{{ $session->ip_address }}</h4>

					<p class="list-group-item-text">
						{{
							Lang::get('user.last_active', array(
								'time' => date('Y-m-d h:i a', strtotime($session->updated_at)),
							))
						}}
					</p>
				</li>
			@endforeach
		</ul>

		<a href="{{ url('user/security/'.$user->hash.'/killall') }}" class="btn btn-default">
			{{ Lang::get('user.kill_other_sessions') }}
		</a>
	</fieldset>

	@if (Access::check(Permissions::USER_STATUS, $user))
		<fieldset>
			<legend>
				<span class="glyphicon glyphicon-cog"></span>
				{{ Lang::get('user.account_settings') }}
			</legend>

			<div class="form-group">
				{{
					Form::label('status', Lang::get('user.profile_status'), array(
						'class' => 'control-label'
					))
				}}

				<div class="radio">
					<label>
						{{ Form::radio('status', UserStatus::INACTIVE, $user->status == UserStatus::INACTIVE) }}
						{{ Lang::get('user.inactive') }}
					</label>
				</div>

				<div class="radio">
					<label>
						{{ Form::radio('status', UserStatus::ACTIVE, $user->status == UserStatus::ACTIVE) }}
						{{ Lang::get('user.active') }}
					</label>
				</div>

				<div class="radio">
					<label>
						{{ Form::radio('status', UserStatus::BLOCKED, $user->status == UserStatus::BLOCKED) }}
						{{ Lang::get('user.blocked') }}
					</label>
				</div>
			</div>
		</fieldset>
	@endif
</div>

<div class="modal-footer">
	{{ Form::hidden('hash', $user->hash) }}

	{{
		Form::submit(Lang::get('global.save'), array(
			'name'     => '_save',
			'class'    => 'btn btn-primary',
		))
	}}

	{{
		link_to("user/view/{$user->hash}", Lang::get('global.close'), array(
			'class' => 'btn btn-default',
		))
	}}
</div>

{{ Form::close() }}
