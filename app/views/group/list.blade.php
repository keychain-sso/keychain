@extends('common.page')

@section('body')
	<div class="row">
		<div class="col-sm-12">
			@if (Access::check(Permissions::GROUP_MANAGE))
				<a href="{{ url('group/create') }}" class="btn btn-default pull-right">
					<span class="glyphicon glyphicon-plus"></span>
					{{ Lang::get('group.create_new_group') }}
				</a>
			@endif

			<h2 class="spacer-none-top">
				<span class="glyphicon glyphicon-th-large"></span>
				{{ Lang::get('global.groups') }}
			</h2>

			<ul class="list-group">
				@foreach ($groups as $group)
					<li class="list-group-item">
						<small class="pull-right text-muted">
							@if ($group->type == GroupTypes::OPEN)
								{{ Lang::get('group.open_group') }}
							@elseif ($group->type == GroupTypes::REQUEST)
								{{ Lang::get('group.request_group') }}
							@elseif ($group->type == GroupTypes::CLOSED)
								{{ Lang::get('group.closed_group') }}
							@endif
						</small>

						<h4 class="list-group-item-heading">
							<a href="{{ url('group/view/'.$group->hash) }}">{{ $group->name }}</a>

							@if (isset($membership[$group->id]))
								<span class="glyphicon glyphicon-link text-primary" title="{{ Lang::get('group.member_of_group') }}"
								      data-toggle="tooltip"></span>
							@endif
						</h4>

						<p class="list-group-item-text">{{{ $group->description }}}</p>
					</li>
				@endforeach
			</ul>
		</div>
	</div>

	<div class="row">
		<div class="col-sm-6 visible-sm visible-md visible-lg text-muted">
			{{
				Lang::get('pagination.range', array(
					'from'  => $groups->getFrom(),
					'to'    => $groups->getTo(),
					'total' => $groups->getTotal(),
				))
			}}
		</div>

		<div class="col-sm-6 text-right">
			{{ $groups->links() }}
		</div>
	</div>
@stop
