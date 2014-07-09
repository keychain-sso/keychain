<nav class="navbar navbar-default navbar-static-top navbar-modal">
	<div class="container-fluid">
		<a href="{{ url('group/view/'.$group->hash) }}" class="close">&times;</a>

		<div class="text-center">
			<h3>{{ $title }}</h3>
		</div>
	</div>
</nav>

@include('common.alerts')
