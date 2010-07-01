<?php defined('SYSPATH') or die('No direct script access.');

class Field_Boolean extends Jelly_Field_Boolean
{
	public $default = 0; // "No"
	
	public $choices = array(
		0 => 'No',
		1 => 'Yes',
	);
}