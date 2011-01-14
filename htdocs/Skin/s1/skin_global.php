<?php

class skin_global {


function rss($param = "") {
global $ibforums;
return <<<EOF

<link rel="alternate" type="application/rss+xml" title="RSS" href="{$ibforums->vars['board_url']}/yandex.php{$param}">

EOF;
}

function signature_separator($sig="") {
global $ibforums;
return <<<EOF

<br>___________<br><div class='signature'>$sig</div>

EOF;
}


function Error($message, $ad_email_one="", $ad_email_two="") {
global $ibforums;
return <<<EOF
<div class="tableborder">
 <div class="maintitle"><img src='{$ibforums->vars['img_url']}/nav_m.gif' alt='' width='8' height='8'>&nbsp;{$ibforums->lang['error_title']}</div>
</div>
<div class="tablefill">
  {$ibforums->lang['exp_text']}<br><br>
  <b>{$ibforums->lang['msg_head']}</b>
  <br><br>
  <span class='postcolor' style='padding:10px'>$message</span>
  <br><br>
  <!--IBF.LOG_IN_TABLE-->
  <!--IBF.POST_TEXTAREA-->
  <br><br>
  <b>{$ibforums->lang['er_links']}</b>
  <br><br>
  &middot; <a href='{$ibforums->base_url}act=Reg&amp;CODE=10'>{$ibforums->lang['er_lost_pass']}</a><br>
  &middot; <a href='{$ibforums->base_url}act=Reg&amp;CODE=00'>{$ibforums->lang['er_register']}</a><br>
  &middot; <a href='{$ibforums->base_url}act=Help&amp;CODE=00'>{$ibforums->lang['er_help_files']}</a><br>
  &middot; <a href="javascript:contact_admin('{$ad_email_one}', '{$ad_email_two}');">{$ibforums->lang['er_contact_admin']}</a>
</div>
<div class="tableborder">
 <div class="pformstrip" align="center">&lt; <a href='javascript:history.go(-1)'>{$ibforums->lang['error_back']}</a></div>
</div>

EOF;
}


function Redirect($Text, $Url, $css) {
global $ibforums;
return <<<EOF

<html>
<head><title>{$ibforums->lang['stand_by']}</title><meta http-equiv='refresh' content='2; url=$Url'></head>
<body>
<table width='100%' height='85%' align='center'>
<tr>
  <td valign='middle'>
	  <table align='center' cellpadding="4" class="tablefill">
	  <tr> 
		<td width="100%" align="center" nowrap="nowrap">
		  {$ibforums->lang['thanks']}, 
		  $Text<br>
            <br>
            <img src="{$ibforums->vars['img_url']}/loading.gif"> <br>
            <br>
		  {$ibforums->lang['transfer_you']}<br><br>
	      (<a href='$Url'>{$ibforums->lang['dont_wait']}</a>)
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


function warn_window($message) {
global $ibforums;
return <<<EOF

<table {$ibforums->skin['white_background']} style='border:2px solid red;'><tr><td>
{$message}
</td></tr>
</table>

EOF;
}


function css_inline($css="",$load_from_file = false) {
global $ibforums;
if ($load_from_file) {
	$css = file_get_contents($ibforums->vars['base_dir']."/cache/css_{$css}.css");
}
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






function Member_bar($msg, $ad_link, $mod_link, $val_link) {
global $ibforums;
return <<<EOF

<table width="100%" id="userlinks" cellspacing="0">
  <tr align='center'>
    <td>
<b>{$ibforums->lang['logged_in_as']} <a href='{$ibforums->base_url}showuser={$ibforums->member['id']}'>{$ibforums->member['name']}</a> !</b> [{$ibforums->input['IP_ADDRESS']}]
    </td>
    <td>
      $ad_link
      $mod_link
      $val_link
      <a href='{$ibforums->base_url}act=UserCP&amp;CODE=00' title='{$ibforums->lang['cp_tool_tip']}' target='_blank'>{$ibforums->lang['your_cp']}</a> &middot;
      <a href='{$ibforums->base_url}act=Msg&amp;CODE=01'>{$msg['TEXT']}</a> &middot;
      <a href='{$ibforums->base_url}act=Select&amp;CODE=mygetnew'>{$ibforums->lang['my_view_new_posts']}</a> 
      <a href='{$ibforums->base_url}act=Select&amp;CODE=getnew'>{$ibforums->lang['view_new_posts']}</a> &middot;
      <a href='javascript:buddy_pop();' title='{$ibforums->lang['bb_tool_tip']}'>{$ibforums->lang['l_qb']}</a> &middot;
      <a href='{$ibforums->base_url}act=Login&amp;CODE=03'>{$ibforums->lang['log_out']}</a>
    </td>
  </tr>
</table>
EOF;
}








function Member_no_usepm_bar($ad_link, $mod_link, $val_link) {
global $ibforums;
return <<<EOF

<table width="100%" id="userlinks" cellspacing="0">
  <tr align='center'>
    <td align='left'>
{$ibforums->lang['logged_in_as']} {$ibforums->member['name']} [{$ibforums->input['IP_ADDRESS']}]
    </td>
    <td align='center'>
      $ad_link
      $mod_link
      $val_link
      <a href='{$ibforums->base_url}act=UserCP&amp;CODE=00' title='{$ibforums->lang['cp_tool_tip']}' target='_blank'>{$ibforums->lang['your_cp']}</a> &middot;
<!--
      <a href='{$ibforums->base_url}act=Msg&amp;CODE=01'>{$msg[TEXT]}</a> &middot;
-->
  <a href='{$ibforums->base_url}act=Select&amp;CODE=mygetnew'>{$ibforums->lang['my_view_new_posts']}</a>
  <a href='{$ibforums->base_url}act=Select&amp;CODE=getnew'>{$ibforums->lang['view_new_posts']}</a>  &middot;
  <a href='javascript:buddy_pop();' title='{$ibforums->lang['bb_tool_tip']}'>{$ibforums->lang['l_qb']}</a> &middot;
  <a href='{$ibforums->base_url}act=Login&amp;CODE=03'>{$ibforums->lang['log_out']}</a>
    </td>
  </tr>
</table>

EOF;
}




function member_bar_disabled() {
global $ibforums;
return <<<EOF

<table width="100%" id="userlinks" cellspacing="6">
  <tr align='center'>
    <td><strong>{$ibforums->lang['mb_disabled']}</strong></td>
  </tr>
</table>

EOF;
}




function Guest_bar() {
global $ibforums;
return <<<EOF

<table width="100%" id="userlinks" cellspacing="6">
  <tr align='center'>
    <td align='left'>{$ibforums->lang['guest_stuff']} [{$ibforums->input['IP_ADDRESS']}]
    </td>
    <td>
      <a href='{$ibforums->base_url}act=Login&amp;CODE=00'>{$ibforums->lang['log_in']}</a> &middot;
      <a href='{$ibforums->base_url}act=Reg&amp;CODE=00'>{$ibforums->lang['register']}</a> &middot;
      <a href='{$ibforums->base_url}act=Reg&amp;CODE=reval'>{$ibforums->lang['ml_revalidate']}</a> &middot;
      <a href="{$ibforums->base_url}showtopic=50223">{$ibforums->lang['why_register']}</a>
    </td>
  </tr>
</table>
EOF;
}





function error_log_in($q_string) {
global $ibforums;
return <<<EOF

<form action='{$ibforums->base_url}' method='post'>
<input type='hidden' name='act' value='Login'>
<input type='hidden' name='CODE' value='01'>
<input type='hidden' name='s' value='{$ibforums->session_id}'>
<input type='hidden' name='referer' value='$q_string'>
<input type='hidden' name='CookieDate' value='1'>
<div class="tableborder">
  <div class="titlemedium">{$ibforums->lang['er_log_in_title']}</div>
  <table>
   <tr>
	<td class="pformleft">{$ibforums->lang['erl_enter_name']}</td>
	<td class="pformright"><input type='text' size='20' maxlength='64' name='UserName' class='forminput'></td>
   </tr>
   <tr>
	<td class="pformleft">{$ibforums->lang['erl_enter_pass']}</td>
	<td class="pformright"><input type='password' size='20' name='PassWord' class='forminput'></td>
   </tr>
  </table>
  <div class="pformstrip" align="center"><input type='submit' name='submit' value='{$ibforums->lang['erl_log_in_submit']}' class='forminput'></div>
</div>
</form>

EOF;
}

function member_valid_warning() {
global $ibforums;
return <<<EOF

<div style="width:50%; position:fixed; left:25%; top:35%; border:8px solid red; z-index:2; cursor:default; padding:30px 0px" class="row1">
  <a onclick="this.parentNode.style.visibility='hidden'" style='position:absolute; right:5px; top:1px'><b>X</b></a>
  <div style="text-align:center; width:100%;">{$ibforums->lang['valid_warning']}</div>
</div>
 
EOF;
}

function validating_link() {
global $ibforums;
return <<<EOF

&nbsp;&middot; <a href='{$ibforums->base_url}act=Reg&amp;CODE=reval'>{$ibforums->lang['ml_revalidate']}</a>

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
<div align='center'>
<input type='button' tabindex='1' value='{$ibforums->lang['err_select']}' onclick='document.mehform.saved.select()'><br>
<form name='mehform'>
<textarea cols='70' rows='5' name='saved' tabindex='2'>$post</textarea>
</form>
</div>

EOF;
}


function pop_up_window($title, $css, $text) {
global $ibforums;
return <<<EOF

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml"> 
 <head> 
  <meta http-equiv="content-type" content="text/html;  charset=windows-1251"> 
  <title>$title</title>
  $css
 </head>
 <body>
 <div style='text-align:left'>
 $text
 </div>
 </body>
</html>

EOF;
}


function forum_show_rules_full($rules) {
global $ibforums;
return <<<EOF

    <div align='left' id='ipbwrapper'><{F_RULES}>&nbsp;<b>{$rules['title']}</b><br><br>{$rules['body']}</div>
	<br>

EOF;
}


function rules_link($url="", $title="") {
global $ibforums;
return <<<EOF

<a href="$url" target="blank_"><img src="{$ibforums->vars['img_url']}/atb_rules.gif" border="0" alt="">$title</a>

EOF;
}


function admin_link() {
global $ibforums;
return <<<EOF
<a href='{$ibforums->vars['board_url']}/admin.{$ibforums->vars['php_ext']}' target='_blank'>{$ibforums->lang['admin_cp']}</a>&nbsp;&middot;
EOF;
}


function mod_link() {
global $ibforums;
return <<<EOF
<a href='{$ibforums->base_url}act=modcp&amp;forum={$ibforums->input['f']}'>{$ibforums->lang['mod_cp']}</a>&nbsp;&middot;
EOF;
}


function show_chat_link_popup() {
global $ibforums;
return <<<EOF
<a href="javascript:chat_pop({$ibforums->vars['chat_width']}, {$ibforums->vars['chat_height']});"><img src="{$ibforums->vars['img_url']}/atb_chat.gif" border="0" alt=""> {$ibforums->lang['live_chat']}</a>
EOF;
}


function board_offline($message = "") {
global $ibforums;
return <<<EOF

<form action='{$ibforums->base_url}' method='post'>
<input type='hidden' name='act' value='Login'>
<input type='hidden' name='CODE' value='01'>
<input type='hidden' name='s' value='{$ibforums->session_id}'>
<input type='hidden' name='referer' value=''>
<input type='hidden' name='CookieDate' value='1'>
<div class='tableborder'>
  <div class='maintitle'><{CAT_IMG}>&nbsp;{$ibforums->lang['offline_title']}</div>
  <div class='tablepad'>$message</div>
  <table width='100%' cellpadding='0' cellspacing='0'>
  <tr>
   <td class='pformleftw'>{$ibforums->lang['erl_enter_name']}</td>
   <td class='pformright'><input type='text' size='20' maxlength='64' name='UserName' class='forminput'></td>
  </tr>
  <tr>
   <td class='pformleftw'>{$ibforums->lang['erl_enter_pass']}</td>
   <td class='pformright'><input type='password' size='20' name='PassWord' class='forminput'></td>
  </tr>
  </table>
  <div class='pformstrip' align='center'><input type='submit' name='submit' value='{$ibforums->lang['erl_log_in_submit']}' class='forminput'></div>
</div>
</form>

EOF;
}


function make_page_jump($tp="", $pp="", $ub="") {
global $ibforums;
return <<<EOF

<a title="{$ibforums->lang['tpl_jump']}" href="javascript:multi_page_jump('$ub',$tp,$pp);">{$ibforums->lang['tpl_pages']}</a>

EOF;
}


function show_chat_link_inline() {
global $ibforums;
return <<<EOF

&nbsp; &nbsp;&nbsp;<img src="{$ibforums->vars['img_url']}/atb_chat.gif" border="0" alt="">&nbsp;<a href='{$ibforums->base_url}act=chat'>{$ibforums->lang['live_chat']}</a>

EOF;
}




















function BoardHeader($time="", $image) {
global $ibforums;
return <<<EOF

<script language='JavaScript' type='text/javascript'>
var session_id  	= "{$ibforums->session_id}";
var st 			= "{$ibforums->input['st']}";
var tpl_q1 		= "{$ibforums->lang['tpl_q1']}";
var base_url		= "{$ibforums->base_url}";

var text_enter_url      = "{$ibforums->lang['jscode_text_enter_url']}"; //lang_post: Введите полный адрес ссылки
var text_enter_url_name = "{$ibforums->lang['jscode_text_enter_url_name']}"; //lang_post: Введите название сайта
var text_enter_email	= "{$ibforums->lang['jscode_text_enter_email']}"; //lang_post: Введите адрес электронной почты
var text_enter_email_name = "{$ibforums->lang['jscode_text_enter_email_name']}"; //lang_post: Введите имя
var list_prompt         = "{$ibforums->lang['js_tag_list']}"; //lang_post: Введите пункт списка....
var text_enter_image	= "{$ibforums->lang['jscode_text_enter_image']}";
var error_no_url        = "{$ibforums->lang['jscode_error_no_url']}"; //lang_post: Вы должны ввести адрес
var error_no_title      = "{$ibforums->lang['jscode_error_no_title']}"; //lang_post: Вы должны ввести название";
var error_no_email      = "{$ibforums->lang['jscode_error_no_email']}"; //lang_posr: Вы должны ввести адрес электронной почты
var error_no_email_name = "{$ibforums->lang['jscode_error_no_email_name']}"; //lang_post: Вы должны ввести имя";
var text_enter_spoiler    = "{$ibforums->lang['jscode_text_enter_spoiler']}";
var text_spoiler_hidden_text    = "{$ibforums->lang['spoiler']}";
</script>

<script type='text/javascript' src='{$ibforums->vars['board_url']}/html/jquery-1.4.2.min.js'></script>
<script type='text/javascript' src='{$ibforums->vars['board_url']}/html/global.js'></script>

<table border='0' width='100%' cellspacing='0' cellpadding='1'>
<tr id='logostrip'>
<td width=210><a href='{$ibforums->vars['home_url']}' title='На главную'><img src='{$ibforums->vars['img_url']}/logo4.gif' alt='На главную' border='0'></a></td>

<td align=center>
<!-- SLOGAN -->
</td>

<td width=468 align=right>
<!-- HEADER_BANNER -->
</td>
</tr>

<tr style='height:24px;'>
<td align=right>
Наши проекты:
</td>
<td colspan=2>
&middot; 
<a href="http://magazine.sources.ru/"><b>Журнал</b></a>
&middot; 
<a href="http://alglib.sources.ru/"><b>Алгоритмы</b></a>
&middot; 
<a href="http://e-science.sources.ru/"><b>Естественные Науки</b></a>
&middot;
<a href="http://wiki.sources.ru/"><b>Wiki</b></a>
&middot;
<a href="http://drkb.ru/" title="Delphi Resources Knowledge Base"><b>DRKB</b></a>
&middot;
<a href="http://www.sources.ru/donate.php"><b>Помощь&nbsp;проекту</b></a>
</td>
</tr>
</table>


<table  width='100%' cellspacing='0' id='submenu'>
<tr align='center'>
 <td><!--IBF.RULES--></td>
 <td><a href='{$ibforums->base_url}showtopic=50223'><{atb_faq}> {$ibforums->lang['tb_faq']}</a></td>
 <td><a href='{$ibforums->base_url}act=Help'><{atb_help}> {$ibforums->lang['tb_help']}</a></td>
 <td><a href='{$ibforums->base_url}act=Search&amp;f={$ibforums->input['f']}'><{atb_search}> {$ibforums->lang['tb_search']}</a></td>
 <td><a href='{$ibforums->base_url}act=Members'><{atb_members}> {$ibforums->lang['tb_mlist']}</a></td>
 <td><a href='{$ibforums->base_url}act=calendar'><{atb_calendar}> {$ibforums->lang['tb_calendar']}</a></td>
 <td><a href='{$ibforums->base_url}act=fav&show=1'>$image Избранное</a></td>
 <td><a href='{$ibforums->base_url}act=store'><{atb_store}> {$ibforums->lang['ibstore']}</a></td>
 <td><{RSS}></td>
</tr>
</table>

<% MEMBER BAR %>

EOF;
}



function start_nav($NEW="") {
global $ibforums;
return <<<EOF
<table border=0 width="100%" cellspacing=0 cellpadding=0>
<tr>
<td id='navstrip' align='left'>
<!--div id='navstrip' align='left'--><{F_NAV}>&nbsp;

EOF;
}




function end_nav() {
global $ibforums, $std;
return <<<EOF

<!--/div-->
</td>
<td align="right" valign="middle" width=468>
<!-- TOP NAV BANNER -->
<br>
<% TOP NAV BANNER %>
</td>
<!-- DONATE 5rub FORM -->
<td align="right" valign="middle" width="200">
	<form action="https://money.yandex.ru/donate.xml" method="post">
		<input type="hidden" name="to" value="41001151000887"/>
		<input type="hidden" name="s5" value="5rub"/>
		
		<table class="donate">
		<col align="right"><col width="50">
		<tr>
			<td class="donate_text">
				<div>{$ibforums->lang['like_sources']}</div>
			</td>
			<td rowspan="2" class="donate_image"><input type="image" src="img/5rub/5rub_gold.png" title="Яндекс.Деньги"></td>
		</tr>
		<tr>
			<td class="donate_button"><input type="submit" title="Яндекс.Деньги" value="{$ibforums->lang['donate_5rub']}"/></td>
		</tr>
		</table>
	</form>
</td>
</tr>
</table>

<br>

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
<input type='button' name='USER' value='USER' onclick="simpletag('USER')" class='codebuttons'>
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
<option value='[URL={$ibforums->base_url}showtopic=50223]FAQ форума[/URL]'>FAQ форума</option>
<option value='.FAQ'>FAQ раздела</option>
<option value='.Поиск'>Поиск</option>
<option value='.Правила'>Правила</option>
<option value='.Правила, п.1'>Правила, п.1</option>
<option value='.Правила, п.2'>Правила, п.2</option>
<option value='.Правила, п.3'>Правила, п.3</option>
<option value='.Правила, п.4'>Правила, п.4</option>
<option value='.Правила, п.5'>Правила, п.5</option>
<option value='.Правила, п.6'>Правила, п.6</option>
<option value='.Правила, п.7'>Правила, п.7</option>
<option value='.Правила, п.8'>Правила, п.8</option>
<option value='.Правила, п.9'>Правила, п.9</option>
<option value='.Правила, п.10'>Правила, п.10</option>
<option value='.Правила, п.11'>Правила, п.11</option>
<option value='.Правила, п.12'>Правила, п.12</option>
<option value='.Правила, п.13'>Правила, п.13</option>
<option value='.Правила, п.14'>Правила, п.14</option>
</select>
&nbsp;

EOF;
}

function forum_show_rules_link($rules) {
global $ibforums;
return <<<EOF

    <div align='left'><{F_RULES}>&nbsp;<b><a href='{$ibforums->base_url}act=SR&amp;f={$rules['fid']}'>{$rules['title']}</a></b></div>

EOF;
}


function PM_popup() {
global $ibforums;
return <<<EOF

<script language='JavaScript' type="text/javascript">
 pm_popup();
</script>

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

function topic_decided() {
global $ibforums;
return <<<EOF

<tr><td class='pformstrip' colspan='2'>{$ibforums->lang['topic_decided_1']}</td></tr>
<tr>
 <td class='pformleft'><input type='checkbox' name='topic_decided' class='checkbox' value='1'> {$ibforums->lang['topic_decided_1']}</td>
 <td class='pformright'>{$ibforums->lang['topic_decided_2']}</td>
</tr>

EOF;
}

function RenderDeletedRow() {
global $ibforums;
return <<<EOF
<span class='movedprefix' style='font-size:10pt;line-height:100%'>{$ibforums->lang['mod_del']}</span>
EOF;
}


}
