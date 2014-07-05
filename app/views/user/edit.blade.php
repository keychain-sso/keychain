{{
	Form::open(array(
		'action' => 'UserController@postEdit',
		'role'   => 'form',
	))
}}

<div class="modal-body">
	@include('user.header')

	<fieldset>
		<legend>
			<span class="glyphicon glyphicon-user"></span>
			{{ Lang::get('user.basic_info') }}
		</legend>

		<div class="form-group">
			{{
				Form::label('first_name', Lang::get('user.first_name'), array(
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
				Form::label('last_name', Lang::get('user.last_name'), array(
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
				Form::label('title', Lang::get('user.title'), array(
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
				Form::label('gender', Lang::get('user.gender'), array(
					'class' => 'control-label'
				))
			}}

			{{
				Form::select('gender', array(
					null => '',
					'M'  => Lang::get('user.male'),
					'F'  => Lang::get('user.female'),
					'O'  => Lang::get('user.other'),
				), $user->gender, array(
					'class' => 'form-control',
				))
			}}
		</div>

		<div class="form-group  has-feedback">
			{{
				Form::label('date_of_birth', Lang::get('user.date_of_birth'), array(
					'class' => 'control-label'
				))
			}}

			{{
				Form::text('date_of_birth', date('Y-m-d', strtotime($user->date_of_birth)), array(
					'class'       => 'form-control',
					'data-toggle' => 'datepicker',
				))
			}}

			<span class="glyphicon glyphicon-calendar text-muted form-control-feedback"></span>
		</div>

		<div class="form-group">
			{{
				Form::label('timezone', Lang::get('user.timezone'), array(
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
				{{ Lang::get('user.contact_info') }}
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
				{{ Lang::get('user.other_details') }}
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
		link_to("user/view/{$user->hash}", Lang::get('global.close'), array(
			'class' => 'btn btn-default',
		))
	}}
</div>

{{ Form::close() }}
