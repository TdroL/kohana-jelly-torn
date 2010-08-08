
var TUploader_instances = {};
var TUploader_instances_counter = 0;
var TUploader_messages = {
	done: 'Done',
	error: 'Error'
};

function TUploadJsReceiver(instance, status, file, hashed, current, total) {
	
	var $el = TUploader_instances[instance];
	
	if(typeof $el != "undefined")
	{
		var $form = $el.closest('form');
		var data = $form.data('torn-locked');
		
		switch(status)
		{
			case 'uploading':
			{
				if(file == 'null' || !file.length)
				{
					$form.TUnlock($form, instance);
					$el.find('.uploader-progress').text('');
				}
				else
				{
					$form.TLock($form, instance);
					$el.find('.uploader-progress').text(file+' '+(Math.round(current/(1024*10.24))/100)+'MiB / '+(Math.round(total/(1024*10.24))/100)+'MiB');
					$el.find('.uploader-cancellink').css('display', $el.find('.uploader-cancellink').data('display'));
				}
				break;
			}
			
			case 'done':
			{
				$form.TUnlock($form, instance);
				
				$el.find('.uploader-progress').text(file+' '+(Math.round(current/(1024*10.24))/100)+'MiB / '+(Math.round(total/(1024*10.24))/100)+'MiB');
				$el.find('.uploader-progress').append(' '+TUploader_messages.done);
				$el.find('.uploader-rememberd').hide();
				$el.find('input.uploader-tmp').val(hashed);
				
				if(!$form.TLocked($form))
				{
					$form.submit();
				}
				
				break;
			}
			
			case 'error':
			{
				$form.TUnlock($form, instance);
				$el.find('.uploader-progress').append(' '+TUploader_messages.error);
				$el.find('.uploader-cancellink').hide();
				break;
			}
			
			case 'canceled':
			{
				$form.TUnlock($form, instance);
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

jQuery.fn.TLock = function(form, index)
{
	var data = form.data('torn-locked');
	
	if(!$.isArray(data)) data = [];
	
	data[index] = 1;
	
	form.data('torn-locked', data);
};

jQuery.fn.TUnlock = function(form, index)
{
	var data = form.data('torn-locked');

	if(!$.isArray(data)) data = [];
	
	data[index] = 0;
	
	form.data('torn-locked', data);
};

jQuery.fn.TLocked = function(form)
{
	var data = form.data('torn-locked');
	
	if(!$.isArray(data)) data = [];
	
	var sum = 0;
		
	data.foreach(function(v){
		sum += v;
	});
	
	return sum > 0;
};

jQuery.fn.TUploader = function(config)
{
	var $el = $(this);
	var $form = $el.closest('form');

	$form.submit(function(){
		var data = $form.data('torn-locked');
		var sum = 0;
		
		data.foreach(function(v){
			sum += v;
		});
		
		if(sum > 0)
		{
			$form.data('torn-trigger', true);
			return false;
		}
	});

	if(!$.browser.flash)
	{
		return $el;
	}
	
	$.getJSON(config, function(json){
	
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