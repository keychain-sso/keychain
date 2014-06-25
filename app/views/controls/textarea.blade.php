<div class="form-group">
	{{
		Form::label($machine_name, $name, array(
			'class' => 'control-label'
		))
	}}

	{{
		Form::textarea($name, $value, array(
			'class'    => 'form-control',
			'rows'     => 3,
			'disabled' => $disabled,
		))
	}}
</div>
