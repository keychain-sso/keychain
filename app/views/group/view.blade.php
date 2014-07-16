@extends('common.page')

@section('body')
	{{ Form::open(array('role'   => 'form')) }}

	<div class="row">
		<div class="col-xs-12">
			<h3 class="spacer-none-top">
				<a href="{{ url('group/list') }}" class="back" title="{{ Lang::get('group.return_group_list') }}" data-toggle="tooltip">
					<span class="glyphicon glyphicon-chevron-left"></span>
				</a>

				{{ $group->name }}
			</h3>

			<p>
				{{{ $group->description }}}

				@if ($group->type == GroupTypes::OPEN)
					{{ Lang::get('group.open_group_exp') }}
				@elseif ($group->type == GroupTypes::REQUEST)
					{{ Lang::get('group.request_group_exp') }}
				@elseif ($group->type == GroupTypes::CLOSED)
					{{ Lang::get('group.closed_group_exp') }}
				@endif
			</p>
		</div>
	</div>

	@if ($actions)
		<nav class="navbar navbar-default" role="navigation">
			<div class="container-fluid">
				<ul class="nav navbar-nav">
					@if ($editor)
						<li>
							<a href="{{ url("group/edit/{$group->hash}") }}">
								<span class="glyphicon glyphicon-pencil"></span>
								{{ Lang::get('group.edit_group') }}
							</a>
						</li>
					@endif

					@if ( ! $pending)
						@if ($member && ($editor || $group->type == GroupTypes::OPEN))
							<li>
								<a href="{{ url("group/leave/{$group->hash}") }}">
									<span class="glyphicon glyphicon-share-alt"></span>
									{{ Lang::get('group.leave_group') }}
								</a>
							</li>
						@elseif ($editor || $group->type != GroupTypes::CLOSED)
							<li>
								<a href="{{ url("group/join/{$group->hash}") }}">
									<span class="glyphicon glyphicon-link"></span>
									{{ Lang::get('group.join_group') }}
								</a>
							</li>
						@endif
					@else
						<li>
							<a href="{{ url("group/withdraw/{$group->hash}") }}">
								<span class="glyphicon glyphicon-remove-circle"></span>
								{{ Lang::get('group.withdraw_request') }}
							</a>
						</li>
					@endif

					@if ($editor && $group->type == GroupTypes::REQUEST)
						<li>
							<a href="{{ url("group/requests/{$group->hash}") }}">
								<span class="label label-counter @if ($requestCount > 0) label-danger @else label-default @endif">
									{{ $requestCount }}
								</span>

								<span class="glyphicon glyphicon-pushpin"></span>
								{{ Lang::get('group.membership_requests') }}
							</a>
						</li>
					@endif

					@if ($manager && $canAdd)
						<li>
							<a href="{{ url("group/add-user/{$group->hash}") }}">
								<span class="glyphicon glyphicon-plus-sign"></span>
								{{ Lang::get('group.add_users') }}
							</a>
						</li>
					@endif
				</ul>

				@if ($access || $manager)
					<ul class="nav navbar-nav navbar-right">
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">
								<span class="glyphicon glyphicon-wrench"></span>
								{{ Lang::get('global.manage') }}
								<span class="caret"></span>
							</a>

							<ul class="dropdown-menu">
								@if ($access)
									<li>
										<a href="{{ url("group/permissions/{$group->hash}") }}">
											{{ Lang::get('group.group_permissions') }}
										</a>
									</li>
								@endif

								@if ($manager)
									<li>
										<a href="#" data-toggle="confirm" data-href="{{ url("group/delete/{$group->hash}") }}"
										   data-prompt="{{ Lang::get('global.click_again') }}"
										   data-wait="{{ Lang::get('global.please_wait') }}">
											{{ Lang::get('group.delete_group') }}
										</a>
									</li>
								@endif
							</ul>
						</li>
					</ul>
				@endif
			</div>
		</nav>
	@else
		<hr />
	@endif

	@if ( ! isset($modal))
		@include('common.alerts')
	@endif

	@if (count($userGroups) > 0)
		<div class="row">
			<div class="col-xs-12">
				<h3 class="spacer-none-top">{{ Lang::get('group.members') }}</h3>
			</div>

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
							'data-url'      => url("group/search/{$group->hash}?member=1"),
							'data-push'     => url("group/view/{$group->hash}"),
							'data-checkbox' => $editor ? Flags::YES : Flags::NO,
						))
					}}

					<span id="search-icon" class="glyphicon glyphicon-search text-muted form-control-feedback"></span>
				</div>
			</div>
		</div>
	@endif

	<div id="search-target" class="row">
		<div id="search-empty" class="col-xs-12 hide">
			<ul class="list-group spacer-none">
				<li class="list-group-item">
					{{ Lang::get('user.no_users_found') }}
				</li>
			</ul>
		</div>

		@foreach ($userGroups as $userGroup)
			<div class="col-xs-3 col-md-2 search-item" data-toggle="clickable">
				@if ($editor)
					{{ Form::checkbox('users[]', $userGroup->user->hash, false, array('class' => 'inlay')) }}
				@endif

				<div class="profile-icon">
					<a href="#" class="thumbnail spacer-sm-bottom">
						@if ( ! empty($userGroup->user->avatar))
							<img src="{{ asset("uploads/avatars/{$userGroup->user->avatar}") }}" alt="" />
						@else
							<img src="{{ asset('img/default-avatar.png') }}" alt="" />
						@endif
					</a>

					<a href="{{ url("user/view/{$userGroup->user->hash}") }}">{{ $userGroup->user->name }}</a>
					<small class="text-muted">{{ $userGroup->emails[0]->address }}</small>
				</div>
			</div>
		@endforeach

		@if (count($userGroups) == 0)
			<div class="col-xs-12">
				<ul class="list-group">
					<li class="list-group-item">{{ Lang::get('group.no_members') }}</li>
				</ul>
			</div>
		@endif
	</div>

	@if ($editor && $remove)
		<hr />

		<div class="row">
			<div class="col-xs-12">
				{{ Form::hidden('hash', $group->hash) }}

				{{
					Form::submit(Lang::get('group.remove_users'), array(
						'name'     => '_remove',
						'class'    => 'btn btn-default',
					))
				}}
			</div>
		</div>

		<div id="paginator" class="row spacer-lg-top">
			<div class="col-sm-6 visible-sm visible-md visible-lg text-muted">
				{{
					Lang::get('pagination.range', array(
						'from'  => $userGroups->getFrom(),
						'to'    => $userGroups->getTo(),
						'total' => $userGroups->getTotal(),
					))
				}}
			</div>

			<div class="col-sm-6 text-right">
				{{ $userGroups->links() }}
			</div>
		</div>
	@endif

	{{ Form::close() }}
@stop
