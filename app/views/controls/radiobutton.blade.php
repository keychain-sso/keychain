@foreach ($options as $option)
	{{
		Form::radio("{$name}[]", $value, $option == $value, array(
			'class'    => 'form-control',
			'disabled' => $disabled,
		))
	}}
@endforeach
