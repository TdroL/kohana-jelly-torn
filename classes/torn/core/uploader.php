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
		
		if(!Torn_Uploader::$collected and !Request::$is_ajax and $chance >= $rand)
		{
			Torn_Uploader::$collected = TRUE;
			// collect garbage
			$cache = Cache::instance();
			$i = 0;
			
			foreach(new DirectoryIterator(Kohana::$cache_dir) as $file)
			{
				$name = $file->getFilename();
				
				if($file->isFile() and preg_match('/^[a-z0-9]{32}-[a-z0-9]{32}$/iD', $name))
				{
					$data = $cache->get($name);
					
					if(!empty($data) and $data['timestamp'] < time())
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
		
		if(!is_array($value))
		{
			return TRUE;
		}
		
		$cache = Cache::instance();
		$surfix = Kohana::config('torn')->surfix;
		
		$cached = $this->model->get($field.$surfix);
		$current = $this->model->get($field, FALSE);
		$used_cached = FALSE;
		$empty_check = FALSE;
		
		if(!Upload::not_empty($value) and empty($cached) and !empty($current))
		{
			$this->model->set($field, $current);
			$array[$field] = $current;
			return TRUE;
		}
		
		if(!Upload::not_empty($value) and preg_match('/^[a-z0-9]{32}-[a-z0-9]{32}$/iD', $cached))
		{
			$value = Arr::get($cache->get($cached), 'upload', $value);
			$used_cached = TRUE;
		}
		
		$modified_rules = $this->rules;
		
		foreach($this->rules as $rule => $params)
		{
			if(!utf8::strcasecmp($rule, 'Upload::not_empty'))
			{
				unset($modified_rules[$rule]);
				$modified_rules['Torn_Uploader::not_empty'] = NULL;
				break;
			}
		}
		
		$validate = Validate::factory(array($field => $value))
							->filters($field, $this->filters)
							->rules($field, $modified_rules)
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
		
		if(($tmp_name = Torn_Uploader::upload_to_cache($value, $field)) and !$used_cached) // Upload::valid passed in Validate above
		{
			$array[$field] = $tmp_name;
		}
		else
		{
			$array[$field] = Arr::get($_POST, $field.$surfix, $value);
		}
		
		$this->model->set($field, $array[$field]);
		return TRUE;
	}
	
	public static function upload_to_cache(array $file, $field)
	{
		if(Upload::not_empty($file)) // Upload::valid passed in Validate above
		{
			$cache = Cache::instance();
			$surfix = Kohana::config('torn')->surfix;
			
			$seed = Arr::get($_POST, '__SEED__', md5(Request::current()->uri().time()));
			$tmp_name = $seed.'-'.md5_file($file['tmp_name']);
			
			if(Upload::save($file, $tmp_name, Kohana::$cache_dir) !== FALSE)
			{
				$timestamp = time() + 24*60*60; // 24h
				$file['tmp_name'] = $tmp_name;
				$cache->set($tmp_name, array(
					'upload' => $file,
					'timestamp' => $timestamp,
				), $timestamp);
				
				$tmp_old_file = Arr::get($_POST, $field.$surfix, Arr::get($_POST, 'old_file')); // old_file - Flash uploader
				if(!empty($tmp_old_file) and file_exists(Kohana::$cache_dir.DIRECTORY_SEPARATOR.$tmp_old_file))
				{
					try
					{
						unlink(Kohana::$cache_dir.DIRECTORY_SEPARATOR.$tmp_old_file);
						$cache->delete($tmp_old_file);
					}
					catch (Exception $e) {}
				}
				
				return $tmp_name;
			}
		}
		
		return FALSE;
	}
	
	public static function not_empty(array $file)
	{
		return isset($file['error'])
			   AND isset($file['tmp_name'])
			   AND $file['error'] === UPLOAD_ERR_OK
			   AND (is_uploaded_file($file['tmp_name'])
			     OR file_exists(Kohana::$cache_dir.DIRECTORY_SEPARATOR.$file['tmp_name'])
			   );
	}
}