<div class="form-group">
	{{
		Form::label($machine_name, $name, array(
			'class' => 'control-label'
		))
	}}

	{{
		Form::checkbox($machine_name, Flags::YES, $value == Flags::YES, array(
			'class'    => 'form-control',
			'disabled' => $disabled,
		))
	}}
</div>
