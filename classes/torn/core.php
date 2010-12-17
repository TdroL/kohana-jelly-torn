<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Torn - Jelly form "generator".
 *
 * @package    Torn
 * @author     _TdroL
 */

abstract class Torn_Core
{
	public $model;
	public $errors = array();
	public $fields = array();
	
	public static $allow_upload = array('enctype' => 'multipart/form-data');
	
	public static function factory(Jelly_Model $model)
	{
		return new Torn($model);
	}
		
	public function __construct(Jelly_Model $model)
	{
		$this->model = $model;
		
		foreach($model->meta()->fields() as $name => $jelly_field)
		{
			if(!preg_match('/Field_(?<name>.*)$/i', get_class($jelly_field), $matches))
			{
				throw new Torn_Field_Exception('Unsupported field type ":field"', array(':field' => get_class($jelly_field)));
			}
			
			$field_name = Kohana::config('torn')->field_prefix.ucfirst($matches['name']);
			
			$class = new ReflectionClass($field_name);
			
			if(!$class->isSubclassOf('Torn_Field'))
			{
				throw new Torn_Field_Exception('Field ":field" must be a subclass of Torn_Field', array(':field' => get_class($field_name)));
			}
			
			$this->fields[$name] = $class->newInstance($jelly_field, $model, $this);
		}
	}

	public function catch_errors(Validate_Exception $e)
	{
		$this->errors = $e->array->errors(Kohana::config('torn')->messages);
	}

	public function has_errors()
	{
		return !empty($this->errors);
	}

	public function errors($view = 'common/errors')
	{
		return View::factory($view)->set('errors', $this->errors);		
	}

	public function __get($key)
	{
		$field = Arr::get($this->fields, $key, NULL);
		
		if($field === NULL)
		{
			throw new Torn_Field_Exception('Unknown field ":field"', array(':field' => $key));
		}
		
		return $field;
	}
	
	public function __call($method, array $args = array())
	{
		return Torn_Helper::factory($this)->__call($method, $args);
	}
}