@extends('common.page')

@section('body')
	<div class="text-center">
		@if ( ! empty($user->avatar))
			<img src="{{ asset("uploads/avatars/{$user->avatar}") }}" class="img-circle img-thumbnail" alt="" />
		@else
			<img src="{{ asset('img/default-avatar.png') }}" class="img-circle img-thumbnail" alt="" />
		@endif

		<h1>{{{ $user->name }}}</h1>
		<p>{{{ $user->title }}}</p>
	</div>

	@if ($editor || $manager)
		<nav class="navbar navbar-default" role="navigation">
			<div class="container-fluid">
				@if ($editor)
					<ul class="nav navbar-nav">
						<li>
							<a href="{{ url("user/edit/{$user->hash}") }}">
								<span class="glyphicon glyphicon-pencil"></span>
								{{ Lang::get('user.edit_profile') }}
							</a>
						</li>

						<li>
							<a href="{{ url("user/emails/{$user->hash}") }}">
								<span class="glyphicon glyphicon-envelope"></span>
								{{ Lang::get('user.manage_emails') }}
							</a>
						</li>

						<li>
							<a href="{{ url("user/keys/{$user->hash}") }}">
								<span class="glyphicon glyphicon-briefcase"></span>
								{{ Lang::get('user.manage_ssh_keys') }}
							</a>
						</li>

						<li>
							<a href="{{ url("user/security/{$user->hash}") }}">
								<span class="glyphicon glyphicon-lock"></span>
								{{ Lang::get('user.security_settings') }}
							</a>
						</li>
					</ul>
				@endif

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
										<a href="{{ url("user/permissions/{$user->hash}") }}">
											{{ Lang::get('user.user_permissions') }}
										</a>
									</li>
								@endif

								@if ($manager)
									<li>
										<a href="#" data-toggle="confirm" data-href="{{ url("user/delete/{$user->hash}") }}"
										   data-prompt="{{ Lang::get('global.click_again') }}"
										   data-wait="{{ Lang::get('global.please_wait') }}">
											{{ Lang::get('user.delete_user') }}
										</a>
									</li>
								@endif
							</ul>
						</li>
					</ul>
				@endif
			</div>
		</nav>
	@endif

	<div class="row">
		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="panel-title">{{ Lang::get('user.basic_info') }}</div>
				</div>

				<ul class="list-group">
					@if ( ! empty($user->gender))
						<li class="list-group-item">
							<h4 class="list-group-item-heading">{{ Lang::get('user.gender') }}</h4>

							<p class="list-group-item-text">
								@if ($user->gender == 'M')
									{{ Lang::get('user.male') }}
								@elseif ($user->gender == 'F')
									{{ Lang::get('user.female') }}
								@else
									{{ Lang::get('user.not_set') }}
								@endif
							</p>
						</li>
					@endif

					@if ( ! empty($user->date_of_birth))
						<li class="list-group-item">
							<h4 class="list-group-item-heading">{{ Lang::get('user.date_of_birth') }}</h4>
							<p class="list-group-item-text">{{ date('Y-m-d', strtotime($user->date_of_birth)) }}</p>
						</li>
					@endif

					<li class="list-group-item">
						<h4 class="list-group-item-heading">{{ Lang::get('user.timezone') }}</h4>
						<p class="list-group-item-text">{{ Utilities::cleanString($user->timezone) }}</p>
					</li>

					@foreach ($fieldView->{FieldCategories::BASIC} as $field)
						<li class="list-group-item">
							<h4 class="list-group-item-heading">{{ $field->name }}</h4>
							<p class="list-group-item-text">{{ nl2br($field->value) }}</p>
						</li>
					@endforeach
				</ul>
			</div>
		</div>

		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="panel-title">{{ Lang::get('user.contact_info') }}</div>
				</div>

				<ul class="list-group">
					<li class="list-group-item">
						<h4 class="list-group-item-heading">{{ Lang::get('user.primary_email') }}</h4>

						<p class="list-group-item-text">
							{{ $emails->primary->address }}

							@if ($emails->primary->verified)
								<span class="glyphicon glyphicon-ok-sign text-muted" title="{{ Lang::get('user.verified') }}"
								      data-toggle="tooltip"></span>
							@endif
						</p>
					</li>

					@if (isset($emails->other))
						<li class="list-group-item">
							<h4 class="list-group-item-heading">{{ Lang::get('user.other_email') }}</h4>

							<ul class="list-group-item-text list-unstyled">
								@foreach ($emails->other as $email)
									<li>
										{{ $email->address }}

										@if ($email->verified)
											<span class="glyphicon glyphicon-ok-sign text-muted" title="{{ Lang::get('user.verified') }}"
											      data-toggle="tooltip"></span>
										@endif
									</li>
								@endforeach
							</ul>
						</li>
					@endif

					@foreach ($fieldView->{FieldCategories::CONTACT} as $field)
						<li class="list-group-item">
							<h4 class="list-group-item-heading">{{ $field->name }}</h4>
							<p class="list-group-item-text">{{ nl2br($field->value) }}</p>
						</li>
					@endforeach
				</ul>
			</div>
		</div>

		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="panel-title">{{ Lang::get('user.group_memberships') }}</div>
				</div>

				<ul class="list-group">
					@foreach ($memberships as $membership)
						<li class="list-group-item">
							<p class="list-group-item-text">
								<a href="{{ url("group/view/{$membership->group->hash}") }}">
									{{ $membership->group->name }}
								</a>
							</p>
						</li>
					@endforeach

					@if (count($memberships) == 0)
						<li class="list-group-item">
							<p class="list-group-item-text">{{ Lang::get('user.no_memberships') }}</p>
						</li>
					@endif
				</ul>
			</div>
		</div>
	</div>

	@if ( ! empty($fieldView->{FieldCategories::OTHER}))
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="panel-title">{{ Lang::get('user.other_details') }}</div>
					</div>

					<ul class="list-group">
						@foreach ($fieldView->{FieldCategories::OTHER} as $field)
							<li class="list-group-item">
								<h4 class="list-group-item-heading">{{ $field->name }}</h4>
								<p class="list-group-item-text">{{ nl2br($field->value) }}</p>
							</li>
						@endforeach
					</ul>
				</div>
			</div>
		</div>
	@endif
@stop
