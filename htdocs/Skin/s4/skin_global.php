<?php

class skin_global {

function rss($param = "") {
global $ibforums;
return <<<EOF

<link rel="alternate" type="application/rss+xml" title="RSS" href="{$ibforums->vars['board_url']}/yandex.php{$param}">

EOF;
}

function forum_filter($data) {
global $ibforums;
return <<<EOF

<div class='tableborder'><div class='maintitle'>
{$ibforums->lang['filter_text']}&nbsp;
{$data}
</div></div><br>
EOF;
}

function css_inline($css="") {
global $ibforums;
return <<<EOF

<style type='text/css'>
{$css}
</style>

EOF;
}

function css_external($css, $img) {
global $ibforums;
return <<<EOF

<LINK REL=STYLESHEET TYPE="text/css" HREF="{$ibforums->vars['board_url']}/cache/css_{$css}.css">

EOF;
}

function pop_up_window($title, $css, $text) {
global $ibforums;
return <<<EOF

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1"> 
<title>$title</title>
$css
</head>
<body>
<div style="text-align:left">
$text
</div>
</body>
</html>

EOF;
}


function forum_show_rules_full($rules) {
global $ibforums;
return <<<EOF

<!-- Show FAQ/Forum Rules -->
  <span class="small"><b>{$rules['title']}</b><br><br>{$rules['body']}</span>
  <br>
<!-- End FAQ/Forum Rules -->

EOF;
}

function Member_bar($msg, $ad_link, $mod_link, $val_link) {
global $ibforums;
return <<<EOF
<div class="tablebasic">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
	<td align="right" valign="top">
	  <span class="small"><b>{$ibforums->lang['logged_in_as']} <a href="{$ibforums->base_url}showuser={$ibforums->member['id']}">{$ibforums->member['name']}</a></b>&nbsp;&middot;&nbsp;<b><a href="{$ibforums->base_url}act=Login&amp;CODE=03">{$ibforums->lang['log_out']}</a></b> | <b><a href="javascript:buddy_pop();" title='{$ibforums->lang['bb_tool_tip']}'>{$ibforums->lang['l_qb']}</a></b><br>
	  <b><a href="{$ibforums->base_url}act=Msg&amp;CODE=01">{$msg[TEXT]}</a></b> | <b><a href="{$ibforums->base_url}act=Search&amp;CODE=getnew">{$ibforums->lang['view_new_posts']}</a></b></span></td>
  </tr>
</table></div>

EOF;
}


function PM_popup() {
global $ibforums;
return <<<EOF

<script language="JavaScript" type="text/javascript">
     <!--
       window.open('index.{$ibforums->vars['php_ext']}?act=Msg&CODE=99&s={$ibforums->session_id}','NewPM','width=500,height=250,resizable=yes,scrollbars=yes'); 
     //-->
     </script>

EOF;
}


function signature_separator($sig="") {
global $ibforums;
return <<<EOF

<br><br>--------------------<br>
<div class="signature">$sig</div>

EOF;
}


function BoardHeader($time="") {
global $ibforums;
return <<<EOF

<script language='JavaScript' type='text/javascript'>
var session_id  = "{$ibforums->session_id}";
var st 		= "{$ibforums->input['st']}";
var tpl_q1 	= "{$ibforums->lang['tpl_q1']}";
var base_url	= "{$ibforums->base_url}";
</script>
<script type='text/javascript' src='{$ibforums->vars['board_url']}/html/global.js'></script>
<table  width='100%' cellspacing='6' id='submenu'>
<tr>
 <td><!-- a href='{$ibforums->base_url}'>{$ibforums->vars['home_name']}</a--></td>
 <td align='right'><!--IBF.RULES-->&nbsp;
  <a href='{$ibforums->base_url}act=Help'><{atb_help}> {$ibforums->lang['tb_help']}</a>
   &nbsp;&nbsp;&nbsp;<a href='{$ibforums->base_url}act=Search&amp;f={$ibforums->input['f']}'><{atb_search}> {$ibforums->lang['tb_search']}</a>
   &nbsp;&nbsp;&nbsp;<a href='{$ibforums->base_url}act=Members'><{atb_members}> {$ibforums->lang['tb_mlist']}</a>
   &nbsp;&nbsp;&nbsp;<a href='{$ibforums->base_url}act=calendar'><{atb_calendar}> {$ibforums->lang['tb_calendar']}</a>&nbsp;&nbsp;&nbsp;<a href='{$ibforums->base_url}act=fav'>$image Избранное</a>
   &nbsp;&nbsp;&nbsp;<a href='{$ibforums->base_url}act=store'><{atb_store}> {$ibforums->lang['ibstore']}</a>
   <!--IBF.CHATLINK-->
 </td>
</tr>
</table>
<% MEMBER BAR %>

EOF;
}


function v12_banner() {
global $ibforums;
return <<<EOF

<div id="ipsbanner"><a href="http://www.ipshosting.com" target="_blank"><img src="html/sys-img/ipshosting.gif" border="0" alt="IPS Hosting"></a></div>

EOF;
}


function member_bar_disabled() {
global $ibforums;
return <<<EOF


EOF;
}


function Member_no_usepm_bar($ad_link, $mod_link, $val_link) {
global $ibforums;
return <<<EOF
<div class="tablebasic">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td align="left" valign="top"><span class="small"> $ad_link $mod_link $val_link</span></td>
	<td align="right" valign="top">
	  <span class="small"><b>{$ibforums->lang['logged_in_as']} <a href="{$ibforums->base_url}showuser={$ibforums->member['id']}">{$ibforums->member['name']}</a></b>&nbsp;&middot;&nbsp;&nbsp;<b><a href="{$ibforums->base_url}act=Login&amp;CODE=03">{$ibforums->lang['log_out']}</a></b> | <b><a href="javascript:buddy_pop();" title="{$ibforums->lang['bb_tool_tip']}">{$ibforums->lang['l_qb']}</a></b><br>
	  <b><a href="{$ibforums->base_url}act=Search&amp;CODE=getnew">{$ibforums->lang['view_new_posts']}</a></b></span></td>
  </tr>
</table></div>

EOF;
}


function show_chat_link_inline() {
global $ibforums;
return <<<EOF

<a href="{$ibforums->base_url}act=chat"><{CHAT}></a>&nbsp;

EOF;
}


function show_chat_link_popup() {
global $ibforums;
return <<<EOF

<a href="javascript:chat_pop({$ibforums->vars['chat_width']}, {$ibforums->vars['chat_height']});"><{CHAT}></a>&nbsp;

EOF;
}


function Guest_bar() {
global $ibforums;
return <<<EOF
<div class="tablebasic">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td align="left" valign="top"><span class="small"></span></td>
	<td align="right" valign="top">
	  <span class="small"><b>{$ibforums->lang['guest_stuff']}</b>&nbsp;&middot;&nbsp;&nbsp;<b><a href="{$ibforums->base_url}act=Login&amp;CODE=00">{$ibforums->lang['log_in']}</a></b> | <b><a href="{$ibforums->base_url}act=Reg&amp;CODE=reval">{$ibforums->lang['ml_revalidate']}</a></b></span></td>
  </tr>
</table></div>

EOF;
}


function Redirect($Text, $Url, $css) {
global $ibforums;
return <<<EOF

<html>
<head>
<title>{$ibforums->lang['stand_by']}</title>
<meta http-equiv="refresh" content="2; url=$Url">
<script type="text/javascript"> </script>
$css
</head>
<body>
<table width="100%" height="85%" align="center">
  <tr>
    <td valign="middle">
	  <table align="center" cellpadding="4" class="tablefill">
	    <tr> 
		  <td width="100%" align="center" nowrap="nowrap">
		  {$ibforums->lang['thanks']}, 
		  $Text<br><br>
		  {$ibforums->lang['transfer_you']}<br><br>
	      (<a href="$Url">{$ibforums->lang['dont_wait']}</a>)
	      </td>
	    </tr>
	  </table>
    </td>
  </tr>
</table>
</body>
</html>

EOF;
}


function start_nav() {
global $ibforums;
return <<<EOF

<div id="navstrip" align="left"><{F_NAV}>&nbsp;

EOF;
}


function rules_link($url="", $title="") {
global $ibforums;
return <<<EOF

<a href="$url" target="blank_"><img src="{$ibforums->vars['img_url']}/atb_rules.gif" border="0" alt=""><span style='color:red'>$title</span></a>

EOF;
}

function end_nav() {
global $ibforums;
return <<<EOF

</div>
<br>

EOF;
}


function board_offline($message = "") {
global $ibforums;
return <<<EOF

<form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" method="post">
<input type="hidden" name="act" value="Login">
<input type="hidden" name="CODE" value="01">
<input type="hidden" name="s" value="{$ibforums->session_id}">
<input type="hidden" name="referer" value="">
<input type="hidden" name="CookieDate" value="1">
<div class="plainborder">
  <div class="maintitle"><{CAT_IMG}>&nbsp;{$ibforums->lang['offline_title']}</div>
  <div class="tablepadtop">$message</div>
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td class="pformleftw">{$ibforums->lang['erl_enter_name']}</td>
      <td class="pformright"><input type="text" size="20" maxlength="64" name="UserName" class="forminput"></td>
    </tr>
    <tr>
      <td class="pformleftw">{$ibforums->lang['erl_enter_pass']}</td>
      <td class="pformright"><input type="password" size="20" name="PassWord" class="forminput"></td>
    </tr>
  </table>
  <div class="pformstriptop" align="center"><input type="submit" name="submit" value="{$ibforums->lang['erl_log_in_submit']}" class="forminput"></div>
</div>
</form>

EOF;
}


function validating_link() {
global $ibforums;
return <<<EOF

<a href="{$ibforums->base_url}act=Reg&amp;CODE=reval">{$ibforums->lang['ml_revalidate']}</a>

EOF;
}


function mod_link() {
global $ibforums;
return <<<EOF

(<b><a href="{$ibforums->base_url}act=modcp&amp;forum={$ibforums->input['f']}">{$ibforums->lang['mod_cp']}</a></b>)

EOF;
}


function admin_link() {
global $ibforums;
return <<<EOF

(<b><a href="{$ibforums->vars['board_url']}/admin.{$ibforums->vars['php_ext']}" target="_blank">{$ibforums->lang['admin_cp']}</a></b>)

EOF;
}


function error_log_in($q_string) {
global $ibforums;
return <<<EOF

<form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" method="post">
<input type="hidden" name="act" value="Login">
<input type="hidden" name="CODE" value="01">
<input type="hidden" name="s" value="{$ibforums->session_id}">
<input type="hidden" name="referer" value="$q_string">
<input type="hidden" name="CookieDate" value="1">
<div class="tableborder" style="margin: 0px 40px 0px 40px">
 <table width="100%" cellspacing="1" cellpadding="4">
   <tr>
     <td colspan="2" class="titlemedium">{$ibforums->lang['er_log_in_title']}</td>
   </tr>
   <tr>
	 <td class="pformleft">{$ibforums->lang['erl_enter_name']}</td>
	 <td class="pformright"><input type="text" size="20" maxlength="64" name="UserName" class="forminput"></td>
   </tr>
   <tr>
	 <td class="pformleft">{$ibforums->lang['erl_enter_pass']}</td>
	 <td class="pformright"><input type="password" size="20" name="PassWord" class="forminput"></td>
   </tr>
   <tr>
     <td colspan="2" class="pformstrip" align="center"><input type="submit" name="submit" value="{$ibforums->lang['erl_log_in_submit']}" class="forminput"></td>
   </tr>
 </table>
</div>
</form>

EOF;
}


function Error($message, $ad_email_one="", $ad_email_two="") {
global $ibforums;
return <<<EOF

<script language="JavaScript" type="text/javascript">
<!--
function contact_admin() {
  // Very basic spam bot stopper
	  
  admin_email_one = '$ad_email_one';
  admin_email_two = '$ad_email_two';
  
  window.location = 'mailto:'+admin_email_one+'@'+admin_email_two+'?subject=Error on the forums';
  
}
//-->
</script>
<div class="plainborder">
  <div class="maintitle"><img src="{$ibforums->vars['img_url']}/nav_m.gif" alt="" width="8" height="8">&nbsp;{$ibforums->lang['error_title']}</div>
  <div class="tablepadtop">
    {$ibforums->lang['exp_text']}<br><br>
    <b>{$ibforums->lang['msg_head']}</b>
    <br><br>
    <span class="postcolor" style="padding:10px">$message</span>
    <br><br>
    <!--IBF.LOG_IN_TABLE-->
    <!--IBF.POST_TEXTAREA-->
    <br><br>
    <span class="small"><b>{$ibforums->lang['er_links']}</b>
    <br><br>
    &middot; <a href="{$ibforums->base_url}act=Reg&amp;CODE=10">{$ibforums->lang['er_lost_pass']}</a><br>
    &middot; <a href="{$ibforums->base_url}act=Reg&amp;CODE=00">{$ibforums->lang['er_register']}</a><br>
    &middot; <a href="{$ibforums->base_url}act=Help&amp;CODE=00">{$ibforums->lang['er_help_files']}</a><br>
    &middot; <a href="javascript:contact_admin();">{$ibforums->lang['er_contact_admin']}</a></span>
  </div>
  <div class="pformstriptop" align="center">&lt; <a href="javascript:history.go(-1)">{$ibforums->lang['error_back']}</a></div>
</div>

EOF;
}


function error_post_textarea($post="") {
global $ibforums;
return <<<EOF

<br>
<div>
<strong>{$ibforums->lang['err_title']}</strong>
<br><br>
{$ibforums->lang['err_expl']}
</div>
<br>
<br>
<div align="center">
  <input type="button" tabindex="1" value="{$ibforums->lang['err_select']}" onclick="document.mehform.saved.select()"><br>
  <form name="mehform">
   <textarea cols="70" rows="5" name="saved" tabindex="2">$post</textarea>
  </form>
</div>

EOF;
}


function forum_show_rules_link($rules) {
global $ibforums;
return <<<EOF

<!-- Show FAQ/Forum Rules -->
	
    <span class="small"><b><a href="{$ibforums->base_url}act=SR&amp;f={$rules['fid']}">{$rules['title']}</a></b></span>
	
    <!-- End FAQ/Forum Rules -->

EOF;
}


function make_page_jump($tp="", $pp="", $ub="") {
global $ibforums;
return <<<EOF

<a title="{$ibforums->lang['tpl_jump']}" href="javascript:multi_page_jump('$ub',$tp,$pp);">{$ibforums->lang['tpl_pages']}</a>

EOF;
}

function mod_buttons_label() {
global $ibforums;
return <<<EOF

<div style="height:3px"><!-- --></div><b>Модераторские</b>: 

EOF;
}

function global_mod_buttons() {
global $ibforums;
return <<<EOF

<input type='button' name='GM' value='GM' onclick="simpletag('GM')" class='codebuttons'>
<input type='button' name='doHTML' value='doHTML' onclick="simpletag('doHTML')" class='codebuttons'>
&nbsp;

EOF;
}


function mod_buttons() {
global $ibforums;
return <<<EOF

<input type='button' name='MOD' value='MOD' onclick="simpletag('MOD')" class='codebuttons'>
<input type='button' name='EX' value='EX' onclick="simpletag('EX')" class='codebuttons'>
&nbsp;

EOF;
}

function common_mod_buttons() {
global $ibforums;
return <<<EOF

<input type='button' name='MM' value='MM' onclick="simpletag('MM')" class='codebuttons'>
<input type='button' name='SF' value='SF' onclick="simpletag('SF')" class='codebuttons'>
<input type='button' name='ST' value='ST' onclick="simpletag('ST')" class='codebuttons'>
<input type='button' name='SALL' value='SALL' onclick="simpletag('SALL')" class='codebuttons'>
<input type='button' name='STALL' value='STALL' onclick="simpletag('STALL')" class='codebuttons'>
<select name='rules' class='codebuttons' onchange="doInsert(this.options[this.selectedIndex].value, '', false)">
<option value='-1'>Ссылки</option>
<option value='.Правила'>Правила</option>
<option value='.Поиск'>Поиск</option>
<option value='.FAQ'>FAQ раздела</option>
<option value='[URL={$ibforums->base_url}showtopic=50223]FAQ форума[/URL]'>FAQ форума</option>
</select>
&nbsp;

EOF;
}

}
?>