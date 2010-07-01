
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
		if(status == 'uploading')
		{
			if(file == 'null' || file == '')
			{
				$el.find('.uploader-progress').text('');
			}
			else
			{
				$el.find('.uploader-progress').text(file+' '+(Math.round(current/(1024*10.24))/100)+'MiB / '+(Math.round(total/(1024*10.24))/100)+'MiB');
			}
		}
		
		if(status == 'done')
		{
			$el.find('.uploader-progress').text(file+' '+(Math.round(current/(1024*10.24))/100)+'MiB / '+(Math.round(total/(1024*10.24))/100)+'MiB');
			$el.find('.uploader-progress').append(' '+TUploader_messages.done);
			$el.find('input.uploader-tmp').val(hashed);
			$el.find('.uploader-cancellink').css('display', $el.find('.uploader-cancellink').data('display'));
		}
		
		if(status == 'error')
		{
			$el.find('.uploader-progress').append(' '+TUploader_messages.error);
		}
	}
}


jQuery.fn.TUploader = function(config)
{
	var $el = $(this);
	var $form = $el.closest('form');

	if(!$.browser.flash)
	{
		return $el;
	}
	
	$.getJSON(config, function(json){
	
		var instance = 'TUploader_'+TUploader_instances_counter.length;
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
			.hide()
			.click(function(){
				if(confirm(json.messages.confirm))
				{
					$el.find('input.uploader-tmp').val('');
					$el.find('.uploader-progress').text('');
					$el.find('.uploader-embed embed').externalInterface('cancel');
					$cancellink.hide();
				}
				return false;
			});
		
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