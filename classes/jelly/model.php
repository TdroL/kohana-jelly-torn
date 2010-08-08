<?php defined('SYSPATH') or die('No direct script access.');

class Jelly_Model extends Jelly_Model_Core
{
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
	
	public function save($key = NULL)
	{
		parent::save($key);
		
		$seed = Arr::get($_POST, '__SEED__') and Session::instance()->delete($seed);
		
		return $this;
	}

	public function delete($key = NULL)
	{
		if(parent::delete($key))
		{
			$seed = Arr::get($_POST, '__SEED__') and Session::instance()->delete($seed);
			
			return TRUE;
		}
		
		return FALSE;
	}
}
