<div class="form-group has-feedback">
	{{
		Form::label($machine_name, $name, array(
			'class' => 'control-label'
		))
	}}

	{{
		Form::text($machine_name, $value, array(
			'class'       => 'form-control',
			'disabled'    => $disabled,
			'data-toggle' => 'datepicker',
		))
	}}

	<span class="glyphicon glyphicon-calendar text-muted form-control-feedback"></span>
</div>
