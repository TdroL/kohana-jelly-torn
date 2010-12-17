<?php defined('SYSPATH') or die('No direct script access.');

return array(
	// if unknown field type, use default
	'default_type' => 'text',
	
	// Field class prefix
	'field_prefix' => 'Torn_Field_',
	
	// html id prefix
	'prefix' => 'field-',
	
	'surfix' => (object) array(
		// hidden cached file field
		'temp' => '-tmp',
		
		// delete cached file field
		'delete_cache' => '-delete-cache',
		
		// delete old file field
		'delete_old' => '-delete-old',
	),
	
	// validation messages
	'messages' => 'validate',
	
	// use flash uploader
	'flash_uploader' => TRUE,
	
	// default controller for managing flash config and upload
	'default_controller' => TRUE,
	'default_controller_lang' => 'pl-pl',

	// CSRF protection
	'token_expiration' => '+60 minutes',
);
