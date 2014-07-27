{{ Form::open(array('role' => 'form')) }}

<div class="modal-body">
	@include('user.header')

	<fieldset>
		<legend>
			<span class="glyphicon glyphicon-user"></span>
			{{ Lang::get('user.basic_info') }}
		</legend>

		<div class="form-group">
			{{ Form::label('name', Lang::get('global.name'), array('class' => 'control-label')) }}
			{{ Form::text('name', $user->name, array('class' => 'form-control')) }}
		</div>

		<div class="form-group">
			{{ Form::label('title', Lang::get('user.title'), array('class' => 'control-label')) }}
			{{ Form::text('title', $user->title, array('class' => 'form-control')) }}
		</div>

		<div class="form-group">
			{{ Form::label('gender', Lang::get('user.gender'), array('class' => 'control-label')) }}

			<div class="radio">
				<label>
					{{ Form::radio('gender', 'M', $user->gender == 'M') }}
					{{ Lang::get('user.male') }}
				</label>
			</div>

			<div class="radio">
				<label>
					{{ Form::radio('gender', 'F', $user->gender == 'F') }}
					{{ Lang::get('user.female') }}
				</label>
			</div>

			<div class="radio">
				<label>
					{{ Form::radio('gender', 'N', $user->gender == 'N') }}
					{{ Lang::get('user.not_set') }}
				</label>
			</div>
		</div>

		<div class="form-group  has-feedback">
			{{ Form::label('date_of_birth', Lang::get('user.date_of_birth'), array('class' => 'control-label')) }}

			{{
				Form::text('date_of_birth', date('Y-m-d', empty($user->date_of_birth) ? null : strtotime($user->date_of_birth)), array(
					'class'       => 'form-control',
					'data-toggle' => 'datepicker',
				))
			}}

			<span class="glyphicon glyphicon-calendar text-muted form-control-feedback"></span>
		</div>

		<div class="form-group">
			{{ Form::label('timezone', Lang::get('user.timezone'), array('class' => 'control-label')) }}
			{{ Form::select('timezone', $timezones, $user->timezone, array('class' => 'form-control')) }}
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
	{{ Form::submit(Lang::get('global.save'), array('class' => 'btn btn-primary')) }}

	<a href="{{ url("user/view/{$user->hash}") }}" class="btn btn-default">
		{{ Lang::get('global.close') }}
	</a>
</div>

{{ Form::close() }}
