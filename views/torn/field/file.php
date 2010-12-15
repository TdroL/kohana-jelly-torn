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
	<script>!window.head && document.write(unescape('%3Cscript src="<?php echo url::site('torn/media/head.load.min.js') ?>"%3E%3C/script%3E'));</script>
	<script>
		(function() {
			window.TornLoadJs = window.TornLoadJs || {};
			
			var list = [],
				_jQuery = '<?php echo url::site('torn/media/jquery.min.js') ?>',
				_plugins = ['<?php echo url::site('torn/media/jquery.flash.js') ?>',
							'<?php echo url::site('torn/media/jquery.TUploader.js') ?>'];
			
			if(!window.jQuery)
			{
				if(!window.TornLoadJs[_jQuery])
				{
					window.TornLoadJs[_jQuery] = true;
					list.push(_jQuery);
				}
			}
			
			for(var i in _plugins)
			{
				if(!window.TornLoadJs[_plugins[i]])
				{
					window.TornLoadJs[_plugins[i]] = true;
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