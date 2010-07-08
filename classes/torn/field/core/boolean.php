<?php defined('SYSPATH') or die('No direct script access.');

class Torn_Field_Core_Boolean extends Torn_Field
{

	public function input(array $attributes = array())
	{
		$choices = array_intersect_key(array_map('__', $this->field->choices), array(0, 1));
		$value = (int) $this->model->__get($this->field->name);
		
		if(!array_key_exists($value, $choices))
		{
			$value = $this->field->default;
		}
		
		$this->view->set(array(
			'name' => $this->field->name,
			'value' => $value,
			'choices' => $choices,
			'attributes' => $attributes,
		));
		
		return parent::input();
	}
	
	public function __toString()
	{
		$choices = array_intersect_key(array_map('__', $this->field->choices), array(0, 1));
		$value = (int) $this->model->__get($this->field->name);
		
		if(!array_key_exists($value, $choices))
		{
			$value = $this->field->default;
		}
		
		return (string) $choices[$value];
	}
}