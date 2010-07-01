<?php echo Form::textarea($name, $value, $attributes + array(
	'id' => $config->form_id_prefix.$name,
	'rows' => 8,
	'cols' => 40,
)); ?>