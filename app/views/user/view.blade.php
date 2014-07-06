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

	@if (Access::check('user.edit', $user))
		<nav class="navbar navbar-default" role="navigation">
			<div class="container-fluid">
				<ul class="nav navbar-nav">
					<li>
						<a href="{{ url('user/edit/'.$user->hash) }}">
							<span class="glyphicon glyphicon-pencil"></span>
							{{ Lang::get('user.edit_profile') }}
						</a>
					</li>

					<li>
						<a href="{{ url('user/emails/'.$user->hash) }}">
							<span class="glyphicon glyphicon-envelope"></span>
							{{ Lang::get('user.manage_email_addresses') }}
						</a>
					</li>

					<li>
						<a href="{{ url('user/keys/'.$user->hash) }}">
							<span class="glyphicon glyphicon-briefcase"></span>
							{{ Lang::get('user.manage_ssh_keys') }}
						</a>
					</li>

					<li>
						<a href="{{ url('user/security/'.$user->hash) }}">
							<span class="glyphicon glyphicon-lock"></span>
							{{ Lang::get('user.security_settings') }}
						</a>
					</li>
				</ul>
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
									{{ Lang::get('user.other') }}
								@endif
							</p>
						</li>
					@endif

					<li class="list-group-item">
						<h4 class="list-group-item-heading">{{ Lang::get('user.date_of_birth') }}</h4>
						<p class="list-group-item-text">{{ date('Y-m-d', strtotime($user->date_of_birth)) }}</p>
					</li>

					<li class="list-group-item">
						<h4 class="list-group-item-heading">{{ Lang::get('user.timezone') }}</h4>
						<p class="list-group-item-text">{{ $user->timezone }}</p>
					</li>

					@foreach ($fieldView->{FieldCategories::BASIC} as $field)
						<li class="list-group-item">
							<h4 class="list-group-item-heading">{{ $field->name }}</h4>
							<p class="list-group-item-text">{{{ $field->value }}}</p>
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

					@if ( ! empty($emails->other))
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
							<p class="list-group-item-text">{{{ $field->value }}}</p>
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
					@if ( ! empty($memberships))
						@foreach ($memberships as $membership)
							<li class="list-group-item">
								<p class="list-group-item-text">
									<a href="{{ url('group/view/'.$membership->group->id) }}">
										{{ $membership->group->name }}
									</a>
								</p>
							</li>
						@endforeach
					@else
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
								<p class="list-group-item-text">{{{ $field->value }}}</p>
							</li>
						@endforeach
					</ul>
				</div>
			</div>
		</div>
	@endif

	@if ($modal !== false)
		<div class="modal modal-editor">
			<div class="modal-dialog">
				<div class="modal-content">
					@include("user.{$modal}")
				</div>
			</div>
		</div>
	@endif
@stop
