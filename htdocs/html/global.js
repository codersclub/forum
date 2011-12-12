function buddy_pop() {
 window.open('index.php?act=buddy&s=' + session_id, 'BrowserBuddy','width=250,height=500,resizable=yes,scrollbars=yes');
}
function chat_pop(cw,ch) {
 window.open('index.php?s=' + session_id + '&act=chat&pop=1','Chat','width='+cw+',height='+ch+',resizable=yes,scrollbars=yes');
}
function multi_page_jump( url_bit, total_posts, per_page ){
 pages = 1; cur_st = parseInt(st); cur_page  = 1;
 if ( total_posts % per_page == 0 ) { pages = total_posts / per_page; }
 else { pages = Math.ceil( total_posts / per_page ); }
 msg = tpl_q1 + " " + pages;
 if ( cur_st > 0 ) { cur_page = cur_st / per_page; cur_page = cur_page -1; }
 show_page = 1;
 if ( cur_page < pages )  { show_page = cur_page + 1; }
 if ( cur_page >= pages ) { show_page = cur_page - 1; }
 else { show_page = cur_page + 1; }
 userPage = prompt( msg, show_page );
 if ( userPage > 0  ) {
  if ( userPage < 1 )     {    userPage = 1;  }
  if ( userPage > pages ) { userPage = pages; }
  if ( userPage == 1 )    {     start = 0;    }
  else { start = Math.round((userPage - 1) * per_page); }
  window.location = url_bit + "&st=" + start;
 }
}
function contact_admin(admin_email_one, admin_email_two) {
  window.location = 'mailto:' + admin_email_one + '@' + admin_email_two + '?subject=Error on the forums';
}
function do_url(pid) { window.location = base_url + '&act=SF&pid=' + pid + '&st=0&f=' + document.forummenu.f.value + '#List'; }
function pm_popup() { window.open('index.php?act=Msg&CODE=99&s=' + session_id,'NewPM','width=500,height=250,resizable=yes,scrollbars=yes'); }

//--------------------------------------------------------------
// Expand/Collapse DIV
//  by vot + MichSpar + Jureth
//--------------------------------------------------------------
function hasClass(e,cls) {
  return e.className.match(new RegExp('(\\s|^)'+cls+'(\\s|$)'));
}
function changeClass(e,class_old,class_new) {
  if (hasClass(e,class_old)) {
    var reg = new RegExp('(\\s|^)'+class_old+'(\\s|$)');
    e.className=e.className.replace(reg,' '+class_new);
  }
}

function openClose(node){ // TODO: переделать на jQuery
	if(hasClass(node, 'closed')) {
		changeClass(node, 'closed', 'open');
	} else {
		changeClass(node, 'open', 'closed');
	}
	return false;
}

function openCloseParent(thethis) {
	openClose(thethis.parentNode);
}

function openCloseId(id){
	openClose(document.getElementById(id));
}

function addForumToMirror()
{
	var number = 0;
	var D = document;
	function nelement(container) {
		this.container = container;
		
		
		this.list = D.getElementById('first_list_element').cloneNode(true);
		this.list.id = this.list.id + number++;
		this.list.value = '';
		this.br = D.createElement('br');
		this.minus = D.createElement('button');
		this.minus.type = 'button';
		this.minus.innerHTML = '-';
		this.minus.me = this;
		
		this.container.appendChild(this.br);
		this.container.appendChild(this.list);
		this.container.appendChild(this.minus);
		
		this.del = function() {
			this.me.container.removeChild(this.me.list);
			this.me.container.removeChild(this.me.br);
			this.me.container.removeChild(this.me.minus);
			delete this.me;
			delete this;
		};
		this.minus.addEventListener('click', this.del, false);
		
	}
	addForumToMirror = function () {
		var i = D.getElementById('mirror_list_container');
		
		new nelement(i);
	};
	return addForumToMirror();
	
}
function syntax_get_code_tag(el)
{
	return $(el).parent().siblings('.code');
}

function addClass(ele,cls) { // TODO: переделать на jQuery
	if (!hasClass(ele,cls)) ele.className += " "+cls;
}
function removeClass(ele,cls) {	 // TODO: переделать на jQuery
	if (hasClass(ele,cls)) {		
		var reg = new RegExp('(\\s|^)'+cls+'(\\s|$)');		
		ele.className=ele.className.replace(reg,' ');	
	}
}
function syntax_change_button(name, enable, id)
{
	var enable_id  = 'code_' + name + '_' + (enable ? 'on'  : 'off') + '_' + id;
	var disable_id = 'code_' + name + '_' + (enable ? 'off' : 'on' ) + '_' + id;
	document.getElementById(enable_id).style.display  = '';
	document.getElementById(disable_id).style.display = 'none';
}

function syntax_collapse(el,tag_id)
{
	var div = syntax_get_code_tag(el);
	if (div.hasClass('code_collapsed')) {
		div.removeClass('code_collapsed');
		div.addClass('code_expanded');
		syntax_change_button('collapse', false, tag_id);
	} else {
		div.addClass('code_collapsed');
		div.removeClass('code_expanded');
		syntax_change_button('collapse', true, tag_id);
	}
	return false;
}
function syntax_wrap(el,tag_id)
{
	var div = syntax_get_code_tag(el);
	if (div.hasClass('code_wrap')) {
		div.removeClass('code_wrap');
		syntax_change_button('wrap', false, tag_id);
	} else {
		div.addClass('code_wrap');
		syntax_change_button('wrap', true, tag_id);
	}
	
	return false;
}
function syntax_numbering(el,tag_id)
{
	var div = syntax_get_code_tag(el);
	/*
	 * отключаю нумерацию именно таким способом, ибо FF копирует "<li>трал€л€</li>" как "# трал€л€" вместо "трал€л€"
	 */
	if (hasClass(div, 'code_numbered')) {
		div.innerHTML = div.innerHTML.replace(/<li[^>]*>/gi, '<div class="code_line">').replace(/<\/li>/gi, '</div>');
		div.childNodes[0].childNodes[0].style.marginLeft = '0px';
		div.childNodes[0].childNodes[0].style.paddingLeft = '0px';
		div.numbered = false;
		syntax_change_button('numbering', false, tag_id);
		div.removeClass('code_numbered');
	} else {
		div.innerHTML = div.innerHTML.replace(/<div[^>]* class="?code_line"?[^>]*>/gi, '<li>').replace(/<\/div>/gi, '</li>');
		div.numbered = true;
		div.childNodes[0].childNodes[0].style.marginLeft = '';
		div.childNodes[0].childNodes[0].style.paddingLeft = '';
		syntax_change_button('numbering', true, tag_id);
		div.addClass( 'code_numbered');
	}
	return false;
}
function syntax_add_show_controls_on_mouseenter() {
	$(document).ready(function(el,dt) {
			$('.code').each(function(index,element) {
				var displayed = false;
				var timer = false;
				var stopTimer = function() {
					if (timer) {
						clearTimeout(timer);
						timer = false;
					}
				};
				var startTimer = function() {
					timer = setTimeout(function(){$(element).siblings('.pre_code').fadeOut('fast');},2000);
				};
				$(element).mouseenter(
				function() {					
					stopTimer();
					$(element).siblings('.pre_code').fadeIn(0);
					if (displayed) {
						return;
					}
					displayed = true;
					
					var left = 0;
					$(element).siblings('.pre_code').each(function(el) {
						var offset = $(this).children().offset();
						// $(this).css('top', (offset.top - $(this).height() - 5) + 'px');
						$(this).css('margin-top', '-1.6em'/*(offset.top - $(this).height() - 5) + 'px'*/);
						
					});
				});
				$(element).mouseleave(startTimer);
				
				$(element).siblings('span')
					.mouseenter(stopTimer)
					.mouseleave(startTimer);
		})
	});
}
/**
 * предварительно подгружает картинки дл€ переключени€ режимов тега [code]
 * @param tag_id
 */
function preloadCodeButtons(tag_id)
{
	
	var loadImage = function(o) {
		if (!o) return;
		var im = new Image(80,80);
		im.src = o.src;
		delete im;
	};
	var getImage = function(o) {
		if (!o) return;
		for(var i = 0; i < o.childNodes.length; ++i) {
			if (o.childNodes[i].tagName && o.childNodes[i].tagName.toLowerCase() == 'img') {
				return o.childNodes[i];
			}
		}
		return null;
	};
	var items = ['numbering', 'wrap', 'collapse'];
	for(var i in items) {
		var name = items[i];
		var enable_id  = 'code_' + name + '_on_' + tag_id;
		var disable_id = 'code_' + name + '_off_' + tag_id;
		loadImage(getImage(document.getElementById(enable_id)));
		loadImage(getImage(document.getElementById(disable_id)));
	}
}

function PopUp(url, name, width,height,center,resize,scroll,posleft,postop) {
	if (posleft != 0) { x = posleft }
	if (postop  != 0) { y = postop  }

	if (!scroll) { scroll = 1 }
	if (!resize) { resize = 1 }

	if ((parseInt (navigator.appVersion) >= 4 ) && (center)) {
	  X = (screen.width  - width ) / 2;
	  Y = (screen.height - height) / 2;
	}
	if (scroll != 0) { scroll = 1 }

	var Win = window.open( url, name, 'width='+width+',height='+height+',top='+Y+',left='+X+',resizable='+resize+',scrollbars='+scroll+',location=no,directories=no,status=no,menubar=no,toolbar=no');
}

function PopUpCD(url, d_width,d_height) {
// First string will be used for title
    if (last=document.getElementById('dialog')) document.body.removeChild(last);

    title = '«агрузка...';

    text = "<div class='jqcd_dialog'><div class='jqcd_content_layer'><div class='jqcd_content' style='padding-left: 10px; padding-right: 10px;'></div></div></div>";

    var newDiv = document.createElement('div');
    newDiv.id = 'dialog';
    newDiv.innerHTML = text;
    document.body.appendChild(newDiv);


    $('#dialog').jqcd({
      width: parseInt(d_width),
      height: parseInt(d_height),
      position: 'center',
      title_text: title,
      auto_open: true,
      resize: true,
      pic_path: '/img/',
      has_buttons: true });

    
    $.get(url, function(data) {
	    str = new String(data);
	    con=str.substring(str.indexOf('\n')+1);
	    title=str.substring(0, str.indexOf('\n')); 
	    $('#dialog').jqcd_set_caption(title);
	    $('#dialog').jqcd_set_content(con);  
    });   


}
