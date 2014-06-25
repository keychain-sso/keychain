<div class="form-group">
	{{
		Form::label($machine_name, $name, array(
			'class' => 'control-label'
		))
	}}

	{{
		Form::select($name, $options, $value, array(
			'class'    => 'form-control',
			'disabled' => $disabled,
		))
	}}
</div>
