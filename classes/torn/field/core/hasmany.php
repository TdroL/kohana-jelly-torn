<?php defined('SYSPATH') or die('No direct script access.');

class Torn_Field_Core_HasMany extends Torn_Field
{

	public function input(array $attributes = array())
	{
		$value = $this->model->__get($this->field->name);
		
		$ids = array();

		foreach ($value as $model)
		{
			$ids[] = $model->id();
		}
		
		$this->view->set(array(
			'name' => $this->field->name,
			'ids' => $ids,
			'foreign' => $this->field->foreign,
			'attributes' => $attributes,
		));
		
		return parent::input();
	}
}