<?php defined('SYSPATH') or die('No direct script access.');

class Field_File extends Jelly_Field_File
{
	public $torn_filename_callback = array(__CLASS__, '__normalize_filename');
	
	public function save($model, $value, $loaded)
	{
		$original = $model->get($this->name, FALSE);
		$config = Kohana::config('torn');

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
				
				$i = 1;
				$original = $name;
				while(file_exists($this->path.$name))
				{
					$info = pathinfo($original);
					
					$name = $info['filename'].'-'.$i.'.'.$info['extension'];
					$i++;
				}
				
				rename(Kohana::$cache_dir.DIRECTORY_SEPARATOR.$value, $this->path.$name);
				$cache->delete($value);
				
				return $name;
			}
		}
		
		$tmp_field = $this->name.$config->surfix;
		
		if(is_string($value) and array_key_exists($tmp_field, $_POST)
		   and empty($value) and empty($_POST[$tmp_field]))
		{
			return $model->get($this->name, FALSE); // don't save
		}
		
		return parent::save($model, $value, $loaded);
	}
	
	public static function __normalize_filename($filename)
	{
		$encoding = 'utf-8';
		
		$name = pathinfo($filename, PATHINFO_FILENAME);
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		
		$name = iconv($encoding, 'ascii//translit', $name);

		$name = utf8::str_ireplace(',', '', $name);
		$name = utf8::str_ireplace('\'', '', $name);

		$name = preg_replace('/[^a-z0-9\-_\$\.\+\!\(\)]/i', '_', $name);
		return preg_replace('/_{2,}/i', '_', $name).'.'.$ext;
	}
}
