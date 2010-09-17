<?php

class skin_ucp {



function subs_row($data) {
global $ibforums;
return <<<EOF

<tr>
  <td class='row3' align='center' width='5%'>{$data['folder_icon']}</td>
  <td class='row3' align='left'>{$data['go_new_post']} <a href='{$ibforums->base_url}act=ST&amp;f={$data['forum_id']}&amp;t={$data['tid']}'>{$data['title']}</a> ( <a href='{$ibforums->base_url}act=ST&amp;f={$data['forum_id']}&amp;t={$data['tid']}' target='_blank'>{$ibforums->lang['new_window']}</a> )<br><span class='desc'>{$data['description']}{$ibforums->lang['subs_start']} {$data['start_date']}</span></td>
  <td class='row3' align='center'>{$data['posts']}</td>
  <td class='row3' align='center'>{$data['views']}</td>
  <td class='row3' align='left'>{$data['last_post_date']}<br>{$ibforums->lang['subs_by']} {$data['last_poster']}</td>
  <td class='row2' align='center'><input type='checkbox' name='id-{$data['trid']}' value='yes' class='forminput'></td>
</tr>

EOF;
}


function subs_forum_row($fid, $fname) {
global $ibforums;
return <<<EOF

                 <tr>
                   <td colspan='6' class='darkrow3' align='left'><b><a href='{$ibforums->base_url}act=SF&amp;f=$fid'>$fname</a></b></td>
                 </tr>

EOF;
}


function subs_none() {
global $ibforums;
return <<<EOF

                 <tr>
                   <td class='row3' align='center' colspan='6'>{$ibforums->lang['subs_none']}</td>
                 </tr>

EOF;
}



function settings_end($data) {
global $ibforums;
return <<<EOF
<div class='pformstrip'>{$ibforums->lang['user_settings_display']}</div>
<table width="100%" cellpadding="4">
<tr>
  <td width='70%'>{$ibforums->lang['show_status']}</td>
  <td align='left'>{$data[STATUS]}</td>
</tr>
<tr>
  <td width='70%'>{$ibforums->lang['show_img']}</td>
  <td align='left'>{$data[ICONS]}</td>
</tr>
<tr>
  <td width='70%'>{$ibforums->lang['show_ratting']}</td>
  <td align='left'>{$data[RATTING]}</td>
</tr>
<tr>
  <td width='70%'>{$ibforums->lang['settings_viewsig']}</td>
  <td align='left'>{$data['SIG']}</td>
</tr>
<tr>
  <td>{$ibforums->lang['post_wrap_size_title']}</td>
  <td align='left'>{$data['PSP']}</td>
</tr>
<tr>
  <td>{$ibforums->lang['settings_viewimg']}</td>
  <td align='left'>{$data[IMG]}</td>
</tr>
<tr>
  <td>{$ibforums->lang['settings_viewava']}</td>
  <td align='left'>{$data[AVA]}</td>
</tr>
</table>
<div class='pformstrip'>{$ibforums->lang['settings_display']}</div>
<table width="100%" cellpadding="4">
<tr>
  <td>{$ibforums->lang['show_filter']}</td>
  <td align='left'>{$data[FILTER]}</td>
</tr>
<tr>
  <td>{$ibforums->lang['css_method']}</td>
  <td align='left'>{$data[CSS]}</td>
</tr>
<tr>
  <td>{$ibforums->lang['settings_dopopup']}</td>
  <td align='left'>{$data[POP]}</td>
</tr>
<tr>
  <td>{$ibforums->lang['open_qr']}<br></td>
  <td align='left'>{$data[QR]}</td>
</tr>
<tr>
  <td>{$ibforums->lang['who_answered']}<br></td>
  <td align='left'>{$data[WP]}</td>
</tr>
<tr>
  <td>{$ibforums->lang['want_list']}<br></td>
  <td align='left'>{$data[CBFL]}</td>
</tr>
<tr>
  <td>{$ibforums->lang['want_search']}<br></td>
  <td align='left'>{$data[SEARCH]}</td>
</tr>
<tr>
  <td><font color="gray">{$ibforums->lang['want_highlight']}</font><br></td>
  <td><font color="red">(более недоступно)</font></td>
</tr>
<tr>
  <td>{$ibforums->lang['close_category']}<br></td>
  <td align='left'>{$data[CATEGORY]}</td>
</tr>
<tr>
  <td>{$ibforums->lang['show_history']}<br></td>
  <td align='left'>{$data[HISTORY]}</td>
</tr>
<tr>
  <td>{$ibforums->lang['show_new']}<br></td>
  <td align='left'>{$data[SHOW_NEW]}</td>
</tr>
<tr>
  <td>{$ibforums->lang['pp_number_posts']}</td>
  <td align='left'><select name='postpage' class='forminput'>{$data['PPS']}</select></td>
</tr>
<tr>
  <td>{$ibforums->lang['pp_number_topics']}</td>
  <td align='left'><select name='topicpage' class='forminput'>{$data['TPS']}</select></td>
</tr>
</table>
<div class='pformstrip'>{$ibforums->lang['syntax_code_highlight_settings']}</div>
<table width="100%" cellpadding="4">
<tr>
  <td>{$ibforums->lang['use_highlight']}<br></td>
  <td align='left'>{$data['SYNTAX']}</td>
</tr>
<tr>
  <td>{$ibforums->lang['syntax_lines_count']}<br></td>
  <td align='left'>{$data['SYNTAX_LINES_COUNT']}</td>
</tr>
<tr>
  <td>{$ibforums->lang['syntax_use_wrap']}<br></td>
  <td align='left'>{$data['SYNTAX_USE_WRAP']}</td>
</tr>
<tr>
  <td>{$ibforums->lang['syntax_use_line_numbering']}<br></td>
  <td align='left'>{$data['SYNTAX_USE_LINE_NUMBERING']}</td>
</tr>
<tr>
  <td>{$ibforums->lang['syntax_use_line_colouring']}<br></td>
  <td align='left'>{$data['SYNTAX_USE_LINE_COLOURING']}</td>
</tr>
</table>

<div class="pformstrip" align='center'><input type='submit' name='submit' value='{$ibforums->lang['settings_submit']}' class='forminput'></div>
</form>

EOF;
}
// barazuk: REMOVED from the FORM (after </table>, before SUBMIT button
// barazuk: &nbsp;<span class='desc'>{$ibforums->lang['need_cookie_yum_yum']}</span>



function avatar_gallery_start_row() {
global $ibforums;
return <<<EOF

<tr>

EOF;
}


function avatar_gallery_cell_row($img, $txt, $form) {
global $ibforums;
return <<<EOF

<td align="center"><img src="html/avatars{$img}" border="0" alt="txt"><br><input type="radio" class="radiobutton" name="avatar" value="$form" id="$form">&nbsp;<strong><label for="$form">$txt</label></strong></td>

EOF;
}


function avatar_gallery_blank_row() {
global $ibforums;
return <<<EOF

<td>&nbsp;</td>

EOF;
}


function avatar_gallery_end_row() {
global $ibforums;
return <<<EOF

</tr>

EOF;
}


function photo_page($cur_photo, $cur_type, $url_photo, $show_size, $key="") {
global $ibforums;
return <<<EOF

<script language = 'javascript' type='text/javascript'>
var url_input = "{$url_photo}";
</script>
<form action="{$ibforums->base_url}auth_key=$key" enctype='multipart/form-data' method="post" name="bob" onsubmit="return checkform();">
<input type='hidden' name='act' value='UserCP'>
<input type='hidden' name='CODE' value='dophoto'>
<div class="pformstrip">{$ibforums->lang['pph_title']}</div>
<p>{$ibforums->lang['pph_desc']}<br>{$ibforums->lang['pph_max']}</p>
<div class="pformstrip">{$ibforums->lang['pph_current']}</div>
<div align="center">
 <p>$cur_photo<br>$cur_type $show_size</p>
</div>
<div class="pformstrip">{$ibforums->lang['pph_change']}</div>
<table class='tablebasic' cellpadding='4'>
<tr>
 <td class='pformleft'>{$ibforums->lang['pph_url']}</td>
 <td class='pformright'><input type='text' onfocus="restore_it()" name='url_photo' value='$url_photo' size='40' class='forminput'>&nbsp;&nbsp;(<a href='javascript:restore_it()'>{$ibforums->lang['pp_restore']}</a>)</td>
</tr>
<!--IPB.UPLOAD-->
<!--IPB.SIZE-->
</table>
<div align="center" class="pformstrip">
  <input type="submit" name='submit' value="{$ibforums->lang['pph_submit']}" class='forminput'>
  &nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" onclick="remove_pressed=1;" name='remove' value="{$ibforums->lang['pph_remove']}" class='forminput'>
</div>
</form>

EOF;
}


function splash($member) {
global $ibforums;
return <<<EOF

     <div class='pformstrip'>{$ibforums->lang['stats_header']}</div>
	  <table width='100%' border="0" cellspacing="0" cellpadding="4">
		<tr> 
		  <td width="40%">{$ibforums->lang['email_address']}</td>
		  <td width="60%">{$member[MEMBER_EMAIL]}</td>
		</tr>
		<tr> 
		  <td width="40%">{$ibforums->lang['number_posts']}</td>
		  <td width="60%">{$member[MEMBER_POSTS]}</td>
		</tr>
		<tr> 
		  <td width="40%">{$ibforums->lang['registered']}</td>
		  <td width="60%">{$member[DATE_REGISTERED]}</td>
		</tr>
		<tr> 
		  <td width="40%">{$ibforums->lang['daily_average']}</td>
		  <td width="60%">{$member[DAILY_AVERAGE]}</td>
		</tr>
	  </table>
	  <div class='pformstrip'>{$ibforums->lang['messenger_summary']}</div>
	  <table width="100%" border="0" cellspacing="0" cellpadding="4">
		<tr> 
		  <td width="40%">{$ibforums->lang['total_messages']}</td>
		  <td width="60%">{$member['total_messages']} {$member['full_percent']}</td>
		</tr>
		<tr> 
		  <td width="40%">{$ibforums->lang['messages_left']}</td>
		  <td width="60%">{$member['space_free']} {$member['full_messenger']}</td>
		</tr>
	  </table>
	  <div class='pformstrip'>{$ibforums->lang['note_pad']}</div>
	  <div align='center'>
	  <p>
		<form name='notepad' action="{$ibforums->base_url}" method="post">
		<input type='hidden' name='act' value='UserCP'>
		<input type='hidden' name='s' value='{$ibforums->session_id}'>
		<input type='hidden' name='CODE' value='20'>
        <textarea cols='65' rows='{$member['SIZE']}' name='notes' class='forminput'>{$member['NOTES']}</textarea>
        <br>
	    {$ibforums->lang['ta_size']}&nbsp;<select name='ta_size' class='forminput'>{$member['SIZE_CHOICE']}</select>
        <input type='submit' value='{$ibforums->lang['submit_notepad']}' class='forminput'>
        </form>
      </p>
     </div>
     

EOF;
}

function personal_panel_username($name="")
{
  global $ibforums;
  $ret = "<tr>
    <td class='pformleft' width='40%'><b>{$ibforums->lang['name']}</b></td>
    <td class='pformright' width='60%'>$name</td> 
  </tr>\n";

  if($ibforums->vars['allow_user_rename'])
  {
    $ret .= "<tr>
    <td class='pformleft' width='40%'>{$ibforums->lang['m_title']}</td>
    <td class='pformright' width='60%'><input type='text' size='40' maxlength='20' name='MemberName' value='' class='forminput'></td> 
  </tr>\n";
  }
  return $ret;
}

function personal_panel($Profile) {
global $ibforums;

return <<<EOF

<form action="{$ibforums->base_url}auth_key={$Profile['key']}" method="post" name='theForm' onsubmit='return ValidateProfile()'>
<input type='hidden' name='act' value='UserCP'>
<input type='hidden' name='CODE' value='21'>

<!--{REQUIRED.FIELDS}-->
<div class='pformstrip'>{$ibforums->lang['profile_title']}</div>
<table width='100%'>
<!--{MEMBERTITLE}-->
<!--{BIRTHDAY}-->
<tr>
  <td class='pformleft'>{$ibforums->lang['website']}</td>
  <td class='pformright'><input type='text' size='40' maxlength='1200' name='WebSite' value='{$Profile['website']}' class='forminput'></td>
</tr>  
<tr>
  <td class='pformleft'>{$ibforums->lang['icq']}</td>
  <td class='pformright'><input type='text' size='40' maxlength='20' name='ICQNumber' value='{$Profile['icq_number']}' class='forminput'></td>
</tr>
<tr>
  <td class='pformleft'>{$ibforums->lang['aol']}</td>
  <td class='pformright'><input type='text' size='40' maxlength='30' name='AOLName' value='{$Profile['aim_name']}' class='forminput'></td>
</tr>
<tr>
  <td class='pformleft'>{$ibforums->lang['yahoo']}</td>
  <td class='pformright'><input type='text' size='40' maxlength='30' name='YahooName' value='{$Profile['yahoo']}' class='forminput'></td>
</tr>
<tr>
  <td class='pformleft'>{$ibforums->lang['msn']}</td>
  <td class='pformright'><input type='text' size='40' maxlength='30' name='MSNName' value='{$Profile['msnname']}' class='forminput'></td>
</tr>
<tr>
  <td class='pformleft'>{$ibforums->lang['integ_msg']}</td>
  <td class='pformright'><input type='text' size='40' maxlength='30' name='integ_msg' value='{$Profile['integ_msg']}' class='forminput'></td>
</tr>
<tr>
  <td class='pformleft'>{$ibforums->lang['location']}<br>(<a href='javascript:CheckLength("location");'>{$ibforums->lang['check_length']}</a>)</td>
  <td class='pformright'><input type='text' size='40' name='Location' value='{$Profile['location']}' class='forminput'></td>
</tr>
<tr>                                                                                        
  <td class='pformleft' valign='top'>{$ibforums->lang['interests']}<br>(<a href='javascript:CheckLength("interest");'>{$ibforums->lang['check_length']}</a>)</td>
  <td class='pformright'><textarea cols='60' rows='10' wrap='soft' name='Interests' class='forminput'>{$Profile['interests']}</textarea></td>
</tr>
<!--{OPTIONAL.FIELDS}-->
<tr>
  <td class='pformstrip' align='center' colspan='2'><input type="submit" value="{$ibforums->lang['submit_profile']}" class='forminput'></td>
</tr>
</table>
</form>

EOF;
}


function required_end() {
global $ibforums;
return <<<EOF

</table>

EOF;
}


function required_title() {
global $ibforums;
return <<<EOF

 <div class='pformstrip'>{$ibforums->lang['cf_required']}</div>
 <table width='100%'>

EOF;
}


function subs_end($text="", $days="") {
global $ibforums;
return <<<EOF

<tr>
 <td align='center' class='titlemedium' valign='middle' colspan='6'><input type='submit' class='forminput' value='{$ibforums->lang['subs_delete']}'>&nbsp;&nbsp;{$ibforums->lang['with_selected']}</td>
</tr>
</table>
</div>
</form>
<div align="right" style="padding:5px"><i>$text</i></div>
<div align="right" style="padding:5px">
 	<form action='{$ibforums->base_url}act=UserCP&amp;CODE=26' method='post'>
 	{$ibforums->lang['show_topics_from']} <select class='forminput' name='datecut'>$days</select>
 	<input type='submit' class='forminput' value='{$ibforums->lang['jmp_go']}'>
 	</form>
</div>

EOF;
}


function avatar_main($data, $formextra="", $hidden_field="", $key="") {
global $ibforums;
return <<<EOF

<script langauge='javascript' type='text/javascript'>
var url_input2 = "{$data['current_url_avatar']}";
</script>
<!--IBF.LIMITS_AVATAR-->
<div class="pformstrip">{$ibforums->lang['av_current']}</div>
<div class="tablepad" align="center">{$data['current_avatar_image']}<br>{$data['current_avatar_type']} {$data['current_avatar_dims']}</div>
<div class="pformstrip">{$ibforums->lang['avatar_pre_title']}</div>
<form action='{$ibforums->base_url}act=UserCP&amp;CODE=getgallery' method='post'>
<table class='tablebasic' cellpadding='4'>
<tr>
 <td class='pformleft'>{$ibforums->lang['av_go_gal']}</td>
 <td class='pformright'>{$data['avatar_galleries']}&nbsp;&nbsp;<input type="submit" value="{$ibforums->lang['av_go_go']}" name="submit" class="forminput"></td>
</tr>
</table>
</form>
<form action='{$ibforums->base_url}auth_key=$key' method='post' $formextra name='creator' onsubmit="return checkform();">
<input type='hidden' name='act' value='UserCP'>
<input type='hidden' name='CODE' value='25'>
$hidden_field
<!--IBF.EXTERNAL_TITLE-->
<!--IBF.URL_AVATAR-->
<!--IBF.UPLOAD_AVATAR-->
<!--IPB.SIZE-->
<div align="center" class="pformstrip">
  <input type="submit" name="submit" value="{$ibforums->lang['av_update']}" class='forminput'>
  &nbsp;&nbsp;&nbsp;<input type="submit" name="remove" onclick="remove_pressed=1;" value="{$ibforums->lang['av_remove']}" class='forminput'>
</div>
</form>

EOF;
}


function photo_page_mansize() {
global $ibforums;
return <<<EOF

<tr>
 <td class='pformleft'>&nbsp;</td>
 <td class='pformright'><strong>{$ibforums->lang['width']}</strong>&nbsp;<input type='text' size='3' name='man_width'>&nbsp;&nbsp;<strong>{$ibforums->lang['height']}</strong>&nbsp;<input type='text' size='3' name='man_height'></td>
</tr>

EOF;
}


function field_entry($title, $desc="", $content) {
global $ibforums;
return <<<EOF

  <tr>
  <td class='pformleft' valign='top' width='40%'><b>$title</b><br>$desc</td>
  <td class='pformright' width='60%'>$content</td>
  </tr>

EOF;
}


function forum_subs_row($data) {
global $ibforums;
return <<<EOF

   <tr>
	 <td class='row1' align='center' width='5%'>{$data['folder_icon']}</td>
	 <td class='row1' align='left'>
		 <b><a href='{$ibforums->base_url}act=SF&amp;f={$data['id']}'>{$data['name']}</a></b>
		 <br><span class='desc'>{$data['description']}</span>
		 <br><br><b>[ <a href='{$ibforums->base_url}act=UserCP&amp;CODE=51&amp;f={$data['id']}'>{$ibforums->lang['ft_unsub']}</a> ]</b>
	 </td>
	 <td class='row1' align='center'>{$data['topics']}</td>
	 <td class='row1' align='center'>{$data['posts']}</td>
	 <td class='row1' align='left'>{$data['last_post']}<br>{$ibforums->lang['in']} {$data['last_topic']}<br>{$ibforums->lang['by']} {$data['last_poster']}</td>
   </tr>

EOF;
}


function member_title($title) {
global $ibforums;
return <<<EOF

   <tr>
   <td class='pformleft'><b>{$ibforums->lang['member_title']}</b></td>
   <td class='pformright'><input type='text' size='40' maxlength='120' name='member_title' value='$title' class='forminput'></td>
   </tr>

EOF;
}


function birthday($day,$month,$year) {
global $ibforums;
return <<<EOF

  <tr>
  <td class='pformleft'><b>{$ibforums->lang['birthday']}</b></td>
  <td class='pformright'>
  <select name='day' class='forminput'>{$day}</select> 
  <select name='month' class='forminput'>{$month}</select> 
  <select name='year' class='forminput'>{$year}</select>
  </td>
  </tr>

EOF;
}


function field_textinput($name, $value="") {
global $ibforums;
return <<<EOF

            <input type='text' size='50' name='$name' value='$value' class='forminput'>

EOF;
}


function field_dropdown($name, $options) {
global $ibforums;
return <<<EOF

            <select name='$name' class='forminput'>$options</select>

EOF;
}


function field_textarea($name, $value) {
global $ibforums;
return <<<EOF

            <textarea cols='60' rows='5' wrap='soft' name='$name' class='forminput'>$value</textarea>

EOF;
}

function delete_account() {
global $ibforums;
return <<<EOF

&middot; <a href='{$ibforums->base_url}act=UserCP&amp;CODE=31'>{$ibforums->lang['m_delete_account']}</a>

EOF;
}

function delete_cancel($days = 0) {
global $ibforums;
return <<<EOF

&middot; {$ibforums->lang['m_delete_account_days']}<br>&nbsp; {$ibforums->lang['subs_left']}: <b>{$days}</b><br>
&nbsp; <a href='{$ibforums->base_url}act=UserCP&amp;CODE=33'>{$ibforums->lang['m_delete_cancel_account']}</a>

EOF;
}


function Menu_bar($base_url, $delete = "") {
global $ibforums;
return <<<EOF

<!--
TABLE TO FIX IE/6 ISSUE
The one where 23% margin + 100% table = 123% in IE6 o_O
-->
<script language='javascript' type='text/javascript'>
var pp_confirm          = "{$ibforums->lang['pp_confirm']}";
var max_location_length = "{$ibforums->vars['max_location_length']}";
var max_interest_length	= "{$ibforums->vars['max_interest_length']}"
var js_location         = "{$ibforums->lang['js_location']}";
var js_max	        = "{$ibforums->lang['js_max']}";
var js_characters       = "{$ibforums->lang['js_characters']}";
var js_used	        = "{$ibforums->lang['js_used']}";
var js_so_far	        = "{$ibforums->lang['js_so_far']}";
var js_interests        = "{$ibforums->lang['js_interests']}";
var av_confirm		= "{$ibforums->lang['av_confirm']}";
var MessageMax  	= "{$ibforums->lang['the_max_length']}";
var Override    	= "{$ibforums->lang['override']}";
var js_max_length	= "{$ibforums->lang['js_max_length']}";
var js_current		= "{$ibforums->lang['js_current']}";
var js_base_url		= "{$ibforums->js_base_url}";
</script>
<script type='text/javascript' src='html/usercp.js'></script>
<table cellspacing='0' cellpadding='0' width='100%'>
<tr>
 <td id='ucpmenu' valign='top'>
    <div class='maintitle'><span style='color:black'>{$ibforums->lang['tt_menu']}</span></div>
	 <div class='pformstrip'>{$ibforums->lang['m_messenger']}</div>
	 <p>
	 &middot; <a href='{$base_url}act=Msg&amp;CODE=04'><strong>{$ibforums->lang['mess_new']}</strong></a><br>
	 &middot; <a href='{$base_url}act=Msg&amp;CODE=01'><strong>{$ibforums->lang['mess_inbox']}</strong></a><br>
	 <!--IBF.FOLDER_LINKS-->
	 &middot; <a href='{$base_url}act=Msg&amp;CODE=01&VID=sent'><strong>{$ibforums->lang['mess_sent']}</strong></a><br>
	 &middot; <a href='{$base_url}act=Msg&amp;CODE=delete'>{$ibforums->lang['mi_prune_msg']}</a><br>
	  &middot; <a href='{$base_url}act=Msg&amp;CODE=07'>{$ibforums->lang['mess_folders']}</a><br>
	 &middot; <a href='{$base_url}act=Msg&amp;CODE=02'>{$ibforums->lang['mess_contact']}</a><br>
	 &middot; <a href='{$base_url}act=Msg&amp;CODE=14'>{$ibforums->lang['mess_archive']}</a><br>
	 &middot; <a href='{$base_url}act=Msg&amp;CODE=20'>{$ibforums->lang['mess_saved']}</a><br>
	 &middot; <a href='{$base_url}act=Msg&amp;CODE=30'>{$ibforums->lang['mess_tracker']}</a><br>
	 </p>
	 <div class='pformstrip'>{$ibforums->lang['m_tracker']}</div>
	 <p>
	 &middot; <a href='{$base_url}act=UserCP&amp;CODE=26'>{$ibforums->lang['m_view_subs']}</a><br>
	 &middot; <a href='{$base_url}act=UserCP&amp;CODE=50'>{$ibforums->lang['m_view_forum']}</a><br>
	 </p>
	 <div class='pformstrip'>{$ibforums->lang['m_personal']}</div>
	 <p>
	 &middot; <a href='{$base_url}act=UserCP&amp;CODE=01'>{$ibforums->lang['m_contact_info']}</a><br>
	 &middot; <a href='{$base_url}act=UserCP&amp;CODE=22'>{$ibforums->lang['m_sig_info']}</a><br>
	 &middot; <a href='{$base_url}act=UserCP&amp;CODE=24'>{$ibforums->lang['m_avatar_info']}</a><br>
	 &middot; <a href='{$base_url}act=UserCP&amp;CODE=photo'>{$ibforums->lang['m_change_photo']}</a><br>
	 </p>
	 <div class='pformstrip'>{$ibforums->lang['m_options']}</div>
	 <p>
	 &middot; <a href='{$base_url}act=UserCP&amp;CODE=02'>{$ibforums->lang['m_email_opt']}</a><br>
	 &middot; <a href='{$base_url}act=UserCP&amp;CODE=04'>{$ibforums->lang['m_board_opt']}</a><br>
	 &middot; <a href='{$base_url}act=UserCP&amp;CODE=06'>{$ibforums->lang['m_skin_lang']}</a><br>
	 &middot; <a href='{$base_url}act=UserCP&amp;CODE=08'>{$ibforums->lang['m_email_change']}</a><br>
         &middot; <a href='{$base_url}act=UserCP&amp;CODE=28'>{$ibforums->lang['m_passy_opt']}</a><br>
         &middot; <a href='{$base_url}act=UserCP&amp;CODE=15'>{$ibforums->lang['m_board_lay']}</a> <br>
         {$delete}
	 </p>
 </td>
 <td style='padding:2px'><!-- --></td>
 <td id="ucpcontent" valign="top">
  <div class='maintitle'><span style='color:black'>{$ibforums->lang['welcome']}</span></div>

EOF;
}


function forum_subs_end() {
global $ibforums;
return <<<EOF

<tr>
 <td align='right' class='titlemedium' valign='middle' colspan='5'><a href='{$ibforums->base_url}act=UserCP&amp;CODE=51&amp;f=all'>{$ibforums->lang['ft_unsub_all']}</a></td>
</tr>
</table>
</div>

EOF;
}


function signature($sig, $t_sig, $key, $select_syntax = "") {
global $ibforums;
return <<<EOF

<form name='REPLIER' action='{$ibforums->base_url}' method='post'>
<input type='hidden' name='act' value='UserCP'>
<input type='hidden' name='CODE' value='23'>
<input type='hidden' name='key' value='$key'>
<div class='pformstrip'>{$ibforums->lang['cp_current_sig']}</div>
<div class='signature' style="width:75%;margin-right:auto;margin-left:auto;padding:6px">$sig</div>
<div class='pformstrip'>{$ibforums->lang['cp_edit_sig']}</div>
<table width="100%">
<tr> 
  <td class="pformright" valign="top" align="center">
        <script type='text/javascript' src='html/ibfcode_{$ibforums->vars['ibf_script_version']}.js'></script>
	   <input type='button' accesskey='b' value=' B ' onclick='simpletag("B")' class='codebuttons' name='B' title='Bold' style="font-weight:bold">
	   <input type='button' accesskey='i' value=' I ' onclick='simpletag("I")' class='codebuttons' name='I' title='Italic' style="font-style:italic">
	   <input type='button' accesskey='u' value=' U ' onclick='simpletag("U")' class='codebuttons' name='U' title='Underline' style="text-decoration:underline">
	   <input type='button' accesskey='s' value=' S ' onclick='simpletag("S")' class='codebuttons' name='S' title='Strike' style="text-decoration:line-through">
	<select name='ffont' class='codebuttons' onchange="alterfont(this.options[this.selectedIndex].value, 'FONT')">
	<option value='0'>{$ibforums->lang['ct_font']}</option>
	<option value='Arial' style='font-family:Arial'>{$ibforums->lang['ct_arial']}</option>
	<option value='Times' style='font-family:Times'>{$ibforums->lang['ct_times']}</option>
	<option value='Courier' style='font-family:Courier'>{$ibforums->lang['ct_courier']}</option>
	<option value='Impact' style='font-family:Impact'>{$ibforums->lang['ct_impact']}</option>
	<option value='Geneva' style='font-family:Geneva'>{$ibforums->lang['ct_geneva']}</option>
	<option value='Optima' style='font-family:Optima'>Optima</option>
	</select><select name='fsize' class='codebuttons' onchange="alterfont(this.options[this.selectedIndex].value, 'SIZE')">
	<option value='0'>{$ibforums->lang['ct_size']}</option>
	<option value='1'>{$ibforums->lang['ct_sml']}</option>
	<option value='7'>{$ibforums->lang['ct_lrg']}</option>
	<option value='14'>{$ibforums->lang['ct_lest']}</option>
	</select>
	<select name='fcolor' class='codebuttons' onchange="alterfont(this.options[this.selectedIndex].value, 'COLOR')">
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
	<br>
	<input type='button' accesskey='h' value=' http:// ' onclick='tag_url()' class='codebuttons' name='url'>
	<input type='button' accesskey='g' value=' IMG ' onclick='tag_image()' class='codebuttons' name='img'>
	<input type='button' accesskey='e' value='  @  ' onclick='tag_email()' class='codebuttons' name='email'>
	<input type='button' accesskey='q' value=' QUOTE ' onclick='simpletag("QUOTE")' class='codebuttons' name='QUOTE'>
	{$select_syntax}
	<input type='button' accesskey='l' value=' LIST ' onclick='tag_list()'class='codebuttons' name="LIST">
	<br><a href='javascript:closeall();'>{$ibforums->lang['js_close_all_tags']}</a>
  </td>
</tr>
</table>
<div align='center'>
  <p>
  <textarea cols='60' rows='12' name='Post' tabindex='3' class='textinput'
        onKeyPress='if (event.keyCode==10 || ((event.metaKey || event.ctrlKey) && event.keyCode==13))
	this.form.go.click()'>$t_sig</textarea><br>(<a href='javascript:CheckLength2()'>
{$ibforums->lang['check_length']}</a>)</p></div>
<div class='pformstrip' align="center"><input type='submit' name=go value='{$ibforums->lang['cp_submit_sig']}' class='forminput'></div>
</form>

EOF;
}


function settings_skin($skin) {
global $ibforums;
return <<<EOF

<div class="pformstrip">{$ibforums->lang['settings_skin']}</div>                
<table width="100%"
<tr>
  <td width="50%">{$ibforums->lang['settings_skin_txt']}</td>
  <td align='left'>$skin &nbsp;&nbsp; <input type='button' value='{$ibforums->lang['cp_skin_preview']}' class='forminput' onclick='do_preview()'></td>
</tr>
</table>

EOF;
}


function photo_page_autosize() {
global $ibforums;
return <<<EOF

<tr>
 <td class='pformleft'>&nbsp;</td>
 <td class='pformright'><strong>{$ibforums->lang['pph_autosize']}</strong><br>({$ibforums->lang['pph_as_desc']})</td>
</tr>

EOF;
}


function email_change_gd($regid="") {
global $ibforums;
return <<<EOF

<div class="pformstrip">{$ibforums->lang['ras_title']}</div>
<table width="100%" style="padding:6px">
<tr>
  <td width='40%'>{$ibforums->lang['ras_numbers']}</td>
  <td>
	 <input type='hidden' name='regid' value='$regid'>
	 <img src='{$ibforums->base_url}act=UserCP&amp;CODE=show_image&amp;rc={$regid}' border='0' alt='Code Bit'>
  </td>
</tr>
<tr>
  <td width='40%'>{$ibforums->lang['ras_text']}</td>
  <td><input type='text' size='32' maxlength='32' name='reg_code' class='forminput'></td>
</tr>
</table>

EOF;
}


function email_change_img($regid="") {
global $ibforums;
return <<<EOF

<div class="pformstrip">{$ibforums->lang['ras_title']}</div>
<table width="100%" style="padding:6px">
<tr>
  <td width='40%'>{$ibforums->lang['ras_numbers']}</td>
  <td>
	 <input type='hidden' name='regid' value='$regid'>
	 <img src='{$ibforums->base_url}act=UserCP&amp;CODE=show_image&amp;rc={$regid}&amp;p=1' border='0' alt='Code Bit'>
	 &nbsp;<img src='{$ibforums->base_url}act=UserCP&amp;CODE=show_image&amp;rc={$regid}&amp;p=2' border='0' alt='Code Bit'>
	 &nbsp;<img src='{$ibforums->base_url}act=UserCP&amp;CODE=show_image&amp;rc={$regid}&amp;p=3' border='0' alt='Code Bit'>
	 &nbsp;<img src='{$ibforums->base_url}act=UserCP&amp;CODE=show_image&amp;rc={$regid}&amp;p=4' border='0' alt='Code Bit'>
	 &nbsp;<img src='{$ibforums->base_url}act=UserCP&amp;CODE=show_image&amp;rc={$regid}&amp;p=5' border='0' alt='Code Bit'>
	 &nbsp;<img src='{$ibforums->base_url}act=UserCP&amp;CODE=show_image&amp;rc={$regid}&amp;p=6' border='0' alt='Code Bit'>
  </td>
</tr>
<tr>
  <td width='40%'>{$ibforums->lang['ras_text']}</td>
  <td><input type='text' size='32' maxlength='32' name='reg_code' class='forminput'></td>
</tr>
</table>

EOF;
}





function boardlay_start() {
global $ibforums;
return <<<EOF

	<div class='pformstrip'>{$ibforums->lang['boardlay_title']}</div><br>
	{$ibforums->lang['layout_descr']}<br>
	<div align="center" class="tableborder">
	<form action="{$ibforums->base_url}" name='mutliact' method="post">
	<input type='hidden' name='act' value='UserCP'>
	<input type='hidden' name='CODE' value='16'>
		<table width="100%" cellspacing="1" cellpadding="4">
		<tr>
			<td class='titlemedium' width='10%' align='center'>{$ibforums->lang['boardlay_sh']}</td>
			<td class='titlemedium' width='90%' align='center'>{$ibforums->lang['boardlay_catfor']}</td>
		</tr>

EOF;
}





function checkbox($data) {
global $ibforums;
return <<<EOF

<input type='checkbox' name='{$data['qid']}{$data['id']}' value='1' {$data['sh']}>

EOF;
}

function boardlay_between($data,$checkbox = "") {
global $ibforums;
return <<<EOF

<tr>
 <td class='{$data['css']}'>{$checkbox}</td>
 <td class='{$data['css']}'>{$data['sub']}{$data['name']}</td>
</tr>

EOF;
}


function menu_bar_msg_folder_link($id, $real) {
global $ibforums;
return <<<EOF

&nbsp;&nbsp;&nbsp;<img src='http://forum.sources.ru/style_images/1/open.gif' border='0'  alt=''> <a href='{$ibforums->base_url}act=Msg&amp;CODE=01&amp;VID=$id'>$real</a><br>

EOF;
}


function photo_page_upload($max_filesize) {
global $ibforums;
return <<<EOF

<tr>
 <td class='pformleft'>{$ibforums->lang['pph_upload']}</td>
 <td class='pformright'><input type='hidden' name='MAX_FILE_SIZE' value='$max_filesize'><input type='file' name='upload_photo' value='' size='40' class='forminput' onclick="clear_it()"></td>
</tr>

EOF;
}


function avatar_external_title() {
global $ibforums;
return <<<EOF

<div class="pformstrip">{$ibforums->lang['avatar_url_title']}</div>

EOF;
}


function skin_lang_end() {
global $ibforums;
return <<<EOF

<div class="pformstrip" align="center"><input type='submit' name='submit' value='{$ibforums->lang['settings_submit']}' class='forminput'></div>
</form>

EOF;
}


function avatar_gallery_end_table() {
global $ibforums;
return <<<EOF

</table>
<div align="center" class="pformstrip">
  <input type="submit" name="submit" value="{$ibforums->lang['av_gall_submit']}" class='forminput'>
  &nbsp;&nbsp;&nbsp;<input type="button" name="remove" onclick="self.location='{$ibforums->base_url}act=UserCP&amp;CODE=24';" value="{$ibforums->lang['av_gall_cancel']}" class='forminput'>
</div>
</form>

EOF;
}


function avatar_limits() {
global $ibforums;
return <<<EOF

<div class="pformstrip">{$ibforums->lang['av_settings']}</div>
<p>{$ibforums->lang['av_text_url']} {$ibforums->lang['av_text_upload']}<br>{$ibforums->lang['av_allowed_files']}</p>

EOF;
}


function avatar_upload_field() {
global $ibforums;
return <<<EOF

<table class='tablebasic' cellpadding='4'>
<tr>
  <td class='pformleft'>{$ibforums->lang['av_upload']}</td>
  <td class='pformright'><input type='file' size='30' name='upload_avatar' class='forminput' onfocus='select_upload()' onclick='select_upload()'></td>
</tr>
</table>

EOF;
}


function avatar_url_field($avatar) {
global $ibforums;
return <<<EOF

<table class='tablebasic' cellpadding='4'>
<tr>
  <td class='pformleft'>{$ibforums->lang['av_url']}</td>
  <td class='pformright'><input type='text' size='40' maxlength='80' name='url_avatar' value='$avatar' class='forminput' onfocus='select_url()'>&nbsp;&nbsp;(<a href='javascript:restore_it()'>{$ibforums->lang['pp_restore']}</a>)</td>
</tr>
</table>

EOF;
}


function settings_header($Profile, $time_select, $time, $dst_check, $key="") {
global $ibforums;
return <<<EOF

<form action="{$ibforums->base_url}" method="post">
<input type='hidden' name='auth_key' value='$key'>
<input type='hidden' name='act' value='UserCP'>
<input type='hidden' name='CODE' value='05'>
<div class="pformstrip">{$ibforums->lang['settings_time']}</div>
<p>
{$ibforums->lang['settings_time_txt']}&nbsp;$time
<br>
$time_select
<br><br>
<input type='checkbox' class='forminput' name='DST' value='1' $dst_check> &nbsp;{$ibforums->lang['dst_box']}
</p>

EOF;
}


function dead_section() {
global $ibforums;
return <<<EOF

<div class="pformstrip">{$ibforums->lang['dead_section_title']}</div>
<p>
{$ibforums->lang['dead_section_text']}
</p>

EOF;
}


function avatar_gallery_start_table($title="", $av_gals="", $current_folder, $key="") {
global $ibforums;
return <<<EOF

<div class="pformstrip">{$ibforums->lang['av_gallery_title']} $title</div>
<form action='{$ibforums->base_url}act=UserCP&amp;CODE=getgallery' method='post'>
<div id="padandcenter"><strong>{$ibforums->lang['av_gall_jump']}</strong>&nbsp;$av_gals&nbsp;&nbsp;<input type="submit" value="{$ibforums->lang['av_go_go']}" name="submit" class="forminput"></div>
</form>
<form action='{$ibforums->base_url}auth_key=$key' method='post'>
<input type='hidden' name='act' value='UserCP'>
<input type='hidden' name='CODE' value='setinternalavatar'>
<input type='hidden' name='current_folder' value='$current_folder'>
<table class='tablebasic' cellpadding='4'>

EOF;
}


function forum_subs_header() {
global $ibforums;
return <<<EOF

<div class='pformstrip'>{$ibforums->lang['forum_subs_header']}</div>
<br>
<div class='tableborder'>
  <table cellpadding='4' cellspacing='1' align='center' width='100%'>
  <tr>
    <td class='titlemedium' align='left' width='5%'>&nbsp;</td>
    <th class='titlemedium' align='left' width='50%'>{$ibforums->lang['ft_forum']}</th>
    <th class='titlemedium' align='center' width='5%'>{$ibforums->lang['ft_topics']}</th>
    <th class='titlemedium' align='center' width='5%'>{$ibforums->lang['ft_posts']}</th>
    <th class='titlemedium' align='center' width='35%'>{$ibforums->lang['ft_last_post']}</th>
  </tr>

EOF;
}


function email($Profile) {
global $ibforums;
return <<<EOF

<form action="{$ibforums->base_url}auth_key={$Profile['key']}" method="post">
<input type='hidden' name='act' value='UserCP'>
<input type='hidden' name='CODE' value='03'>
<div class="pformstrip">{$ibforums->lang['privacy_settings']}</div>
<br>
<table width="100%">
<tr>
  <td align='right' valign='top'><input type='checkbox' name='hide_email' value='1' {$Profile['hide_email']}></td>
  <td align='left' width='100%'>{$ibforums->lang['hide_email']}</td>
</tr>  
<tr>
  <td align='right' valign='top'><input type='checkbox' name='admin_send' value='1' {$Profile['allow_admin_mails']}></td>
  <td align='left'  width='100%'>{$ibforums->lang['admin_send']}</td>
</tr>
</table>
<br>
<div class="pformstrip">{$ibforums->lang['board_prefs']}</div>
<br>
<table width="100%">
<tr>
  <td align='right' valign='top'><input type='checkbox' name='send_full_msg' value='1' {$Profile['email_full']}></td>
  <td align='left'  width='100%'>{$ibforums->lang['send_full_msg']}</td>
</tr>
<tr>
  <td align='right' valign='top'><input type='checkbox' name='pm_reminder' value='1' {$Profile['email_pm']}></td>
  <td align='left'  width='100%'>{$ibforums->lang['pm_reminder']}</td>
</tr>
<tr>
  <td align='right' valign='top'><input type='checkbox' name='auto_track' value='1' {$Profile['auto_track']}></td>
  <td align='left'  width='100%'>{$ibforums->lang['auto_track']}</td>
</tr>
</table>
<br>
<div class="pformstrip" align="center"><input type="submit" value="{$ibforums->lang['submit_email']}" class='forminput'></div>
</form>

EOF;
}


function skin_lang_header($lang_select, $smile_select, $key="") {
global $ibforums;
return <<<EOF

<form action="{$ibforums->base_url}auth_key=$key" method="post" name='prefs'>
<input type='hidden' name='act' value='UserCP'>
<input type='hidden' name='CODE' value='07'>
<div class="pformstrip">{$ibforums->lang['settings_title']}</div>                
<table width="100%">
<tr>
  <td width="50%">{$ibforums->lang['settings_lang_txt']}</td>
  <td align='left'>$lang_select</td>
</tr>
</table>
<div class="pformstrip">{$ibforums->lang['settings_smile']}</div>                
<table width="100%">
<tr>
  <td width="50%">{$ibforums->lang['settings_smile_txt']}</td>
  <td align='left'>$smile_select
  <input type='button' value='{$ibforums->lang['cp_smile_preview']}' class='forminput' onclick="do_smile_preview()">
  </td>
</tr>
</table>

EOF;
}


function forum_subs_none() {
global $ibforums;
return <<<EOF

	<tr>
	  <td class='row1' align='center' colspan='5'>{$ibforums->lang['forum_subs_none']}</td>
	</tr>

EOF;
}


function subs_header() {
global $ibforums;
return <<<EOF

<div class='pformstrip'>{$ibforums->lang['subs_header']}</div>
<form action="{$ibforums->base_url}" name='mutliact' method="post">
<input type='hidden' name='act' value='UserCP'>
<input type='hidden' name='CODE' value='27'>
<br>
<div align="center" class="tableborder">
<table width="100%" cellspacing="1" cellpadding="4">
<tr>
  <td class='titlemedium' align='left' width='5%'>&nbsp;</td>
  <th class='titlemedium' align='left' width='55%'>{$ibforums->lang['subs_topic']}</th>
  <th class='titlemedium' align='center' width='5%'>{$ibforums->lang['subs_replies']}</th>
  <th class='titlemedium' align='center' width='5%'>{$ibforums->lang['subs_view']}</th>
  <th class='titlemedium' align='left' width='25%'>{$ibforums->lang['subs_last_post']}</th>
  <td align='center' width='5%' class='titlemedium'><input name="allbox" type="checkbox" value="Check All" onClick="CheckAll();"></td>
</tr>

EOF;
}


function forum_jump($data, $menu_extra="") {
global $ibforums;
return <<<EOF

<div align='right'>{$data}</div>

EOF;
}


function avatar_mansize() {
global $ibforums;
return <<<EOF

<table class='tablebasic' cellpadding='4'>
<tr>
 <td class='pformleft'>&nbsp;</td>
 <td class='pformright'><strong>{$ibforums->lang['width']}</strong>&nbsp;<input type='text' size='3' name='man_width'>&nbsp;&nbsp;<strong>{$ibforums->lang['height']}</strong>&nbsp;<input type='text' size='3' name='man_height'></td>
</tr>
</table>

EOF;
}


function delete_self() {
global $ibforums;
return <<<EOF

<div class='pformstrip'>{$ibforums->lang['m_delete_finished']}</div>
{$ibforums->lang['m_deleted']}

EOF;
}


function delete_self_check( $check = "") {
global $ibforums;
return <<<EOF

<div class='pformstrip'>{$ibforums->lang['m_delete_warning']}</div>{$ibforums->lang['m_delete_self']}
<br> <br> <br>
<table border='1' border='black' cellpadding='1' cellspacing='1' align='center'  width='10%'>
<tr>
<td class='pformstrip'><u><a href="{$ibforums->vars['board_url']}/index.php?act=UserCP&CODE=32&check={$check}">{$ibforums->lang['m_delete_submit']}</a></u></td>
</tr>
</table>


EOF;
}


function avatar_autosize() {
global $ibforums;
return <<<EOF

<table class='tablebasic' cellpadding='4'>
<tr>
 <td class='pformleft'>&nbsp;</td>
 <td class='pformright'><strong>{$ibforums->lang['av_autosize']}</strong><br>({$ibforums->lang['av_as_desc']})</td>
</tr>
</table>

EOF;
}


function email_change($txt="", $msg="") {
global $ibforums;
return <<<EOF

<script language='Javascript' type="text/javascript">
  do_msg('{$msg}');  
</script>
<form action='{$ibforums->base_url}' method='post' name='form1'>
<input type='hidden' name='act' value='UserCP'>
<input type='hidden' name='CODE' value='09'>
<div class="pformstrip">{$ibforums->lang['change_email_title']}</div>
<p>$txt</p>
<table width="100%" style="padding:6px">
<tr>
  <td width='40%'><strong>{$ibforums->lang['ce_new_email']}</strong></td>
  <td align='left'><input type='text' name='in_email_1' value='' class='forminput'></td>
</tr>
<tr>
  <td><strong>{$ibforums->lang['ce_new_email2']}</strong></td>
  <td align='left'><input type='text' name='in_email_2' value='' class='forminput'></td>
</tr>
<tr>
  <td><strong>{$ibforums->lang['ec_passy']}</strong></td>
  <td align='left'><input type='password' name='password' value='' class='forminput'></td>
</tr>
</table>
<!--ANTIBOT-->
<div align="center" class="pformstrip"><input type="submit" name='change_email' value="{$ibforums->lang['account_email_submit']}" class='forminput'></div>
</form>

EOF;
}


function CP_end() {
global $ibforums;
return <<<EOF

 </td>
</tr>
</table>
<br clear="all">

EOF;
}


function pass_change() {
global $ibforums;
return <<<EOF

<form action="{$ibforums->base_url}" method="post" name='form1'>
<input type='hidden' name='act' value='UserCP'>
<input type='hidden' name='CODE' value='29'>
<div class="pformstrip">{$ibforums->lang['account_pass_title']}</div>
<p>{$ibforums->lang['pass_change_text']}</p>
<table width="100%" style="padding:6px">
<tr>
  <td><b>{$ibforums->lang['account_pass_old']}</b></td>
  <td><input type='password' name='current_pass' value='' class='forminput'></td>
</tr>
<tr>
  <td><b>{$ibforums->lang['account_pass_new']}</b></td>
  <td><input type='password' name='new_pass_1' value='' class='forminput'></td>
</tr>
<tr>
  <td><b>{$ibforums->lang['account_pass_new2']}</b></td>
  <td><input type='password' name='new_pass_2' value='' class='forminput'></td>
</tr>
</table>
<div align="center" class="pformstrip"><input type="submit" name='s_pass' value="{$ibforums->lang['account_pass_submit']}" class='forminput'></div>
</form>

EOF;
}


function boardlay_end() {
global $ibforums;
return <<<EOF

		<tr><td class='pformstrip' colspan='2' align='center'><input type='submit' class='forminput' value='{$ibforums->lang['jmp_go']}'></td></tr>
		</table>
		</form>
	</div>

EOF;
}


}