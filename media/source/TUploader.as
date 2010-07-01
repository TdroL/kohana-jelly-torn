package
{
	
	import flash.display.*;
	import flash.events.*;
	import flash.text.*;
	import flash.external.*;
	import com.adobe.serialization.json.JSON;
	
	import flash.net.*;
	import flash.utils.*;
	import flash.events.TimerEvent;
	
	
	public class TUploader extends MovieClip
	{
		
		var file:FileReference;
		var filefilters:Array;
		var req:URLRequest = new URLRequest();
		var ready:Boolean = false;

		var settings:Object = {
				url: 'uploader.php',
				filters: {
						'Images': '*.jpg;*.png;*.gif'
				},
				max_size: -1, // unlimited
				debug: true,
				messages: {
					file_is_too_big: 'File is too big',
					config_error: 'Torn Uploader: IO error (config)',
					io_error: 'The file could not be uploaded',
					error: 'Error'
				}
			};
			
		var output:Object = {
				instance: null,
				file: null,
				hashed: null,
				status: 'waiting',
				current: 0,
				total: 0
			};

		public function TUploader()
		{
			ExternalInterface.addCallback('cancel', function(){
				this.cancel();
			});
			
			if(getFlashVars().config)
			{
				var loader:URLLoader = new URLLoader();
				var request:URLRequest = new URLRequest();
				
				request.url = getFlashVars().config;
				loader.load(request);
				
				loader.addEventListener(Event.COMPLETE, function(e:Event){
					var loader:URLLoader = URLLoader(e.target);
					
					try
					{
						var override:Object = JSON.decode(loader.data);
						
						for(var key in settings)
						{
							if(override[key])
							{
								settings[key] = override[key];
							}
						}
					}
					catch(error) {}

					setup();
				});
				
				loader.addEventListener(IOErrorEvent.IO_ERROR, function(e:Event){
					if(settings.debug)
					{
						alert(settings.messages.config_error);
					}
					
					output.status = 'io_error';
					notify_js();
				});
			}
			else
			{
				setup();
			}
		}
		
		public function setup()
		{
			req.url = settings.url;
			req.method = URLRequestMethod.POST;

			
			filefilters = [];
			var i = 0;
			
			for(var it in settings.filters)
			{
				filefilters[i++] = new FileFilter(it, settings.filters[it]);
			}

			if(getFlashVars().instance)
			{
				output.instance = getFlashVars().instance;
			}

			file = new FileReference();
			
			file.addEventListener(Event.CANCEL, cancel_func);
			file.addEventListener(Event.COMPLETE, complete_func);
			file.addEventListener(IOErrorEvent.IO_ERROR, io_error);
			file.addEventListener(Event.OPEN, open_func);
			file.addEventListener(ProgressEvent.PROGRESS, progress_func);
			file.addEventListener(Event.SELECT, select_handler);
			file.addEventListener(DataEvent.UPLOAD_COMPLETE_DATA, show_message);		
			select_btn.addEventListener(MouseEvent.CLICK, browse);
			
			output.status = 'ready';
			notify_js();
		}
		
		public function browse(e:MouseEvent)
		{
			if(output.status == 'uploading')
			{
				cancel_upload(e);
			}
			
			file.browse(filefilters);
		}
		
		private function cancel_func(e:Event)
		{
			output.status = 'canceled';
			output.file = null;
			output.hashed = null;
			output.current = 0;
			output.total = 0;
			notify_js();
		}
		
		private function complete_func(e:Event)
		{
			output.status = 'complete';
			output.current = output.total;
			notify_js();
		}
		
		private function io_error(e:IOErrorEvent)
		{
			output.status = 'io_error';
			alert(settings.messages.io_error);
			notify_js();
		}
		
		private function open_func(e:Event)
		{
			
		}
		
		private function progress_func(e:ProgressEvent)
		{
			output.current = e.bytesLoaded;
			output.total = e.bytesTotal;
			output.status = 'uploading';
			notify_js();
		}
		
		private function select_handler(e:Event)
		{
			if(settings.max_size > 0 && file.size > settings.max_size)
			{
				alert(settings.messages.file_is_too_big);
				return;
			}
			
			var postData:URLVariables = new URLVariables();
			postData['old_file'] = output.hashed;
			req.data = postData;
			
			file.upload(req);
			output.status = 'selected';
			output.file = file.name;
			notify_js();
		}
		
		private function show_message(e:DataEvent)
		{
			output.status = e.data; // e.data - from PHP
			
			var match:Array = output.status.match(/^done;([a-z0-9]{32}-[a-z0-9]{32})$/i);
			if(match.length > 1)
			{
				output.status = 'done';
				output.hashed = match[1];
			}
			notify_js();
		}
		
		private function cancel_upload(e:MouseEvent)
		{
			file.cancel();
		}
		
		private function notify_js()
		{
			navigateToURL(
						new URLRequest('javascript: TUploadJsReceiver("'+output.instance+'", "'+output.status+'", "'+output.file+'", "'+output.hashed+'", '+output.current+', '+output.total+')'
						), '_self');
		}
		
		private function alert(message:String)
		{
			navigateToURL(
						new URLRequest('javascript: alert("'+message+'")'
						), '_self');
		}
		
		private function getFlashVars():Object {
			return Object(LoaderInfo(this.loaderInfo).parameters);
		}
	}	
}