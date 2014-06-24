@extends('common.page')

@section('body')
	@include('profile.header')

	<div class="row">
		<div class="col-md-6">
			<fieldset>
				<legend>
					<span class="glyphicon glyphicon-th-list"></span>
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

					{{
						Form::text('date_of_birth', date('jS F Y', strtotime($user->date_of_birth)), array(
							'class' => 'form-control',
						))
					}}
				</div>

				<div class="form-group">
					{{
						Form::label('timezone', Lang::get('global.timezone'), array(
							'class' => 'control-label'
						))
					}}

					{{
						Form::text('timezone', $user->timezone, array(
							'class' => 'form-control',
						))
					}}
				</div>
			</fieldset>
		</div>
	</div>
@stop
