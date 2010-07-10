<?php defined('SYSPATH') or die('No direct script access.');

if(Kohana::config('torn')->default_controller)
{
	Route::set('torn/upload', 'torn/upload')
		->defaults(array(
			'controller' => 'torn',
			'action'     => 'upload',
		));
		
	Route::set('torn/config', 'torn/config')
		->defaults(array(
			'controller' => 'torn',
			'action'     => 'config',
		));
		
	Route::set('torn/cancel', 'torn/cancel(/<hash>)',
		array(
			'hash' => '(?:[a-z0-9]{32}-[a-z0-9]{32})|(?:--hash--)'
		))
		->defaults(array(
			'controller' => 'torn',
			'action'     => 'cancel',
			'hash'       => NULL,
		));
		
	Route::set('torn/media', 'torn/media/<media>',
		array(
			'media' => '(.+?\.(js|swf))(\+.+?\.js)*',
		))
		->defaults(array(
			'controller' => 'torn',
			'action'     => 'media',
		));
}

Torn_Uploader::garbage_collector();