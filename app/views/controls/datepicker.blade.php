<div class="form-group">
	{{
		Form::label($machine_name, $name, array(
			'class' => 'control-label'
		))
	}}

	<div class="input-group">
		{{
			Form::text($machine_name, $value, array(
				'class'       => 'form-control',
				'disabled'    => $disabled,
				'data-toggle' => 'datepicker',
			))
		}}

		<span class="input-group-addon">
			<span class="glyphicon glyphicon-calendar"></span>
		</span>
	</div>
</div>
