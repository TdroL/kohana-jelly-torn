<?php defined('SYSPATH') or die('No direct script access.');

return array(
	// if unknown field type, use default
	'default_type' => 'text',
	
	// Field class prefix
	'field_prefix' => 'Torn_Field_',
	
	// html ids prefix
	'prefix' => 'field-',
	
	// hidden cached file field
	'surfix' => '-tmp',
	
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
