@extends('common.page')

@section('body')
	<div class="text-center">
		@if ( ! empty($user->avatar))
			{{
				HTML::image(asset('uploads/avatars'.$user->avatar), null, array(
					'class' => 'img-circle img-thumbnail',
				))
			}}
		@else
			{{
				HTML::image(asset('img/default-avatar.png'), null, array(
					'class' => 'img-circle img-thumbnail',
				))
			}}
		@endif

		<h1>{{ sprintf('%s %s', $user->first_name, $user->last_name) }}</h1>
		<p>{{ $user->title }}</p>
	</div>

	<nav class="navbar navbar-default" role="navigation">
		<div class="container-fluid">
			<ul class="nav navbar-nav">
				<li>
					<a href="{{ url('profile/edit/'.$user->hash) }}">
						<span class="glyphicon glyphicon-pencil"></span>
						{{ Lang::get('profile.edit_profile') }}
					</a>
				</li>

				<li>
					<a href="#">
						<span class="glyphicon glyphicon-envelope"></span>
						{{ Lang::get('profile.manage_email_addresses') }}
					</a>
				</li>

				<li>
					<a href="#">
						<span class="glyphicon glyphicon-lock"></span>
						{{ Lang::get('profile.security_settings') }}
					</a>
				</li>
			</ul>
		</div>
	</nav>


	<div class="row">
		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="panel-title">{{ Lang::get('profile.basic_info') }}</div>
				</div>

				<ul class="list-group">
					@if ( ! empty($user->gender))
						<li class="list-group-item">
							<h4 class="list-group-item-heading">{{ Lang::get('global.gender') }}</h4>

							<p class="list-group-item-text">
								@if ($user->gender == 'M')
									{{ Lang::get('global.male') }}
								@elseif ($user->gender == 'F')
									{{ Lang::get('global.female') }}
								@else
									{{ Lang::get('global.other') }}
								@endif
							</p>
						</li>
					@endif

					<li class="list-group-item">
						<h4 class="list-group-item-heading">{{ Lang::get('global.date_of_birth') }}</h4>
						<p class="list-group-item-text">{{ date('jS F Y', strtotime($user->date_of_birth)) }}</p>
					</li>

					<li class="list-group-item">
						<h4 class="list-group-item-heading">{{ Lang::get('global.timezone') }}</h4>
						<p class="list-group-item-text">{{ $user->timezone }}</p>
					</li>

					@foreach ($fields->{FieldCategories::BASIC} as $field)
						<li class="list-group-item">
							<h4 class="list-group-item-heading">{{ $field->name }}</h4>
							<p class="list-group-item-text">{{ $field->value }}</p>
						</li>
					@endforeach
				</ul>
			</div>
		</div>

		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="panel-title">{{ Lang::get('profile.contact_info') }}</div>
				</div>

				<ul class="list-group">
					<li class="list-group-item">
						<h4 class="list-group-item-heading">{{ Lang::get('global.primary_email') }}</h4>

						<p class="list-group-item-text">
							<a href="mailto:{{ $emails->primary }}">{{ $emails->primary }}</a>
						</p>
					</li>

					@if ( ! empty($emails->other))
						<li class="list-group-item">
							<h4 class="list-group-item-heading">{{ Lang::get('global.other_email') }}</h4>

							<ul class="list-group-item-text list-unstyled">
								@foreach ($emails->other as $email)
									<li>
										<a href="mailto:{{ $email }}">{{ $email }}</a>
									</li>
								@endforeach
							</ul>
						</li>
					@endif

					@foreach ($fields->{FieldCategories::CONTACT} as $field)
						<li class="list-group-item">
							<h4 class="list-group-item-heading">{{ $field->name }}</h4>
							<p class="list-group-item-text">{{ $field->value }}</p>
						</li>
					@endforeach
				</ul>
			</div>
		</div>

		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="panel-title">{{ Lang::get('profile.group_memberships') }}</div>
				</div>

				<ul class="list-group">
					@if ( ! empty($memberships))
						@foreach ($memberships as $membership)
							<li class="list-group-item">
								<p class="list-group-item-text">
									<span class="glyphicon glyphicon-user"></span>

									<a href="{{ url('group/'.$membership->group->id) }}">
										{{ $membership->group->name }}
									</a>
								</p>
							</li>
						@endforeach
					@else
						<li class="list-group-item">
							<p class="list-group-item-text">{{ Lang::get('profile.no_memberships') }}</p>
						</li>
					@endif
				</ul>
			</div>
		</div>
	</div>

	@if ( ! empty($fields->{FieldCategories::OTHER}))
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="panel-title">{{ Lang::get('profile.other_details') }}</div>
					</div>

					<ul class="list-group">
						@foreach ($fields->{FieldCategories::OTHER} as $field)
							<li class="list-group-item">
								<h4 class="list-group-item-heading">{{ $field->name }}</h4>
								<p class="list-group-item-text">{{ $field->value }}</p>
							</li>
						@endforeach
					</ul>
				</div>
			</div>
		</div>
	@endif
@stop
