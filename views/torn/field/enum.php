<?php echo Form::select($name, $choices, $value, $attributes + array('id' => $config->prefix.$name)); ?>