<?php defined('SYSPATH') or die('No direct script access.'); 

class Torn_Core_Helper
{
	protected $parent;
	
	public static function factory(Torn $parent)
	{
		return new Torn_Helper($parent);
	}
	
	public function __construct(Torn $parent)
	{
		$this->parent = $parent;
	}
	
	public function __call($method, array $args = array())
	{
		if(!preg_match('/^__/i', $method))
		{
			$class = new ReflectionClass($this);
			
			if($class->hasMethod($method))
			{
				$method = $class->getMethod($method);
				
				if(!$method->isStatic() and $method->isPublic())
				{
					return $method->invokeArgs($this, $args);
				}
			}
		}
		return NULL;
	}

	public function check()
	{
		return $_POST and ($seed = Arr::get($_POST, '__SEED__')) and Session::instance()->get($seed, 0) > time();
	}
	
	public function open($url = NULL, array $attr = array())
	{
		$expiration = Kohana::config('torn.token_expiration');

		$seed = md5(md5(Request::current()->uri().time()).Text::random('alnum', 32));

		if(is_string($expiration))
		{
			$expiration = strtotime($expiration);
		}
		else
		{
			$expiration = time() + (int) $expiration;
		}

		Session::instance()->set($seed, $expiration);
		
		return Form::open($url, $attr).Form::hidden('__SEED__', $seed);
	}
	
	public function close()
	{
		return Form::close();
	}
	
	public function upload()
	{
		if(!$_FILES)
		{
			return;
		}
		
		$surfix = Kohana::config('torn')->surfix->temp;
		$surfix_len = utf8::strlen($surfix);
		
		foreach($_POST as $key => $tmp_name)
		{
			if(utf8::substr($key, -$surfix_len) == $surfix)
			{
				$field = utf8::substr($key, 0, -$surfix_len);
				$this->parent->model->set($field, $tmp_name);
			}
		}
		
		$cache = Cache::instance();
		
		foreach($_FILES as $key => $upload)
		{
			$this->parent->model->set($key, $upload);
			
			if(!isset($this->parent->fields[$key]) or !($this->parent->fields[$key] instanceof Torn_Field_File))
			{
				continue;
			}
			
			if (upload::not_empty($upload) AND upload::valid($upload))
			{
				$seed = Arr::get($_POST, '__SEED__', md5(Request::current()->uri().time()));
				$tmp_name = $seed.'-'.md5_file($upload['tmp_name']);
				
				if(upload::save($upload, $tmp_name, Kohana::$cache_dir) !== FALSE)
				{
					$timestamp = 24*60*60;
					$cache->set($tmp_name, array(
						'upload' => $upload,
						'timestamp' => $timestamp,
					), $timestamp);
					
					$tmp_old_file = Arr::get($_POST, $key.$surfix);
					if(!empty($tmp_old_file) and file_exists(Kohana::$cache_dir.DIRECTORY_SEPARATOR.$tmp_old_file))
					{
						try
						{
							unlink(Kohana::$cache_dir.DIRECTORY_SEPARATOR.$tmp_old_file);
							$cache->delete($tmp_old_file);
						}
						catch (Exception $e) {}
					}
					
					$this->parent->model->set($key, $tmp_name);
				}
			}
		}
	}
}
