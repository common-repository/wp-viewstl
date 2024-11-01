jQuery(function() {
	jQuery('.colorpicker').spectrum({
		color: jQuery('#stl_color').attr('color'),
		change: function(color) {
			//alert("Not yet implemented on ViewSTL, coming soon! : " + color.toHexString());
			document.getElementById('vs_iframe').contentWindow.postMessage({msg_type:'set_color', value:color.toHexString()}, '*');
		}
	});

	jQuery('.stl_select').click(function(){
		jQuery('#stl_dl_link').attr('href', jQuery(this).attr('url'));
		document.getElementById("vs_iframe").contentWindow.postMessage({msg_type:'load', url:jQuery(this).attr('url')}, '*');
	});

	jQuery('.render_select').change(function(){
		document.getElementById('vs_iframe').contentWindow.postMessage({msg_type:'set_shading', type:jQuery('.render_select').val()}, '*');
	});

	jQuery('.capture_image').click(function(){
		document.getElementById('vs_iframe').contentWindow.postMessage({msg_type:'get_photo'}, '*');
	});

	
	//RECIEVED CAPTURE REQUEST!
	window.onmessage = function(e)
	{
		if ((e.origin=="http://www.viewstl.com")&&(e.data.msg_type))
		{
			if (e.data.msg_type=='photo')
			{
				var model_img = document.createElement("img");
				model_img.src = e.data.img_data;
				
				var w = window.open();
				var html =	'<div style="width:100%; text-align:center; font-size:28px;">Image Captured! ' +
						'<a href-lang="image/png" href="' + model_img.src + '" title="capture.png" download>Click here to download</a></div>' +
                                        	'<audio autoplay><source src="' + jQuery('#capture_div').attr('camsnd') + '" /></audio>' +
						'<img id="capture" src="' + model_img.src + '" />';
				w.document.writeln(html);
			}
		}
	};
});