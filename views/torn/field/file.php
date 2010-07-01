<div id="<?php echo $name ?>--container">
	<?php echo Form::file($name, $attributes + array('id' => $config->form_id_prefix.$name)); ?>
	<?php echo Form::hidden($name.$config->form_tmp_file_field_surfix, $tmp, array('class' => 'uploader-tmp')) ?>

	<div class="uploader-label"><?php echo __('Current file:') ?> <?php echo $value ?></div>
</div>
<?php if($config->flash_uploader): ?>
	<?php echo html::script('torn/media/jquery-1.4.2.min.js+jquery.plugin.1.0.3.js+jquery.flash.js+jquery.externalinterface.js+jquery.TUploader.js') ?>
	<script>
		$('#<?php echo $name ?>--container').TUploader("<?php echo url::site('torn/config') ?>");
	</script>
<?php endif ?>