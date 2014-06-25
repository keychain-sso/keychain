<div class="form-group">
	{{
		Form::label($machine_name, $name, array(
			'class' => 'control-label'
		))
	}}

		@foreach ($options as $option)
		{{
			Form::radio("{$name}[]", $value, $option == $value, array(
				'class'    => 'form-control',
				'disabled' => $disabled,
			))
		}}
	@endforeach
</div>
