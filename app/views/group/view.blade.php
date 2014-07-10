@extends('common.page')

@section('body')
	{{
		Form::open(array(
			'action' => 'GroupController@postView',
			'role'   => 'form',
		))
	}}

	<div class="row">
		<div class="col-sm-12">
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
							<a href="{{ url('group/edit/'.$group->hash) }}">
								<span class="glyphicon glyphicon-pencil"></span>
								{{ Lang::get('group.edit_group') }}
							</a>
						</li>
					@endif

					@if ( ! $pending)
						@if ($member && ($editor || $group->type == GroupTypes::OPEN))
							<li>
								<a href="{{ url('group/leave/'.$group->hash) }}">
									<span class="glyphicon glyphicon-share-alt"></span>
									{{ Lang::get('group.leave_group') }}
								</a>
							</li>
						@elseif ($editor || $group->type != GroupTypes::CLOSED)
							<li>
								<a href="{{ url('group/join/'.$group->hash) }}">
									<span class="glyphicon glyphicon-link"></span>
									{{ Lang::get('group.join_group') }}
								</a>
							</li>
						@endif
					@else
						<li>
							<a href="{{ url('group/withdraw/'.$group->hash) }}">
								<span class="glyphicon glyphicon-remove-circle"></span>
								{{ Lang::get('group.withdraw_request') }}
							</a>
						</li>
					@endif

					@if ($editor && $group->type == GroupTypes::REQUEST)
						<li>
							<a href="{{ url('group/requests/'.$group->hash) }}">
								<span class="label label-counter @if ($requests > 0) label-danger @else label-default @endif">
									{{ $requests }}
								</span>

								<span class="glyphicon glyphicon-pushpin"></span>
								{{ Lang::get('group.membership_requests') }}
							</a>
						</li>
					@endif
				</ul>

				@if ($manager)
					<ul class="nav navbar-nav navbar-right">
						<li>
							<a href="#">
								<span class="glyphicon glyphicon-wrench"></span>
								{{ Lang::get('global.manage') }}
							</a>
						</li>
					</ul>
				@endif
			</div>
		</nav>
	@endif

	@if ($modal === false)
		@include('common.alerts')
	@endif

	<div class="row">
		<div class="col-sm-12">
			<h3 class="spacer-none-top">{{ Lang::get('group.members') }}</h3>
		</div>

		@foreach ($userGroups as $userGroup)
			<div class="col-xs-3 col-md-2">
				@if ($editor)
					{{
						Form::checkbox('users', $userGroup->user->hash, false, array(
							'class' => 'inlay',
						))
					}}
				@endif

				<div class="text-center">
					<span class="thumbnail spacer-sm-bottom">
						@if ( ! empty($userGroup->user->avatar))
							<img src="{{ asset('uploads/avatars'.$user->avatar) }}" alt="" />
						@else
							<img src="{{ asset('img/default-avatar.png') }}" alt="" />
						@endif
					</span>

					<a href="{{ url('user/view/'.$userGroup->user->hash) }}">
						{{ $userGroup->user->first_name }}
						{{ $userGroup->user->last_name }}
					</a>
				</div>
			</div>
		@endforeach

		@if (count($userGroups) == 0)
			<div class="col-sm-12">
				<ul class="list-group">
					<li class="list-group-item">{{ Lang::get('group.no_members') }}</li>
				</ul>
			</div>
		@endif
	</div>

	@if ($editor && $remove)
		<div class="row">
			<div class="col-sm-12">
				<hr />

				{{ Form::hidden('hash', $group->hash) }}

				{{
					Form::submit(Lang::get('group.remove_users'), array(
						'name'     => '_remove',
						'class'    => 'btn btn-default',
					))
				}}
			</div>
		</div>
	@endif

	{{ Form::close() }}

	@if ($modal !== false)
		<div class="modal modal-editor">
			<div class="modal-dialog">
				<div class="modal-content">
					@include("group.{$modal}")
				</div>
			</div>
		</div>
	@endif
@stop
