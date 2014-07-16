<p>{{ Lang::get('email.salutation', array('name' => $user['name'])) }}</p>

<p>
	{{ $action }}
	{{ Lang::get('email.click_link') }}
</p>

<p>{{ $token }}</p>
<hr />
<em>{{ Lang::get('email.system_generated') }}</em>
