<div class="modal-body">
	<nav class="navbar navbar-default navbar-static-top navbar-modal">
		<div class="container-fluid">
			<a href="{{ $return }}" class="close">&times;</a>

			<div class="text-center">
				<h3 class="spacer-none-top">{{ $title }}</h3>
			</div>
		</div>
	</nav>

	{{ Form::open(array('role' => 'form')) }}

	<fieldset>
		<legend>
			<span class="glyphicon glyphicon-import"></span>
			{{ Lang::get('global.add_permissions') }}
		</legend>

		@if ( ! $show->subjects)
			<div class="form-group">
				{{ Form::label('subject', Lang::get('global.user_group'), array('class' => 'control-label')) }}

				<div class="input-group">
					<div class="input-group-btn">
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
							{{ Lang::get('global.user') }}
							<span class="caret"></span>
						</button>

						<ul class="dropdown-menu">
							<li><a href="#">{{ Lang::get('global.user') }}</a></li>
							<li><a href="#">{{ Lang::get('global.group') }}</a></li>
						</ul>
					</div>

					{{ Form::text('subject', isset($subject) ? $subject->name : null, array('class' => 'form-control')) }}
					{{ Form::hidden('subject_id', isset($subject) ? $subject->id : null) }}
					{{ Form::hidden('subject_type', isset($subject) ? get_class($subject) : 'User') }}
				</div>
			</div>
		@endif

		@if ( ! $show->fields)
			<div class="form-group">
				{{ Form::label('field', Lang::get('global.field')) }}
				{{ Form::select('field',
			</div>
		@endif
	</fieldset>

	@include('acl.editor')

	{{ Form::close() }}
</div>
