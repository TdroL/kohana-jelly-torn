<?php defined('SYSPATH') or die('No direct script access.');

class Torn_Field_Core_Password extends Torn_Field
{

	public function input(array $attributes = array())
	{
		$this->view->set(array(
			'name' => $this->field->name,
			'value' => $this->model->__get($this->field->name),
			'attributes' => $attributes,
		));
		
		return parent::input();
	}
}