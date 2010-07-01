<?php defined('SYSPATH') or die('No direct script access.');

class Torn_Field_Core_File extends Torn_Field
{

	public function input(array $attributes = array())
	{
		$tmp = $this->model->__get($this->field->name);
		
		if(!is_string($tmp))
		{
			$tmp = NULL;
		}
		
		$this->view->set(array(
			'name' => $this->field->name,
			'value' => $this->model->get($this->field->name, FALSE),
			'tmp' => $tmp,
			'attributes' => $attributes,
		));
		
		return parent::input();
	}
}