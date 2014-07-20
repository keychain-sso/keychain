<p>{{ Lang::get('email.salutation', array('user' => $user['name'])) }}</p>

<p>
	{{ $action }}

	@if (isset($justification))
		{{ $justification }}
	@endif

	@if (isset($link))
		{{ Lang::get('email.link_group') }}
	@endif
</p>

@if (isset($link))
	<p>
		{{ url("group/requests/{$group['hash']}") }}
	</p>
@endif

<hr />
<em>{{ Lang::get('email.system_generated') }}</em>
