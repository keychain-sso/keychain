<nav class="navbar navbar-default navbar-static-top navbar-modal">
	<div class="container-fluid">
		@if (empty($group->id))
			<a href="{{ url('group/list') }}" class="close">&times;</a>
		@else
			<a href="{{ url("group/view/{$group->hash}") }}" class="close">&times;</a>
		@endif

		<div class="text-center">
			<h3 class="spacer-none-top">{{ $title }}</h3>
		</div>
	</div>
</nav>

@include('common.alerts')
