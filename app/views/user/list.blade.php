@extends('common.page')

@section('body')
	<div class="row">
		<div class="col-xs-12">
			@if (Access::check(Permissions::USER_MANAGE))
				<a href="{{ url('user/create') }}" class="btn btn-default pull-right">
					<span class="glyphicon glyphicon-plus"></span>
					{{ Lang::get('user.create_new_user') }}
				</a>
			@endif

			<h2 class="spacer-none-top">
				<span class="glyphicon glyphicon-user"></span>
				{{ Lang::get('global.users') }}
			</h2>
		</div>
	</div>

	<div class="row">
		<div class="col-xs-8 col-sm-5 col-md-4 col-lg-3">
			<div class="form-group has-feedback">
				{{
					Form::text('search', null, array(
						'class'         => 'form-control',
						'placeholder'   => Lang::get('global.search_user'),
						'autocomplete'  => 'off',
						'data-toggle'   => 'user-search',
						'data-size'     => 'col-xs-3 col-md-2',
						'data-item'     => '.search-item',
						'data-target'   => '#search-target',
						'data-empty'    => '#search-empty',
						'data-icon'     => '#search-icon',
						'data-pages'    => '#paginator',
						'data-url'      => url('user/search'),
						'data-push'     => url('user/list'),
						'data-checkbox' => Flags::NO,
					))
				}}

				<span id="search-icon" class="glyphicon glyphicon-search text-muted form-control-feedback"></span>
			</div>
		</div>
	</div>

	<div id="search-target" class="row">
		<div id="search-empty" class="col-xs-12 hide">
			<ul class="list-group spacer-none">
				<li class="list-group-item">
					{{ Lang::get('user.no_users_found') }}
				</li>
			</ul>
		</div>

		@foreach ($users as $user)
			<div class="col-xs-3 col-md-2 search-item">
				<div class="profile-icon">
					<a href="{{ url("user/view/{$user->hash}") }}" class="thumbnail spacer-sm-bottom">
						@if ( ! empty($user->avatar))
							<img src="{{ asset("uploads/avatars/{$user->avatar}") }}" alt="" />
						@else
							<img src="{{ asset('img/default-avatar.png') }}" alt="" />
						@endif
					</a>

					<a href="{{ url("user/view/{$user->hash}") }}">{{ $user->name }}</a>
					<small class="text-muted">{{ $user->primaryEmail[0]->address }}</small>
				</div>
			</div>
		@endforeach
	</div>

	<hr />

	<div id="paginator" class="row">
		<div class="col-sm-6 visible-sm visible-md visible-lg text-muted">
			{{
				Lang::get('pagination.range', array(
					'from'  => $users->getFrom(),
					'to'    => $users->getTo(),
					'total' => $users->getTotal(),
				))
			}}
		</div>

		<div class="col-sm-6 text-right">
			{{ $users->links() }}
		</div>
	</div>
@stop
