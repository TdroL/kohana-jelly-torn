<?php defined('SYSPATH') or die('No direct script access.'); 

abstract class Torn_Core_Field
{
	protected $field;
	protected $model;
	protected $parent;
	
	protected $view;
	
	public function __construct(Jelly_Field $field, Jelly_Model $model, Torn $parent)
	{
		$this->field = $field;
		$this->model = $model;
		$this->parent = $parent;
		
		$this->view = new View();
	}
	
	public function label($transalte = TRUE)
	{
		$label = ($transalte) ? __($this->field->label) : $this->field->label;
		
		return Form::label(Kohana::config('torn')->form_id_prefix.$this->field->name, $label);
	}
	
	public function input()
	{
		$this->view->set('config', Kohana::config('torn'));
		$this->_set_view();
		
		return $this->view;
	}
	
	public function has_error()
	{
		return isset($this->parent->errors[$this->field->name]);
	}
	
	public function error()
	{
		return $this->has_error() ? $this->parent->errors[$this->field->name] : NULL;
	}
	
	public function __toString()
	{
		return $this->model->get($this->field->name);
	}
	
	protected function _set_view()
	{
		if(preg_match('/Field_(?:Core_)?(?<field>.*)$/i', get_class($this), $matches))
		{
			$field = strtolower($matches['field']);

			if(Kohana::find_file('views/torn/field', $field))
			{
				$this->view->set_filename('torn/field/'.$field);
			}
			else
			{
				$this->view = NULL;
			}
		}
	}
}