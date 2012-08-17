<?php

class skin_forum {


function render_pinned_start() {
global $ibforums;
return <<<EOF
    <tr>
      <td align='center' class='darkrow1'>&nbsp;</td>
      <td align='center' class='darkrow1'>&nbsp;</td>
	  <td align='left' class='darkrow1' colspan='5' style='padding:6px'><b>{$ibforums->lang['pinned_start']}</b></td>
    </tr>

EOF;
}


function quick_search($data) {
global $ibforums;
return <<<EOF

<div align='right'>
	<form action='{$ibforums->base_url}' method='post' name='search'>
		<input type='hidden' name='forums' value='{$data['id']}'>
		<input type='hidden' name='cat_forum' value='forum'>
		<input type='hidden' name='act' value='Search'>
		<input type='hidden' name='joinname' value='1'>
		<input type='hidden' name='searchsubs' value='1'>
		<input type='hidden' name='CODE' value='01'>
		{$ibforums->lang['search_forum']}&nbsp;
		<input type='text' size='30' name='keywords' class='forminput' placeholder='{$ibforums->lang['enter_keywords']}'>
		<input type='submit' value='{$ibforums->lang['search_go']}' class='forminput'>
	</form>
</div>

EOF;
}


function show_sub_link($fid) {
global $ibforums;
return <<<EOF

&#0124; <a href='{$ibforums->base_url}act=Track&amp;f=$fid&amp;type=forum'>{$ibforums->lang['ft_title']}</a>

EOF;
}


function page_title($title="", $pages="") {
global $ibforums;
return <<<EOF

<div><span class='pagetitle'>$title</span>$pages</div>

EOF;
}


function forum_active_users($active=array()) {
global $ibforums;
return <<<EOF

<div class='darkrow2' style='padding:6px'>{$ibforums->lang['active_users_title']} ({$ibforums->lang['active_users_detail']})</div>
	  <div class='row2' style='padding:6px'>{$ibforums->lang['active_users_members']} {$active['names']}</div>

EOF;
}


function Forum_log_in($data) {
global $ibforums;
return <<<EOF

<form action='{$ibforums->base_url};act=SF&amp;f=$data' method='post'>
<input type='hidden' name='act' value='SF'>
<input type='hidden' name='f' value='$data'>
<input type='hidden' name='L' value='1'>
<input type='hidden' name='s' value='{$ibforums->session_id}'>
<div class='tableborder'>
  <div class='maintitle'><{CAT_IMG}>&nbsp;{$ibforums->lang['need_password']}</div>
  <div class='tablepad'>{$ibforums->lang['need_password_txt']}</div>
  <div class='tablepad' style='text-align:center'>
    <strong>{$ibforums->lang['enter_pass']}</strong>
    <br>
    <input type='password' size='20' name='f_password'>
  </div>
  <div class='pformstrip' align='center'><input type='submit' value='{$ibforums->lang['f_pass_submit']}' class='forminput'></div>
</div>
</form>

EOF;
}

function mark_forum_read($data) {
global $ibforums;
return <<<EOF

<a href='{$ibforums->base_url}act=Login&amp;CODE=04&amp;f={$data['id']}'>{$ibforums->lang['mark_as_read']}</a>

EOF;
}

function PageTop($data) {
global $ibforums;
return <<<EOF

<script language='javascript' type='text/javascript'>
var js_base_url = "{$ibforums->js_base_url}";
</script>
<script type='text/javascript' src='html/forums.js'></script>
Координаторы: {$data['moderators']}
<br>
<!--IBF.SUBFORUMS-->
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
 <td align='left' width="20%" nowrap="nowrap">{$data['SHOW_PAGES']}</td>
 <td align='right' width="80%">{$data[TOPIC_BUTTON]}{$data[POLL_BUTTON]}</td>
 <div align='right'>
    <form action='{$ibforums->base_url}' method='post' name='search'>
	<input type='hidden' name='forums' value='{$data['id']}'>
	<input type='hidden' name='cat_forum' value='forum'>
	<input type='hidden' name='act' value='Search'>
	<input type='hidden' name='joinname' value='1'>
	<input type='hidden' name='searchsubs' value='1'>
	<input type='hidden' name='CODE' value='01'>
	{$ibforums->lang['search_forum']}&nbsp;
	<input type='text' size='30' name='keywords' class='forminput' value='{$ibforums->lang['enter_keywords']}' onfocus='this.value = "";'>
	<input type='submit' value='{$ibforums->lang['search_go']}' class='forminput'>
   </form>
</div>
</tr>
</table><br>
{$data['filter']}
<div class="tableborder">
 <div class='maintitle'><{CAT_IMG}>&nbsp;{$data['name']}</div>
  {$data['modform_open']}
  <table width='100%' border='0' cellspacing='1' cellpadding='4'>
   <tr>{$data['last_column']}</tr>

EOF;
}


function last_mod_column() {
global $ibforums;
return <<<EOF

<td width='20'align='center' class='titlemedium'><img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' height='1'></td>
<td width='20' align='center' class='titlemedium'><img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' height='1'></td>
<th width='76%' align='left' nowrap="nowrap" class='titlemedium'>{$ibforums->lang['h_topic_title']}</th>
<th width='7%' align='center' nowrap="nowrap" class='titlemedium'>{$ibforums->lang['h_hits']}</th>
<th width='13%' align='center' nowrap="nowrap" class='titlemedium'>{$ibforums->lang['h_topic_starter']}</th>
<th width='4%' align='center' class='titlemedium'>{$ibforums->lang['h_mod_checkbox']}</th>

EOF;
}

function last_column() {
global $ibforums;
return <<<EOF

<td width='20' align='center' class='titlemedium'><img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' height='1'></td>
<td width='20' align='center' class='titlemedium'><img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' height='1'></td>
<th width='80%' align='left' nowrap="nowrap" class='titlemedium'>{$ibforums->lang['h_topic_title']}</th>
<th width='7%' align='center' nowrap="nowrap" class='titlemedium'>{$ibforums->lang['h_hits']}</th>
<th width='13%' align='center' nowrap="nowrap" class='titlemedium'>{$ibforums->lang['h_topic_starter']}</th>

EOF;
}

function modform_open($data) {
global $ibforums;
return <<<EOF

<form name='topic' action='{$ibforums->base_url}act=modcp&amp;f={$data['id']}&amp;view={$ibforums->input['view']}&amp;prune_day={$ibforums->input['prune_day']}&amp;sort_by={$ibforums->input['sort_by']}&amp;sort_key={$ibforums->input['sort_key']}&amp;st={$ibforums->input['st']}&amp;CODE=topicchoice' method='post' onsubmit="return checkdelete('{$ibforums->lang['cp_js_delete']}');">

EOF;
}

function modform_close() {
global $ibforums;
return <<<EOF

<tr>
<td class='darkrow3' colspan='6'>{$ibforums->lang['t_w_selected']}
<select name='tact' class='forminput'>
 <option value='close'>{$ibforums->lang['cpt_close']}</option>
 <option value='open'>{$ibforums->lang['cpt_open']}</option>
 <option value='pin'>{$ibforums->lang['cpt_pin']}</option>
 <option value='unpin'>{$ibforums->lang['cpt_unpin']}</option>
 <option value='move'>{$ibforums->lang['cpt_move']}</option>
 <option value='delete'>{$ibforums->lang['cpt_delete']}</option>
 <option value='approve'>{$ibforums->lang['cpt_approve']}</option>
 <option value='decline'>{$ibforums->lang['cpt_decline']}</option>
 <option value='hide'>{$ibforums->lang['cpt_hide']}</option>
 <option value='show'>{$ibforums->lang['cpt_show']}</option>
</select> &nbsp;<input type='submit' value='{$ibforums->lang['sort_submit']}' class='forminput'></form>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<form action='{$ibforums->base_url}act=modcp&f={$ibforums->input['f']}' method='post'>
<select name='CODE' class='forminput'>
 <option value='rules_edit'>{$ibforums->lang['rules_edit']}</option>
 <option value='ip'>{$ibforums->lang['menu_ip']}</option>
 <option value='highlight'>{$ibforums->lang['cp_syntax']}</option>
 <option value='members'>{$ibforums->lang['menu_users']}</option>
 <option value='prune'>{$ibforums->lang['cp_prune_posts']}</option>
</select> &nbsp;<input type='submit' value='{$ibforums->lang['sort_submit']}' class='forminput'></form>
</td>
</tr>

</div>

EOF;
}

function TableEnd($data) {
global $ibforums;
return <<<EOF
{$data['modform_close']}
</table>
<!--IBF.FORUM_ACTIVE-->
<div align='center' class='darkrow2' style='padding:4px'>
 <form action='{$ibforums->base_url}act=SF&amp;f={$data['id']}&amp;view={$ibforums->input['view']}' method='post'>
  {$ibforums->lang['showing_text']}{$ibforums->lang['sort_text']}&nbsp;<input type='submit' value='{$ibforums->lang['sort_submit']}' class='forminput'>
 </form>
</div>
</div>
<br>
<table width='100%' cellpadding='0' cellspacing='0' border='0'>
<tr>
 <td align='left' width='20%' nowrap='nowrap'>{$data['SHOW_PAGES']}</td>
 <td align='right' width='80%'>{$data[TOPIC_BUTTON]}{$data[POLL_BUTTON]}</td>
</tr>
</table>
<div align='right'>
{$data[FORUM_JUMP]}
</div>

EOF;
}

function mod_checkbox($class, $tid) {
global $ibforums;
return <<<EOF

<td class='$class' align='center'><input type='checkbox' name='TID_$tid' value='1' class='forminput' onclick="cca(this,'darkrow2');"></td>

EOF;
}

function show_mod_link($fid) {
global $ibforums;
return <<<EOF

<br>
<strong>{$ibforums->lang['post_modq']} <a href='{$ibforums->base_url}act=modcp'>{$ibforums->lang['post_click']}</a></strong>

EOF;
}


function show_rules($rules) {
global $ibforums;
return <<<EOF

<div class='tableborder'>
  <div class='maintitle'>{$rules['title']}</div>
  <div class='tablepad'>{$rules['body']}</div>
  <div class='pformstrip' align='center'>&gt;&gt;<a href='{$ibforums->base_url}act=SF&amp;f={$rules['fid']}'>{$ibforums->lang['back_to_forum']}</a></div>
</div>

EOF;
}

function render_pinned_row($data) {
global $ibforums;
return <<<EOF

    <tr> 
      <td align='center' class='row4'><a href="{$ibforums->base_url}act=fav&topic={$data['tid']}" style="text-decoration:none">{$data['folder_img']}</a></td>
      <td align='center' class='row2'>{$data['topic_icon']}</td>
      <td class='{$data['color']}'>{$data['go_new_post']}{$data['prefix']} <a href='{$ibforums->base_url}showtopic={$data['tid']}'{$data['forum_title']}>{$data['title']}</a> {$data[PAGES]}
      <br><span class='desc'>{$data['description']}</span>{$data['queued_link']}</td>
      <td align='center' class='row2'>{$data['views']}</td>
      <td align='center' class='row4'>{$data['starter']}</td>
      {$data['mod_checkbox']}
    </tr>

EOF;
}

function RenderRow($data) {
global $ibforums;
return <<<EOF

    <tr> 
      <td align='center' class='row4'><a href="{$ibforums->base_url}act=fav&topic={$data['tid']}" style="text-decoration:none">{$data['folder_img']}</a></td>
      <td align='center' class='row2'>{$data['topic_icon']}</td>
      <td class='{$data['color']}'>{$data['go_new_post']}{$data['prefix']} <a href='{$ibforums->base_url}showtopic={$data['tid']}'{$data['forum_title']}>{$data['title']}</a> {$data[PAGES]}
      <br><span class='desc'>{$data['description']}</span>{$data['queued_link']}</td>
      <td align='center' class='row2'>{$data['views']}</td>
      <td align='center' class='row4'>{$data['starter']}</td>
      {$data['mod_checkbox']}
    </tr>

EOF;
}


function render_pinned_end() {
global $ibforums;
return <<<EOF
    <tr>
      <td align='center' class='darkrow1'>&nbsp;</td>
      <td align='center' class='darkrow1'>&nbsp;</td>
	  <td align='left' class='darkrow1' colspan='5' style='padding:6px'><b>{$ibforums->lang['regular_topics']}</b></td>
    </tr>

EOF;
}


function show_page_jump($total, $pp, $qe) {
global $ibforums;
return <<<EOF

<a href="javascript:multi_page_jump( $total, $pp, '$qe' )" title="{$ibforums->lang['tpl_jump']}">{$ibforums->lang['multi_page_forum']}</a>

EOF;
}


function show_no_matches() {
global $ibforums;
return <<<EOF

<tr> 
					<td class='row4' colspan='7' align='center'>
						<br>
                         <b>{$ibforums->lang['no_topics']}</b>
						<br><br>
					</td>
        </tr>

EOF;
}


function who_link($tid, $posts) {
global $ibforums;
return <<<EOF

<a href='javascript:who_posted($tid);'>$posts</a>

EOF;
}


function who_no_link($posts) {
global $ibforums;
return <<<EOF

{$posts}

EOF;
}


}
?>
