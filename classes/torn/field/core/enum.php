<?php defined('SYSPATH') or die('No direct script access.');

class Torn_Field_Core_Enum extends Torn_Field
{

	public function input(array $attributes = array())
	{
		$this->view->set(array(
			'name' => $this->field->name,
			'value' => $this->model->__get($this->field->name),
			'choices' => array_map('__', $this->field->choices),
			'attributes' => $attributes,
		));
		
		return parent::input();
	}
}