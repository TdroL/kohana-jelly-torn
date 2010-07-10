<?php defined('SYSPATH') or die('No direct script access.');

class Torn_Core_Uploader
{
	public $filters = array();
	public $rules = array();
	public $callbacks = array();
	
	public $model;

	public static function init(Jelly_Field_File $field, Jelly_Model $model)
	{
		$uploader = new Torn_Uploader($field, $model);
		
		$field->filters = array();
		$field->rules = array();
		$field->callbacks = array(array($uploader, 'invoke'));
	}
	
	public static $collected = FALSE;
	
	public static function garbage_collector($chance = 5)
	{
		if(function_exists('mt_rand'))
		{
			$rand = mt_rand(0, 100);
		}
		else
		{
			$rand = rand(0, 100);
		}
		
		if(!static::$collected and !Request::$is_ajax and $chance >= $rand)
		{
			static::$collected = TRUE;
			// collect garbage
			$cache = Cache::instance();
			$i = 0;
			
			foreach(new DirectoryIterator(Kohana::$cache_dir) as $file)
			{
				$name = $file->getFilename();
				
				if($file->isFile() and preg_match('/^[a-z0-9]{32}-[a-z0-9]{32}$/iD', $name))
				{
					$data = $cache->get($name);
					$time = $file->getMTime();
					
					if(!empty($data) or ($time + $data['timestamp']) < time())
					{
						try
						{
							unlink($file->getPathname());
							$cache->delete($name);
							$i++;
						}
						catch (Exception $e) {}
					}
				}
			}
			
			Kohana::$log->add('info', 'Torn Uploader garbage collector removed :items files', array(':items' => $i));
		}
	}
	
	public function __construct(Jelly_Field_File $field, Jelly_Model $model)
	{
		$this->filters   = $field->filters;
		$this->rules     = $field->rules;
		$this->callbacks = $field->callbacks;

		if(!in_array('Upload::valid', $this->callbacks))
		{
			$this->callbacks[] = 'Upload::valid';
		}

		$this->model = $model;
	}
	
	public function invoke(Validate $array, $field)
	{		
		$value = $array[$field];
		
		echo Kohana::debug('Torn invoked', $value);
		
		if(!is_array($value))
		{
			return TRUE;
		}
		
		$validate = Validate::factory(array($field => $value))
							->filters($field, $this->filters)
							->rules($field, $this->rules)
							->callbacks($field, $this->callbacks);
						
		if(!$validate->check())
		{
			foreach($validate->errors() as $v)
			{
				list($error, $params) = $v;
				$array->error($field, $error, $params);
			}
			
			return FALSE;
		}
		
		$validate = $array->as_array();
		$value = $validate[$field];
		
		$cache = Cache::instance();
		$surfix = Kohana::config('torn')->form_tmp_file_field_surfix;
		
		if(Upload::not_empty($value)) // Upload::valid passed in Validate above
		{
			$seed = Arr::get($_POST, '__SEED__', md5(Request::current()->uri().time()));
			$tmp_name = $seed.'-'.md5_file($value['tmp_name']);
			
			if(Upload::save($value, $tmp_name, Kohana::$cache_dir) !== FALSE)
			{
				$timestamp = 24*60*60; // 24h
				$cache->set($tmp_name, array(
					'upload' => $value,
					'timestamp' => $timestamp,
				), $timestamp);
				
				$tmp_old_file = Arr::get($_POST, $field.$surfix);
				if(!empty($tmp_old_file) and file_exists(Kohana::$cache_dir.DIRECTORY_SEPARATOR.$tmp_old_file))
				{
					try
					{
						unlink(Kohana::$cache_dir.DIRECTORY_SEPARATOR.$tmp_old_file);
						$cache->delete($tmp_old_file);
					}
					catch (Exception $e) {}
				}
				
				$array[$field] = $tmp_name;
			}
		}
		else
		{
			$array[$field] = Arr::get($_POST, $field.$surfix, $value);
		}
		
		$this->model->set($field, $array[$field]);
		return TRUE;
	}
}