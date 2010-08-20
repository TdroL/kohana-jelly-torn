<?php defined('SYSPATH') or die('No direct script access.');

class Torn_Field_Core_BelongsTo extends Torn_Field
{

	public function input(array $attributes = array())
	{
		$options = Jelly::select($this->field->foreign['model'])->execute()->as_array(':primary_key', ':name_key');
		
		$value = $this->model->__get($this->field->name);
		
		if($this->field->null OR empty($value) AND ! array_key_exists('not_empty', $this->field->rules))
		{
			$options = array_merge(array('' => __('None')), $options);
		}
		
		$this->view->set(array(
			'name' => $this->field->name,
			'value' => $value,
			'options' => $options,
			'attributes' => $attributes,
		));
		
		return parent::input();
	}
}