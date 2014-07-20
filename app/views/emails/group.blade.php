<p>{{ Lang::get('email.salutation', array('user' => $user['name'])) }}</p>

<p>{{ $action }}</p>

@if (isset($justification))
	<p>{{{ $justification }}}</p>
@endif

@if (isset($link))
	<p>{{ Lang::get('email.link_group') }}</p>
@endif

@if (isset($link))
	<p>{{ url("group/requests/{$group['hash']}") }}</p>
@endif

<hr />
<em>{{ Lang::get('email.system_generated') }}</em>
