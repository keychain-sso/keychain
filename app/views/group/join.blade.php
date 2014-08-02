{{ Form::open(array('role' => 'form')) }}

<div class="modal-body">
	@include('group.header')

	<p>{{ Lang::get('group.provide_justification') }}</p>

	<div class="form-group">
		{{ Form::textarea('justification', null, array('class' => 'form-control')) }}
	</div>
</div>

<div class="modal-footer">
	{{ Form::hidden('hash', $group->hash) }}
	{{ Form::submit(Lang::get('global.submit'), array('class' => 'btn btn-primary')) }}

	<a href="{{ url("group/view/{$group->hash}") }}" class="btn btn-default">
		{{ Lang::get('global.close') }}
	</a>
</div>

{{ Form::close() }}
