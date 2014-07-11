@extends('common.page')

@section('body')
	<div class="row">
		<div class="col-sm-12">
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

	<div class="row spacer-lg-top spacer-lg-bottom">
		<div class="col-xs-8 col-sm-5 col-md-4 col-lg-3">
			<div class="form-group has-feedback">
				{{
					Form::text('search', null, array(
						'class'       => 'form-control',
						'placeholder' => Lang::get('global.search_user'),
					))
				}}

				<span class="glyphicon glyphicon-search text-muted form-control-feedback"></span>
			</div>
		</div>
	</div>

	<div class="row">
		@foreach ($users as $user)
			<div class="col-xs-3 col-md-2">
				<div class="profile-icon">
					<span class="thumbnail spacer-sm-bottom">
						@if ( ! empty($user->avatar))
							<img src="{{ asset('uploads/avatars'.$user->avatar) }}" alt="" />
						@else
							<img src="{{ asset('img/default-avatar.png') }}" alt="" />
						@endif
					</span>

					<a href="{{ url('user/view/'.$user->hash) }}">
						{{ $user->first_name }}
						{{ $user->last_name }}
					</a>

					<small class="text-muted">
						{{ $user->emails[0]->address }}
					</small>
				</div>
			</div>
		@endforeach
	</div>

	<hr />

	<div class="row">
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
