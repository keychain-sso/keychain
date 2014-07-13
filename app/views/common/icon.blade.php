@foreach ($users as $user)
	<div class="col-xs-3 col-md-2 search-item" data-toggle="clickable">
		@if ($checkbox)
			{{
				Form::checkbox('users', $user->hash, false, array(
					'class' => 'inlay',
				))
			}}
		@endif

		<div class="profile-icon">
			<a @if ($checkbox) href="#" @else href="{{ url("user/view/{$user->hash}") }}" @endif class="thumbnail spacer-sm-bottom">
				@if ( ! empty($user->avatar))
					<img src="{{ asset("uploads/avatars/{$user->avatar}") }}" alt="" />
				@else
					<img src="{{ asset('img/default-avatar.png') }}" alt="" />
				@endif
			</a>

			<a href="{{ url("user/view/{$user->hash}") }}">
				{{ $user->first_name }}
				{{ $user->last_name }}
			</a>

			<small class="text-muted">
				{{ $user->primaryEmail[0]->address }}
			</small>
		</div>
	</div>
@endforeach
