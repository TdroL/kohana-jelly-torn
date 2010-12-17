var TUploader_instances = {};
var TUploader_instances_counter = 0;
var TUploader_messages = {
	done: 'Done',
	error: 'Error'
};
var TUploader_config = {};
var TUploader_queue = {};

TUploadJsReceiver = function(instance, status, file, hashed, current, total) {
	
	var $el = TUploader_instances[instance];
	
	if(typeof $el != "undefined")
	{
		var $form = $el.closest('form');
		var data = $form.data('torn.locked');
		
		switch(status)
		{
			case 'uploading':
			{
				if(file == 'null' || !file.length)
				{
					$el.find('.uploader-progress').text('');
					
					$form.TUnlock(instance);
				}
				else
				{
					$form.TLock(instance);
					
					$el.find('.uploader-progress').text(file+' '+(Math.round(current/(1024*10.24))/100)+'MiB / '+(Math.round(total/(1024*10.24))/100)+'MiB');
					$el.find('.uploader-cancellink').css('display', $el.find('.uploader-cancellink').data('display'));
				}
				break;
			}
			
			case 'done':
			{
				$el.find('.uploader-progress').text(file+' '+(Math.round(current/(1024*10.24))/100)+'MiB / '+(Math.round(total/(1024*10.24))/100)+'MiB');
				$el.find('.uploader-progress').append(' '+TUploader_messages.done);
				$el.find('.uploader-rememberd').hide();
				$el.find('input.uploader-tmp').val(hashed);
				
				$form.TUnlock(instance);
				break;
			}
			
			case 'error':
			{
				$el.find('.uploader-progress').append(' '+TUploader_messages.error);
				$el.find('.uploader-cancellink').hide();
				
				$form.TUnlock(instance, false);
				break;
			}
			
			case 'canceled':
			{
				$form.TUnlock(instance, false);
				$el.find('.uploader-progress').text('Anulowano');
				
				if(!$el.find('input.uploader-tmp').val().length)
				{
					$el.find('.uploader-cancellink').hide();
				}
				break;
			}
		}
	}
}

$.fn.TLock = function(index)
{
	var $form = $(this),
		data = $form.data('torn.locked');
	
	if(!$.isArray(data)) data = [];
	
	data[index] = 1;
	
	$form.data('torn.locked', data);
};

$.fn.TUnlock = function(index, status)
{
	var $form = $(this),
		data = $form.data('torn.locked');
	
	if(!$.isArray(data)) data = [];
	
	data[index] = 0;
	
	$form.data('torn.locked', data);
	
	status = status || true;
	
	if(!status)
	{
		$form.data('torn.freezed', false);
	}
	
	if($form.data('torn.freezed') === true && !$form.TLocked())
	{
		$form.submit();
	}
};

$.fn.TLocked = function()
{
	var data = $(this).data('torn.locked');
	
	if(!$.isArray(data)) data = [];
	
	for(var i in data)
	{
		if(data[i])
		{
			return true;
		}
	}
	
	return false;
};

function getConfig(config, fn)
{
	TUploader_config[config] = TUploader_config[config] || null;
	
	if(TUploader_config[config] === null)
	{
		TUploader_queue[config] = TUploader_queue[config] || [];
		TUploader_queue[config].push(fn);
		
		if(TUploader_queue[config].length == 1)
		{
			$.getJSON(config, function(json) {
				TUploader_config[config] = json;
				
				for(var i in TUploader_queue[config])
				{
					var fn = TUploader_queue[config][i];
					fn(TUploader_config[config]);
				}
			});
		}
		
		return;
	}
	
	fn(TUploader_config[config]);
}

$.fn.TUploader = function(config)
{
	if(!$.fn.flash.hasFlash())
	{
		return $el;
	}
	
	var $el = $(this);
	var $form = $el.closest('form');

	$form.submit(function(){
		if($form.TLocked())
		{
			$form.data('torn.freezed', true);
			return false;
		}
	});
	
	getConfig(config, function(json){
	
		var instance = 'TUploader_'+TUploader_instances_counter;
		TUploader_instances_counter++;
		TUploader_instances[instance] = $el;
		TUploader_messages = json.messages;
		
		var $container = $('<div class="uploader-container" />');
		$container.append('<a href="#browse" class="uploader-fakelink" />');
		$container.append(' ');
		$container.append('<a href="#cancel" class="uploader-cancellink" />');
		$container.append('<div class="uploader-progress" />');
		$container.append('<div class="uploader-embed" />');
	
		$el.find('input[type=file]').after($container).hide();
		$el.find('.uploader-delete-cache').remove();
		
		var $fakelink = $container.find('.uploader-fakelink');
		var $cancellink = $container.find('.uploader-cancellink');
		
		$fakelink
			.text(json.messages.select_file)
			.click(function(){ return false; });
			
		$cancellink
			.text(json.messages.cancel)
			.data('display', $cancellink.css('display'))
			.click(function(){
				if(confirm(json.messages.confirm))
				{
					$el.find('input.uploader-tmp').val('');
					$el.find('.uploader-progress').text('');
					$el.find('.uploader-embed embed').get(0).cancel();
					$cancellink.hide();
				}
				return false;
			});
		
		if (!$el.find('input.uploader-tmp').val().length)
		{
			$cancellink.hide();
		}
		
		$container.find('.uploader-embed').flash({
			src: json.swf,
			width: $fakelink.outerWidth(),
			height: $fakelink.outerHeight(),
			scale: 'exactfit',
			allowscriptaccess: 'always',
			wmode: 'transparent',
			flashvars: {
				'instance': instance,
				'config': config
			}
		}, {expressInstall: true});
		
		$container.css('position', 'relative');
		$container.find('.uploader-embed').css({
			position: 'absolute',
			top: $fakelink.position().top,
			left: $fakelink.position().left,
		});
	});
	
	return $el;
}