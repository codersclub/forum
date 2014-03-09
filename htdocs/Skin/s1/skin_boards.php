<?php

class skin_boards {



function bottom_links() {
global $ibforums;
return <<<EOF
   <div id='BottomLinks'><a id='DeleteCookiesButtons' href="{$ibforums->base_url}act=Login&amp;CODE=06">{$ibforums->lang['d_delete_cookies']}</a> &middot; <a id="MarkReadAllButton" href="{$ibforums->base_url}act=Login&amp;CODE=05">{$ibforums->lang['d_post_read']}</a></div>

EOF;
}

function CatPlus($id) {
global $ibforums;
return <<<EOF

<a class="category-expand-button category-expand-button-{$id}" href={$ibforums->base_url}expcat={$id}><{C_PLUS}></a>&nbsp;

EOF;
}

function CatMinus($id) {
global $ibforums;
return <<<EOF

<a class="category-collapse-button category-collapse-button-{$id}" href={$ibforums->base_url}colcat={$id}><{C_MINUS}></a>&nbsp;


EOF;
}

function CatHeader_Collapsed($info,$plus = "") {
global $ibforums;
return <<<EOF

<div class="tableborder category category-{$info['id']} category-collapsed"> <div class='maintitle' align='left'>{$plus}<a class='title-link category-title-link' href="{$ibforums->base_url}c={$info['id']}">{$info['name']}</a></div></div>

EOF;
}

function CatHeader_Expanded($Data,$minus = "") {
global $ibforums;
return <<<EOF

 <div class="tableborder category category-{$Data['id']} category-expanded">
  <div class='maintitle' align='left'>{$minus}<a href="{$ibforums->base_url}c={$Data['id']}">{$Data['name']}</a></div>
    <table width="100%" border="0" cellspacing="1" cellpadding="4" class="forums-list">
		<tr class="forums-list-header">
    <th colspan="2" align="center" width="2%" class='titlemedium forum-image-header'><img border="0" src="{$ibforums->vars['img_url']}/spacer.gif" alt="" width="28" height="1"></th>
		<th align="left" width="59%" class='titlemedium forum-title-header'>{$ibforums->lang['cat_name']}</th>
    <th align="center" width="7%" class='titlemedium forum-topics-header'>{$ibforums->lang['topics']}</th>
    <th align="center" width="7%" class='titlemedium forum-replies-header'>{$ibforums->lang['replies']}</th>
    <th align="left" width="25%" class='titlemedium forum-lastpost-header'>{$ibforums->lang['last_post_info']}</th>
    </tr>

EOF;
}

function ShowAllLink() {
global $ibforums;
return <<<EOF

<a id='ShowAllLink' class='show-all-link' href='{$ibforums->base_url}&show=all'>{$ibforums->lang['show_all_forums']}</a>

EOF;
}

function PageTop($show_all = "") {
global $ibforums;
return <<<EOF

<!--GLOBAL.MESSAGE-->
<table border=0 width='100%' id='QuickLinks'>
<tr id='SecondNewsRow' class='news-row'>
 <td align='left' class='news-wrapper news-second-wrapper'><!--IBF.SECONDNEWSLINK--></td>
 <td align='right' class='quick-log-in-wrapper'><!--IBF.QUICK_LOG_IN--></td>
 </tr>
<tr id='FirstNewsRow' class='news-row'>
 <td align='left' class='news-wrapper news-first-wrapper'><!--IBF.NEWSLINK--></td>
 <td><!-- --></td>
 </tr>
 <tr id='PollsRow' class='polls-row'>
 <td align='left' class='polls-wrapper'><!--IBF.OUR_POLL_LINK--></td>
 <td align='right' id='ShowAllWrapper' class='show-all-wrapper'>{$show_all}</td>
</tr>
</table>

EOF;
}

function quick_log_in() {
global $ibforums;
return <<<EOF

<div align="right" id="QuickLogin"><span id="QuickLoginTitle" class="title"><strong>{$ibforums->lang["qli_title"]}</strong></span>
<form id="QuickLoginForm" style="display:inline" action="{$ibforums->base_url}" method="post">
<input type="hidden" name="act" value="Login">
<input type="hidden" name="CODE" value="01">
<input type="hidden" name="CookieDate" value="1">
<input class="forminput username" size="14" name="UserName" placeholder="{$ibforums->lang['qli_name']}">
<input type="password" class="forminput password" size="14" name="PassWord" placeholder="{$ibforums->lang['qli_pass']}">
<input type="submit" class="forminput button" value="{$ibforums->lang['qli_go']}">
</form>
</div>

EOF;
}

function birthdays($birthusers="", $total="", $birth_lang="") {
global $ibforums;
return <<<EOF

	<tr id="BirthsdaysTitleRow">
		<td class='pformstrip' id='BirthsdaysTitle' colspan='2'>{$ibforums->lang['birthday_header']}</td>
  </tr>
	<tr id="BirthsdaysRow">
		<td class='row2 block-image' width='5%' valign='middle'><{F_ACTIVE}></td>
		<td class='row4' id="Birthsdays" width='95%'><div class='birthsdays-title title'>$birth_lang</div><div class='birthsdays-text'>$birthusers</div></td>
  </tr>

EOF;
}

function stats_header() {
global $ibforums;
return <<<EOF

	<div class='stats-links'>
		<a class='administration-link' href='{$ibforums->base_url}act=Stats&amp;CODE=leaders'>{$ibforums->lang['sm_forum_leaders']}</a> |
		<a class='activity-link' href='{$ibforums->base_url}act=Select&amp;CODE=getactive'>{$ibforums->lang['sm_todays_posts']}</a> |
		<a class='today-10-link' href='{$ibforums->base_url}act=Stats'>{$ibforums->lang['sm_today_posters']}</a> |
		<a class='top-10-link' href='{$ibforums->base_url}act=Members&amp;max_results=10&amp;sort_key=posts&amp;sort_order=desc'>{$ibforums->lang['sm_all_posters']}</a>
	</div>
	<div class="tableborder board-stats">
		<div class="maintitle">{$ibforums->lang['board_stats']}</div>
		<table cellpadding='4' cellspacing='1' border='0' width='100%'>

EOF;
}

function ActiveFriends($active) {
global $ibforums;
return <<<EOF

<div id='FriendsOnline' class='friends-online'>
<span class='friends-title'>{$ibforums->lang['your_friends']}</span>
<span class='friends-list'>{$active[FRIENDS]}</span>
</div>
<hr>

EOF;
}

function ActiveUsers($active, $friends = "") {
global $ibforums;
//todo явно прописанные группы пользователей
return <<<EOF
	<tr class='online-summary-row'>
		<td class='pformstrip online-summary' colspan='2'>$active[TOTAL] {$ibforums->lang['active_users']}</td>
  </tr>
  <tr class='online-detailed-row'>
		<td width="5%" class='row2 block-image'><{F_ACTIVE}></td>
		<td class='row4 online-detailed' width='95%'>
      <span class='guests-online-total'><b>{$active['GUESTS']}</b> {$ibforums->lang['guests']}</span>, <span class='members-online-total'><b>{$active['MEMBERS']}</b> {$ibforums->lang['public_members']} <b>{$active['ANON']}</b> {$ibforums->lang['anon_members']}</span>
      <div class='thin'>{$friends}<div class='members-online'>{$active['NAMES']}</div>
      <div class='members-categories'>[<a href="{$ibforums->base_url}act=Members&max_results=30&filter=4&sort_order=asc&sort_key=name&st=0"><span class='movedprefix'>администраторы</span></a>,&nbsp;<a href="{$ibforums->base_url}act=Members&max_results=30&filter=7&sort_order=asc&sort_key=name&st=0"><span style='color:blue'>модераторы</span></a>,&nbsp;<a href="{$ibforums->base_url}act=Members&max_results=30&filter=26&sort_order=asc&sort_key=name&st=0"><span style='color:purple'>ветераны</span></a>,&nbsp;<a href="{$ibforums->base_url}act=Members&max_results=30&filter=9&sort_order=asc&sort_key=name&st=0"><span class='voteprefix'>координаторы проектов</span></a>,&nbsp;<a href="{$ibforums->base_url}act=Members&max_results=30&filter=25&sort_order=asc&sort_key=name&st=0"><span style='color:navy'>участники клуба</span></a>,&nbsp;<a href="{$ibforums->base_url}act=Members&max_results=30&filter=3&sort_order=asc&sort_key=name&st=0">участники</a>,&nbsp;<span style='color:gray'>наказанные</span>]</div></div>
      {$active['links']}
    </td>
  </tr>

EOF;
}

function active_user_links() {
global $ibforums;
return <<<EOF

<div class='online-detailed-more-links' id="OnlineShowMoreWrapper">
<span id='OnlineShowMoreTitle' class='title online-show-more-title'>{$ibforums->lang['oul_show_more']}</span>
<a id='ShowOnlineByActions' class='show-online-by-actions-link' href='{$ibforums->base_url}act=Online&amp;CODE=listall&amp;sort_key=click'>{$ibforums->lang['oul_click']}</a>,
<a id='ShowOnlineByName' class='show-online-by-name-link' href='{$ibforums->base_url}act=Online&amp;CODE=listall&amp;sort_key=name&amp;sort_order=asc&amp;show_mem=reg'>{$ibforums->lang['oul_name']}</a>
</div>

EOF;
}

function stats_footer() {
global $ibforums;
return <<<EOF

  <TR>
    <TD class=tablefooter colspan=2><!-- -->
    </td>
  </TR>

</table>
 </div>

EOF;
}

function forum_redirect_row($info) {
global $ibforums;
return <<<EOF

       <tr class="forum-row forum-{$info['id']} forum-redirect">
         <td {$info[colspan]}class="row4 forum-image" align="center"><{BR_REDIRECT}></td>
         <td class="row4 forum-title"><b><a href="{$ibforums->base_url}showforum={$info['id']}" {$info['redirect_target']}>{$info['name']}</a></b><div class='desc'>{$info['description']}</div></td>
         <td class="row2 forum-topics" align="center">-</td>
         <td class="row2 forum-replies" align="center">-</td>
         <td class="row2 forum-lastpost">{$ibforums->lang['rd_hits']}: {$info['redirect_hits']}</td>
       </tr>

EOF;
}

function show_global_message($message) {
global $ibforums;
return <<<EOF

<table  width="100%" cellspacing="6" id="GlobalMessage">
<tr>
 <td>
<center>
{$message}
</center>
 </td>
</tr>
</table>

EOF;
}

function ForumRow($info) {
global $ibforums;
return <<<EOF

<tr class="forum-row forum forum-{$info['id']}">
 {$info['tree']}
 <td class="row2 forum-title"><b><a href="{$ibforums->base_url}showforum={$info['id']}">{$info['name']}</a></b> <span class='desc'>{$info['description']}</div></td>
 <td class="row4 forum-topics" align="center">{$info['topics']}</td>
 <td class="row4 forum-replies" align="center">{$info['posts']}</td>
 <td class="row2 forum-lastpost"><time class='block' datetime='{$info['last_post_std']}'>{$info['last_post']}</time><div class='b-last-topic-row'>{$ibforums->lang['in']}: {$info['last_topic']}</div><div class='b-poster-row'>{$ibforums->lang['by']}: {$info['last_poster']}</div></td>
</tr>

EOF;
}

function newslink($fid="", $title="", $tid="") {
global $ibforums;
return <<<EOF
<div class='news news-first'><b><span class='news-header news-first-header'>{$ibforums->lang['newslink']}</span> <a class='news-link news-first-link' href='{$ibforums->base_url}showtopic=$tid&view=getnewpost'><span class='voteprefix'>$title</span></a></b></div>

EOF;
}

function secondnewslink($fid="", $title="", $tid="") {
global $ibforums;
return <<<EOF

<div class='news news-second'><b><span class='news-header news-second-header'>{$ibforums->lang['secondnewslink']}</span> <a class='news-link news-second-link' href='{$ibforums->base_url}showtopic=$tid&view=getnewpost'><span style='color:blue'>$title</span></a></b><br></div>

EOF;
}

function our_poll_link($fid="", $title="", $tid="") {
global $ibforums;
return <<<EOF

<div class='poll'><b><span class='poll-header'>{$ibforums->lang['our_polls_link']}</span> <a class='poll-link' href='{$ibforums->base_url}showtopic=$tid&view=getnewpost'><span style='color:blue'>$title</span></a></b><br></div>

EOF;
}

function forum_img_with_link($img, $id) {
global $ibforums;
return <<<EOF

<a class='forum-mark-read-link' href='{$ibforums->base_url}act=Login&amp;CODE=04&amp;f={$id}' title='{$ibforums->lang['bi_markread']}' style='text-decoration:none'>{$img}</a>

EOF;
}

function calendar_events($events = "") {
global $ibforums;
return <<<EOF

<tr class='calendar-events-title-row'>
    <td class='pformstrip calendar-events-title' colspan='2'>{$ibforums->lang['calender_f_title']}</td>
    	</tr>
      <tr class='calendar-events-row'>
          <td class='row2 block-image' width='5%' valign='middle'><{F_ACTIVE}></td>
          <td class='row4 calendar-events' width='95%'>$events</td>
        </tr>

EOF;
}

//looks like unused
function ShowStats($text) {
global $ibforums;
return <<<EOF

<tr class='stats-title-row'>
  <td class='pformstrip stats-title-row' colspan='2'>{$ibforums->lang['board_stats']}</td>
</tr>
<tr class='stats-row'>
  <td class='row2 block-image' width='5%' valign='middle'><{F_STATS}></td>
  <td class='row4 stats' width="95%" align='left'>$text</td>
</tr>

EOF;
}

function TodayOnline($activity) {
global $ibforums;
return <<<EOF

<tr class='online-stats-title-row'>
  <td class='pformstrip online-stats-title' colspan='2'>{$ibforums->lang['today_online']}</td>
</tr>
<tr class='online-stats-row'>
  <td class='row2 block-image' width='5%' valign='middle'><{F_STATS}></td>
  <td class='row4 online-stats' width="95%" align='left'><div class="online-stats-today">{$activity}</div><div class='thin online-stats-record'>
{$ibforums->lang['online_record']}<br>{$ibforums->lang['category_record']}</div><div class='online-stats-peak'>{$ibforums->lang['most_online']}</div></td>
</tr>

EOF;
}

function forumrow_lastunread_link($fid, $tid) {
global $ibforums;
return <<<EOF

<a class='forum-last-unread-link' href='{$ibforums->base_url}showtopic=$tid&amp;view=getlastpost' title='{$ibforums->lang['tt_golast']}'><{LAST_POST}></a>

EOF;
}

function end_all_cats() {
global $ibforums;
return <<<EOF



EOF;
}

/**
 * @todo achtung Результат этой функции идёт в preg_replace в качестве регулярки
 */
function active_list_sep() {
global $ibforums;
return <<<EOF

,

EOF;
}

function end_this_cat() {
global $ibforums;
return <<<EOF

  <TR class='category-footer-row'>
    <TD class='tablefooter' colspan=6><!-- -->
    </td>
  </TR>

      </table>
    </div>

EOF;
}

function subforum_img_with_link($img, $id) {
global $ibforums;
return <<<EOF

<a class='forum-mark-all-read-link' href='{$ibforums->base_url}act=Login&amp;CODE=04&amp;f={$id}&amp;i=1' title='{$ibforums->lang['bi_markallread']}' style='text-decoration:none'>{$img}</a>

EOF;
}

function subheader($fid) {
global $ibforums;
return <<<EOF

{$fid}
 <div class="tableborder b-subforums-wrapper">
   <table width="100%" border="0" cellspacing="1" cellpadding="4" class='b-subforums'>
   <thead>
   <tr class='forums-list-header'>
  <th colspan="2" align="center" class='titlemedium forum-image-header'><img border="0" src="{$ibforums->vars['img_url']}/spacer.gif" alt="" width="28" height="1"></th>
  <th align='left' width="59%" class='titlemedium forum-title-header'>{$ibforums->lang['cat_name']}</th>
  <th align="center" width="7%" class='titlemedium forum-topics-header'>{$ibforums->lang['topics']}</th>
  <th align="center" width="7%" class='titlemedium forum-replies-header'>{$ibforums->lang['replies']}</th>
  <th align='left' width="27%" class='titlemedium forum-lastpost-header'>{$ibforums->lang['last_post_info']}</th>
   </tr>
   </thead>

EOF;
}


}
