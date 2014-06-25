<div class="form-group">
	{{
		Form::label($machine_name, $name, array(
			'class' => 'control-label'
		))
	}}

	{{
		Form::text($name, $value, array(
			'class'    => 'form-control',
			'disabled' => $disabled,
		))
	}}
</div>
