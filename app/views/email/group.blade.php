<p>{{ Lang::get('email.salutation', array('user' => $user['name'])) }}</p>

<p>{{ $action }}</p>

@if (isset($justification))
	<p>{{{ $justification }}}</p>
@endif

<p>{{ Lang::get('email.link_login', array('title' => Config::get('app.title'))) }}</p>
<p>{{ url('auth/login') }}</p>

<hr />
<em>{{ Lang::get('email.system_generated') }}</em>
