@extends('common.page')

@section('body')
	<div class="row">
		<div class="col-sm-12">
			<h3>
				<span class="glyphicon glyphicon-th-large"></span>
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
					@if ($manager)
						<li>
							<a href="{{ url('group/edit/'.$group->id) }}">
								<span class="glyphicon glyphicon-pencil"></span>
								{{ Lang::get('group.edit_group') }}
							</a>
						</li>
					@endif

					@if ($member && ($manager || $group->type == GroupTypes::OPEN))
						<li>
							<a href="{{ url('group/leave/'.$group->id) }}">
								<span class="glyphicon glyphicon-share-alt"></span>
								{{ Lang::get('group.leave_group') }}
							</a>
						</li>
					@elseif ($manager || $group->type == GroupTypes::OPEN || $group->type == GroupTypes::REQUEST)
						<li>
							<a href="{{ url('group/edit/'.$group->id) }}">
								<span class="glyphicon glyphicon-link"></span>
								{{ Lang::get('group.join_group') }}
							</a>
						</li>
					@endif
				</ul>
			</div>
		</nav>
	@endif
@stop
