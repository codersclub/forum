<?php

class skin_topic {



function Show_attachments($data) {
global $ibforums;
return <<<EOF

<br>
<br>
<strong><span class='edit'>{$ibforums->lang['attached_file']} ( {$ibforums->lang['attach_hits']}: {$data['hits']} )</span></strong>
<br>
<a href='{$ibforums->base_url}act=Attach&amp;type=post&amp;id={$data['pid']}' title='{$ibforums->lang['attach_dl']}' target='_blank'>{$data['image']}</a>
&nbsp;<a href='{$ibforums->base_url}act=Attach&amp;type=post&amp;id={$data['pid']}' title='{$ibforums->lang['attach_dl']}' target='_blank'>{$data['name']}</a>
&nbsp;<span class='edit'>({$data['size']})</span>

EOF;
}


function Show_attachments_img($aid) {
global $ibforums;
return <<<EOF


<br>
<br>
<strong><span class='edit'>{$ibforums->lang['pic_attach']}</span></strong>
<br>
<img src='{$ibforums->base_url}act=Attach&amp;type=post&amp;id=$aid' class='attach' alt='{$ibforums->lang['pic_attach']}'>

EOF;
}


function get_box_alreadytrack() {
global $ibforums;
return <<<EOF

<br>{$ibforums->lang['already_sub']}

EOF;
}


function rep_options_links($stuff) {
global $ibforums;
return <<<EOF

<a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?act=rep&CODE=02&mid=$stuff[mid]&f=$stuff[f]&t=$stuff[t]&p=$stuff[p]'><{WARN_MINUS}></a><a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?act=rep&CODE=01&mid=$stuff[mid]&f=$stuff[f]&t=$stuff[t]&p=$stuff[p]'><{WARN_ADD}></a>

EOF;
}


function topic_opts_closed() {
global $ibforums;
return <<<EOF

<a href="javascript:ShowHide('topic_open','topic_closed')" title="{$ibforums->lang['to_open']}"><{T_OPTS}></a>

EOF;
}


function get_box_enablesig($checked) {
global $ibforums;
return <<<EOF

<br><input type='checkbox' name='enablesig' class='checkbox' value='yes' $checked>&nbsp;{$ibforums->lang['enable_sig']}

EOF;
}


function nameField_reg() {
global $ibforums;
return <<<EOF

<!-- REG NAME -->

EOF;
}


function nameField_unreg($data) {
global $ibforums;
return <<<EOF

<tr>
 <td colspan="2" class='pformstrip'>{$ibforums->lang['unreg_namestuff']}</td>
</tr>
<tr>
  <td class='pformleft'>{$ibforums->lang['guest_name']}</td>
  <td class='pformright'><input type='text' size='40' maxlength='40' name='UserName' value='$data' class='textinput'></td>
</tr>

EOF;
}


function get_box_enabletrack($checked) {
global $ibforums;
return <<<EOF

<br><input type='checkbox' name='enabletrack' class='checkbox' value='1' $checked>&nbsp;{$ibforums->lang['enable_track']}

EOF;
}


function mm_end() {
global $ibforums;
return <<<EOF

</select>&nbsp;<input type='submit' value='{$ibforums->lang['mm_submit']}' class='forminput'></form>

EOF;
}


function mm_entry($id, $name) {
global $ibforums;
return <<<EOF

<option value='$id'>$name</option>

EOF;
}


function mm_start($tid) {
global $ibforums;
return <<<EOF

<br>
<form action='{$ibforums->base_url}act=mmod&amp;t=$tid' method='post'>
<input type='hidden' name='check' value='1'>
<select name='mm_id' class='forminput'>
<option value='-1'>{$ibforums->lang['mm_title']}</option>

EOF;
}


function warn_title($id, $title) {
global $ibforums;
return <<<EOF


<a href="javascript:PopUp('{$ibforums->base_url}act=warn&amp;mid={$id}&amp;CODE=view','Pager','500','450','0','1','1','1')">{$title}</a>:


EOF;
}


function smilie_table() {
global $ibforums;
return <<<EOF

<table class='tablefill' cellpadding='4' align='center'>
<tr>
<td align="center" colspan="{$ibforums->vars['emo_per_row']}"><b>{$ibforums->lang['click_smilie']}</b></td>
</tr>
<!--THE SMILIES-->
<tr>
<td align="center" colspan="{$ibforums->vars['emo_per_row']}"><b><a href='javascript:emo_pop()'>{$ibforums->lang['all_emoticons']}</a></b></td>
</tr>
</table>

EOF;
}


function Mod_Panel($data, $fid, $tid, $key="") {
global $ibforums;
return <<<EOF

<div align='left' style='float:left;width:auto'>
	<form method='POST' style='display:inline' name='modform' action='{$ibforums->base_url}'>
	<input type='hidden' name='t' value='$tid'>
	<input type='hidden' name='f' value='$fid'>
	<input type='hidden' name='st' value='{$ibforums->input['st']}'>
	<input type='hidden' name='auth_key' value='$key'>
	<input type='hidden' name='act' value='Mod'>
	<select name='CODE' class='forminput' style="font-weight:bold;color:red">
	<option value='-1' style='color:black'>{$ibforums->lang['moderation_ops']}</option>
	$data
	</select>&nbsp;<input type='submit' value='{$ibforums->lang['jmp_go']}' class='forminput'></form>
  </div>

EOF;
}


function RenderRow($post, $author) {
global $ibforums;
return <<<EOF
 <table width='100%' border='0' cellspacing='1' cellpadding='3'>
 <tr>
    <td valign='middle' class='row4' width='10%'>
      <a name='entry{$post["pid"]}'></a>
      <span class='{$post["name_css"]}'><b>{$author['name']}</b></span>
    </td>
    <td class='row4' valign='middle' width="90%" align="right">
      {$post['checkbox']}{$post['delete_button']}   {$post['edit_button']}
    </td>
 </tr></table>
 <table width='100%'>
 <tr>
   <td valign='top' class='{$post['post_css']}'>
     <div class='postcolor'>{$post['post']}{$post['attachment']}</div>
   </td>
 </tr>
 </table>
 <div class='darkrow1' style='height:5px'><!-- --></div>

EOF;
}


function new_report_link($data) {
global $ibforums;
return <<<EOF


<a href='{$ibforums->base_url}act=report&amp;f={$data['FORUM']['id']}&amp;t={$data['TOPIC']['tid']}'>{$ibforums->lang['report_link']}</a>


EOF;
}


function report_link($data) {
global $ibforums;
return <<<EOF

<a href='{$ibforums->base_url}act=report&amp;f={$data['forum_id']}&amp;t={$data['topic_id']}&amp;p={$data['pid']}&amp;st={$ibforums->input['st']}'><{P_REPORT}></a>

EOF;
}


function ip_show($data) {
global $ibforums;
return <<<EOF

<span class='desc'><br>{$ibforums->lang['ip']}: $data</span>

EOF;
}


function topic_active_users($active=array()) {
global $ibforums;
return <<<EOF

<div class="activeuserstrip">{$ibforums->lang['active_users_title']} ({$ibforums->lang['active_users_detail']})</div>
	  <div class='row2' style='padding:6px'>{$ibforums->lang['active_users_members']} {$active['names']}</div>

EOF;
}


function warn_level_rating($id, $level,$min=0,$max=10) {
global $ibforums;
return <<<EOF

&lt;&nbsp;$min ( <a href="javascript:PopUp('{$ibforums->base_url}act=warn&amp;mid={$id}&amp;CODE=view','Pager','500','450','0','1','1','1')">{$level}</a> ) $max&nbsp;&gt;

EOF;
}


function warn_level_warn($id, $percent) {
global $ibforums;
return <<<EOF

{$ibforums->lang['tt_warn']} (<a href="javascript:PopUp('{$ibforums->base_url}act=warn&amp;mid={$id}&amp;CODE=view','Pager','500','450','0','1','1','1')">{$percent}</a>%)

EOF;
}


function topic_opts_open($fid, $tid) {
global $ibforums;
return <<<EOF

<div id='topic_open' style='display:none;z-index:2;'>
    <div class="tableborder">
	  <div class='maintitle'><{CAT_IMG}>&nbsp;<a href="javascript:ShowHide('topic_open','topic_closed')">{$ibforums->lang['to_close']}</a></div>
	  <div class='tablepad'>
	   <b><a href='{$ibforums->base_url}act=Track&amp;f={$fid}&amp;t={$tid}'>{$ibforums->lang['tt_title']}</a></b>
	   <br>
	   <span class='desc'>{$ibforums->lang['tt_desc']}</span>
	   <br><br>
	   <b><a href='{$ibforums->base_url}act=Track&amp;f={$fid}&amp;type=forum'>{$ibforums->lang['ft_title']}</a></b>
	   <br>
	   <span class='desc'>{$ibforums->lang['ft_desc']}</span>
	   <br><br>
	   <b><a href='{$ibforums->base_url}act=Print&amp;client=choose&amp;f={$fid}&amp;t={$tid}'>{$ibforums->lang['av_title']}</a></b>
	   <br>
	   <span class='desc'>{$ibforums->lang['av_desc']}</span>
	 </div>
   </div>
</div>

EOF;
}


function TableFooter($data, $report_link) {
global $ibforums;
return <<<EOF
<!--IBF.TOPIC_ACTIVE-->
<div class="activeuserstrip" align="center">&laquo; <a href='{$ibforums->base_url}showtopic={$data[TOPIC]['tid']}&amp;view=old'>{$ibforums->lang['t_old']}</a> | <strong><a href='{$ibforums->base_url}showforum={$data['FORUM']['id']}'>{$data['FORUM']['name']}</a></strong> | <a href='{$ibforums->base_url}showtopic={$data[TOPIC]['tid']}&amp;view=new'>{$ibforums->lang['t_new']}</a> &raquo;</div>
</div><br>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td width="60%" valign="top"><!--IBF.NAVIGATION--><br><br>{$data['TOPIC']['why_close']}</td>
<td width="40%" align="right" valign="top">$report_link</td>
</tr>
<tr>
 <td align='left' width="20%" nowrap="nowrap">{$data[TOPIC][SHOW_PAGES]}</td>
 <td align='right' width="80%"><!--IBF.QUICK_REPLY_CLOSED--> {$data[TOPIC][REPLY_BUTTON]}{$data[TOPIC][TOPIC_BUTTON]}{$data[TOPIC][POLL_BUTTON]}
</td>
</tr>
<tr><td><!--IBF.MOD_PANEL--></td></tr>
<tr><td><!--IBF.MULTIMOD--></td></tr>
</table>
<!--IBF.QUICK_REPLY_OPEN-->
{$data['TOPIC']['modform_close']}
<br>
<div align='right'>{$data[FORUM]['JUMP']}</div>
<br>

EOF;
}


function Show_attachments_img_thumb($file_name, $width, $height, $aid) {
global $ibforums;
return <<<EOF


<br>
<br>
<strong><span class='edit'>{$ibforums->lang['pic_attach_thumb']}</span></strong>
<br>
<a href='{$ibforums->base_url}act=Attach&amp;type=post&amp;id=$aid' title='{$ibforums->lang['pic_attach_thumb']}' target='_blank'><img src='{$ibforums->vars['upload_url']}/$file_name' width='$width' height='$height' class='attach' alt='{$ibforums->lang['pic_attach']}'></a>

EOF;
}


function quick_reply_box_open($fid="",$tid="",$show="hide", $warning = "", $key="", $syntax_select = "", $mod_buttons = "") {
global $ibforums;
return <<<EOF

<script language="javascript1.2" type="text/javascript">
<!--
var MessageMax  = "{$ibforums->lang['the_max_length']}";
var Override    = "{$ibforums->lang['override']}";
MessageMax      = parseInt(MessageMax);
if ( MessageMax < 0 )
{
	MessageMax = 0;
}
function keyb_pop()
{
window.open('index.{$ibforums->vars['php_ext']}?act=legends&CODE=keyb&s={$ibforums->session_id}','Legends','width=700,height=160,resizable=yes,scrollbars=yes'); 
}
function emo_pop()
{
  window.open('index.{$ibforums->vars['php_ext']}?act=legends&CODE=emoticons&s={$ibforums->session_id}','Legends','width=250,height=500,resizable=yes,scrollbars=yes');
}
function bbc_pop()
{
  window.open('index.{$ibforums->vars['php_ext']}?act=legends&CODE=bbcode&s={$ibforums->session_id}','Legends','width=700,height=500,resizable=yes,scrollbars=yes');
}
	function ValidateForm(isMsg) {
		MessageLength  = document.REPLIER.Post.value.length;
		errors = "";
		if (isMsg == 1)
		{
			if (document.REPLIER.msg_title.value.length < 2)
			{
				errors = "{$ibforums->lang['msg_no_title']}";
			}
		}
		if (MessageLength < 2) {
			 errors = "{$ibforums->lang['js_no_message']}";
		}
		if (MessageMax !=0) {
			if (MessageLength > MessageMax) {
				errors = "{$ibforums->lang['js_max_length']} " + MessageMax + " {$ibforums->lang['js_characters']}. {$ibforums->lang['js_current']}: " + MessageLength;
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
	var error_no_url        = "{$ibforums->lang['jscode_error_no_url']}";
	var error_no_title      = "{$ibforums->lang['jscode_error_no_title']}";
	var error_no_email      = "{$ibforums->lang['jscode_error_no_email']}";
	var error_no_width      = "{$ibforums->lang['jscode_error_no_width']}";
	var error_no_height     = "{$ibforums->lang['jscode_error_no_height']}";
	var text_enter_url      = "{$ibforums->lang['jscode_text_enter_url']}";
	var text_enter_url_name = "{$ibforums->lang['jscode_text_enter_url_name']}";
	var text_enter_image    = "{$ibforums->lang['jscode_text_enter_image']}";
	var list_prompt         = "{$ibforums->lang['js_tag_list']}";
	var prompt_start        = "{$ibforums->lang['js_text_to_format']}";
	//-->
</script>
<script language="JavaScript" type="text/javascript">
var rusLet=new Array("Э","Щ","Щ","Ч","Ч","Ш","Ш","Ё","Ё","Ё","Ё","Ю","Ю","Ю","Ю","Я","Я","Я","Я","Ж","Ж","А","Б","В","Г","Д","Е","З","ИЙ","ИЙ","ЫЙ","ЫЙ","И","Й","К","КС","Л","М","Н","О","П","Р","С","Т","У","Ф","Х","Ц","Щ","Ы","э","щ","ч","ш","ё","ё","ю","ю","я","я","ж","а","б","в","г","д","е","з","ий","ий","ый","ый","и","й","к","кс","л","м","н","о","п","р","с","т","у","ф","х","ц","щ","щ","ы","ъ","ъ","ь");
var engReg=new Array(/E'/g,/SHCH/g,/Shch/g,/CH/g,/Ch/g,/SH/g,/Sh/g,/YO/g,/JO/g,/Yo/g,/Jo/g,/YU/g,/JU/g,/Yu/g,/Ju/g,/YA/g,/JA/g,/Ya/g,/Ja/g,/ZH/g,/Zh/g,/A/g,/B/g,/V/g,/G/g,/D/g,/E/g,/Z/g,/II/g,/IY/g,/YI/g,/YY/g,/I/g,/J/g,/K/g,/X/g,/L/g,/M/g,/N/g,/O/g,/P/g,/R/g,/S/g,/T/g,/U/g,/F/g,/H/g,/C/g,/W/g,/Y/g,/e'/g,/shch/g,/ch/g,/sh/g,/yo/g,/jo/g,/yu/g,/ju/g,/ya/g,/ja/g,/zh/g,/a/g,/b/g,/v/g,/g/g,/d/g,/e/g,/z/g,/ii/g,/iy/g,/yi/g,/yy/g,/i/g,/j/g,/k/g,/x/g,/l/g,/m/g,/n/g,/o/g,/p/g,/r/g,/s/g,/t/g,/u/g,/f/g,/h/g,/c/g,/w/g,/#/g,/y/g,/`/g,/~/g,/'/g);
function rusLang()
{
var textar = document.REPLIER.Post.value;
if (textar) {
 for (i=0; i<engReg.length; i++)
 { textar = textar.replace(engReg[i], rusLet[i]) }
 document.REPLIER.Post.value = textar; }
}
</script>
{$warning}
<div align='left' id='qr_open' style="display:$show;position:relative;">
<form name='REPLIER' action="{$ibforums->base_url}" method='post' onsubmit='return ValidateForm()'>
<input type='hidden' name='act' value='Post'>
<input type='hidden' name='CODE' value='03'>
<input type='hidden' name='f' value='$fid'>
<input type='hidden' name='t' value='$tid'>
<input type='hidden' name='st' value='{$ibforums->input['st']}'>
<input type='hidden' name='auth_key' value='$key'>
<div class="tableborder">
<table cellpadding="0" cellspacing="0" width="100%">
<!--IBF.NAME_FIELD-->
 <tr><td class='pformstrip' colspan='2'>{$ibforums->lang['ib_code_buttons']}</td></tr>
 <tr>
   <td class='pformright' align='center' colspan='2'>
	   <input type='button' accesskey='b' value=' B ' onclick='simpletag("B")' class='codebuttons' name='B' style="font-weight:bold">
	   <input type='button' accesskey='i' value=' I ' onclick='simpletag("I")' class='codebuttons' name='I' style="font-style:italic">
	   <input type='button' accesskey='u' value=' U ' onclick='simpletag("U")' class='codebuttons' name='U' style="text-decoration:underline">&nbsp;
	   <select name='ffont' class='codebuttons' onchange="alterfont(this.options[this.selectedIndex].value, 'FONT')">
	   <option value='0'>{$ibforums->lang['ct_font']}</option>
	   <option value='Arial' style='font-family:Arial'>{$ibforums->lang['ct_arial']}</option>
	   <option value='Times' style='font-family:Times'>{$ibforums->lang['ct_times']}</option>
	   <option value='Courier' style='font-family:Courier'>{$ibforums->lang['ct_courier']}</option>
	   <option value='Impact' style='font-family:Impact'>{$ibforums->lang['ct_impact']}</option>
	   <option value='Geneva' style='font-family:Geneva'>{$ibforums->lang['ct_geneva']}</option>
	   <option value='Optima' style='font-family:Optima'>Optima</option>
	   </select> <select name='fsize' class='codebuttons' onchange="alterfont(this.options[this.selectedIndex].value, 'SIZE')">
	   <option value='0'>{$ibforums->lang['ct_size']}</option>
	   <option value='1'>{$ibforums->lang['ct_sml']}</option>
	   <option value='7'>{$ibforums->lang['ct_lrg']}</option>
	   <option value='14'>{$ibforums->lang['ct_lest']}</option>
	   </select> <select name='fcolor' class='codebuttons' onchange="alterfont(this.options[this.selectedIndex].value, 'COLOR')">
	   <option value='0'>{$ibforums->lang['ct_color']}</option>
	   <option value='black' style='color:black'>{$ibforums->lang['ct_black']}</option>
	   <option value='blue' style='color:blue'>{$ibforums->lang['ct_blue']}</option>
	   <option value='red' style='color:red'>{$ibforums->lang['ct_red']}</option>
	   <option value='purple' style='color:purple'>{$ibforums->lang['ct_purple']}</option>
	   <option value='orange' style='color:orange'>{$ibforums->lang['ct_orange']}</option>
	   <option value='yellow' style='color:yellow'>{$ibforums->lang['ct_yellow']}</option>
	   <option value='gray' style='color:gray'>{$ibforums->lang['ct_grey']}</option>
	   <option value='green' style='color:green'>{$ibforums->lang['ct_green']}</option>
	   </select>
	   {$syntax_select}
	   <input type='button' accesskey='q' value='QUOTE' onclick='simpletag("QUOTE")' class='codebuttons' name='QUOTE'>
	   <input type='button' accesskey='p' value='Spoiler' onclick='simpletag("SPOILER")' class='codebuttons' name='SPOILER'>
<br>
	   <input type='button' accesskey='h' value=' http:// ' onclick='tag_url()' class='codebuttons' name='url'>
	   <input type='button' accesskey='g' value=' IMG ' onclick='tag_image()' class='codebuttons' name='img'>
	   <input type='button' accesskey='l' value=' LIST ' onclick='tag_list()' class='codebuttons' name="LIST">&nbsp;
           <input type='button' accesskey='y' value='TRANSLIT' onClick='rusLang()'class='codebuttons' name="TRANSLIT">
           <input type='button' accesskey='r' value='Русская клавиатура' onclick='javascript:keyb_pop()' class='codebuttons'>
	   &nbsp;&nbsp;<a href='javascript:closeall();'>{$ibforums->lang['js_close_all_tags']}</a>
   </td>
   </tr>
   <tr><td colspan="2" class='pformstrip'>{$ibforums->lang['post']}</td></tr>
   <tr>
     <td class='pformleft' align='center' width='10%'>
       <!--SMILIE TABLE--><br>
       <div class='desc'><strong>&middot; <a href='javascript:bbc_pop()'>{$ibforums->lang['bbc_help']}</a> &middot;</strong></div>
     </td>
     <td class="pformright" valign='top' width='90%' height='100%'>
     	<textarea cols='100' rows='23' name='Post'
	onKeyPress='if (event.keyCode==10 || ((event.metaKey || event.ctrlKey) && event.keyCode==13))
	this.form.submit.click()' tabindex='3' style='width:99%' class='textinput'></textarea>
     </td>
   </tr>
   <tr>
	<td class='pformleft' align='center'><b>{$ibforums->lang['po_options']}</b></td>
	<td class='pformright' colspan='2'>
	 <!--IBF.EMO-->
	 <!--IBF.TRACK-->
         <input type='hidden' name='add_merge_edit' value='1' 'checked' class='forminput'>
    </td>
   </tr>
 <tr>
  <td class='pformstrip' align='center' style='text-align:center' colspan='2'>
	<input type="submit" name="submit" value="{$ibforums->lang['submit_reply']}" tabindex='4' class='forminput' accesskey='s'>&nbsp;
	<input type="submit" name="preview" value="{$ibforums->lang['button_preview']}" tabindex='5' class='forminput'>
  </td>
</tr>
</table>
</div>
</form>
</div>


EOF;
}


function mod_wrapper($id="", $text="") {
global $ibforums;
return <<<EOF

<option value='$id'>$text</option>

EOF;
}


function get_box_enableemo($checked) {
global $ibforums;
return <<<EOF

<input type='checkbox' name='enableemo' class='checkbox' value='yes' $checked>&nbsp;{$ibforums->lang['enable_emo']}

EOF;
}


function quick_reply_box_closed() {
global $ibforums;
return <<<EOF

<!-- DEFAULT DIV -->
	<a href="javascript:ShowHide('qr_open','qr_closed');" title="{$ibforums->lang['qr_open']}" accesskey="f"><{T_QREPLY}></a>

EOF;
}


function start_poll_link($fid, $tid) {
global $ibforums;
return <<<EOF

<a href="{$ibforums->base_url}act=Post&amp;CODE=14&amp;f=$fid&amp;t=$tid">{$ibforums->lang['new_poll_link']}</a> &#124;&nbsp;

EOF;
}


function golastpost_link($fid, $tid) {
global $ibforums;
return <<<EOF

( <a href='{$ibforums->base_url}act=ST&amp;f=$fid&amp;t=$tid&amp;view=getnewpost'>{$ibforums->lang['go_new_post']}</a> )

EOF;
}


function PageTop($data) {
global $ibforums;
return <<<EOF
<script language='javascript' type='text/javascript'>
    <!--
    
    function link_to_post(pid)
    {
    	temp = prompt( "{$ibforums->lang['tt_prompt']}", "{$ibforums->base_url}showtopic={$ibforums->input['t']}&view=findpost&p=" + pid );
    	return false;
    }
    
    function delete_post(theURL) {
       if (confirm('{$ibforums->lang['js_del_1']}')) {
          window.location.href=theURL;
       }
       else {
          alert ('{$ibforums->lang['js_del_2']}');
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
	
	function ShowHide(id1, id2) {
	  if (id1 != '') expMenu(id1);
	  if (id2 != '') expMenu(id2);
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
    //-->
    </script>
<script type='text/javascript' src='html/ibfcode_01.js'></script>
<a name='top'></a>
<!--IBF.FORUM_RULES-->
{$data['TOPIC']['links']}
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
 <td align='left' width="20%" nowrap="nowrap">{$data['TOPIC']['SHOW_PAGES']}&nbsp;{$data['TOPIC']['go_new']}</td>
 <td align='right' width="80%">{$data[TOPIC][REPLY_BUTTON]}<a href='{$ibforums->base_url}act=Post&amp;CODE=00&amp;f={$data[FORUM]['id']}' title='{$ibforums->lang['start_new_topic']}'><{A_POST}></a>{$data[TOPIC][POLL_BUTTON]}</td>
</tr>
</table>
<br>
{$data['TOPIC']['modform_open']}
<div class="tableborder">
    <div class='maintitle'><{CAT_IMG}>&nbsp;<b>{$data['TOPIC']['title']}</b>{$data['TOPIC']['description']}</div>
	<!--{IBF.POLL}-->
	<div align='right' class='postlinksbar'>
	  <b><!--{IBF.START_NEW_POLL}--><a href='{$ibforums->base_url}act=Track&amp;f={$data['FORUM']['id']}&amp;t={$data['TOPIC']['tid']}'>{$ibforums->lang['track_topic']}</a> |
	  <a href='{$ibforums->base_url}act=Forward&amp;f={$data['FORUM']['id']}&amp;t={$data['TOPIC']['tid']}'>{$ibforums->lang['forward']}</a> |
	  <a href='{$ibforums->base_url}act=Print&amp;client=printer&amp;f={$data['FORUM']['id']}&amp;t={$data['TOPIC']['tid']}'>{$ibforums->lang['print']}</a>{$data['TOPIC']['fav_text']}</b>
	</div>

EOF;
}

function RenderDeletedRow() {
global $ibforums;
return <<<EOF

<span style='color:red;font-size:10pt;line-height:100%'>{$ibforums->lang['mod_del']}</span>

EOF;
}

function get_box_enable_offtop($checked) {
global $ibforums;
return <<<EOF


<br><input type='checkbox' name='offtop' class='checkbox' value='1' $checked>&nbsp;{$ibforums->lang['enable_offtop']}


EOF;
}


}
?>