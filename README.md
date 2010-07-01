Torn is a form generator for [Jelly](http://github.com/jonathangeiger/kohana-jelly).

# Requirements

* [Jelly](http://github.com/jonathangeiger/kohana-jelly) 0.9.6.2
* [Cache](http://github.com/kohana/cache)

# Features

* Extendable fields
* Build-in flash uploader

# Examples

Add Torn to submodules list in bootstrap.php **before** Jelly:

	Kohana::modules(array(
		...
		'jelly-torn' => MODPATH.'jelly-torn',
		'jelly' => MODPATH.'jelly',
		...
	));
	
Example - action (with file upload):

	public function action_update()
	{
		$post = Jelly::select('post', $this->request->param('id'));
		
		$torn = new Torn($post);
		
		$this->template->content->form = $torn;
		
		if($_POST)
		{
			try
			{
				$post->set($_FILES + $_POST);
				$post->save();
			}
			catch (Validate_Exception $e)
			{
				$torn->catch_errors($e);
			}
		}
	}

Example - view:

	<?php echo $form->open(NULL, Form::$allow_upload) ?>
	
	<?php if($form->has_errors()): ?>
	<div class="errors">
		<h3>Errors</h3>
		<ul>
		<?php foreach($form->errors as $error): ?>
			<li><?php echo $error ?></li>
		<?php endforeach ?>
		</ul>
	</div>
	<?php endif ?>
	
	<dl>
		<dt><?php echo $form->title->label() ?></dt>
		<dd>
			<?php echo $form->title->input() ?>
		</dd>
		
		<dt><?php echo $form->body->label() ?></dt>
		<dd>
			<?php echo $form->body->input() ?>
		</dd>
		
		<dt><?php echo $form->file->label() ?></dt>
		<dd>
			<?php echo $form->file->input() ?>
		</dd>
		
		<dd><input type="submit" value="Update" /></dd>
	</dl>
	
	<?php echo $form->close() ?>
