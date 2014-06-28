<div class="form-group">
	{{
		Form::label($machine_name, $name, array(
			'class' => 'control-label'
		))
	}}

	{{
		Form::select($machine_name, $options, $value, array(
			'class'    => 'form-control',
			'disabled' => $disabled,
		))
	}}
</div>
