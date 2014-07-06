@extends('common.page')

@section('body')
	<div class="row">
		<div class="col-sm-12">
			<h2>
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
							<a href="{{ url('group/view/'.$group->id) }}">{{ $group->name }}</a>

							@if (isset($membership[$group->id]))
								<span class="glyphicon glyphicon-link text-success" title="{{ Lang::get('group.member_of_group') }}"
								      data-toggle="tooltip"></span>
							@endif
						</h4>

						<p class="list-group-item-text">{{{ $group->description }}}</p>
					</li>
				@endforeach
			</ul>

			<div class="text-center">
				{{ $groups->links() }}
			</div>
		</div>
	</div>
@stop
