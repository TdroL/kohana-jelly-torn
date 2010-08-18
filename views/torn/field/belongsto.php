<?php echo Form::select(
	$name,
	$options,
	$value->id() === null ? '': $value->id(),
	$attributes + array('id' => $config->prefix.$name)
); ?>