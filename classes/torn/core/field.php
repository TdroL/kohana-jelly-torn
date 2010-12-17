<?php defined('SYSPATH') or die('No direct script access.'); 

abstract class Torn_Core_Field
{
	protected $field;
	protected $model;
	protected $parent;
	
	protected $view;
	public $filename;
	
	public function __construct(Jelly_Field $field, Jelly_Model $model, Torn $parent)
	{
		$this->field = $field;
		$this->model = $model;
		$this->parent = $parent;
		
		$this->view = new View();
		$this->filename = 'torn/field/'.$this->_get_filename();
	}
	
	public function label($transalte = TRUE)
	{
		$label = ($transalte) ? __($this->field->label) : $this->field->label;
		
		return Form::label(Kohana::config('torn')->prefix.$this->field->name, $label);
	}
	
	public function input()
	{
		$this->view->set('config', Kohana::config('torn'));
		$this->view->set_filename($this->filename);
		
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
	
	public function value()
	{
		return $this->model->__get($this->field->name);
	}
	
	public function __toString()
	{
		return (string) $this->model->__get($this->field->name);
	}
	
	public function set_filename($filename, $torn_dir = FALSE)
	{
		$this->filename = $filename;
		if($torn_dir !== FALSE)
		{
			$this->filename = 'torn/field/'.$filename;
		}
		
		return $this;
	}
	
	protected function _get_filename()
	{
		if(preg_match('/Field_(?:Core_)?(?<field>.*)$/i', get_class($this), $matches))
		{
			$field = strtolower($matches['field']);
			if(Kohana::find_file('views/torn/field', $field))
			{
				return $field;
			}
		}
		return NULL;
	}
}