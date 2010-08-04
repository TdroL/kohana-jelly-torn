<?php defined('SYSPATH') or die('No direct script access.');

return array(
	// if unknown field type, use default
	'default_type_field' => 'text',
	
	// Field classes prefix
	'torn_field_prefix' => 'Torn_Field_',
	
	// html ids prefix
	'form_id_prefix' => 'field-',
	
	// hidden cached file field
	'form_tmp_file_field_surfix' => '-tmp',
	
	// validation messages
	'error_messages_file' => 'validate',
	
	// use flash uploader
	'flash_uploader' => TRUE,
	
	// default controller for managing flash config and upload
	'default_controller' => TRUE,
	'default_controller_lang' => 'pl-pl',

	// CSRF protection
	'token_expiration' => '+60 minutes',
);
