<div id="<?php echo $name ?>--container">
	<?php echo Form::file($name, $attributes + array('id' => $config->prefix.$name)); ?>
	<?php echo Form::hidden($name.$config->surfix, $tmp, array('class' => 'uploader-tmp')) ?>
	<?php if(!empty($cached)): ?>
	<div class="uploader-rememberd"><?php echo __('Uploaded file:') ?> <?php echo $cached ?></div>
	<?php endif ?>
	
	<?php if(!empty($value)): ?>
	<div class="uploader-label"><?php echo __('Current file:') ?> <?php echo $value ?></div>
	<?php endif ?>

	<?php if($config->flash_uploader): ?>
	<?php echo html::script('torn/media/jquery-1.4.2.min.js+jquery.plugin.1.0.3.js+jquery.flash.js+jquery.TUploader.js') ?>
	<script>
		$('#<?php echo $name ?>--container').TUploader('<?php echo url::site('torn/config') ?>');
	</script>
	<?php endif ?>
</div>