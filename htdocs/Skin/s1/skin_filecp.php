<?php

class skin_filecp {

function wrapper($out,$ranpic_output) {
global $ibforums;
return <<<EOF

<table align="center" width="100%" border='0' cellspacing="1">
 <tr valign="top">
  <td align="left" width="20%" style='height:100%'>
    <table width="100%" align="center" class='tableborder' cellspacing="1" cellpadding="0">
    <tr>
      <td class="maintitle"><img src="{$ibforums->vars['img_url']}/nav_m.gif"> {$ibforums->lang['links']}</td>
    </tr>
    </table>

    <table align='center' width='100%' class='tableborder' cellspacing='0'>
    <tr>
	 <td class='row4' width='100%' align='left'><br />
	&nbsp;&middot;<a href="{$ibforums->base_url}act=Downloads">{$ibforums->lang['download_link']}</a><br />
	{$out['manage']}
	{$out['add']}
	{$out['edit']}
	{$out['favs']}
	&nbsp;&middot;<a href="{$ibforums->base_url}act=FileCP&amp;do=stats">{$ibforums->lang['stats_link']}</a><br />
	&nbsp;&middot;<a href="{$ibforums->base_url}act=Downloads&amp;do=search">{$ibforums->lang['search_link']}</a><br />
	<br />
	</td>
	</tr>
	</table><br />
	{$out['notes']}
	{$out['top_submitters']}
	{$ranpic_output}

    <table width="100%" align="center" class='tableborder' cellspacing="1" cellpadding="0">
    <tr>
    <td class="maintitle"><img src="{$ibforums->vars['img_url']}/nav_m.gif"> {$ibforums->lang['stats']}</td>
    </tr>
    </table>
    <table align='center' width='100%' class='tableborder' cellspacing='0'>
    <tr>
	 <td class='row4' width='100%' align='left'><br />
	&nbsp;{$ibforums->lang['stats_files']}: {$out['files']}<br />
	&nbsp;{$ibforums->lang['stats_downs']}: {$out['down']}<br />
	&nbsp;{$ibforums->lang['stats_cats']}: {$out['cats']}<br />
	<br />
      </td>
    </tr>
    </table>
  </td>

<!--
  <td width="5%">
  <!-- Spacer -->
<!--
  </td>
-->
  <td width="80%" align="left">

EOF;
}

function random_pic($randompic){
global $ibforums;
return <<<EOF

	<table width="100%" align="center" class='tableborder' cellspacing="1" cellpadding="0">
  	<tr>
    <td class="maintitle"><img src="{$ibforums->vars['img_url']}/nav_m.gif"> {$ibforums->lang['random_pic_header']}</td>
  	</tr>
	</table>
   <table align='center' width='100%' class='tableborder' cellspacing='0'>
    <tr>
	 <td class='row4' width='100%' align='center'>
	 <br />
	<a href="{$ibforums->base_url}download={$randompic['id']}">
	<img src="{$randompic['screenshot']}" alt="{$randompic['name']}" border="0" width="150" /></a>
	<br />
	{$ibforums->lang['randompic_name']}: <a href="{$ibforums->base_url}download={$randompic['id']}">{$randompic['sname']}</a><br />
	{$ibforums->lang['randompic_author']}: <a href="{$ibforums->base_url}showuser={$randompic['mid']}">{$randompic['author']}</a><br />
	<br />
	</td>
	</tr>
	</table><br />	

EOF;
}

function bottom( ) {
global $ibforums;
return <<<EOF

     </td>
 </tr>
</table>
<!--
<p align='center' id='copyright'>&copy;2003 {$ibforums->lang['copyright']} <a href='http://bfarber.com' target='_blank'>bfarber</a><br />
					   Based on code written by Parmeet Sing and Sno</p>
-->

EOF;
}

function show_top_submitters($most){
global $ibforums;
return <<<EOF

	<table width="100%" align="center" class='tableborder' cellspacing="1" cellpadding="0">
  	<tr>
    <td class="maintitle"><img src="{$ibforums->vars['img_url']}/nav_m.gif"> {$ibforums->lang['top_submitters_header']}</td>
  	</tr>
	</table>
   <table align='center' width='100%' class='tableborder' cellspacing='0'>
    <tr>
	 <td class='row4' width='100%' align='left'>
	 <br />
		{$most}<br />
	</td>
	</tr>
	</table><br />	
	
EOF;
}

function show_global_notes($notes){
global $ibforums;
$notes = str_replace("&lt;","<",$notes);
$notes = str_replace("&gt;",">",$notes);
$notes = str_replace("&#39;","'",$notes);
return <<<EOF

	<table width="100%" align="center" class='tableborder' cellspacing="1" cellpadding="0">
  	<tr>
    <td class="maintitle"><img src="{$ibforums->vars['img_url']}/nav_m.gif"> {$ibforums->lang['global_notes_header']}</td>
  	</tr>
	</table>
   <table align='center' width='100%' class='tableborder' cellspacing='0'>
    <tr>
	 <td class='row4' width='100%' align='center'>
	 <br />
		{$notes}<br />
	<br />
	</td>
	</tr>
	</table><br />	
	
EOF;
}


function file_cp_splash($out) {
global $ibforums;
return <<<EOF

<table width="100%" align="center" class='tableborder' cellspacing="1" cellpadding="0">
  <tr>
    <td class="maintitle"><img src="{$ibforums->vars['img_url']}/nav_m.gif"> {$ibforums->lang['bf_page_title']}</td>
  </tr>
</table>
<table align="center" width="100%" class='tableborder' cellspacing="1" cellpadding="4">
<tr>
	 <td class='row4' width='100%' align='left'><br />
	&nbsp;&middot;<a href="{$ibforums->base_url}act=Downloads">{$ibforums->lang['download_link']}</a><br /><br />
	&nbsp;&middot;<a href="{$ibforums->base_url}act=FileCP">{$ibforums->lang['filecp_link']}</a><br /><br />
	&nbsp;{$out['manage']}
	&nbsp;{$out['add']}
	&nbsp;{$out['edit']}
	&nbsp;{$out['favs']}
	&nbsp;&middot;<a href="{$ibforums->base_url}act=FileCP&amp;do=stats">{$ibforums->lang['stats_link']}</a><br /><br />
	&nbsp;&middot;<a href="{$ibforums->base_url}act=Downloads&amp;do=search">{$ibforums->lang['search_link']}</a><br />
	<br />
	</td>
	</tr>
	</table>
EOF;
}


function file_cp_top() {
global $ibforums;
return <<<EOF

<script type='text/javascript'>
<!--
function sure(){
var name = confirm("{$ibforums->lang['confirm_delete']}")
if (name == true)
{
return true;
}
else
{
return false;
}
}
//-->
</script>

<table width="100%" align="center" class='tableborder' cellspacing="1" cellpadding="0">
  <tr>
    <td class="maintitle"><img src="{$ibforums->vars['img_url']}/nav_m.gif"> {$ibforums->lang['bf_page_title']} -> {$ibforums->lang['bf_files_title']}</td>
  </tr>
</table>
<table align="center" width="100%" class='tableborder' cellspacing="1" cellpadding="4">
<tr>
<td colspan='6' class='titlemedium' align='left' width='100%'>{$ibforums->lang['bf_sub_head']}</td>
</tr><tr>
	<td class='category' width='20%'>{$ibforums->lang['bf_file_name']}</td>
	<td class='category' width='20%'>{$ibforums->lang['bf_file_date']}</td>
	<td class='category' width='20%' align='center'>{$ibforums->lang['bf_file_updated']}</td>
	<td class='category' width='10%' align='center'>{$ibforums->lang['bf_file_down']}</td>
	<td class='category' width='10%' align='center'>{$ibforums->lang['bf_file_views']}</td>
	<td class='category' width=20%' align='center'>{$ibforums->lang['bf_file_control']}</td>
	</tr>


EOF;
}


function file_downs_top() {
global $ibforums;
return <<<EOF

<table width="100%" align="center" class='tableborder' cellspacing="1" cellpadding="0">
  <tr>
    <td class="maintitle"><img src="{$ibforums->vars['img_url']}/nav_m.gif"> {$ibforums->lang['bf_page_title']} -> {$ibforums->lang['bf_downs_title']}</td>
  </tr>
</table>
<table align="center" width="100%" class='tableborder' cellspacing="1" cellpadding="4">
<tr>
<td colspan='2' class='titlemedium' align='left' width='100%'>{$ibforums->lang['bf_down_head']}</td>
</tr><tr>
	<td class='category' width='25%'>{$ibforums->lang['bf_file_name']}</td>
	<td class='category' width='20%'>{$ibforums->lang['bf_file_date1']}</td>
</tr>

EOF;
}


function file_favs_top() {
global $ibforums;
return <<<EOF

<script type='text/javascript'>
<!--
function sure(){
var name = confirm("{$ibforums->lang['confirm_delete']}")
if (name == true)
{
return true;
}
else
{
return false;
}
}
//-->
</script>
<table width="100%" align="center" class='tableborder' cellspacing="1" cellpadding="0">
  <tr>
    <td class="maintitle"><img src="{$ibforums->vars['img_url']}/nav_m.gif"> {$ibforums->lang['bf_page_title']} -> {$ibforums->lang['bf_favs_title']}</td>
  </tr>
</table>
<table align="center" width="100%" class='tableborder' cellspacing="1" cellpadding="4">
<tr>
<td colspan='3' class='titlemedium' align='left' width='100%'>{$ibforums->lang['bf_favs_head']}</td>
</tr><tr>
	<td class='category' width='25%'>{$ibforums->lang['bf_file_name']}</td>
	<td class='category' width='20%'>{$ibforums->lang['bf_file_date2']}</td>
	<td class='category' width='30%'>{$ibforums->lang['bf_file_control']}</td>
</tr>


EOF;
}




function no_downs_submitted(){
global $ibforums;
return <<<EOF

<tr>
<td colspan='6' class='row4' style="font-size:15px;" align='center'><i>{$ibforums->lang['bf_none']}</i></td>
</tr>

EOF;
}

function no_downs_down(){
global $ibforums;
return <<<EOF

<tr>
<td colspan='2' class='row4' style="font-size:15px;" align='center'><i>{$ibforums->lang['bf_none1']}</i></td>
</tr>

EOF;
}


function no_downs_fav(){
global $ibforums;
return <<<EOF

<tr>
<td colspan='3' class='row4' style="font-size:15px;" align='center'><i>{$ibforums->lang['bf_none2']}</i></td>
</tr>

EOF;
}



function file_cp_info($info){
global $ibforums;
return <<<EOF


	<tr>
	<td class='row4' width='20%'><a href="{$ibforums->base_url}download={$info['id']}" target="_blank">{$info['name']}</a></td>
	<td class='row4' width='20%'>{$info['date']}</td>
	<td class='row4' width='20%' align='center'>{$info['updated']}</td>
	<td class='row4' width='10%' align='center'>{$info['downloads']}</td>
	<td class='row4' width='10%' align='center'>{$info['views']}</td>
	<td class='row4' width='20%' align='center'>
		<a href="{$ibforums->base_url}act=FileCP&amp;do=edit&amp;id={$info['id']}" target="_blank">{$ibforums->lang['bf_file_edit']}</a> /
		<a href="{$ibforums->base_url}act=FileCP&amp;do=remove&amp;type=sub&amp;id={$info['id']}" onClick="return sure()">{$ibforums->lang['bf_file_del']}</a>
	</td>
	</tr>

EOF;
}


function file_down_info($info){
global $ibforums;
return <<<EOF


	<tr>
	<td class='row4' width='25%'><a href="{$ibforums->base_url}download={$info['id']}" target="_blank">{$info['name']}</a></td>
	<td class='row4' width='20%'>{$info['date']}</td>
	</tr>

EOF;
}

function file_fav_info($info){
global $ibforums;
return <<<EOF

<tr>
	<td class='row4' width='25%'><a href="{$ibforums->base_url}download={$info['sid']}" target="_blank">{$info['sname']}</a></td>
	<td class='row4' width='20%'>{$info['date']}</td>
	<td class='row4' width='30%'><a href="{$ibforums->base_url}act=FileCP&amp;do=remove&amp;type=fav&amp;id={$info['id']}" onClick="return sure()">{$ibforums->lang['bf_file_del']}</a></td>
</tr>

EOF;
}


function file_cp_end(){
global $ibforums;
return <<<EOF

</table></td></tr>

EOF;
}


function file_stats($database, $member, $totaldown, $thisdown) {
global $ibforums;
return <<<EOF

<table width="100%" align="center" class='tableborder' cellspacing="1" cellpadding="0">
  <tr>
    <td class="maintitle"><img src="{$ibforums->vars['img_url']}/nav_m.gif"> {$ibforums->lang['bf_page_title']}</td>
  </tr>
</table>
<table align="center" width="100%" class='tableborder' cellspacing="1" cellpadding="4">

	<tr>
	<td width=25% class='row4'>{$ibforums->lang['bf_database']}</td><td width='75%' class='row4'>
		{$database}
	</td>
	</tr><tr>
	<td width=25% class='row4'>{$ibforums->lang['bf_mem_stat']}</td><td width='75%' class='row4'>
		<a href="{$ibforums->base_url}act=FileCP&do=files">{$member}</a>
	</td>
	</tr><tr>
	<td width=25% class='row4'>{$ibforums->lang['bf_tot_down']}</td><td width='75%' class='row4'>
		{$totaldown}
	</td>
	</tr><tr>
	<td width=25% class='row4'>{$ibforums->lang['bf_this_down']}</td><td width='75%' class='row4'>
		<a href="{$ibforums->base_url}act=FileCP&do=downs">{$thisdown}</a>
	</td>
	</tr>
</table>

EOF;
}


function file_statistics($data) {
global $ibforums;
return <<<EOF

<table width="100%" class='tableborder' align='center' cellspacing="1" cellpadding="0">

<tr><td width='50%'>
<table width="100%" class='tableborder' align='center' cellspacing="1" cellpadding="0">
   <tr> 
	<td colspan='2' class='maintitle'> 
	
	     &nbsp; {$ibforums->lang['bf_topdown']}

</td>
</tr>

  <tr> 
     <td class='titlemedium' align="center" width="50%" >{$ibforums->lang['bf_topdown_n']}</td>
     <td class='titlemedium' align="center" width="30%" >{$ibforums->lang['bf_topdown_a']}</td>
  </tr>
  <tr> 

<td class="row4" width='50%'>{$data['downloads_down']}</td>

<td class="row4" width="30%">{$data['downloadsdownloads']}</td>

</tr>
</table></td><td width='50%'>
<table width="100%" class='tableborder' align='center' cellspacing="1" cellpadding="0">
  <tr> 
    <td colspan='2' class='maintitle'> 

      	&nbsp; {$ibforums->lang['bf_topview']}
   </td>
</tr>
  <tr> 
     <td class='titlemedium' align="center" width="50%">{$ibforums->lang['bf_topdown_n']}</td>
     <td class='titlemedium' align="center" width="30%">{$ibforums->lang['bf_topview_a']}</td>
  </tr>
<tr> 

<td class="row4" width='50%'>{$data['downloads_views']}</td>

<td class="row4" width="30%">{$data['viewsviews']}</td>
</tr>
</table>
</td></tr>
<tr><td width='50%'>
<table width="100%" class='tableborder' align='center' cellspacing="1" cellpadding="0">
<tr> 
<td colspan='2' class='maintitle'> 
	
	     &nbsp; {$ibforums->lang['bf_topup']}

</td>
</tr>
  <tr> 
     <td class='titlemedium' align="center" width="50%" >{$ibforums->lang['bf_member']}</td>
     <td class='titlemedium' align="center" width="30%" >{$ibforums->lang['bf_sub']}</td>
       </tr>
<tr> 
<td class="row4" width='50%'>{$data['authors']}</td>

<td class="row4" width="30%">{$data['scripts']}</td>
</tr>
</table></td><td width='50%'>
<table width="100%" class='tableborder' align='center' cellspacing="1" cellpadding="0">
<tr> 
<td colspan='2' class='maintitle' > 
	
	     &nbsp; {$ibforums->lang['bf_topdown1']}

</td>
</tr>
  <tr> 
     <td class='titlemedium' align="center" width="50%" >{$ibforums->lang['bf_member']}</td>
     <td class='titlemedium' align="center" width="30%" >{$ibforums->lang['bf_topdown_a']}</td>
       </tr>
<tr> 
<td class="row4" width='50%'>{$data['downloaders']}</td>

<td class="row4" width="30%">{$data['downloads']}</td>
</tr>
</table>
</td></tr></table>


EOF;
}

function upload_file($ext) {
global $ibforums;
return <<<EOF
  <tr>
    <td width="30%" class="row4">{$ibforums->lang['add_file_browse']}<br />
	{$ibforums->lang['max_file_size']}:&nbsp;{$ibforums->vars['d_max_dwnld_size']}&nbsp;{$ibforums->lang['kb']}<br />
	{$ibforums->lang['allowed_ext']}&nbsp;{$ext}</td>
    <td width="70%" class="row4"><input type="file" name="file"></td>
  </tr>
EOF;
}
function link($default,$ext) {
global $ibforums;
return <<<EOF
  <tr>
    <td width="30%" class="row4">{$ibforums->lang['add_link_browse']}<br />
	{$ibforums->lang['allowed_ext']}&nbsp;{$ext}</td>
    <td width="70%" class="row4"><input type="text" name="link" value='{$default}'></td>
  </tr>
EOF;
}
function screen_link($defaultss,$sext,$ssreq) {
global $ibforums;
return <<<EOF
 <tr>
    <td width="30%" class="row4">{$ibforums->lang['add_screenshot']} {$ssreq}<br />
	{$ibforums->lang['allowed_ext']}&nbsp; {$sext}</td>
    <td width="70%" class="row4"><input type="text" name="screen1"></td>
  </tr>
EOF;
}
function custom_field($title, $value="") {
global $ibforums;
return <<<EOF
		<tr>
              <td class="row4" valign='top'><b>{$title}</b></td>
              <td align='left' class='row4'>{$value}</td>
            </tr>
EOF;
}

function field_entry($title, $content, $reqq) {
global $ibforums;
return <<<EOF
<tr>
  <td class='row4' valign='top'>{$title} {$reqq}</td>
  <td class='row4'>{$content}</td>
</tr>
EOF;
}

function field_textinput($name, $value="") {
global $ibforums;
return <<<EOF
<input type='text' size='30' name='{$name}' value='{$value}' class='forminput' />
EOF;
}

function field_dropdown($name, $options) {
global $ibforums;
return <<<EOF
<select name='{$name}' class='forminput'>{$options}</select>
EOF;
}

function field_textarea($name, $value) {
global $ibforums;
return <<<EOF
<textarea cols='60' rows='5' name='{$name}' class='forminput'>{$value}</textarea>
EOF;
}
function screen_upload($sext,$ssreq) {
global $ibforums;
return <<<EOF
 <tr>
    <td width="30%" class="row4">{$ibforums->lang['add_screenshot']} {$ssreq}<br />
	{$ibforums->lang['max_file_size']}:&nbsp;{$ibforums->vars['d_screen_max_dwnld_size']}&nbsp;{$ibforums->lang['kb']}<br />
	{$ibforums->lang['allowed_ext']}&nbsp; {$sext}</td>
    <td width="70%" class="row4"><input type="file" name="screen"></td>
  </tr>
EOF;
}

function show_edit_downloads($cats,$info,$link,$required_output,$optional_output) {
global $ibforums;
$ibcode=$ibforums->member['g_ibcode_download']?"{$ibforums->lang['code_en']}":"{$ibforums->lang['code_dis']}";
$html=$ibforums->member['g_html_download']?"{$ibforums->lang['code_en']}":"{$ibforums->lang['code_dis']}";
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
	
function emo_pop()
{
  window.open('index.{$ibforums->vars['php_ext']}?act=legends&CODE=emoticons&s={$ibforums->session_id}','Legends','width=250,height=500,resizable=yes,scrollbars=yes'); 
}
function bbc_pop()
{
  window.open('index.{$ibforums->vars['php_ext']}?act=legends&CODE=bbcode&s={$ibforums->session_id}','Legends','width=700,height=500,resizable=yes,scrollbars=yes'); 
}	
function CheckLength() {
	MessageLength  = document.REPLIER.Post.value.length;
	message  = "";
		if (MessageMax > 0) {
			message = "{$ibforums->lang['js_post']}: {$ibforums->lang['js_max_length']} " + MessageMax + " {$ibforums->lang['js_characters']}.";
		} else {
			message = "";
		}
		alert(message + "      {$ibforums->lang['js_used']} " + MessageLength + " {$ibforums->lang['js_characters']}.");
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
	
	// IBC Code stuff
	var text_enter_url      = "{$ibforums->lang['jscode_text_enter_url']}";
	var text_enter_url_name = "{$ibforums->lang['jscode_text_enter_url_name']}";
	var text_enter_image    = "{$ibforums->lang['jscode_text_enter_image']}";
	var text_enter_email    = "{$ibforums->lang['jscode_text_enter_email']}";
	var text_enter_flash    = "{$ibforums->lang['jscode_text_enter_flash']}";
	var text_code           = "{$ibforums->lang['jscode_text_code']}";
	var text_quote          = "{$ibforums->lang['jscode_text_quote']}";
	var error_no_url        = "{$ibforums->lang['jscode_error_no_url']}";
	var error_no_title      = "{$ibforums->lang['jscode_error_no_title']}";
	var error_no_email      = "{$ibforums->lang['jscode_error_no_email']}";
	var error_no_width      = "{$ibforums->lang['jscode_error_no_width']}";
	var error_no_height     = "{$ibforums->lang['jscode_error_no_height']}";
	var prompt_start        = "{$ibforums->lang['js_text_to_format']}";
	var help_bold           = "{$ibforums->lang['hb_bold']}";
	var help_italic         = "{$ibforums->lang['hb_italic']}";
	var help_under          = "{$ibforums->lang['hb_under']}";
	var help_font           = "{$ibforums->lang['hb_font']}";
	var help_size           = "{$ibforums->lang['hb_size']}";
	var help_color          = "{$ibforums->lang['hb_color']}";
	var help_close          = "{$ibforums->lang['hb_close']}";
	var help_url            = "{$ibforums->lang['hb_url']}";
	var help_img            = "{$ibforums->lang['hb_img']}";
	var help_email          = "{$ibforums->lang['hb_email']}";
	var help_quote          = "{$ibforums->lang['hb_quote']}";
	var help_list           = "{$ibforums->lang['hb_list']}";
	var help_code           = "{$ibforums->lang['hb_code']}";
	var help_click_close    = "{$ibforums->lang['hb_click_close']}";
	var list_prompt         = "{$ibforums->lang['js_tag_list']}";
	
	//-->
</script>
<form action="?act=Downloads&amp;do=edit" method="post" enctype="multipart/form-data" name='REPLIER'>
<input type="hidden" name="id" value="{$info['id']}" />
<table width="100%" align="center" class='tableborder' cellspacing="1" cellpadding="0">
  <tr>
    <td class="maintitle"><img src="{$ibforums->vars['img_url']}/nav_m.gif"> {$ibforums->lang['bf_page_title']} -> {$ibforums->lang['title_edit']}</td>
  </tr>
</table>
<table align="center" class='tableborder' cellspacing="1" cellpading="0" width="100%">
 <tr>
  <td colspan='2' align='center' width='100%' class='row4'><br />{$ibforums->lang['whats_required']}<br /><br /></td>
 </tr> 
  <tr>
      <td width="30%" valign="top" class="row4">{$ibforums->lang['add_filename']} *</td>
    <td width="70%" valign="top" class="row4"><input type="text" name="sname" value="{$info['sname']}"></td>
  </tr>
  <tr>
      <td width="30%" valign="top" class="row4">{$ibforums->lang['add_author']}</td>
    <td width="70%" valign="top" class="row4"><input type="text" name="author" value="{$info['author']}"></td>
  </tr>
 <tr>
   <td class='row4'>
	   <input type='radio' class='radiobutton' name='bbmode' value='ezmode' onclick='setmode(this.value)' />&nbsp;<b>{$ibforums->lang['bbcode_guided']}</b><br />
	   <input type='radio' class='radiobutton' name='bbmode' value='normal' onclick='setmode(this.value)' checked="checked" />&nbsp;<b>{$ibforums->lang['bbcode_normal']}</b>
	   <script type='text/javascript' src='html/ibfcode.js?{$ibforums->vars['client_script_version']}'></script>
   </td>
   <td class='row4'>
	   <input type='button' accesskey='b' value=' B '       onclick='simpletag("B")' class='codebuttons' name='B' style="font-weight:bold" onmouseover="hstat('bold')" />
	   <input type='button' accesskey='i' value=' I '       onclick='simpletag("I")' class='codebuttons' name='I' style="font-style:italic" onmouseover="hstat('italic')" />
	   <input type='button' accesskey='u' value=' U '       onclick='simpletag("U")' class='codebuttons' name='U' style="text-decoration:underline" onmouseover="hstat('under')" />
	   
	   <select name='ffont' class='codebuttons' onchange="alterfont(this.options[this.selectedIndex].value, 'FONT')"  onmouseover="hstat('font')">
	   <option value='0'>{$ibforums->lang['ct_font']}</option>
	   <option value='Arial' style='font-family:Arial'>{$ibforums->lang['ct_arial']}</option>
	   <option value='Times' style='font-family:Times'>{$ibforums->lang['ct_times']}</option>
	   <option value='Courier' style='font-family:Courier'>{$ibforums->lang['ct_courier']}</option>
	   <option value='Impact' style='font-family:Impact'>{$ibforums->lang['ct_impact']}</option>
	   <option value='Geneva' style='font-family:Geneva'>{$ibforums->lang['ct_geneva']}</option>
	   <option value='Optima' style='font-family:Optima'>Optima</option>
	   </select><select name='fsize' class='codebuttons' onchange="alterfont(this.options[this.selectedIndex].value, 'SIZE')" onmouseover="hstat('size')">
	   <option value='0'>{$ibforums->lang['ct_size']}</option>
	   <option value='1'>{$ibforums->lang['ct_sml']}</option>
	   <option value='7'>{$ibforums->lang['ct_lrg']}</option>
	   <option value='14'>{$ibforums->lang['ct_lest']}</option>
	   </select><select name='fcolor' class='codebuttons' onchange="alterfont(this.options[this.selectedIndex].value, 'COLOR')" onmouseover="hstat('color')">
	   <option value='0'>{$ibforums->lang['ct_color']}</option>
	   <option value='blue' style='color:blue'>{$ibforums->lang['ct_blue']}</option>
	   <option value='red' style='color:red'>{$ibforums->lang['ct_red']}</option>
	   <option value='purple' style='color:purple'>{$ibforums->lang['ct_purple']}</option>
	   <option value='orange' style='color:orange'>{$ibforums->lang['ct_orange']}</option>
	   <option value='yellow' style='color:yellow'>{$ibforums->lang['ct_yellow']}</option>
	   <option value='gray' style='color:gray'>{$ibforums->lang['ct_grey']}</option>
	   <option value='green' style='color:green'>{$ibforums->lang['ct_green']}</option>
	   </select>
	   &nbsp; <a href='javascript:closeall();' onmouseover="hstat('close')">{$ibforums->lang['js_close_all_tags']}</a>
	   <br />
	   <input type='button' accesskey='h' value=' http:// ' onclick='tag_url()'            class='codebuttons' name='url' onmouseover="hstat('url')" />
	   <input type='button' accesskey='g' value=' IMG '     onclick='tag_image()'          class='codebuttons' name='img' onmouseover="hstat('img')" />
	   <input type='button' accesskey='e' value='  @  '     onclick='tag_email()'          class='codebuttons' name='email' onmouseover="hstat('email')" />
	   <input type='button' accesskey='q' value=' QUOTE '   onclick='simpletag("QUOTE")'   class='codebuttons' name='QUOTE' onmouseover="hstat('quote')" />
	   <input type='button' accesskey='p' value=' CODE '    onclick='simpletag("CODE")'    class='codebuttons' name='CODE' onmouseover="hstat('code')" />
	   <input type='button' accesskey='l' value=' LIST '     onclick='tag_list()'          class='codebuttons' name="LIST" onmouseover="hstat('list')" />
	   <!--<input type='button' accesskey='l' value=' SQL '     onclick='simpletag("SQL")'     class='codebuttons' name='SQL'>
	   <input type='button' accesskey='t' value=' HTML '    onclick='simpletag("HTML")'    class='codebuttons' name='HTML'>-->
	   <br />
	   {$ibforums->lang['hb_open_tags']}:&nbsp;<input type='text' name='tagcount' size='3' maxlength='3' style='font-size:10px;font-family:verdana,arial;border:0px;font-weight:bold;' readonly="readonly" class='row1' value="0" />
	   &nbsp;<input type='text' name='helpbox' size='50' maxlength='120' style='width:auto;font-size:10px;font-family:verdana,arial;border:0px' readonly="readonly" class='row1' value="{$ibforums->lang['hb_start']}" />
	</td>
   </tr>
   <tr>
     <td width='30%' class='row4' align='center'>
	  {$ibforums->lang['add_desc']} *<br />
	  <i>{$ibforums->lang['ibcode_is']}{$ibcode}</i><br />
	  <i>{$ibforums->lang['html_is']}{$html}<i>
	   <!--SMILIE TABLE-->
	   <br /><div class='desc'><strong><a href='javascript:CheckLength()'>{$ibforums->lang['check_length']}</a> &middot; <a href='javascript:bbc_pop()'>{$ibforums->lang['bbc_help']}</a></strong><br /></div>
     </td>
    <td width="70%" class="row4"><textarea name="Post" cols="38" rows="9">{$info['sdesc']}</textarea></td>
  </tr>
  <tr>
      <td width="30%" class="row4">{$ibforums->lang['add_cat']} *
		<br /> {$ibforums->lang['current_cat']} {$info['cname']}</td>
    <td width="70%" class="row4"><select name="cat">
          <option value="">{$ibforums->lang['choose_cat']}</option>
          {$cats}
        </select></td>
  </tr>
{$required_output}
{$optional_output}
{$link}
  <tr>
    <td width="100%" colspan='2' align='center' class="row4"><input type="submit" name="Submit" value="{$ibforums->lang['submit_but']}"></td>
  </tr>
</table>
</form>

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

function file_manage($in) {
global $ibforums;
return <<<EOF
<table width="100%" align="center" class='tableborder' cellspacing="1" cellpadding="0">
  <tr>
    <td class="maintitle"><img src="{$ibforums->vars['img_url']}/nav_m.gif"> {$ibforums->lang['bf_page_title']} -> {$ibforums->lang['bf_manage_title']}</td>
  </tr>
</table>
<table align="center" class='tableborder' cellspacing="1" cellpading="0" width="100%">
 <tr>
  <td align='center' width='100%' class='tableborder'><div class='row4'><br />{$ibforums->lang['whats_available']}<br /><br /></div></td>
 </tr> 
  <tr>
      <td width="100%" valign="top" align='left' class="row4">
<br />
{$in['approve']}
{$in['edit']}
{$in['links']}
{$in['tcheck']}
{$in['optimize']}
<br />
</td>
  </tr>
</table>

EOF;
}

function show_accept_downloads_top( ) {
global $ibforums;
return <<<EOF

<script language='JavaScript' type="text/javascript">
<!--

var ie  = document.all  ? 1 : 0;
//var ns4 = document.layers ? 1 : 0;

function hl(cb)
{
   if (ie)
   {
	   while (cb.tagName != "TR")
	   {
		   cb = cb.parentElement;
	   }
   }
   else
   {
	   while (cb.tagName != "TR")
	   {
		   cb = cb.parentNode;
	   }
   }
   cb.className = 'hlight';
}

function dl(cb) {
   if (ie)
   {
	   while (cb.tagName != "TR")
	   {
		   cb = cb.parentElement;
	   }
   }
   else
   {
	   while (cb.tagName != "TR")
	   {
		   cb = cb.parentNode;
	   }
   }
   cb.className = 'dlight';
}

function cca(cb) {
   if (cb.checked)
   {
	   hl(cb);
   }
   else
   {
	   dl(cb);
   }
}
	   
function CheckAll(cb) {
	var fmobj = document.mutliact;
	for (var i=0;i<fmobj.elements.length;i++) {
		var e = fmobj.elements[i];
		if ((e.name != 'allbox') && (e.type=='checkbox') && (!e.disabled)) {
			e.checked = fmobj.allbox.checked;
			if (fmobj.allbox.checked)
			{
			   hl(e);
			}
			else
			{
			   dl(e);
			}
		}
	}
}

//-->
</script>

<table width="100%" align="center" class='tableborder' cellspacing="1" cellpadding="0">
  <tr>
    <td class="maintitle"><img src="{$ibforums->vars['img_url']}/nav_m.gif"> {$ibforums->lang['bf_page_title']} -> {$ibforums->lang['bf_manage_title']} -> {$ibforums->lang['bf_approve_title']}</td>
  </tr>
</table>
<table align="center" class='tableborder' cellspacing="1" cellpading="0" width="100%">
<form action='index.php?act=FileCP&amp;do=multiapprove' method='post' name='mutliact'>
 <tr>
<td nowrap='nowrap' class='titlemedium' width="10%">{$ibforums->lang['edit_id']}</td>
<td nowrap='nowrap' class='titlemedium' width="80%">{$ibforums->lang['edit_name']}</td>
<td nowrap='nowrap' class='titlemedium' align='center' width="10%"><input name="allbox" type="checkbox" value="Check All" onclick="CheckAll();" /></td>
</tr>
EOF;
}

function show_accept_downloads($info) {
global $ibforums;
return <<<EOF
<tr>
<td valign="top" class="row4" width="10%"><a href="{$ibforums->base_url}download={$info['id']}">{$info['id']}</a></td>
<td valign="top" class="row4" width="80%"><a href="{$ibforums->base_url}download={$info['id']}">{$info['sname']}</a>&nbsp;( <a href="{$ibforums->base_url}act=FileCP&amp;do=edit&amp;id={$info['id']}">{$ibforums->lang['filecp_link_edit']}</a> | <a href="{$ibforums->base_url}act=FileCP&amp;do=manage&amp;type=approve&amp;id={$info['id']}">{$ibforums->lang['filecp_link_accept']}</a> | <a href="{$ibforums->base_url}act=Downloads&amp;do=download&amp;id={$info['id']}">{$ibforums->lang['filecp_link_download']}</a> | <a href="{$ibforums->base_url}act=FileCP&amp;do=manage&amp;type=delete&amp;id={$info['id']}">{$ibforums->lang['filecp_link_del']}</a>)</td>
<td width='10%' class='row4' align='center'><input type='hidden' name='{$info['id']}' value='1' /><input type='checkbox' name='file_{$info['id']}' value='yes' class='forminput' onclick="cca(this);" /></td>
</tr>
EOF;
}

function show_manage_bottom( ) {
global $ibforums;
return <<<EOF

<tr valign='middle'>
<td colspan='3' class='titlemedium' align='center'><input type='submit' value='{$ibforums->lang['approve_form_but']}' class='forminput' /></td></tr>
</table>
</form>
EOF;
}

function show_mod_search( ) {
global $ibforums;
return <<<EOF
<form action="?act=FileCP&amp;do=manage&amp;type=listedit" method="post" enctype="multipart/form-data">
<table width="100%" align="center" class='tableborder' cellspacing="1" cellpadding="0">
  <tr>
    <td class="maintitle"><img src="{$ibforums->vars['img_url']}/nav_m.gif"> {$ibforums->lang['bf_page_title']} -> {$ibforums->lang['bf_manage_title']} -> {$ibforums->lang['bf_editsearch_title']}</td>
  </tr>
</table>
<table align="center" class='tableborder' cellspacing="1" cellpading="0" width="100%">
 <tr>
      <td width="15%" valign="top" class="row4">{$ibforums->lang['search_filename']}</td>
<td width="85%" valign="top" class="row4"><input type="text" name="name"></td>
</tr>
  <tr>
<td width="100%" valign="top" align='center' colspan='2' class="row4"><b>{$ibforums->lang['enter_or']}</b></td>
</tr>
  <tr>
      <td width="15%" valign="top" class="row4">{$ibforums->lang['search_fileid']}</td>
<td width="85%" valign="top" class="row4"><input type="text" name="id"></td>
</tr>
  <tr>
<td width="100%" valign="top" align='center' colspan='2' class="row4"><b>{$ibforums->lang['enter_or']}</b></td>
</tr>
  <tr>
<td width="100%" valign="top" colspan='2' class="row4"><a href="{$ibforums->base_url}act=FileCP&amp;do=manage&amp;type=listedit&amp;name=%">{$ibforums->lang['search_viewall']}</a></td>
</tr>
  <tr>
<td width="100%" valign="top" align='center' colspan='2' class="row4"><input type="submit" value="{$ibforums->lang['search_but']}"></td>
</tr>
</table>
</form>
EOF;
}

function show_listedit_top( ) {
global $ibforums;
return <<<EOF
<table width="100%" align="center" class='tableborder' cellspacing="1" cellpadding="0">
  <tr>
    <td class="maintitle"><img src="{$ibforums->vars['img_url']}/nav_m.gif"> {$ibforums->lang['bf_page_title']} -> {$ibforums->lang['bf_manage_title']} -> {$ibforums->lang['bf_editsearch_title']}</td>
  </tr>
</table>
<table align="center" class='tableborder' cellspacing="1" cellpading="0" width="100%">
 <tr>
<td nowrap='nowrap' class='titlemedium' width="10%">{$ibforums->lang['edit_id']}</td>
<td nowrap='nowrap' class='titlemedium' width="90%">{$ibforums->lang['edit_name']}</td>
</tr>
EOF;
}

function show_listedit_row( $info ) {
global $ibforums;
return <<<EOF
<tr>
<td valign="top" class="row4" width="10%"><a href="{$ibforums->base_url}download={$info['id']}">{$info['id']}</a></td>
<td valign="top" class="row4" width="90%"><a href="{$ibforums->base_url}download={$info['id']}">{$info['sname']}</a>&nbsp;( <a href="{$ibforums->base_url}act=FileCP&amp;do=edit&amp;id={$info['id']}">{$ibforums->lang['filecp_link_edit']}</a> | <a href="{$ibforums->base_url}act=FileCP&amp;do=manage&amp;type=delete&amp;id={$info['id']}">{$ibforums->lang['filecp_link_del']}</a>)</td>
</tr>
EOF;
}

function show_linkcheck_top( ) {
global $ibforums;
return <<<EOF
<table width="100%" align="center" class='tableborder' cellspacing="1" cellpadding="0">
  <tr>
    <td class="maintitle"><img src="{$ibforums->vars['img_url']}/nav_m.gif"> {$ibforums->lang['bf_page_title']} -> {$ibforums->lang['bf_manage_title']} -> {$ibforums->lang['bf_linkcheck_title']}</td>
  </tr>
</table>
<table align="center" class='tableborder' cellspacing="1" cellpading="0" width="100%">
 <tr>
<td nowrap='nowrap' class='titlemedium' width="10%">{$ibforums->lang['edit_id']}</td>
<td nowrap='nowrap' class='titlemedium' width="90%">{$ibforums->lang['edit_name']}</td>
</tr>
EOF;
}

function show_tcheck_top( ) {
global $ibforums;
return <<<EOF
<script language='JavaScript' type="text/javascript">
<!--

var ie  = document.all  ? 1 : 0;
//var ns4 = document.layers ? 1 : 0;

function hl(cb)
{
   if (ie)
   {
	   while (cb.tagName != "TR")
	   {
		   cb = cb.parentElement;
	   }
   }
   else
   {
	   while (cb.tagName != "TR")
	   {
		   cb = cb.parentNode;
	   }
   }
   cb.className = 'hlight';
}

function dl(cb) {
   if (ie)
   {
	   while (cb.tagName != "TR")
	   {
		   cb = cb.parentElement;
	   }
   }
   else
   {
	   while (cb.tagName != "TR")
	   {
		   cb = cb.parentNode;
	   }
   }
   cb.className = 'dlight';
}

function cca(cb) {
   if (cb.checked)
   {
	   hl(cb);
   }
   else
   {
	   dl(cb);
   }
}
	   
function CheckAll(cb) {
	var fmobj = document.mutliact;
	for (var i=0;i<fmobj.elements.length;i++) {
		var e = fmobj.elements[i];
		if ((e.name != 'allbox') && (e.type=='checkbox') && (!e.disabled)) {
			e.checked = fmobj.allbox.checked;
			if (fmobj.allbox.checked)
			{
			   hl(e);
			}
			else
			{
			   dl(e);
			}
		}
	}
}

//-->
</script>

<table width="100%" align="center" class='tableborder' cellspacing="1" cellpadding="0">
  <tr>
    <td class="maintitle"><img src="{$ibforums->vars['img_url']}/nav_m.gif"> {$ibforums->lang['bf_page_title']} -> {$ibforums->lang['bf_manage_title']} -> {$ibforums->lang['bf_tcheck_title']}</td>
  </tr>
</table>
<form action='index.php?act=FileCP&amp;do=tcreate' method='post' name='mutliact'>
<table align="center" class='tableborder' cellspacing="1" cellpading="0" width="100%">
 <tr>
<td nowrap='nowrap' class='titlemedium' width="10%">{$ibforums->lang['edit_id']}</td>
<td nowrap='nowrap' class='titlemedium' width="10%">{$ibforums->lang['edit_tid']}</td>
<td nowrap='nowrap' class='titlemedium' width="70%">{$ibforums->lang['edit_name']}</td>
<td nowrap='nowrap' class='titlemedium' align='center' width="10%"><input name="allbox" type="checkbox" value="Check All" onclick="CheckAll();" /></td>
</tr>
EOF;
}

function topic_valid_row($data){
global $ibforums;
return <<<EOF

<tr>
<td width='10%' class='row4'><a href='{$ibforums->base_url}download={$data['id']}'>{$data['id']}</a></td>
<td width='10%' class='row4'><a href='{$ibforums->base_url}showtopic={$data['topic']}'>{$data['topic']}</td>
<td width='70%' class='row4'><a href='{$ibforums->base_url}download={$data['id']}'>{$data['sname']}</a></td>
<td width='10%' class='row4' align='center'><input type='hidden' name='{$data['id']}' value='1' /><input type='checkbox' name='file_{$data['id']}' value='yes' class='forminput' onclick="cca(this);" /></td>
</tr>

EOF;
}

function topic_valid_bottom(){
global $ibforums;
return <<<EOF

<tr valign='middle'>
<td colspan='4' class='titlemedium' align='center'><input type='submit' value='{$ibforums->lang['t_topic_create']}' class='forminput' /></td></tr>
</table>
</form>
EOF;
}


function link_valid_row($data){
global $ibforums;
return <<<EOF

<tr>
<td width='5%' class='row4'><a href='{$ibforums->base_url}download={$data['id']}'>{$data['id']}</a></td>
<td width='95%' class='row4'><a href='{$ibforums->base_url}download={$data['id']}'>{$data['sname']}</a></td>
</tr>

EOF;
}
}
?>