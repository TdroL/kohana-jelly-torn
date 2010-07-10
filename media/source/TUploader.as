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
		
		var file:FileReference = new FileReference();
		var filefilters:Array;
		var req:URLRequest = new URLRequest();
		var ready:Boolean = false;

		var settings:Object = {
				url: 'uploader.php',
				cancel: 'uploader.php?hash=--hash--',
				filters: {
						'All files': '*.*'
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
				file.dispatchEvent(new Event(Event.CANCEL));
				file.cancel();
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
			
			file.addEventListener(Event.CANCEL, cancelHandler);
			file.addEventListener(Event.COMPLETE, completeHandler);
			file.addEventListener(IOErrorEvent.IO_ERROR, io_errorHandler);
			file.addEventListener(Event.OPEN, openHandler);
			file.addEventListener(ProgressEvent.PROGRESS, progressHandler);
			file.addEventListener(Event.SELECT, selectHandler);
			file.addEventListener(DataEvent.UPLOAD_COMPLETE_DATA, show_messageHandler);		
			select_btn.addEventListener(MouseEvent.CLICK, browseHandler);
			
			output.status = 'ready';
			notify_js();
		}
		
		public function browseHandler(e:MouseEvent)
		{
			if(output.status == 'uploading')
			{
				cancel_upload(e);
			}
			
			file.browse(filefilters);
		}
		
		private function cancelHandler(e:Event)
		{
			var loader:URLLoader = new URLLoader();
			var request:URLRequest = new URLRequest();
				
			request.url = settings.cancel.replace(/--hash--/ig, (output.hashed != null && output.hashed.length) ? output.hashed : "");
			try
			{
				loader.load(request);
			}
			catch (error:Error)
			{
                trace("Unable to load requested document.");
            }

			
			
			output.status = 'canceled';
			output.file = null;
			output.hashed = null;
			output.current = 0;
			output.total = 0;
			notify_js();
		}
		
		private function completeHandler(e:Event)
		{
			output.status = 'complete';
			output.current = output.total;
			notify_js();
		}
		
		private function io_errorHandler(e:IOErrorEvent)
		{
			output.status = 'io_error';
			alert(settings.messages.io_error);
			notify_js();
		}
		
		private function openHandler(e:Event)
		{
			
		}
		
		private function progressHandler(e:ProgressEvent)
		{
			output.current = e.bytesLoaded;
			output.total = e.bytesTotal;
			output.status = 'uploading';
			notify_js();
		}
		
		private function selectHandler(e:Event)
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
		
		private function show_messageHandler(e:DataEvent)
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