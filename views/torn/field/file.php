<div id="<?php echo $name ?>--container">
	<?php echo Form::file($name, $attributes + array('id' => $config->prefix.$name)); ?>
	<?php echo Form::hidden($name.$config->surfix->temp, $tmp, array('class' => 'uploader-tmp')) ?>
	<?php if(!empty($cached)): ?>
	<div class="uploader-rememberd">
		<?php echo __('Uploaded file:') ?> <?php echo $cached ?>
		<div class="uploader-delete-cache">
		<label><?php echo Form::checkbox($name.$config->surfix->delete_cache, '1') ?> <?php echo __('Cancel') ?></label>
		</div>
	</div>
	<?php endif ?>
	
	<?php if(!empty($value)): ?>
	<div class="uploader-label">
		<?php echo __('Current file:') ?> <?php echo $value ?>
		<div class="uploader-delete-old">
			<label><?php echo Form::checkbox($name.$config->surfix->delete_old, '1') ?> <?php echo __('Delete') ?></label>
		</div>
	</div>
	<?php endif ?>

	<?php if($config->flash_uploader): ?>
	<script>!window.head && document.write(unescape('%3Cscript src="<?php echo url::site('torn/media/head.load.min.js') ?>"%3E%3C/script%3E'));</script>
	<script>
		(function() {
			window.TLoadJs = window.TLoadJs || {};
			
			var list = [],
				_jQuery = '<?php echo url::site('torn/media/jquery.min.js') ?>',
				_plugins = ['<?php echo url::site('torn/media/jquery.flash.js') ?>',
							'<?php echo url::site('torn/media/jquery.TUploader.js') ?>'];
			
			if(!window.jQuery && !window.TLoadJs[_jQuery])
			{
				window.TLoadJs[_jQuery] = true;
				list.push(_jQuery);
			}
			
			for(var i in _plugins)
			{
				if(!window.TLoadJs[_plugins[i]])
				{
					window.TLoadJs[_plugins[i]] = true;
					list.push(_plugins[i]);
				}
			}
			
			head.ready(function() {
				$('#<?php echo $name ?>--container').TUploader('<?php echo url::site('torn/config') ?>');
			});
			
			if(list.length > 0)
			{
				head.js.apply(null, list);
			}
		})();
	</script>
	<?php endif ?>
</div>