<p>{{ Lang::get('email.salutation', array('user' => $user['name'])) }}</p>

<p>
	{{ $action }}
	{{ Lang::get('email.link_email') }}
</p>

<p>{{ $token }}</p>
<hr />
<em>{{ Lang::get('email.system_generated') }}</em>
