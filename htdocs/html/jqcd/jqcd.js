/* jQuery custom Dialog v 1.0 RC3
 * Распространяется по лицензии GNU GPL v3 
 * 
*/


$(window).resize(function() {
	$("#jqcd_modal_layer").height($(document).height());
});		

function jqcd_create(id, opts, content){
	
	$('body').append('<div id="'+id+'"><div class="jqcd_dialog"><div class="jqcd_content" style="padding-left: 10px; padding-right: 10px;"></div></div></div>');	
	$('#'+id).jqcd(opts);
	$('#'+id).jqcd_set_content(content);
}

(function($) {
	var jqcd_list = new Array;
	var jqcd_btn_c = 0;
	var	dialog_z_index = 1001;
	var	dialog_z_index_topmost = 1100;
	var jqcd_old_ie = false;
	if($.browser.msie&&parseInt($.browser.version, 10)<9)
		jqcd_old_ie = true;
	
	$.fn.jqcd_set_top = function(curr_dlg){
		$.each(jqcd_list, function(i,v){
				var opts = v.data('opts');
				if (opts != undefined) { 
					if(opts['topmost'])
						v.css('z-index', dialog_z_index_topmost);
					else
						v.css('z-index', dialog_z_index);
				}	
				
		});
		curr_dlg.css('z-index', dialog_z_index+10);
	};
	
	$.fn.jqcd_def_btn_focus = function(curr_dlg){
		var opts = curr_dlg.data('opts');
		if(opts['default_btn']!=undefined)
			opts['default_btn'].focus();		
	};
	
	$.fn.jqcd = function(options){
		var defaults = {
	 			width: 400, 
	 			height: 200,
	 			modal: false,
	 			title_text: 'Dialog',
	 			on_close: function(){dlg.jqcd_close();},	
	 			auto_open: false,
	 			resize: true, 
	 			position: 'center',
	 			topmost: false,
	 			posX: 0,
	 			posY: 0,
	 			has_buttons: true,
	 			buttons_position: 'right',
	 			button_panel_height: 35,
	 			has_shadow: true,
	 			min_width: 220,
	 			min_height: 100,
	 			content_overflow: 'auto',
	 			default_btn: undefined,
	 			panel_btn: false
	 			
	  		}, 	opts = $.extend(defaults, options);

		if(this.attr('id')==undefined)
			return;
		
		this.data('opts', opts);
		if(!opts['auto_open'])
			this.hide();
		
		var mouse_x=0, mouse_y=0, clicked=false, resize_clicked=false, tb_clicked=false, dx, dy, th;
		var dlg = this;
		var content_layer = dlg.find(".jqcd_dialog");
		var border = 2*parseInt(content_layer.css('borderBottomWidth'));
		jqcd_list.push(dlg);
		
		opts['width'] += border;
		opts['height'] += border;
		dlg.addClass('jqcd');
		dlg.width(opts['width']);
		dlg.height(opts['height']);
		
		content_layer.prepend('<div class="jqcd_title"></div>');
		var title = dlg.find(".jqcd_title");
		title.html('<span class="jqcd_title_text">'+opts['title_text']+'</span><div class="jqcd_close_btn"></div>');
		var title_text = dlg.find(".jqcd_title_text");
		
		var  close_btn = dlg.find('.jqcd_close_btn');
		var img_path = close_btn.css('background-image');
		var exp = /(?:http:\/\/[\w.]+\/)?(?!:\/\/)[^<^>^"^'^\s]+\.(?:png|jpg|jpeg)(?!\w)/ig;
		var result = img_path.match(exp)[0];
		img_path = result.replace(/\w+.(?:png|jpg)/, '');
		img_path = img_path.replace('url(', '');
		//alert(img_path);
		
		var dialog_content = dlg.find(".jqcd_content");
		dlg.find('.jqcd_close_btn').click(function(){
					opts["on_close"]();
		});
		
		if(opts['has_buttons'])
			content_layer.append('<div class="jqcd_buttons_panel" style="text-align:'+opts['buttons_position']+'"></div>');
		else
			opts['button_panel_height'] = 18;
		
		if(opts['resize'])
			content_layer.append('<img src="'+img_path+'resize.png" class="jqcd_resizer" />');

		if(opts['modal']&&!$("div").hasClass("jqcd_modal_layer"))
				$("body").append('<div id="jqcd_modal_layer" class="jqcd_modal_layer"></div>');
		
		if(opts['modal']&&opts['auto_open'])
		{
			$("#jqcd_modal_layer").show();
			$("#jqcd_modal_layer").height($(window).height());
		}
		
		if(opts['topmost'])
			dlg.css('z-index', dialog_z_index_topmost);
		
		dlg.prepend('<div class="jqcd_disabler"></div>');
		if(opts['has_shadow']&&jqcd_old_ie){
			dlg.append('<div class="jqcd_shadow"></div>');
			var oshadow = dlg.find(".jqcd_shadow");
			oshadow.width(opts['width']-2);
			oshadow.height(opts['height']-2);
		}		
		
		if(!opts['has_shadow'])
		{
			content_layer.css('box-shadow','0px 0px 0px rgb(120,120,120)');
			content_layer.css('-moz-box-shadow','0px 0px 0px rgb(120,120,120)');
			content_layer.css('-webkit-box-shadow','0px 0px 0px rgb(120,120,120)');
		}
		
		if(opts['auto_open'])
		{
			dlg.jqcd_adjust();		
			dlg.jqcd_adjust_title(title_text, title, content_layer, dialog_content);
			dlg.jqcd_set_top(dlg);
		}
		
		dlg.bind('keydown', function(e) {
	        if(e.keyCode==27)
	        		opts['on_close']();
		});
		
		close_btn.mousedown(function(e) {
			tb_clicked = true;
			close_btn.css('background-image', 'url("'+img_path+'btn_pushed.png")');			
		});
		
		close_btn.mouseup(function(e) {
			tb_clicked = false;
			close_btn.css('background-image', 'url("'+img_path+'btn_normal.png")');
		});
		
		close_btn.hover(
		  function () {
			  close_btn.css('background-image', 'url("'+img_path+'btn_hover.png")');
		  },
		  function () {
			  if(!tb_clicked)
				  close_btn.css('background-image', 'url("'+img_path+'btn_normal.png")');
		  }
		);
		
		title.mousedown(function(e){
			mouse_x = e.pageX;
			mouse_y = e.pageY;
			clicked = true;
			dlg.jqcd_set_top(dlg);
			e.preventDefault();
			dlg.jqcd_def_btn_focus(dlg);	
		});

		dlg.find('.jqcd_resizer').mousedown(function(e){
			mouse_x = e.pageX;
			mouse_y = e.pageY;
			resize_clicked = true;
			$('body').css('cursor', 'nw-resize');
			dlg.css('cursor', 'nw-resize');
			e.preventDefault();
			dlg.jqcd_def_btn_focus(dlg);
		});
		
		
		dialog_content.mousedown(function(e){
			dlg.jqcd_set_top(dlg);
		});
		
		title.mouseup(function(e){
			clicked = false;
		});
		
		$(document).mouseup(function(e){
			clicked = false;
			if(tb_clicked)
			{
				close_btn.css('background-image', 'url("'+img_path+'btn_normal.png")');
				tb_clicked = false;
			}
			resize_clicked = false;
			dlg.css('cursor', 'auto');
			$('body').css('cursor', 'auto');
		});
		
		
		$(document).mousemove(function(e){
			if(tb_clicked)
				return false;
			if(resize_clicked||clicked)
			{
				dx = mouse_x-e.pageX;
				dy = mouse_y-e.pageY;				
			}
			if(resize_clicked)
			{
				var dwd = dlg.width()-dx;
				var dyd = dlg.height()-dy;
				var min_height = opts['min_height'];
				if(dwd>opts['min_width']&&dx!=0)
				{
					dlg.width(dwd);
					mouse_x = mouse_x-dx;
					
					th = dlg.jqcd_adjust_title(title_text, title, content_layer, dialog_content);
					if(opts['min_height']==0)
						min_height = border+th+opts['button_panel_height'];
					if(dlg.height()<min_height)
						dlg.height(min_height);
				}
				if(dyd>min_height&&dy!=0)
				{	
					mouse_y = mouse_y-dy;
					dlg.height(dyd);
					content_layer.height(content_layer.height()-dy);
					dialog_content.height(dialog_content.height()-dy);
				}	
				if(opts['has_shadow']&&jqcd_old_ie){
					oshadow.width(dlg.width()-2);
					oshadow.height(dlg.height()-2);
				}
				return false;
			}
			if(clicked)
			{				
				var offset = dlg.offset();
				var dwdo = $(window).width()-dlg.outerWidth();
				var ldx = offset.left-dx;
				if(ldx>0&&ldx<=dwdo)
				{
					offset.left = ldx;
					mouse_x = mouse_x-dx;
				}
				if(ldx<=0)
					offset.left = 0;
				if(ldx>dwdo)
					offset.left = dwdo;
				if(offset.top-dy>=0)
				{
					offset.top = offset.top-dy;
					mouse_y = mouse_y-dy;
				}
				else
					offset.top = 0;
				
				dlg.offset(offset);
				return false;
			}
		});
		
	};	
	
	$.fn.jqcd_set_content = function(content){
		this.find(".jqcd_content").html(content);
	};
	
	$.fn.jqcd_open = function(){
		this.show();
		var opts = this.data('opts');
		if(opts["modal"])
		{
			opts["content_overflow"] = $("body").css("overflow");
			$("body").css("overflow", "hidden");
			$("#jqcd_modal_layer").height($(document).height());
			$("#jqcd_modal_layer").show();
		}
		if(opts["has_shadow"]&&jqcd_old_ie)
		{
			var os = this.find(".jqcd_shadow");
			os.width(opts['width']-2);
			os.height(opts['height']-2);			
		}
		this.width(opts["width"]);
		this.height(opts["height"]);
		this.jqcd_adjust();
		
		this.jqcd_adjust_title(this.find(".jqcd_title_text"), this.find(".jqcd_title"), 
				this.find(".jqcd_dialog"), this.find(".jqcd_content"));
		this.jqcd_set_top(this);
	};
	
	$.fn.jqcd_adjust_title = function(title_text, title, content_layer, dialog_content)
	{
		var opts = this.data('opts');
		var th = title_text.height();
		if(th<30)
		{
			th=30;
			title_text.css('line-height', '24px');
		}
		else
		{
			th+=5;
			title_text.css('line-height', '18px');
		}
		title.height(th);
		var clh = this.height()-2*parseInt(content_layer.css('borderBottomWidth'));
		dialog_content.height(clh-th-opts['button_panel_height']);
		content_layer.height(clh);
		return th;
	};
	
	$.fn.jqcd_adjust = function() 
	{	
		var opts = this.data('opts');
		var to = $(window).scrollTop();
		var dlg = this;
		if(opts['position']=='center')		
		{
			dlg.css({ left:$(window).width()/2-dlg.width()/2, top:$(window).height()/2-dlg.height()/2+to });
			return;
		}
		if(opts['position']=='topleft')
			dlg.css({top:to, left:0});
		if(opts['position']=='bottomleft')
			dlg.css({left: 0, top: $(window).height()-dlg.outerHeight()+to});
		if(opts['position']=='topright')
			dlg.css({left: $(window).width()-dlg.outerWidth(), top: to });
		if(opts['position']=='bottomright')		
			dlg.css({left: $(window).width()-dlg.outerWidth(), top: $(window).height()-dlg.outerHeight()+to});
		if(opts['position']=='define')
			dlg.css({left:opts['posX'], top: opts['posY']});
	};
	
	$.fn.jqcd_close = function(){
		var opts = this.data('opts');
		if(opts["modal"])
		{
			$("#jqcd_modal_layer").hide();
			$("body").css("overflow", opts["content_overflow"]);
		}
		this.hide();
	};
	
	
	$.fn.jqcd_set_caption = function(text){
		this.find(".jqcd_title_text").text(text);
		this.jqcd_adjust_title(this.find(".jqcd_title_text"), this.find(".jqcd_title"), 
				this.find(".jqcd_dialog"), this.find(".jqcd_content"));
	};
	
	$.fn.jqcd_set_width = function(width){
		var opts = this.data('opts');
		opts['width'] = width;
		this.width(width);
		this.jqcd_adjust();
		this.jqcd_adjust_title(this.find(".jqcd_title_text"), this.find(".jqcd_title"), 
				this.find(".jqcd_dialog"), this.find(".jqcd_content"));	
		if(opts['has_shadow']&&jqcd_old_ie)
			this.find(".jqcd_shadow").width(this.width()-2);
	};

	$.fn.jqcd_set_height = function(height){
		var opts = this.data('opts');
		opts['height'] = height;
		this.height(height);
		this.jqcd_adjust();
		this.jqcd_adjust_title(this.find(".jqcd_title_text"), this.find(".jqcd_title"), 
				this.find(".jqcd_dialog"), this.find(".jqcd_content"));	
		if(opts['has_shadow']&&jqcd_old_ie)
			this.find(".jqcd_shadow").height(this.height()-2);
	};	
	
	
	$.fn.jqcd_buttons_pos = function(pos){
		this.find(".jqcd_buttons_panel").css('text-align', pos);
	};
	
	$.fn.jqcd_on_close = function(handler){
		var opts = this.data('opts');
		opts['on_close'] = handler;
	};	
	
	$.fn.jqcd_disable = function(){
		var d = this.find(".jqcd_disabler");
		d.show();
		d.width(this.width());
		d.height(this.height());
	};
	
	$.fn.jqcd_enable = function(){
		this.find(".jqcd_disabler").hide();
	};
	
	$.fn.jqcd_topmost = function(is_top){
		if(is_top)
			this.css('z-index', dialog_z_index_topmost);
		else
			this.css('z-index', dialog_z_index);
	};
	
	$.fn.jqcd_set_pos = function(pos){
		var opts = this.data('opts');
		opts['position'] = pos;
		this.jqcd_adjust();
	};
	
	$.fn.jqcd_set_pos_x = function(x){
		var opts = this.data('opts');
		opts['posX'] = x;
		this.jqcd_adjust();
	};	
	
	$.fn.jqcd_set_pos_y = function(y){
		var opts = this.data('opts');
		opts['posY'] = y;
		this.jqcd_adjust();
	};		
	
	$.fn.jqcd_set_modal = function(is_modal){
		var opts = this.data('opts');
		opts['modal'] = is_modal;
		if(is_modal)
		{
			if(!$("div").hasClass("jqcd_modal_layer"))
				$("body").append('<div id="jqcd_modal_layer" class="jqcd_modal_layer"></div>');
			this.jqcd_set_top(this);
			opts["content_overflow"] = $("body").css("overflow");
			$("body").css("overflow", "hidden");
			$("#jqcd_modal_layer").show();
			$("#jqcd_modal_layer").height($(document).height());
		}
		else
		{
			$("#jqcd_modal_layer").hide();
		}
	};
	
	$.fn.jqcd_get_opt = function(option){
		var opts = this.data('opts');
		return opts[option];
	};	
	
	$.fn.jqcd_add_close_button = function(title, is_default){
		var opts = this.data('opts');
		if(is_default==undefined)
			is_default = true;
		jqcd_btn_c++;
		this.jqcd_add_button('jqcd_close_'+jqcd_btn_c, title, is_default, opts['on_close'], true);
		return 'jqcd_close_'+jqcd_btn_c;
	};
	
	$.fn.jqcd_add_button = function(id, title, is_default, handler, is_close){
		var opts = this.data('opts');
		if(is_close==undefined)
			is_close = false;		
		this.find(".jqcd_buttons_panel").append('<button id="'+id+'">'+title+'</button>');
		if(is_close)
		{
			$('#'+id).click(function(){
				opts['on_close']();
			});			
		}
		else
		{
			$('#'+id).click(function(){
				handler();
			});
		}
		if(is_default)
		{
			opts['default_btn'] = $('#'+id);
			$('#'+id).addClass('jqcd_panel_default_button');
			$('#'+id).focus();
		}
		else
			$('#'+id).addClass('jqcd_panel_buttons');
		
		if(opts['panel_btn'])
			$('#'+id).css('margin-left', '5px');
		else
			opts['panel_btn'] = true;	
	};	
})(jQuery);