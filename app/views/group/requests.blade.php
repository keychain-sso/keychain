<div class="modal-body">
	@include('group.header')

	<p>
		@foreach ($requests as $request)
			<div class="row">
				<div class="col-xs-2">
					<span class="thumbnail thumbnail-thread">
						@if ( ! empty($request->user->avatar))
							<img src="{{ asset("uploads/avatars{$request->user->avatar}") }}" alt="" />
						@else
							<img src="{{ asset('img/default-avatar.png') }}" alt="" />
						@endif
					</span>
				</div>

				<div class="col-xs-10">
					<div class="popover popover-thread right">
						<div class="arrow"></div>
						<h3 class="popover-title">
							{{
								Lang::get('group.request_header', array(
									'user'      => link_to("user/view/{$request->user->hash}",
														   sprintf('%s %s', $request->user->first_name, $request->user->last_name)),
									'timestamp' => date('Y-m-d h:i a', strtotime($request->created_at)),
								))
							}}
						</h3>

						<div class="popover-content">
							<p>{{ nl2br($request->justification) }}</p>

							<div class="text-right">
								<a href="{{ url("group/requests/{$group->hash}/approve/{$request->id}") }}" class="btn btn-xs btn-success">
									{{ Lang::get('global.approve') }}
								</a>

								<a href="{{ url("group/requests/{$group->hash}/reject/{$request->id}") }}" class="btn btn-xs btn-danger">
									{{ Lang::get('global.reject') }}
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		@endforeach

		@if (count($requests) == 0)
			<ul class="list-group">
				<li class="list-group-item">
					{{ Lang::get('group.no_requests') }}
				</li>
			</ul>
		@endif
	</p>
</div>

<div class="modal-footer">
	<a href="{{ url("group/view/{$group->hash}") }}" class="btn btn-default">
		{{ Lang::get('global.close') }}
	</a>
</div>
