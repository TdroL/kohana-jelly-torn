<?php defined('SYSPATH') or die('No direct script access.');

class Field_File extends Jelly_Field_File
{
	public $torn_filename_callback = array(__CLASS__, '__normalize_filename');
	
	public function save($model, $value, $loaded)
	{
		$original = $model->get($this->name, FALSE);

		if(is_string($value) and preg_match('/^[a-z0-9]{32}-[a-z0-9]{32}$/i', $value))
		{
			if(file_exists(Kohana::$cache_dir.DIRECTORY_SEPARATOR.$value))
			{
				if($this->delete_old_file AND $original != $this->default)
				{
					if(file_exists($original))
					{
						try
						{
							unlink($original);
						}
						catch (Exception $e) {}
					}
				}
				
				$cache = Cache::instance();
				
				$cached = $cache->get($value);
				$name = $cached['upload']['name'];
				
				if(is_callable($this->torn_filename_callback))
				{
					$name = call_user_func($this->torn_filename_callback, $name);
				}
				
				rename(Kohana::$cache_dir.DIRECTORY_SEPARATOR.$value, $this->path.$name);
				$cache->delete($value);
				
				return $name;
			}
		}
		
		return parent::save($model, $value, $loaded);
	}
	
	public static function __normalize_filename($filename)
	{
		$name = pathinfo($filename, PATHINFO_FILENAME);
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		$name = preg_replace('/\s++/isuD', '_', $name);
		return preg_replace('/_{2,}/isD', '_', $name).'.'.$ext;
	}
}
