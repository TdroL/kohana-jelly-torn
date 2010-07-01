<?php defined('SYSPATH') or die('No direct script access.');

class Torn_Field_Core_BelongsTo extends Torn_Field
{

	public function input(array $attributes = array())
	{
		$this->view->set(array(
			'name' => $this->field->name,
			'value' => $this->model->__get($this->field->name),
			'options' => Jelly::select($this->field->foreign['model'])->execute()->as_array(':primary_key', ':name_key'),
			'attributes' => $attributes,
		));
		
		return parent::input();
	}
}