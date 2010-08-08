<?php echo Form::textarea($name, $value, $attributes + array(
	'id' => $config->prefix.$name,
	'rows' => 8,
	'cols' => 40,
)); ?>