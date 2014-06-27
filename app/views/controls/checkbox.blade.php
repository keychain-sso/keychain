<div class="form-group">
	{{
		Form::label($machine_name, $name, array(
			'class' => 'control-label'
		))
	}}

	<div class="checkbox">
		<label>
			{{
				Form::checkbox($machine_name, Flags::YES, $value == Flags::YES, array(
					'disabled' => $disabled,
				))
			}}

			{{{ $options }}}
		</label>
	</div>
</div>
