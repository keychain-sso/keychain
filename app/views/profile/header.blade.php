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
			<li @if (Request::segment(2) == 'edit') class="active" @endif>
				<a href="{{ url('profile/edit/'.$user->hash) }}">
					<span class="glyphicon glyphicon-pencil"></span>
					{{ Lang::get('profile.edit_profile') }}
				</a>
			</li>

			<li @if (Request::segment(2) == 'emails') class="active" @endif>
				<a href="#">
					<span class="glyphicon glyphicon-envelope"></span>
					{{ Lang::get('profile.manage_email_addresses') }}
				</a>
			</li>

			<li @if (Request::segment(2) == 'security') class="active" @endif>
				<a href="#">
					<span class="glyphicon glyphicon-lock"></span>
					{{ Lang::get('profile.security_settings') }}
				</a>
			</li>
		</ul>
	</div>
</nav>
