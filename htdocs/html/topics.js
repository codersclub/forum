function link_to_post(pid) {
  temp = prompt( tt_prompt, base_url + "showtopic=" + tid + "&view=findpost&p=" + pid );
  return false;
}
function delete_post(theURL) {

  if (confirm(js_del_1)) {
    window.location.href=theURL;
  } else {
    alert(js_del_2);
  } 
}
function keyb_pop() {
  window.open('index.php?act=legends&CODE=keyb&s=' + session_id,'Legends','width=700,height=160,resizable=yes,scrollbars=yes'); 
}
function emo_pop() {
  window.open('index.php?act=legends&CODE=emoticons&s=' + session_id,'Legends','width=250,height=500,resizable=yes,scrollbars=yes');
}
function bbc_pop() {
  window.open('index.php?act=legends&CODE=bbcode&s=' + session_id,'Legends','width=700,height=500,resizable=yes,scrollbars=yes');
}
function ValidateForm(isMsg) {
 MessageLength  = document.REPLIER.Post.value.length;
 errors = "";
 if (isMsg == 1)
 {
	if (document.REPLIER.msg_title.value.length < 2)
	{
		errors = msg_no_title;
	}
 }
 if (MessageLength < 2) {
	 errors = js_no_message;
 }
 if (MessageMax !=0) {
	if (MessageLength > MessageMax) {
		errors = js_max_length + " " + MessageMax + " " + js_characters + ". " + js_current + ": " + MessageLength;
	}
 }
 if (errors != "" && Override == "") {
	alert(errors);
	return false;
 } else {
	document.REPLIER.submit.disabled = true;
	return true;
 }
}
function rusLang() {
var textar = document.REPLIER.Post.value;
if (textar) {
 for (i=0; i<engReg.length; i++)
 { textar = textar.replace(engReg[i], rusLet[i]) }
   document.REPLIER.Post.value = textar; }
}
function expMenu(id) {
  var itm = null;
  if (document.getElementById) {
    itm = document.getElementById(id);
  } else if (document.all){
    itm = document.all[id];
  } else if (document.layers){
    itm = document.layers[id];
  }
  if (!itm) {
    // do nothing
  }
  else if (itm.style) {
    if (itm.style.display == "none") { itm.style.display = ""; }
    else { itm.style.display = "none"; }
  }
  else { itm.visibility = "show"; }
}
function ShowHide(id1, id2) {
  if (id1 != '') expMenu(id1);
  if (id2 != '') expMenu(id2);
}
var D=document;
function toChangeLink(ID,html,url)
{
    ID=D.getElementById(ID)
    ID.innerHTML=html
    if(url)ID.href=url
}
unique_id.c=0
function unique_id(o)
{
    return o.id=!o.id?o.id='so__'+(++unique_id.c):o.id;
}
function JSRequest(url,p)
{
   try{
    var s=D.createElement('script')
    s.type="text/javascript"
            s.src=url+"&linkID="+p+"&random="+Math.random();
    D.body.appendChild(s)
    return false
   }catch(e){return true}
}
function restoreAndDecline(el,p) {
	var post_object = $('#post_' + p);
	var url = el.href + '&ajax=on';
	
	$.get(url, function(data) {
			post_object.replaceWith(data);
		});
	
	
	return false;
}
function setPostHTML(i,h)
{
   i=D.getElementById(i);
   while(i.tagName!='TBODY' && i.tagName!='TABLE')i=i.parentNode;
   i=i.rows[1].cells[1].getElementsByTagName('DIV')[0]
   i.innerHTML=h
   if(!(is_ie && !Boolean(document.body.contentEditable)))h.replace(new RegExp("<script>(.*?)<\/script>","gi"),function($,s){eval(s)})
}

function clearFirstUploadField() {
	D.getElementById('first_upload_container').innerHTML = D.getElementById('first_upload_container').innerHTML;
}
function checkUploadSize(e) {
	if (this.files && this.files[0].size) {
		if ( this.files[0].size > (max_attach_size * 1024) ) {
			// alert(upload_attach_too_big);
			$(this).siblings('span').text(upload_attach_too_big);
		} else {
			$(this).siblings('span').text('');
		}
	}
}
function deleteUploadBox() {
	$(this).parent().remove();
}
$(window).load(function() {
	$('#first_upload_container input[type=file]').
		change(checkUploadSize);
});
function addUpload()
{
	var number = 0;
	addUpload = function () {
		function do_add() {
			var cur_number = ++number;
			var i = $('#upload_container');
			var node = $('#first_upload_container').clone();
			node.removeAttr('id');
			node.children().removeAttr('onclick');
			
			node.children('[name=deleteBox]').click(deleteUploadBox);
			node.children('[name=addTag]').click(function(){ tag_attach(cur_number) });
			node.children('[type=file]')
				.attr('name','FILE_UPLOAD[' + cur_number + ']' )
				.change(checkUploadSize);
			
			i.append(node);
			
		};
		do_add();
		
	}
	return addUpload();
}
function startAutoDelete(default_days,el) {
	var select = $('<select>');
	var days = [1,2,3,5,7,10,14,21,30];
	var link = el.href;
	var do_request = function() {
		$(select).change(do_request);
		var days_count = select.val();
		var request_link = link + '&ajax=on&days='+days_count;
		var timer = false;
		clearTimeout(timer);
		$.get(request_link, function(data) {
			var replace_function = function() {
				var text;
				var count = $(select).val();
				if (count == 0) {
					text = post_delete1;
				} else {
					text = post_delete2.replace(/[0-9]+/, count);
				}
				$(select).replaceWith(
						$('<a/>')
							.attr('href',link)
							.click(function(e) {e.stopPropagation();e.preventDefault();return startAutoDelete(count,e.target);})
							.text(text)
						);
			};
			if (days_count == 0) {
				changeAutodeleteMessage(select,'');
			} else {
				changeAutodeleteMessage(select,data);
			}
			$(select).unbind('mouseout');
			$(select).unbind('mousemove');
			$(select).unbind('mouseover');
			$(select).mouseout(function(){
				timer = setTimeout(function(){replace_function();}, 2000);
			});
			$(select).mouseover(function(){clearTimeout(timer);});
			$(select).mousemove(function(){clearTimeout(timer);});
			timer = setTimeout(replace_function, 3000);
		});
	};
	$(select).append($('<option>').attr('value','0').text('disable'));
	
	for(var i in days) {
		var option = $('<option>').attr('value',days[i]).text(days[i] + ' day');
		if (days[i] == default_days) {
			option.attr('selected','selected');
		}
		$(select).append(option);
	}
	var parent = $(el).parent();
	$(el).empty();
	parent.append(select);
	do_request(select);
	return false;
}
function stopAutoDelete(default_days,el)
{
	
	var link = el.href;
	
	var do_request = function() {
		var request_link = link + '&ajax=on&days=0';
		$.get(request_link, function(data) {
			changeAutodeleteMessage(el,'');
		});
	};
	$(el).text(post_delete1);
	$(el).removeAttr('onclick');
	$(el).click(function(e) {e.stopPropagation();e.preventDefault();return startAutoDelete(default_days,e.target);});
	do_request();
	changeAutodeleteMessage(el,'');
	return false;
}
function changeAutodeleteMessage(link,new_message)
{
	var msg = $(link).parents('table').find('.autodelete_message');
	if (msg.length == 0) {
		if (new_message == '') {
			;
		} else {
			$(link).parents('table').find('div.postcolor').append('<span class="autodelete_message">'+new_message+'</span>');
		}
		return false;
	}
	if (new_message == '') {
		msg.empty();
	} else {
		msg.text(new_message);
	}
	return false;
}