<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Core_Torn extends Controller
{
	public function before()
	{
		parent::before();
		
		$this->request->headers['Content-Type'] = 'text/plain';
		
		if($lang = Kohana::config('torn')->default_controller_lang)
		{
			I18n::lang($lang);
		}
		
		if(class_exists('FirePHP_Profiler'))
		{
			FirePHP_Profiler::instance()->set_config('enabled', FALSE);
		}
	}
	
	public function action_upload()
	{
		$field = 'Filedata';

		if(($value = Arr::get($_FILES, $field, FALSE)) === FALSE)
		{
			$this->request->response = 'error';
			return;
		}
		
		if(!Upload::not_empty($value) or !Upload::valid($value))
		{
			$this->request->response = 'error';
			return;
		}
			
		if(($tmp_name = Torn_Uploader::upload_to_cache($value, $field)))
		{			
			$this->request->response = 'done;'.$tmp_name;
		}
		else
		{
			$this->request->response = 'error';
		}
	}
	
	public function action_cancel()
	{
		$file = $this->request->param('hash');
		
		if(!empty($file) and file_exists(Kohana::$cache_dir.DIRECTORY_SEPARATOR.$file))
		{
			try
			{
				unlink(Kohana::$cache_dir.DIRECTORY_SEPARATOR.$file);
				Cache::instance()->delete($file);
			}
			catch (Exception $e) {}
		}
		
		Kohana::$log->add('info', 'canceled :hash', array(':hash' => $file));
	}
	
	public function action_config()
	{
		$max_size = strtolower(trim(ini_get('upload_max_filesize')));
		$max_size = preg_replace('/([kmg])b/i', '$1', $max_size);
		
		$sizer = substr($max_size, -1);
		$max_size = (int) $max_size;
		
		switch($sizer)
		{
			case 'g':
			{
				$max_size *= 1024;
			}
			case 'm':
			{
				$max_size *= 1024;
			}
			case 'k':
			{
				$max_size *= 1024;
			}
		}
		
		$config = array(
			'url' => Url::base(TRUE, TRUE).Route::get('torn/upload')->uri(),
			'cancel' => Url::base(TRUE, TRUE).Route::get('torn/cancel')->uri(array('hash' => '--hash--')),
			'swf' => Url::site('torn/media/TUploader.swf'),
			'max_size' => $max_size,
			'filters' => array(
				__('All files') => '*.*',
			),
			'messages' => array(
				'file_is_too_big' => __('File is too big'),
				'config_error' => __('Torn Uploader: IO error (config)'),
				'io_error' => __('The file could not be uploaded'),
				'select_file' => __('Select file'),
				'confirm' => __('Are you sure?'),
				'cancel' => __('Cancel'),
				'error' => __('Error'),
				'done' => __('Done')
			)
		);
		
		$this->request->response = json_encode($config);
	}
	
	public function action_media()
	{	
		if($media = $this->request->param('media'))
		{
			if(preg_match('/\.swf$/iD', $media))
			{
				$file = preg_replace('/^(.+?)\.swf$/iD', '$1', $media);
				
				$this->request->headers['Content-Type'] = File::mime_by_ext('swf');
				
				if($file = Kohana::find_file('media', $file, 'swf'))
				{
					$this->request->response = file_get_contents($file);
				}
				
				return;
			}
			
			$this->request->headers['Content-Type'] = File::mime_by_ext('js');
			
			foreach(explode('+', $media) as $file)
			{
				$file = preg_replace('/^(.+?)\.js$/iD', '$1', $file);
				
				if($file = Kohana::find_file('media', $file, 'js'))
				{
					$this->request->response .= file_get_contents($file).PHP_EOL;
				}
			}
		}
	}
}