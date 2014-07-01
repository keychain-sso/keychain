{{
	Form::open(array(
		'action' => 'ProfileController@postSecurity',
		'role'   => 'form',
	))
}}

<div class="modal-body">
	@include('profile.header')

	<fieldset>
		<legend>
			<span class="glyphicon glyphicon-lock"></span>
			{{ Lang::get('profile.change_password') }}
		</legend>

		@if ( ! Access::check('user.manage', $user))
			<div class="form-group">
				{{
					Form::label('old_password', Lang::get('profile.old_password'), array(
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
				Form::label('new_password', Lang::get('profile.new_password'), array(
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
				Form::label('confirm_password', Lang::get('profile.confirm_new_password'), array(
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

	@if (Access::check('user.manage', $user))
		<fieldset>
			<legend>
				<span class="glyphicon glyphicon-cog"></span>
				{{ Lang::get('profile.account_settings') }}
			</legend>

			<div class="form-group">
				{{
					Form::label('status', Lang::get('profile.profile_status'), array(
						'class' => 'control-label'
					))
				}}

				<div class="radio">
					<label>
						{{ Form::radio('status', UserStatus::INACTIVE, $user->status == UserStatus::INACTIVE) }}
						{{ Lang::get('profile.inactive') }}
					</label>
				</div>

				<div class="radio">
					<label>
						{{ Form::radio('status', UserStatus::ACTIVE, $user->status == UserStatus::ACTIVE) }}
						{{ Lang::get('profile.active') }}
					</label>
				</div>

				<div class="radio">
					<label>
						{{ Form::radio('status', UserStatus::BLOCKED, $user->status == UserStatus::BLOCKED) }}
						{{ Lang::get('profile.blocked') }}
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
		link_to("profile/view/{$user->hash}", Lang::get('global.close'), array(
			'class' => 'btn btn-default',
		))
	}}
</div>

{{ Form::close() }}
