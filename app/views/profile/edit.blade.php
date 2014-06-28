{{
	Form::open(array(
		'action' => 'ProfileController@postEdit',
		'role'   => 'form',
	))
}}

<div class="modal-body">
	<nav class="navbar navbar-default navbar-static-top navbar-lg navbar-modal">
		<div class="container-fluid">
			<ul class="nav nav-icons text-center">
				<li class="active">
					<a title="{{ Lang::get('profile.edit_profile') }}" data-toggle="tooltip">
						<span class="glyphicon glyphicon-pencil"></span>
					</a>
				</li>

				<li>
					<a href="{{ url('profile/emails/'.$user->hash) }}" title="{{ Lang::get('profile.manage_email_addresses') }}" data-toggle="tooltip">
						<span class="glyphicon glyphicon-envelope"></span>
					</a>
				</li>

				<li>
					<a href="{{ url('profile/security/'.$user->hash) }}" title="{{ Lang::get('profile.security_settings') }}" data-toggle="tooltip">
						<span class="glyphicon glyphicon-lock"></span>
					</a>
				</li>
			</ul>
		</div>
	</nav>

	@include('common.alerts')

	<fieldset>
		<legend>
			<span class="glyphicon glyphicon-user"></span>
			{{ Lang::get('profile.basic_info') }}
		</legend>

		<div class="form-group">
			{{
				Form::label('first_name', Lang::get('global.first_name'), array(
					'class' => 'control-label'
				))
			}}

			{{
				Form::text('first_name', $user->first_name, array(
					'class' => 'form-control',
				))
			}}
		</div>

		<div class="form-group">
			{{
				Form::label('last_name', Lang::get('global.last_name'), array(
					'class' => 'control-label'
				))
			}}

			{{
				Form::text('last_name', $user->last_name, array(
					'class' => 'form-control',
				))
			}}
		</div>

		<div class="form-group">
			{{
				Form::label('title', Lang::get('global.title'), array(
					'class' => 'control-label'
				))
			}}

			{{
				Form::text('title', $user->title, array(
					'class' => 'form-control',
				))
			}}
		</div>

		<div class="form-group">
			{{
				Form::label('gender', Lang::get('global.gender'), array(
					'class' => 'control-label'
				))
			}}

			{{
				Form::select('gender', array(
					null => '',
					'M'  => Lang::get('global.male'),
					'F'  => Lang::get('global.female'),
					'O'  => Lang::get('global.other'),
				), $user->gender, array(
					'class' => 'form-control',
				))
			}}
		</div>

		<div class="form-group">
			{{
				Form::label('date_of_birth', Lang::get('global.date_of_birth'), array(
					'class' => 'control-label'
				))
			}}

			<div class="input-group">
				{{
					Form::text('date_of_birth', date('Y-m-d', strtotime($user->date_of_birth)), array(
						'class'       => 'form-control',
						'data-toggle' => 'datepicker',
					))
				}}

				<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
				</span>
			</div>
		</div>

		<div class="form-group">
			{{
				Form::label('timezone', Lang::get('global.timezone'), array(
					'class' => 'control-label'
				))
			}}

			{{
				Form::select('timezone', $timezones, $user->timezone, array(
					'class' => 'form-control',
				))
			}}
		</div>

		@foreach ($fieldEdit->{FieldCategories::BASIC} as $field)
			{{ $field }}
		@endforeach
	</fieldset>

	@if ( ! empty($fieldEdit->{FieldCategories::CONTACT}))
		<fieldset>
			<legend>
				<span class="glyphicon glyphicon-phone-alt"></span>
				{{ Lang::get('profile.contact_info') }}
			</legend>

			@foreach ($fieldEdit->{FieldCategories::CONTACT} as $field)
				{{ $field }}
			@endforeach
		</fieldset>
	@endif

	@if ( ! empty($fieldEdit->{FieldCategories::OTHER}))
		<fieldset>
			<legend>
				<span class="glyphicon glyphicon-th"></span>
				{{ Lang::get('profile.other_details') }}
			</legend>

			@foreach ($fieldEdit->{FieldCategories::OTHER} as $field)
				{{ $field }}
			@endforeach
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
