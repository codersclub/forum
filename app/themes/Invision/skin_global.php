<?php

class skin_global {


function rss($param = "") {
global $ibforums;
return <<<EOF

<link class="rss-link" rel="alternate" type="application/rss+xml" title="RSS" href="{$ibforums->vars['board_url']}/yandex.php{$param}">

EOF;
}

function signature_separator($sig="") {
return <<<EOF
<div class='b-signature'>$sig</div>
EOF;
}

function Error($message, $ad_email_one="", $ad_email_two="") {
global $ibforums;
return <<<EOF
<div class="tableborder">
 <h2><img src='{$ibforums->skin['ImagesPath']}/nav_m.gif' alt=''>&nbsp;{$ibforums->lang['error_title']}</h2>
</div>
<div class="b-error-message">
  <div class="b-error-subtitle">{$ibforums->lang['exp_text']}</div>
  <h4>{$ibforums->lang['msg_head']}</h4>
  <div class='b-error-description'>$message</div>
  <div class="b-error-login"><!--IBF.LOG_IN_TABLE--></div>
  <div class="b-error-post-data"><!--IBF.POST_TEXTAREA--></div>
  <h4>{$ibforums->lang['er_links']}</h4>
  <ul class="b-links">
  <li><a href='{$ibforums->base_url}act=Reg&amp;CODE=10'>{$ibforums->lang['er_lost_pass']}</a></li>
  <li><a href='{$ibforums->base_url}act=Reg&amp;CODE=00'>{$ibforums->lang['er_register']}</a></li>
  <li><a href='{$ibforums->base_url}act=Help&amp;CODE=00'>{$ibforums->lang['er_help_files']}</a></li>
  <li><a href="javascript:contact_admin('{$ad_email_one}', '{$ad_email_two}');">{$ibforums->lang['er_contact_admin']}</a></li>
  </ul>
</div>
<div class="tableborder">
 <div class="pformstrip" align="center">&lt; <a href='javascript:history.go(-1)'>{$ibforums->lang['error_back']}</a></div>
</div>

EOF;
}

function Redirect($Text, $Url, $css) {
global $ibforums;
return <<<EOF
<!DOCTYPE html>
<html>
<head>
  <title>{$ibforums->lang['stand_by']}</title><meta http-equiv='refresh' content='2; url=$Url'>
</head>
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
            <img src="{$ibforums->skin['ImagesPath']}/loading.gif"> <br>
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
return <<<EOF

<div class='warning-message'>
{$message}
</div>

EOF;
}

function css_external($css) {
    return Assets::make($css)->toLink();
}

function action_button_wrapper($html, $name) {
	return (empty($html))
		? $html
		: "<li class=\"b-action-button {$name}\">{$html}</li>";
}

function Member_bar($msg, $ad_link, $mod_link, $val_link) {
global $ibforums;
$ad_link  = $this->action_button_wrapper($ad_link, 'b-user-admin_cp-button');
$mod_link = $this->action_button_wrapper($mod_link, 'b-user-mod_cp-button');
$val_link = $this->action_button_wrapper($val_link, 'b-user-validate-button');

return <<<EOF

<table width="100%" id="userlinks" class='b-user-links' cellspacing="0">
  <tr>
    <td class='b-welcome-message__wrapper'>
      <span class='b-welcome-message'>
        <span class='b-welcome-message__prefix'>{$ibforums->lang['hello']}</span>
        <a href='{$ibforums->base_url}showuser={$ibforums->member['id']}'>{$ibforums->member['name']}</a>
        <span class="b-welcome-message__suffix"> !</span>
      </span>
      <span class='b-welcome-message__user-ip'>[{$ibforums->input['IP_ADDRESS']}]</span>
    </td>
    <td class='b-user-action-buttons-wrapper'>
    <ul class="b-action-buttons b-action-buttons b-user-action-buttons">
      $ad_link
      $mod_link
      $val_link
  <li class='b-action-button b-user-cp-button'><a class='b-action-button-link' href='{$ibforums->base_url}act=UserCP&amp;CODE=00' title='{$ibforums->lang['cp_tool_tip']}' target='_blank'>{$ibforums->lang['your_cp']}</a></li>
  <li class='b-action-button b-user-pm-button'><a class='b-action-button-link' href='{$ibforums->base_url}act=Msg&amp;CODE=01'>{$msg['TEXT']}</a></li>
  <li class='b-action-button b-user-my_new_posts-button'><a class='b-action-button-link' href='{$ibforums->base_url}act=Select&amp;CODE=mygetnew'>{$ibforums->lang['my_view_new_posts']}</a></li>
  <li class='b-action-button b-user-new_posts-button no-middot'><a class='b-action-button-link' href='{$ibforums->base_url}act=Select&amp;CODE=getnew'>{$ibforums->lang['view_new_posts']}</a></li>
  <li class='b-action-button b-user-buddy-button'><a class='b-action-button-link' href='javascript:buddy_pop();' title='{$ibforums->lang['bb_tool_tip']}'>{$ibforums->lang['l_qb']}</a></li>
  <li class='b-action-button b-user-logout-button'><a class='b-action-button-link' href='{$ibforums->base_url}act=Login&amp;CODE=03'>{$ibforums->lang['log_out']}</a></li>
      </ul>
    </td>
  </tr>
</table>
EOF;
}








function Member_no_usepm_bar($ad_link, $mod_link, $val_link) {
global $ibforums;
	$ad_link  = $this->action_button_wrapper($ad_link, 'b-user-admin_cp-button');
	$mod_link = $this->action_button_wrapper($mod_link, 'b-user-mod_cp-button');
	$val_link = $this->action_button_wrapper($val_link, 'b-user-validate-button');
return <<<EOF

<table id="userlinks" class="b-user-links">
  <tr>
    <td>
      <span class='b-welcome-message'>
        <span class='b-welcome-prefix'>{$ibforums->lang['hello']}</span>
        {$ibforums->member['name']}
        <span class="b-welcome-suffix">!</span>
      </span>
      <span class='b-user-ip'>[{$ibforums->input['IP_ADDRESS']}]</span>
    </td>
    <td>
    <ul class="b-action-buttons b-user-action-buttons">
      $ad_link
      $mod_link
      $val_link
	  <li class='b-action-button b-user-cp-button'><a class='b-action-button-link' href='{$ibforums->base_url}act=UserCP&amp;CODE=00' title='{$ibforums->lang['cp_tool_tip']}' target='_blank'>{$ibforums->lang['your_cp']}</a></li>
	  <li class='b-action-button b-user-my_new_posts-button'><a class='b-action-button-link' href='{$ibforums->base_url}act=Select&amp;CODE=mygetnew'>{$ibforums->lang['my_view_new_posts']}</a></li>
	  <li class='b-action-button b-user-new_posts-button no-middot'><a class='b-action-button' href='{$ibforums->base_url}act=Select&amp;CODE=getnew'>{$ibforums->lang['view_new_posts']}</a></li>
	  <li class='b-action-button b-user-buddy-button'><a class='b-action-button-link' href='javascript:buddy_pop();' title='{$ibforums->lang['bb_tool_tip']}'>{$ibforums->lang['l_qb']}</a></li>
	  <li class='b-action-button b-user-logout-button'><a class='b-action-button-link' href='{$ibforums->base_url}act=Login&amp;CODE=03'>{$ibforums->lang['log_out']}</a></li>
    </ul>
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

<table id="userlinks" class="b-user-links">
  <tr>
    <td>
        <span class='b-welcome-message'><span class='b-welcome-prefix'>{$ibforums->lang['hello']} </span>Гость<span class="b-welcome-suffix"> !</span></span> <span class='b-user-ip'>[{$ibforums->input['IP_ADDRESS']}]</span>
    </td>
    <td class='b-user-action-buttons-wrapper'>
      <ul class="b-action-buttons b-user-action-buttons">
        <li class="b-action-button b-login-button"><a class="b-action-button-link" href='{$ibforums->base_url}act=Login&amp;CODE=00'>{$ibforums->lang['log_in']}</a></li>
        <li class="b-action-button b-register-button"><a class="b-action-button-link" href='{$ibforums->base_url}act=Reg&amp;CODE=00'>{$ibforums->lang['register']}</a></li>
        <li class="b-action-button b-revalidate-button"><a class="b-action-button-link" href='{$ibforums->base_url}act=Reg&amp;CODE=reval'>{$ibforums->lang['ml_revalidate']}</a></li>
        <li class="b-action-button b-why_register-button"><a class="b-action-button-link" href="{$ibforums->base_url}showtopic=50223">{$ibforums->lang['why_register']}</a></li>
      </ul>
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


function pop_up_window($title, $js, $css, $text) {
global $ibforums;
return <<<EOF
<!DOCTYPE html>
<html>
 <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <title>$title</title>
  $css
  $js
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

<div class='rules-wrapper'>
  <div class='rules-title'>
    <span class='rules-title-image'><{F_RULES}></span>
    <span class='rules-title-text'><b>{$rules['title']}</b></span>
  </div>
  <div class='rules-text'>{$rules['body']}</div>
</div>

EOF;
}


function rules_link($url="", $title="") {
global $ibforums;
return <<<EOF

<a href="$url" target="blank_" class='rules-link'>$title</a>

EOF;
}


function admin_link() {
global $ibforums;
return <<<EOF
<a class='b-action-button' href='{$ibforums->vars['board_url']}/admin.{$ibforums->vars['php_ext']}' target='_blank'>{$ibforums->lang['admin_cp']}</a>
EOF;
}


function mod_link() {
global $ibforums;
return <<<EOF
<a class='b-action-button' href='{$ibforums->base_url}act=modcp&amp;forum={$ibforums->input['f']}'>{$ibforums->lang['mod_cp']}</a>&nbsp;&middot;
EOF;
}

function show_chat_link_popup() {
global $ibforums;
return <<<EOF
<a href="javascript:chat_pop({$ibforums->vars['chat_width']}, {$ibforums->vars['chat_height']});"><img src="{$ibforums->skin['ImagesPath']}/atb_chat.gif" alt=""> {$ibforums->lang['live_chat']}</a>
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

&nbsp; &nbsp;&nbsp;<img src="{$ibforums->skin['ImagesPath']}/atb_chat.gif" alt="">&nbsp;<a href='{$ibforums->base_url}act=chat'>{$ibforums->lang['live_chat']}</a>

EOF;
}

function BoardHeader($fav_active) {
global $ibforums;
	if ($fav_active) {
		$image = '<{atb_favs_new}>';
		$fav_class = 'has-new-favorites';
	}else{
		$image = '<{atb_favs}>';
		$fav_class = '';
	}

return <<<EOF
<table id='b-header'>
<tr id='logostrip'>
<td class='b-logo-wrapper'><a class='b-logo_link' href='{$ibforums->vars['home_url']}' title='На главную'><img class='b-logo_img' src='{$ibforums->skin['ImagesPath']}/logo4.gif' alt='На главную'></a></td>

<td align='center' class='b-slogan-wrapper' colspan=2>
Наши проекты:
<br>
<a class='b-neighbor-link b-neighbor-link_magazine' href="https://magazine.sources.ru/"><b>Журнал</b></a>
&nbsp;
&middot;
&nbsp;
<a class='e-neighbor-link e-neighbor-link-discuz' href="https://codersclub.org/discuz.ml/" title="Discuz! MultiLingual"><b>Discuz!ML</b></a>
&nbsp;
&middot;
&nbsp;
<a class='b-neighbor-link b-neighbor-link_wiki' href="https://wiki.sources.ru/"><b>Wiki</b></a>
&nbsp;
&middot;
&nbsp;
<a class='b-neighbor-link b-neighbor-link_drkb' href="https://drkb.ru/" title="Delphi Resources Knowledge Base"><b>DRKB</b></a>
&nbsp;
&middot;
&nbsp;
<a class='b-neighbor-link b-neighbor-link_donate' href="/donate.php"><b>Помощь&nbsp;проекту</b></a>
</td>
</tr>

<tr class='b-neighbor-links-wrapper'>
<td class='b-neighbor-links-title'>
<!-- SLOGAN -->
</td>
<td class='b-neighbor-links' colspan=2>
<!-- HEADER_BANNER -->
</td>
</tr>
</table>


<table  width='100%' cellspacing='0' id='top-menu' class='b-hor_menu b-menu'>
<tr align='center'>
 <td class='b-menu-item b-menu-item-rules'><!--IBF.RULES--></td>
 <td class='b-menu-item b-menu-item-faq'><a href='{$ibforums->base_url}showtopic=50223'>{$ibforums->lang['tb_faq']}</a></td>
 <td class='b-menu-item b-menu-item-help'><a href='{$ibforums->base_url}act=Help'>{$ibforums->lang['tb_help']}</a></td>
 <td class='b-menu-item b-menu-item-search'><a href='{$ibforums->base_url}act=Search&amp;f={$ibforums->input['f']}'>{$ibforums->lang['tb_search']}</a></td>
 <td class='b-menu-item b-menu-item-members'><a href='{$ibforums->base_url}act=Members'>{$ibforums->lang['tb_mlist']}</a></td>
 <td class='b-menu-item b-menu-item-calendar'><a href='{$ibforums->base_url}act=calendar'>{$ibforums->lang['tb_calendar']}</a></td>
 <td class='b-menu-item b-menu-item-favorites {$fav_class}'><a href='{$ibforums->base_url}act=fav&show=1'>Избранное</a></td>
<!--
 <td class='b-menu-item b-menu-item-store'><a href='{$ibforums->base_url}act=store'>{$ibforums->lang['ibstore']}</a></td>
-->
 <td class='b-menu-item b-menu-item-rss'><a href="index.php?showtopic=81342">RSS</a></td>
</tr>
</table>

<% MEMBER BAR %>

EOF;
}

function bottomBreadcrumbs($items){
	$output = '<ul class="b-breadcrumbs b-breadcrumbs-bottom    ">';
	foreach($items as $item)
		$output .= '<li class="b-breadcrumbs-element">' . $item . '</li>';
	$output .= '</ul>';
	return $output;
}

function topBreadcrumbs($items){
	$output = '<ul class="b-breadcrumbs b-breadcrumbs-top">';
	foreach($items as $item)
		$output .= '<li class="b-breadcrumbs-element">' . $item . '</li>';
	$output .= '</ul>';
	return $output;
}

function start_nav($NEW="") {
global $ibforums;
return <<<EOF
<table id='top-navigation' class='b-top-navigation-row' cellpadding=0>
<tr>
<td class='b-navigation-wrapper'>
EOF;
}

function end_nav() {
global $ibforums, $std;
return <<<EOF
</td>
<td class='b-top-nav-banner-wrapper'>
<!-- TOP NAV BANNER -->

<% TOP NAV BANNER %>
</td>
<!-- DONATE 5rub FORM -->
<td class='b-donate-wrapper'>
		<table class="donate">
		<col align="right"><col width="50">
		<tr>
			<td class="donate_text">
				<div>{$ibforums->lang['like_sources']}</div>
			</td>
			<td rowspan="2" class="donate_image">
				<a href="/donate/"><img src="img/5rub/5rub_gold.png" title="Помоги проекту"></a>
			</td>
		</tr>
		<tr>
			<td class="donate_button">
				<a href="/donate/">Помоги проекту!</a>
			</td>
		</tr>
		</table>
</td>
</tr>
</table>

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

<div align='left' class='rules-link-wrapper'>
  <span class='rules-title-image'><{F_RULES}></span>
  <a class='rules-link' href='{$ibforums->base_url}act=SR&amp;f={$rules['fid']}'>
    <span class='rules-title-text'><b>{$rules['title']}</b></span>
  </a>
</div>

EOF;
}

function forum_filter($data) {
global $ibforums;
return <<<EOF

<div class='b-forum-filter-wrapper tableborder'><div class='maintitle b-forum-filter'>
{$ibforums->lang['filter_text']}&nbsp;{$data}
</div></div>

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

function RenderDeletedRow($delete_level = 1 ) {
  global $ibforums;
  if ($delete_level == 2) {
    return <<<EOF
{$ibforums->lang['del_by_user']}
EOF;
  } else {
    return <<<EOF
<span class='movedprefix' style='font-size:10pt;line-height:100%'>{$ibforums->lang['mod_del']}</span>
EOF;
  }
}

function RenderScriptStatsRow($ex_time, $query_cnt, $timestamp, $sload) {
return <<<EOF
  <div class="b-script-execution-stats">[ Script execution time: {$ex_time} ] &nbsp; [ {$query_cnt} queries used ] &nbsp; [ Generated: {$timestamp} ] &nbsp; {$sload}</div>
EOF;
}

function renderActionButtons($actions, $list_classes = "", $item_classes = ""){
	$output = '<ul class="b-action-buttons ' . $list_classes . '">';
	foreach($actions as $class => $action)
		if(!empty($action))
			$output .= '<li class="b-action-button ' . (is_string($class) ? $class . ' ' : '' ) . $item_classes . '">' . $action . '</li>';
	$output .= "</ul>";
	return $output;
}

    /**
     * @deprecated
     * @param $unixtime
     * @param string $class
     * @return string
     */
function renderTime($unixtime, $class = '') {
	return '<time class="' . $class . '" datetime="' . date('c', $unixtime) . '">' . Ibf::app()->functions->get_date($unixtime) . '</time>';
}

function topicsListLegend(){
	$ibforums = Ibf::app();
	return <<<EOF
<div class="b-legend">
<div class="b-legend-block-wrapper">
	<ul class="b-legend-block">
	  <li class="b-legend-item"><span class="b-legend-item_image"><{B_NEW}></span><span class="b-legend-item_description">{$ibforums->lang["pm_open_new"]}</span></li>
	  <li class="b-legend-item"><span class="b-legend-item_image"><{B_NORM}></span><span class="b-legend-item_description">{$ibforums->lang["pm_open_no"]}</span></li>
	  <li class="b-legend-item"><span class="b-legend-item_image"><{B_HOT}></span><span class="b-legend-item_description">{$ibforums->lang["pm_hot_new"]}</span></li>
	  <li class="b-legend-item"><span class="b-legend-item_image"><{B_HOT_NN}></span><span class="b-legend-item_description">{$ibforums->lang["pm_hot_no"]}</span></li>
	  <li class="b-legend-item"><span class="b-legend-item_image"><{B_PIN}></span><span class="b-legend-item_description">{$ibforums->lang["pm_pin"]}</span></li>
	  <li class="b-legend-item"><span class="b-legend-item_image"><{B_MIRRORED}></span><span class="b-legend-item_description">{$ibforums->lang["pm_mirror"]}</span></li>
	  <li class="b-legend-item"><span class="b-legend-item_image"><{B_MIRRORED_NO}></span><span class="b-legend-item_description">{$ibforums->lang["pm_mirror_no"]}</span></li>
  </ul>
</div>

<div class="b-legend-block-wrapper">
	<ul class="b-legend-block">
	  <li class="b-legend-item"><span class="b-legend-item_image"><{B_POLL}></span><span class="b-legend-item_description">{$ibforums->lang["pm_poll"]}</span></li>
	  <li class="b-legend-item"><span class="b-legend-item_image"><{B_POLL_NN}></span><span class="b-legend-item_description">{$ibforums->lang["pm_poll_no"]}</span></li>
	  <li class="b-legend-item"><span class="b-legend-item_image"><{B_DECIDED}></span><span class="b-legend-item_description">{$ibforums->lang["pm_open_decided"]}</span></li>
	  <li class="b-legend-item"><span class="b-legend-item_image"><{B_LOCKED}></span><span class="b-legend-item_description">{$ibforums->lang["pm_locked"]}</span></li>
	  <li class="b-legend-item"><span class="b-legend-item_image"><{B_MOVED}></span><span class="b-legend-item_description">{$ibforums->lang["pm_moved"]}</span></li>
    </ul>
</div>
</div>
EOF;

}

function tags_MM($text){
	$title = Ibf::app()->lang['mod_mes'];
	return <<<EOF
<div class="tag-mm"><div class="tag-mm-header">{$title}</div><div class="tag-mm-body">{$text}</div></div>
EOF;
}

function tags_GM($text) {
	$title = Ibf::app()->lang['glob_mod_mes'];
	return <<<EOF
<div class="tag-gm"><div class='tag-gm-header'>{$title}</div><div class='tag-gm-body'>{$text}</div></div>
EOF;

}

function tags_ListUnordered($text){
	return <<<EOF
<ul class="tag-list">{$text}</ul>
EOF;
}

function tags_ListOrdered($text, $type){
return <<<EOF
<ol class="tag-list" type="{$type}">{$text}</ol>
EOF;
}

function tags_Mod($text) {
	return <<<EOF
<div class="tag-mod"><div class="tag-mod__prefix">M</div><div class="tag-mod__body">{$text}</div></div>
EOF;
}

function tags_Ex($text) {
	return <<<EOF
<div class="tag-ex"><div class="tag-ex__prefix">!</div><div class="tag-ex__body">{$text}</div></div>
EOF;
}

function tags_Size($value, $text) {
	return <<<EOF
<span class='tag-size' data-value='{$value}' style='font-size:{$value}pt;'>{$text}</span>
EOF;
}

function tags_Color($value, $text) {
	return <<<EOF
<span class="tag-color" data-value="{$value}" style="color: #{$value}">{$text}</span>
EOF;
}

function tags_ColorNamed($value, $text) {
	return <<<EOF
<span class="tag-color tag-color-named" data-value="{$value}" style="color: {$value}">{$text}</span>
EOF;
}

function tags_Font($value, $text) {
	return <<<EOF
<span class="tag-font" data-value="{$value}" style="font-family:{$value}">{$text}</span>
EOF;
}

function tags_spoiler_top($header) {
	return <<<EOF
<div class="tag-spoiler spoiler closed"><div class="spoiler_header" onclick="openCloseParent(this)">{$header}</div><div class="body">
EOF;
}

function tags_spoiler_bottom(){
	return <<<EOF
</div></div>
EOF;
}

function renderSelect($items, $selected, $attributes = []){
    array_walk($attributes, function(&$item, $key){ $item = sprintf('%s="%s"', $key, htmlentities($item)); });
    $output = '<select ' . implode(' ', $attributes) . '>';
    foreach($items as $value => $title) {
        $output .= sprintf('<option value="%s" %s>%s</option>', $value, $selected == $value ? 'selected' : '', $title);
    }
    $output .= '</selected>';
    return $output;
}

function wrapper(){
    ob_start();
    require __DIR__ . '/wrapper.inc';
    return ob_get_clean();
}
}
