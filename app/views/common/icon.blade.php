@foreach ($users as $user)
	<div class="search-item" data-toggle="clickable">
		@if ($checkbox)
			{{ Form::checkbox('users', $user->hash, false, array('class' => 'inlay')) }}
		@endif

		<div class="profile-icon">
			<a @if ($checkbox) href="#" @else href="{{ url("user/view/{$user->hash}") }}" @endif class="thumbnail spacer-sm-bottom">
				@if ( ! empty($user->avatar))
					<img src="{{ asset("uploads/avatars/{$user->avatar}") }}" alt="" />
				@else
					<img src="{{ asset('img/default-avatar.png') }}" alt="" />
				@endif
			</a>

			<a href="{{ url("user/view/{$user->hash}") }}">{{{ $user->name }}}</a>
			<small class="text-muted">{{ $user->primaryEmail[0]->address }}</small>
		</div>
	</div>
@endforeach
