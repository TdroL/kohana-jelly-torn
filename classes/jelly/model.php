<?php defined('SYSPATH') or die('No direct script access.');

class Jelly_Model extends Jelly_Model_Core
{
	
	public function original($key)
	{
		return Arr::get($this->_original, $key);
	}
	
	public function validate($data = NULL)
	{
		foreach ($this->_meta->fields() as $column => $field)
		{
			if($field instanceof Field_File)
			{
				Torn_Uploader::init($field, $this);
			}
		}
		
		return parent::validate($data);
	}
}