/**
 * tinymce plugin for 3Q-Media
 * creates a preview for the shortcodes inside the tinymce editor
 * video and livestream shortcodes becomes a image with a play button
 */
(function() {
	tinymce.create('tinymce.plugins.threeQ', {

		init : function(ed, url) {
			var t = this;
			t.url = url;
			//replace shortcode before editor content set
			ed.on('BeforeSetcontent', function(e) {
				e.content = t._do_spot(e.content);
				e.content = t._do_livestream_spot(e.content);
			});
			
			//replace shortcode as its inserted into editor (which uses the exec command)
			ed.on('ExecCommand',function(ed, cmd) {
			    if (cmd ==='mceInsertContent'){
			    	var content = t._do_spot(tinyMCE.activeEditor.getContent());
			    	content = t._do_livestream_spot(e.content);
                                    tinyMCE.activeEditor.setContent( content );
				}
			    
			});
			//replace the image back to shortcode on save
			ed.on('PostProcess',function(ed) {
				if (ed.get)
					ed.content = t._get_spot(ed.content);
					ed.content = t._get_livestream_spot(ed.content);
			});
		},
		
		_do_livestream_spot : function(co) {
			return co.replace(/\[3q type="livestream"([^\]]*)\]/g, function(a,b){
				var img = tinymce.DOM.encode(b);
				image = '<div class="threeQ_preview_wrapper"><img '+img.replace("data-image=", "src=")+' class="threeQ-livestream-image" ></div>';
				image = image.replace(new RegExp("&quot;", 'g'), '');
				return image;
			});
		},
		_get_livestream_spot : function(co) {
			return co.replace(/(<div class="threeQ_preview_wrapper"><img[^>]+class=([\'\"])threeQ-livestream-image[^>]*><\/div>)/g, function(a,b){
				var shortcode = tinymce.DOM.encode(a);
				shortcode = _.unescape(shortcode);
				shortcode = shortcode.replace('<div class="threeQ_preview_wrapper"><img', "[3q type=\"livestream\" ");
				shortcode = shortcode.replace('class="threeQ-livestream-image"', "");
				shortcode = shortcode.replace('src="', 'data-image="');
				shortcode = shortcode.replace('/></div>', ' layout=\"responsive\" ]');
				return shortcode;
			});
			
		},
		// method to replace the video shortcut with image
		_do_spot : function(co) {
			return co.replace(/\[3q type="video"([^\]]*)\]/g, function(a,b){
				var img = tinymce.DOM.encode(b);
				image = '<div class="threeQ_preview_wrapper"><img '+img.replace("data-thumb=", "src=")+' class="threeQ-image" ></div>';
				image = image.replace(new RegExp("&quot;", 'g'), '');
				return image;
			});
		},
		// method to relpace the preview image to shortcut
		_get_spot : function(co) {
			return co.replace(/(<div class="threeQ_preview_wrapper"><img[^>]+class=([\'\"])threeQ-image[^>]*><\/div>)/g, function(a,b){
				var shortcode = tinymce.DOM.encode(a);
				shortcode = _.unescape(shortcode);
				shortcode = shortcode.replace('<div class="threeQ_preview_wrapper"><img', "[3q type=\"video\" ");
				shortcode = shortcode.replace('class="threeQ-image"', "");
				shortcode = shortcode.replace('src="', 'data-thumb="');
				shortcode = shortcode.replace('/></div>', ' layout=\"responsive\" ]');
				return shortcode;
			});
		}
	});

	tinymce.PluginManager.add('threeQ', tinymce.plugins.threeQ);
})();