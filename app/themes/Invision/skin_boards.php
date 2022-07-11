<?php

class skin_boards {



function bottom_links() {
global $ibforums;
return <<<EOF
   <div id="BottomLinks"><a id="DeleteCookiesButtons" href="{$ibforums->base_url}act=Login&amp;CODE=06">{$ibforums->lang['d_delete_cookies']}</a> &middot; <a id="MarkReadAllButton" href="{$ibforums->base_url}act=Login&amp;CODE=05">{$ibforums->lang['d_post_read']}</a></div>

EOF;
}

function CatPlus($id) {
global $ibforums;
return <<<EOF
<a class="category-expand-button" data-id="$id" href={$ibforums->base_url}expcat={$id}><{C_PLUS}></a>
EOF;
}

function CatMinus($id) {
global $ibforums;
return <<<EOF
<a class="category-collapse-button" data-id="$id" href={$ibforums->base_url}colcat={$id}><{C_MINUS}></a>
EOF;
}

function CatHeader_Collapsed($info,$plus = "") {
global $ibforums;
return <<<EOF

<div class="b-category" data-category-id="{$info['id']}" data-status="collapsed">
	<h2>{$plus}<a class="b-category-title-link" href="{$ibforums->base_url}c={$info['id']}">{$info['name']}</a></h2>
	<div class="b-category-footer"></div>
</div>

EOF;
}

function CatHeader_Expanded($Data,$minus = "") {
global $ibforums;
return <<<EOF

 <div class="tableborder b-category b-category-{$Data['id']} b-category-expanded" data-category-id="{$Data['id']}" >
  <h2>{$minus}<a class="b-category-title-link" href="{$ibforums->base_url}c={$Data['id']}">{$Data['name']}</a></h2>
    <table class="b-forums-list">
        <thead>
		<tr class="b-header-row">
	        <th colspan="2" align="center" width="2%" class="b-column-icon"><img src="{$ibforums->skin['ImagesPath']}/spacer.gif" alt="" width="28" height="1"></th>
			<th width="59%" class="b-column b-column_title">{$ibforums->lang['cat_name']}</th>
		    <th width="7%" class="b-column b-column_topics">{$ibforums->lang['topics']}</th>
		    <th width="7%" class="b-column b-column_replies">{$ibforums->lang['replies']}</th>
		    <th width="25%" class="b-column b-column_lastpost">{$ibforums->lang['last_post_info']}</th>
	    </tr>
	    </thead>

EOF;
}

function ShowAllLink() {
global $ibforums;
return <<<EOF

<a id="ShowAllLink" class="b-show-all-link" href="{$ibforums->base_url}&show=all">{$ibforums->lang['show_all_forums']}</a>

EOF;
}

    /**
     * @deprecated
     * @param string $show_all
     * @return string
     */
function PageTop($show_all = "") {
global $ibforums;
return <<<EOF

<table id="QuickLinks" class="b-news-wrapper">
<tr id="SecondNewsRow" class="b-news-row">
 <td class="news-wrapper news-second-wrapper"><!--IBF.SECONDNEWSLINK--></td>
 <td align="right" class="b-quick-login-wrapper"><!--IBF.QUICK_LOG_IN--></td>
 </tr>
<tr id="FirstNewsRow" class="b-news-row">
 <td class="news-wrapper news-first-wrapper"><!--IBF.NEWSLINK--></td>
 <td><!-- --></td>
 </tr>
 <tr id="PollsRow" class="b-polls-row b-news-row">
 <td class="polls-wrapper"><!--IBF.OUR_POLL_LINK--></td>
 <td id="ShowAllWrapper" class="show-all-wrapper">{$show_all}</td>
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
		<td class="pformstrip" id="BirthsdaysTitle" colspan="2">{$ibforums->lang['birthday_header']}</td>
  </tr>
	<tr id="BirthsdaysRow">
		<td class="row2 block-image" width="5%" valign="middle"><{F_ACTIVE}></td>
		<td class="row4" id="Birthsdays" width="95%"><div class="birthsdays-title title">$birth_lang</div><div class="birthsdays-text">$birthusers</div></td>
  </tr>

EOF;
}

function stats_header() {
global $ibforums;
return <<<EOF

	<div class="stats-links">
		<a class="administration-link" href="{$ibforums->base_url}act=Stats&amp;CODE=leaders">{$ibforums->lang["sm_forum_leaders"]}</a> |
		<a class="activity-link" href="{$ibforums->base_url}act=Select&amp;CODE=getactive">{$ibforums->lang['sm_todays_posts']}</a> |
		<a class="today-10-link" href="{$ibforums->base_url}act=Stats">{$ibforums->lang['sm_today_posters']}</a> |
		<a class="top-10-link" href="{$ibforums->base_url}act=Members&amp;max_results=10&amp;sort_key=posts&amp;sort_order=desc">{$ibforums->lang['sm_all_posters']}</a>
	</div>
	<div class="tableborder board-stats">
		<div class="maintitle">{$ibforums->lang['board_stats']}</div>
		<table cellspacing="1">

EOF;
}

function ActiveFriends($active) {
global $ibforums;
return <<<EOF

<div id="FriendsOnline" class="friends-online">
<span class="friends-title">{$ibforums->lang['your_friends']}</span>
<span class="friends-list">{$active['FRIENDS']}</span>
</div>

EOF;
}

function ActiveUsers($active, $friends = "") {
global $ibforums;

return <<<EOF
	<tr class="online-summary-row">
		<td class="pformstrip online-summary" colspan="2">{$active['TOTAL']} {$ibforums->lang['active_users']}</td>
  </tr>
  <tr class="online-detailed-row">
		<td width="5%" class="row2 block-image"><{F_ACTIVE}></td>
		<td class="row4 online-detailed" width="95%">
      <span class="guests-online-total"><b>{$active['GUESTS']}</b> {$ibforums->lang['guests']}</span>, <span class="members-online-total"><b>{$active['MEMBERS']}</b> {$ibforums->lang['public_members']} <b>{$active['ANON']}</b> {$ibforums->lang['anon_members']}</span>
      <div class="thin">{$friends}<div class="members-online">{$active['NAMES']}</div>
      <div class="members-groups-list-wrapper">{$active['groups_list_html']}</div></div>
      {$active['links']}
    </td>
  </tr>
EOF;
}

function renderMemberGroupsList($groups, $add_offenders = TRUE) {
	$output = '<ul class="members-groups-list">';
	foreach($groups as $group) {
		$output .= '<li>' . $this->renderMemberGroup($group) . '</li>';
	}
	if($add_offenders)
		{
			$output .= '<li><span class="group-offenders">' . Ibf::app()->lang['group_offenders'] . '</span></li>';
		}
	$output .= '</ul>';
	return $output;
EOF;
}

function renderMemberGroup($group) {
	$ibforums = Ibf::app();
	return <<<EOF
	<a href="{$ibforums->base_url}act=Members&max_results=30&filter={$group['g_id']}&sort_order=asc&sort_key=name&st=0">{$group['prefix']}{$group['g_title']}{$group['suffix']}</a>
EOF;
}

function active_user_links() {
global $ibforums;
return <<<EOF

<div class="online-detailed-more-links" id="OnlineShowMoreWrapper">
<span id="OnlineShowMoreTitle" class="title online-show-more-title">{$ibforums->lang['oul_show_more']}</span>
<a id="ShowOnlineByActions" class="show-online-by-actions-link" href="{$ibforums->base_url}act=Online&amp;CODE=listall&amp;sort_key=click">{$ibforums->lang['oul_click']}</a>,
<a id="ShowOnlineByName" class="show-online-by-name-link" href="{$ibforums->base_url}act=Online&amp;CODE=listall&amp;sort_key=name&amp;sort_order=asc&amp;show_mem=reg">{$ibforums->lang['oul_name']}</a>
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
if(empty($info['colspan'])) {
    $info['colspan'] = '';
}
return <<<EOF

       <tr class="b-forums-list-row forum-{$info['id']} forum-redirect" data-forum-id="{$info['id']}" data-category-id="{$info['category']}">
         <td {$info['colspan']}class="row4 b-column b-column_icon"><{BR_REDIRECT}></td>
         <td class="row4 b-column b-column_title"><a class="b-title-link" href="{$ibforums->base_url}showforum={$info['id']}" {$info['redirect_target']}>{$info['name']}</a><div class="desc">{$info['description']}</span></td>
         <td class="row2 b-column b-column_topics">-</td>
         <td class="row2 b-column b-column_replies">-</td>
         <td class="row2 b-column b-column_lastpost">{$ibforums->lang['rd_hits']}: {$info['redirect_hits']}</td>
       </tr>

EOF;
}

    /**
     * @deprecated
     * @param $message
     * @return string
     */
function renderGlobalMessage($message) {
return <<<EOF
<div id="GlobalMessage">{$message}</div>
EOF;
}

function ForumRow($info) {
global $ibforums;
return <<<EOF

<tr class="b-forums-list-row" data-forum-id="{$info['id']}" data-category-id="{$info['category']}" data-allow-poll="{$info['allow_poll']}" data-allow-topics="{$info['sub_can_post']}">
 {$info['tree']}
 <td class="row2 b-column b-column_title"><a class="b-title-link" href="{$ibforums->base_url}showforum={$info['id']}">{$info['name']}</a> <span class="desc">{$info['description']}</span></td>
 <td class="row4 b-column b-column_topics">{$info['topics']}</td>
 <td class="row4 b-column b-column_replies">{$info['posts']}</td>
 <td class="row2 b-column b-column_lastpost"><time class="block" datetime="{$info['last_post_std']}">{$info['last_post']}</time><div class="b-last-topic-row">{$ibforums->lang['in']}: {$info['last_topic']}</div><div class="b-poster-row">{$ibforums->lang['by']}: {$info['last_poster']}</div></td>
</tr>

EOF;
}

function newslink($fid="", $title="", $tid="") {
global $ibforums;
return <<<EOF
<div class="news news-first"><span class="news-header news-first-header">{$ibforums->lang['newslink']}</span> <a class="news-link news-first-link" href="{$ibforums->base_url}showtopic=$tid&view=getnewpost"><span class="voteprefix">$title</span></a></div>

EOF;
}

function secondnewslink($fid="", $title="", $tid="") {
global $ibforums;
return <<<EOF

<div class="news news-second"><span class="news-header news-second-header">{$ibforums->lang["secondnewslink"]}</span> <a class="news-link news-second-link" href="{$ibforums->base_url}showtopic=$tid&view=getnewpost"><span style="color:blue">$title</span></a></div>

EOF;
}

function our_poll_link($fid="", $title="", $tid="") {
global $ibforums;
return <<<EOF

<div class="poll"><span class="poll-header">{$ibforums->lang['our_polls_link']}</span> <a class="poll-link" href="{$ibforums->base_url}showtopic=$tid&view=getnewpost"><span style="color:blue">$title</span></a></div>

EOF;
}

function forum_img_with_link($img, $id) {
global $ibforums;
return <<<EOF

<a class="forum-mark-read-link" href="{$ibforums->base_url}act=Login&amp;CODE=04&amp;f={$id}" title="{$ibforums->lang["bi_markread"]}" style="text-decoration:none">{$img}</a>

EOF;
}

function calendar_events($events = "") {
global $ibforums;
return <<<EOF

<tr class="calendar-events-title-row">
    <td class="pformstrip calendar-events-title" colspan="2">{$ibforums->lang["calender_f_title"]}</td>
    	</tr>
      <tr class="calendar-events-row">
          <td class="row2 block-image" width="5%" valign="middle"><{F_ACTIVE}></td>
          <td class="row4 calendar-events" width="95%">$events</td>
        </tr>

EOF;
}

//looks like unused
function ShowStats($text) {
global $ibforums;
return <<<EOF

<tr class="stats-title-row">
  <td class="pformstrip stats-title-row" colspan="2">{$ibforums->lang["board_stats"]}</td>
</tr>
<tr class="stats-row">
  <td class="row2 block-image" width="5%" valign="middle"><{F_STATS}></td>
  <td class="row4 stats" width="95%" align="left">$text</td>
</tr>

EOF;
}

function TodayOnline($activity) {
global $ibforums;
return <<<EOF

<tr class="online-stats-title-row">
  <td class="pformstrip online-stats-title" colspan="2">{$ibforums->lang["today_online"]}</td>
</tr>
<tr class="online-stats-row">
  <td class="row2 block-image" width="5%" valign="middle"><{F_STATS}></td>
  <td class="row4 online-stats" width="95%" align="left">
    <div class="online-stats-today">{$activity}</div>
    <div class="thin online-stats-record"><div class="online-total-record">{$ibforums->lang["online_record"]}</div><div class="online-category-record">{$ibforums->lang["category_record"]}</div></div>
    <div class="online-stats-peak">{$ibforums->lang["most_online"]}</div></td>
</tr>

EOF;
}

function forumrow_lastunread_link($fid, $tid) {
global $ibforums;
return <<<EOF

<a class="forum-last-unread-link" href="{$ibforums->base_url}showtopic=$tid&amp;view=getlastpost" title="{$ibforums->lang["tt_golast"]}"><{LAST_POST}></a>

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
	  <tfoot>
		  <TR class="b-forum-list-footer">
		    <TD class="tablefooter" colspan=6><!-- -->
		    </td>
		  </TR>
	  </tfoot>
  </table>
	<div class="b-category-footer"></div>
    </div>

EOF;
}

function subforum_img_with_link($img, $id) {
global $ibforums;
return <<<EOF

<a class="forum-mark-all-read-link" href="{$ibforums->base_url}act=Login&amp;CODE=04&amp;f={$id}&amp;i=1" title="{$ibforums->lang["bi_markallread"]}" style="text-decoration:none">{$img}</a>

EOF;
}

function subheader($fid) {
global $ibforums;
    //todo добавить название головного форума, если темы в нём запрещены и есть только подразделы
return <<<EOF

{$fid}
 <div class="tableborder b-subforums-list-wrapper">
   <table cellspacing="1" class="b-subforums-list">
   <thead>
   <tr class="b-header-row">
  <th colspan="2" class="b-column b-column_icon"><img src="{$ibforums->skin["ImagesPath"]}/spacer.gif" alt="" width="28" height="1"></th>
  <th width="59%" class="b-column b-column_title">{$ibforums->lang["cat_name"]}</th>
  <th width="7%" class="b-column b-column_topics">{$ibforums->lang["topics"]}</th>
  <th width="7%" class="b-column b-column_replies">{$ibforums->lang["replies"]}</th>
  <th width="27%" class="b-column b-column_last-post">{$ibforums->lang["last_post_info"]}</th>
   </tr>
   </thead>

EOF;
}


}
