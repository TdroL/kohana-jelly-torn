<?php defined('SYSPATH') or die('No direct script access.');

class Torn_Field_Core_BelongsTo extends Torn_Field
{

	public function input(array $attributes = array())
	{
		$options = Jelly::select($this->field->foreign['model'])->execute()->as_array(':primary_key', ':name_key');
		
		if($this->field->null)
		{
			$options = array_merge(array(0 => __('None')), $options);
		}
		
		$this->view->set(array(
			'name' => $this->field->name,
			'value' => $this->model->__get($this->field->name),
			'options' => $options,
			'attributes' => $attributes,
		));
		
		return parent::input();
	}
}