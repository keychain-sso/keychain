@foreach ($items as $item)
	<li><a href="#" data-value="{{ $item->id }}">{{{ $item->name }}}</a></li>
@endforeach
