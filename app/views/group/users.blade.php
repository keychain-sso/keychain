{{ Form::open(array('role' => 'form')) }}

<div class="modal-body">
	@include('group.header')

	<div class="row">
		<div class="col-xs-12">
			<div class="form-group has-feedback">
				{{
					Form::text('search', null, array(
						'class'         => 'form-control',
						'placeholder'   => Lang::get('global.search_user'),
						'autocomplete'  => 'off',
						'data-toggle'   => 'search',
						'data-size'     => 'col-xs-4',
						'data-item'     => '.search-item',
						'data-target'   => '#search-modal-target',
						'data-empty'    => '#search-modal-empty',
						'data-icon'     => '#search-modal-icon',
						'data-pages'    => '#paginator-modal',
						'data-url'      => url("group/member-search/{$group->hash}"),
						'data-push'     => url("group/add-user/{$group->hash}"),
						'data-checkbox' => Flags::YES,
					))
				}}

				<span id="search-modal-icon" class="glyphicon glyphicon-search text-muted form-control-feedback"></span>
			</div>
		</div>
	</div>

	<div id="search-modal-target" class="row spacer-lg-bottom">
		<div id="search-modal-empty" class="col-xs-12 hide">
			<ul class="list-group spacer-none">
				<li class="list-group-item">
					{{ Lang::get('user.no_users_found') }}
				</li>
			</ul>
		</div>

		@foreach ($users as $user)
			<div class="col-xs-4 search-item">
				<div class="profile-icon" data-toggle="clickable">
					{{ Form::checkbox('users[]', $user->hash, false, array('class' => 'inlay')) }}

					<a href="#" class="thumbnail spacer-sm-bottom">
						@if ( ! empty($user->avatar))
							<img src="{{ asset("uploads/avatars/{$user->avatar}") }}" alt="" />
						@else
							<img src="{{ asset('img/default-avatar.png') }}" alt="" />
						@endif
					</a>

					<a href="{{ url("user/view/{$user->hash}") }}" class="show-block">{{{ $user->name }}}</a>
					<small class="text-muted">{{ $user->primaryEmail[0]->address }}</small>
				</div>
			</div>
		@endforeach
	</div>

	<div id="paginator-modal">
		<div class="col-sm-12 text-center">
			{{ $users->links() }}
		</div>
	</div>
</div>

<div class="modal-footer">
	{{ Form::hidden('hash', $group->hash) }}
	{{ Form::submit(Lang::get('global.add'), array('class' => 'btn btn-primary')) }}

	<a href="{{ url("group/view/{$group->hash}") }}" class="btn btn-default">
		{{ Lang::get('global.close') }}
	</a>
</div>

{{ Form::close() }}
