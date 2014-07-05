<p>
	{{
		Lang::get('email.salutation', array(
			'name' => $user['first_name'],
		))
	}}
</p>

<p>
	{{ $action }}
	{{ Lang::get('email.click_link') }}
</p>

<p>{{ $token }}</p>
<hr />
<em>{{ Lang::get('email.system_generated') }}</em>
