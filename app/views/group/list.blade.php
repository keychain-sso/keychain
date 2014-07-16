@extends('common.page')

@section('body')
	<div class="row">
		<div class="col-xs-12">
			@if ($manager)
				<a href="{{ url('group/create') }}" class="btn btn-default pull-right">
					<span class="glyphicon glyphicon-plus"></span>
					{{ Lang::get('group.create_new_group') }}
				</a>
			@endif

			<h2 class="spacer-none-top">
				<span class="glyphicon glyphicon-th-large"></span>
				{{ Lang::get('global.groups') }}
			</h2>

			@if ( ! isset($modal))
				@include('common.alerts')
			@endif

			<ul class="list-group">
				@foreach ($groupItems as $groupItem)
					<li class="list-group-item">
						<small class="pull-right text-muted">
							@if ($groupItem->type == GroupTypes::OPEN)
								{{ Lang::get('group.open_group') }}
							@elseif ($groupItem->type == GroupTypes::REQUEST)
								{{ Lang::get('group.request_group') }}
							@elseif ($groupItem->type == GroupTypes::CLOSED)
								{{ Lang::get('group.closed_group') }}
							@endif
						</small>

						<h4 class="list-group-item-heading">
							<a href="{{ url("group/view/{$groupItem->hash}") }}">{{ $groupItem->name }}</a>

							@if (in_array($groupItem->id, $userGroups))
								<span class="glyphicon glyphicon-link text-primary" title="{{ Lang::get('group.member_of_group') }}"
								      data-toggle="tooltip"></span>
							@endif
						</h4>

						<p class="list-group-item-text">{{{ $groupItem->description }}}</p>
					</li>
				@endforeach
			</ul>
		</div>
	</div>

	<div class="row">
		<div class="col-sm-6 visible-sm visible-md visible-lg text-muted">
			{{
				Lang::get('pagination.range', array(
					'from'  => $groupItems->getFrom(),
					'to'    => $groupItems->getTo(),
					'total' => $groupItems->getTotal(),
				))
			}}
		</div>

		<div class="col-sm-6 text-right">
			{{ $groupItems->links() }}
		</div>
	</div>
@stop
