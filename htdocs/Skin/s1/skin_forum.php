<?php

class skin_forum {



function show_sub_link($fid) {
global $ibforums;
return <<<EOF


&#0124; <a href='{$ibforums->base_url}act=Track&f=$fid&type=forum'>{$ibforums->lang['ft_title']}</a>


EOF;
}

function mod_checkbox($tid) {
global $ibforums;
return <<<EOF

<td class='row4 topic-column-mod_checkbox' align='center'><input type='checkbox' name='TID_$tid' value='1' class='forminput'></td>

EOF;
}

function RenderRow($data) {
global $ibforums;
$topic_classes = "topic-id-{$data['tid']} topic-author-{$data['starter_id']}";

foreach(['pinned', 'hidden', 'decided', 'club', 'closed', 'has_mirror', 'is_mirror', 'has_new', 'deleted', 'has_my_posts', 'mine', 'favorite'] as $key)
	if ($data[$key])
		$topic_classes .= ' topic-' . $key;
if ($data['state'] == 'moved' || $data['state'] == 'link') $topic_classes .= ' topic-moved';
if ($data['state'] == 'closed') $topic_classes .= ' topic-closed';
if ($data['poll_state']) $topic_classes .= ' topic-is_poll';

return <<<EOF

    <tr class="topic-row {$topic_classes}">
      <td class='row4 topic-column-status'><a href="{$ibforums->base_url}act=fav&topic={$data['tid']}" style="text-decoration:none" class="topic-status-link">{$data['folder_img']}</a></td>
      <td class='row2 topic-column-icon'>{$data['topic_icon']}</td>
      <td class='row4 topic-column-title'>{$data['go_new_post']}{$data['prefix']} <a class="topic-link" href='{$ibforums->base_url}showtopic={$data['tid']}'{$data['forum_title']}>{$data['title']}</a> {$data[PAGES]}
      <div class='desc'>{$data['description']}</span>{$data['queued_link']}</td>
      <td class='row2 topic-column-author'>{$data['starter']}</td>
      <td class='row4 topic-column-posts_num'>{$data['posts']}</td>
      <td class='row2 topic-column-views_num'>{$data['views']}</td>
      <td class='row2 topic-column-last_post'{$data['colspan']}><time datetime="{$data['last_post_std']}" class='last-post-date block'>{$data['last_post']}</time><span class='last-post-text'><a href='{$ibforums->base_url}showtopic={$data['tid']}&view=getlastpost'>{$data['last_text']}</a></span> <span class='last-post-author'>{$data['last_poster']}</span></td>
      {$data['mod_checkbox']}
    </tr>

EOF;
}

function who_link($tid, $posts) {
global $ibforums;
return <<<EOF

<a href='javascript:who_posted($tid);'>$posts</a>

EOF;
}


function show_no_matches() {
global $ibforums;
return <<<EOF

<tr> 
<td class='row4' colspan='7' align='center'><br>
<b>{$ibforums->lang['no_topics']}</b><br><br>
</td>
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


function show_rules($rules) {
global $ibforums;
return <<<EOF


<div class='tableborder'>
  <div class='maintitle'>{$rules['title']}</div>
  <div class='tablepad'>{$rules['body']}</div>
  <div class='pformstrip' align='center'>&gt;&gt;<a href='{$ibforums->base_url}act=SF&f={$rules['fid']}'>{$ibforums->lang['back_to_forum']}</a></div>
</div>


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



function Forum_log_in($data) {
global $ibforums;
return <<<EOF


<form action='{$ibforums->base_url}act=SF&f=$data' method='post'>
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


function show_page_jump($total, $pp, $qe) {
global $ibforums;
return <<<EOF


<a href="javascript:multi_page_jump( $total, $pp, '$qe' )" title="{$ibforums->lang['tpl_jump']}">{$ibforums->lang['multi_page_forum']}</a>


EOF;
}


function who_no_link($posts) {
global $ibforums;
return <<<EOF

{$posts}

EOF;
}

function mark_forum_read($data) {
global $ibforums;
return <<<EOF

<a href='{$ibforums->base_url}act=Login&CODE=04&f={$data['id']}'>{$ibforums->lang['mark_as_read']}</a>

EOF;
}

function last_mod_column() {
global $ibforums;
return <<<EOF

<th width='23%' class='titlemedium topic-column-last_post'>{$ibforums->lang['h_last_action']}</th>
<th width='4%' class='titlemedium topic-column-mod_checkbox'>{$ibforums->lang['h_mod_checkbox']}</th>

EOF;
}

function last_column() {
global $ibforums;
return <<<EOF

<th width='27%' class='titlemedium topic-column-last_post'>{$ibforums->lang['h_last_action']}</th>

EOF;
}


function PageTop($data) {
global $ibforums;
return <<<EOF

<script language='javascript' type='text/javascript'>
var js_base_url = "{$ibforums->js_base_url}";
</script>
<script type='text/javascript' src='html/forums.js?{$ibforums->vars['client_script_version']}'></script>
<table style='b-moderators-row' border=0 width="100%"><tr>
<td class='moderators' align='left'><span class='moderators-title title'>Модераторы:</span> <span class='moderators-list'>{$data['moderators']}</span></td>
<td class='quick-search' align='right'>{$data['quick_search']}</td></tr></table>
<!--IBF.SUBFORUMS-->
<a name='List'></a>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
 <td class='pages-list-wrapper' align='left' width="20%" nowrap="nowrap">{$data['SHOW_PAGES']}{$data['show_all_topics']}</td>
 <td class='new-buttons-wrapper' align='right' width="80%"><span class='new-topic-button'>{$data[TOPIC_BUTTON]}</span><span class='new-poll-button>'{$data[POLL_BUTTON]}</span></td>
</tr>
</table>
<div align='center' id='forum-subscribtion-buttons' class='b-forum-subscribtion-buttons'>{$data['mark_read']} <!--IBF.SUB_FORUM_LINK--></div>
{$data['filter']}
 <div class="tableborder topics-wrapper">
  <div class='maintitle forum-title'><{CAT_IMG}>&nbsp;{$data['name']}</div>
   {$data['modform_open']}
   <table width='100%' border='0' cellspacing='1' cellpadding='4' class='topics'>
    <thead>
    <tr class='darkrow2 topics-header'> 
     <th class='titlemedium topic-column-status'><img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' width='20' height='1'></td>
     <th class='titlemedium topic-column-icon'><img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' width='20' height='1'></td>
     <th width='45%' class='titlemedium topic-column-title'>{$ibforums->lang['h_topic_title']}</th>
     <th width='14%' class='titlemedium topic-column-author'>{$ibforums->lang['h_topic_starter']}</th>
     <th width='7%' class='titlemedium topic-column-posts_num'>{$ibforums->lang['h_replies']}</th>
     <th width='7%' class='titlemedium topic-column-views_num'>{$ibforums->lang['h_hits']}</th>
     {$data['last_column']}
    </tr>
	</thead>
EOF;
}


function show_mod_link($fid) {
global $ibforums;
return <<<EOF

<br><strong>{$ibforums->lang['post_modq']} <a href='{$ibforums->base_url}act=modcp'>{$ibforums->lang['post_click']}</a></strong>

EOF;
}


function modform_open($data) {
global $ibforums;
return <<<EOF

<form name='topic' action='{$ibforums->base_url}act=modcp&f={$data['id']}&view={$ibforums->input['view']}&prune_day={$ibforums->input['prune_day']}&sort_by={$ibforums->input['sort_by']}&sort_key={$ibforums->input['sort_key']}&st={$ibforums->input['st']}&CODE=topicchoice' method='post' onsubmit="return checkdelete('{$ibforums->lang['cp_js_delete']}');">

EOF;
}

function modform_close() {
global $ibforums;
return <<<EOF

<tr class='topics-mod-actions'>
<td class='darkrow3 topic-actions' colspan='3'>{$ibforums->lang['t_w_selected']}
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
</td>
<td class='darkrow3 other-actions' colspan='5'>{$ibforums->lang['other_funcs']}
<form action='{$ibforums->base_url}act=modcp&f={$ibforums->input['f']}' method='post'>
<select name='CODE' class='forminput'>
 <option value='rules_edit'>{$ibforums->lang['rules_edit']}</option>
 <option value='ip'>{$ibforums->lang['menu_ip']}</option>
 <option value='highlight'>{$ibforums->lang['cp_syntax']}</option>
 <option value='members'>{$ibforums->lang['menu_users']}</option>
 <option value='prune'>{$ibforums->lang['cp_prune_posts']}</option>
</select> &nbsp;<input type='submit' value='{$ibforums->lang['sort_submit']}' class='forminput'></form>
</td>
</tr>

EOF;
}



function TableEnd($data) {
global $ibforums;
return <<<EOF
<tfoot>
{$data['modform_close']}
</tfoot>
</table>
<!--IBF.FORUM_ACTIVE-->
<div align='center' class='darkrow2' style='padding:4px'>
 <form action='{$ibforums->base_url}act=SF&f={$data['id']}&view={$ibforums->input['view']}' method='post'>
  {$ibforums->lang['showing_text']}{$ibforums->lang['sort_text']}&nbsp;
  <input type='submit' value='{$ibforums->lang['sort_submit']}' class='forminput'>
 </form>
</div>

<div class=tablefooter><!-- --></div>

</div>
<br>
<table width='100%' cellpadding='0' cellspacing='0' border='0'>
<tr>
 <td align='left' width='20%' nowrap="nowrap">{$data['SHOW_PAGES']}</td>
 <td align='right' width='80%'>{$data[TOPIC_BUTTON]}{$data[POLL_BUTTON]}</td>
</tr>
</table>


<br>
<!--IBF.NAVIGATION-->
<br><br>
<table border="0" width="100%">
<tr>
<td width="30%">
  <{B_NEW}>&nbsp;&nbsp;{$ibforums->lang['pm_open_new']}
  <br><{B_NORM}>&nbsp;&nbsp;{$ibforums->lang['pm_open_no']}
  <br><{B_HOT}>&nbsp;&nbsp;{$ibforums->lang['pm_hot_new']}
  <br><{B_HOT_NN}>&nbsp;&nbsp;{$ibforums->lang['pm_hot_no']}
  <br><{B_PIN}>&nbsp;&nbsp;{$ibforums->lang['pm_pin']}
  <br><{B_MIRRORED}>&nbsp;&nbsp;{$ibforums->lang['pm_mirror']}
  <br><{B_MIRRORED_NO}>&nbsp;&nbsp;{$ibforums->lang['pm_mirror_no']}
  </td>
<td width="30%">
  <{B_POLL}>&nbsp;&nbsp;{$ibforums->lang['pm_poll']}
  <br><{B_POLL_NN}>&nbsp;&nbsp;{$ibforums->lang['pm_poll_no']}
  <br><{B_DECIDED}>&nbsp;&nbsp;{$ibforums->lang['pm_open_decided']}
  <br><{B_LOCKED}>&nbsp;&nbsp;{$ibforums->lang['pm_locked']}
  <br><{B_MOVED}>&nbsp;&nbsp;{$ibforums->lang['pm_moved']}
</td>
<td width="40%" valign="top">{$data[FORUM_JUMP]}
</td>
</tr>
</table>
<br>
<div align='center'><a href='{$ibforums->base_url}act=Login&CODE=04&f={$data['id']}'>{$ibforums->lang['mark_as_read']}</a> <!--IBF.SUB_FORUM_LINK--></div>
<br>
<br clear="all">


EOF;
}




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

function renderGoNewPostLink($topic){
	$ibf = Ibf::app();
	return "<a class='e-go_new_post-link' href='{$ibf->base_url}showtopic={$topic['tid']}&amp;view=getnewpost'><{NEW_POST}></a>";
}

function renderPinnedTopicPrefix() {
	return "<span class='pinnedprefix'>" . Ibf::app()->vars['pre_pinned'] . "</span> ";
}

function renderClubTopicPrefix() {
	return "<span class='clubprefix'>" . Ibf::app()->vars['pre_club'] . "</span> ";
}

function renderMarkSubforumRead($id) {
	$ibf = Ibf::app();
	return <<<EOF
	<div class='b-subforum-subscribtion-buttons'>
		<a class='e-mark_as_read-button' href='{$ibf->base_url}act=Login&amp;CODE=04&amp;f={$id}&amp;i=1'>{$ibf->lang['mark_as_read']}</a>
	</div>
EOF;
}

}
