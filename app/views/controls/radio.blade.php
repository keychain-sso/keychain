<div class="form-group">
	{{
		Form::label($machine_name, $name, array(
			'class' => 'control-label'
		))
	}}

	@foreach ($options as $option)
		<div class="radio">
			<label>
				{{
					Form::radio($machine_name, $option, $option == $value, array(
						'disabled' => $disabled,
					))
				}}

				{{{ $option }}}
			</label>
		</div>
	@endforeach
</div>
