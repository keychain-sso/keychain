{{ Form::open(array('role' => 'form')) }}

<div class="modal-body">
	@include('user.header')

	<fieldset>
		<legend>
			<span class="glyphicon glyphicon-envelope"></span>
			{{ Lang::get('user.email_addresses') }}
		</legend>

		<ul class="list-group">
			<li class="list-group-item">
				<div class="pull-right">
					@if ($emails->primary->verified)
						<a class="btn btn-default btn-xs disabled">{{ Lang::get('user.verified') }}</a>
					@else
						<a href="{{ url("user/emails/{$user->hash}/verify/{$emails->primary->id}") }}" class="btn btn-xs btn-default">
							{{ Lang::get('user.verify') }}
						</a>
					@endif

					<div title="{{ Lang::get('user.primary_cant_remove') }}" class="show-inline-table" data-toggle="tooltip">
						<a class="btn btn-xs btn-danger disabled">
							{{ Lang::get('global.remove') }}
						</a>
					</div>
				</div>

				<span class="glyphicon glyphicon-star text-success" title="{{ Lang::get('user.primary_email') }}"
				      data-toggle="tooltip"></span>

				{{ $emails->primary->address }}
			</li>

			@if (isset($emails->other))
				@foreach ($emails->other as $email)
					<li class="list-group-item">
						<div class="pull-right">
							@if ($email->verified)
								<a class="btn btn-default btn-xs disabled">{{ Lang::get('user.verified') }}</a>
							@else
								<a href="{{ url("user/emails/{$user->hash}/verify/{$email->id}") }}" class="btn btn-xs btn-default">
									{{ Lang::get('user.verify') }}
								</a>
							@endif

							<a href="{{ url("user/emails/{$user->hash}/remove/{$email->id}") }}" class="btn btn-xs btn-danger">
								{{ Lang::get('global.remove') }}
							</a>
						</div>

						@if ($email->verified)
							<a href="{{ url("user/emails/{$user->hash}/primary/{$email->id}") }}">
								<span class="glyphicon glyphicon-star-empty text-muted" title="{{ Lang::get('user.set_as_primary') }}"
									  data-toggle="tooltip"></span>
							</a>
						@else
							<span class="glyphicon glyphicon-star-empty text-danger" title="{{ Lang::get('user.primary_verify') }}"
								  data-toggle="tooltip"></span>
						@endif

						{{ $email->address }}
					</li>
				@endforeach
			@endif
		</ul>
	</fieldset>

	<fieldset>
		<legend>
			<span class="glyphicon glyphicon-export"></span>
			{{ Lang::get('user.add_new_email') }}
		</legend>

		<div class="form-group">
			{{ Form::label('email', Lang::get('user.email_address'), array('class' => 'control-label')) }}
			{{ Form::text('email', null, array('class' => 'form-control')) }}
		</div>
	</fieldset>
</div>

<div class="modal-footer">
	{{ Form::hidden('hash', $user->hash) }}
	{{ Form::submit(Lang::get('global.add'), array('class' => 'btn btn-primary')) }}

	<a href="{{ url("user/view/{$user->hash}") }}" class="btn btn-default">
		{{ Lang::get('global.close') }}
	</a>
</div>

{{ Form::close() }}
