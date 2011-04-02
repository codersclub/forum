<?php

class skin_boards {



function bottom_links() {
global $ibforums;
return <<<EOF

<br>
   <div align='right'><a href="{$ibforums->base_url}act=Login&amp;CODE=06">{$ibforums->lang['d_delete_cookies']}</a> &middot; <a href="{$ibforums->base_url}act=Login&amp;CODE=05">{$ibforums->lang['d_post_read']}</a></div>

EOF;
}

function CatPlus($id) {
global $ibforums;
return <<<EOF

<a href={$ibforums->base_url}expcat={$id}><{C_PLUS}></a>&nbsp;

EOF;
}

function CatMinus($id) {
global $ibforums;
return <<<EOF

<a href={$ibforums->base_url}colcat={$id}><{C_MINUS}></a>&nbsp;


EOF;
}

function CatHeader_Collapsed($info,$plus = "") {
global $ibforums;
return <<<EOF

<div class="tableborder"> <div class='maintitle' align='left'>{$plus}<a href="{$ibforums->base_url}c={$info['id']}">{$info['name']}</a></div></div>
<br>

EOF;
}

function CatHeader_Expanded($Data,$minus = "") {
global $ibforums;
return <<<EOF

 <div class="tableborder">
  <div class='maintitle' align='left'>{$minus}<a href="{$ibforums->base_url}c={$Data['id']}">{$Data['name']}</a></div>
    <table width="100%" border="0" cellspacing="1" cellpadding="4"><tr>
    <th colspan="2" align="center" width="2%" class='titlemedium'><img border="0" src="{$ibforums->vars['img_url']}/spacer.gif" alt="" width="28" height="1"></th>          <th align="left" width="59%" class='titlemedium'>{$ibforums->lang['cat_name']}</th>
          <th align="center" width="7%" class='titlemedium'>{$ibforums->lang['topics']}</th>
          <th align="center" width="7%" class='titlemedium'>{$ibforums->lang['replies']}</th>
          <th align="left" width="25%" class='titlemedium'>{$ibforums->lang['last_post_info']}</th>
    </tr>

EOF;
}


function ShowAllLink() {
global $ibforums;
return <<<EOF

<td align='right'><a href='{$ibforums->base_url}&show=all'>{$ibforums->lang['show_all_forums']}</a></td>

EOF;
}


function PageTop($lastvisit, $show_all = "") {
global $ibforums;
return <<<EOF

<!--GLOBAL.MESSAGE-->
<table border=0 width='100%'>
<tr>
 <td align='left'><!--IBF.SECONDNEWSLINK--></td>
 <td align='right'><!--IBF.QUICK_LOG_IN--></td>
 </tr>
<tr>
 <td align='left'><!--IBF.NEWSLINK--></td>
 <td><!-- --></td>
 </tr>
 <tr>
 <td align='left'><!--IBF.OUR_POLL_LINK--></td>
 <td align='riht'>{$show_all}</td>
</tr>
</table>

EOF;
}


function quick_log_in() {
global $ibforums;
return <<<EOF

<div align="right"><strong>{$ibforums->lang["qli_title"]}</strong>
<form style="display:inline" action="{$ibforums->base_url}" method="post">
<input type="hidden" name="act" value="Login">
<input type="hidden" name="CODE" value="01">
<input type="hidden" name="CookieDate" value="1">
<input type="text" class="forminput" size="10" name="UserName" onfocus="this.value=''" value="{$ibforums->lang['qli_name']}">
<input type="password" class="forminput" size="10" name="PassWord" onfocus="this.value=''" value="ibfrules">
<input type="submit" class="forminput" value="{$ibforums->lang['qli_go']}">
</form>
</div>

EOF;
}


function birthdays($birthusers="", $total="", $birth_lang="") {
global $ibforums;
return <<<EOF

<tr>
           <td class='pformstrip' colspan='2'>{$ibforums->lang['birthday_header']}</td>
    	</tr>
    	<tr>
          <td class='row2' width='5%' valign='middle'><{F_ACTIVE}></td>
          <td class='row4' width='95%'>$birth_lang<br>$birthusers</td>
        </tr>

EOF;
}


function stats_header() {
global $ibforums;
return <<<EOF

    <br>
	<div align='center'>
		<a href='{$ibforums->base_url}act=Stats&amp;CODE=leaders'>{$ibforums->lang['sm_forum_leaders']}</a> |
		<a href='{$ibforums->base_url}act=Select&amp;CODE=getactive'>{$ibforums->lang['sm_todays_posts']}</a> |
		<a href='{$ibforums->base_url}act=Stats'>{$ibforums->lang['sm_today_posters']}</a> |
		<a href='{$ibforums->base_url}act=Members&amp;max_results=10&amp;sort_key=posts&amp;sort_order=desc'>{$ibforums->lang['sm_all_posters']}</a>
	</div>
    <br>
	<div class="tableborder">
		<div class="maintitle">{$ibforums->lang['board_stats']}</div>
		<table cellpadding='4' cellspacing='1' border='0' width='100%'>

EOF;
}



function ActiveFriends($active) {
global $ibforums;
return <<<EOF

{$ibforums->lang['your_friends']}
{$active[FRIENDS]}
<hr>

EOF;
}



function ActiveUsers($active, $friends = "") {
global $ibforums;
return <<<EOF
	<tr>
           <td class='pformstrip' colspan='2'>$active[TOTAL] {$ibforums->lang['active_users']}</td>
    	</tr>
    	<tr>
          <td width="5%" class='row2'><{F_ACTIVE}></td>
          <td class='row4' width='95%'>
            <b>{$active['GUESTS']}</b> {$ibforums->lang['guests']}, <b>{$active['MEMBERS']}</b> {$ibforums->lang['public_members']} <b>{$active['ANON']}</b> {$ibforums->lang['anon_members']}
            <div class='thin'>{$friends}{$active['NAMES']}<br><br>
            [<a href="{$ibforums->base_url}act=Members&max_results=30&filter=4&sort_order=asc&sort_key=name&st=0"><span class='movedprefix'>администраторы</span></a>,&nbsp;<a href="{$ibforums->base_url}act=Members&max_results=30&filter=7&sort_order=asc&sort_key=name&st=0"><span style='color:blue'>модераторы</span></a>,&nbsp;<a href="{$ibforums->base_url}act=Members&max_results=30&filter=26&sort_order=asc&sort_key=name&st=0"><font color='purple'>ветераны</font></a>,&nbsp;<a href="{$ibforums->base_url}act=Members&max_results=30&filter=9&sort_order=asc&sort_key=name&st=0"><span class='voteprefix'>координаторы проектов</span></a>,&nbsp;<a href="{$ibforums->base_url}act=Members&max_results=30&filter=25&sort_order=asc&sort_key=name&st=0"><font color='navy'>участники клуба</font></a>,&nbsp;<a href="{$ibforums->base_url}act=Members&max_results=30&filter=3&sort_order=asc&sort_key=name&st=0">участники</a>,&nbsp;<font color=gray>наказанные</font>]</div>
            {$active['links']}
          </td>
        </tr>

EOF;
}




function active_user_links() {
global $ibforums;
return <<<EOF

{$ibforums->lang['oul_show_more']} <a href='{$ibforums->base_url}act=Online&amp;CODE=listall&amp;sort_key=click'>{$ibforums->lang['oul_click']}</a>, <a href='{$ibforums->base_url}act=Online&amp;CODE=listall&amp;sort_key=name&amp;sort_order=asc&amp;show_mem=reg'>{$ibforums->lang['oul_name']}</a>

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

       <tr>
         <td {$info[colspan]}class="row4" align="center"><{BR_REDIRECT}></td>
         <td class="row4"><b><a href="{$ibforums->base_url}showforum={$info['id']}" {$info['redirect_target']}>{$info['name']}</a></b><br><span class='desc'>{$info['description']}</span></td>
         <td class="row2" align="center">-</td>
         <td class="row2" align="center">-</td>
         <td class="row2">{$ibforums->lang['rd_hits']}: {$info['redirect_hits']}</td>
       </tr>

EOF;
}


function show_global_message($message) {
global $ibforums;
return <<<EOF

<table  width="100%" cellspacing="6" id="submenu">
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

<tr>
 {$info['tree']}
 <td class="row2"><b><a href="{$ibforums->base_url}showforum={$info['id']}">{$info['name']}</a></b> <span class='desc'>{$info['description']}</span></td>
 <td class="row4" align="center">{$info['topics']}</td>
 <td class="row4" align="center">{$info['posts']}</td>
 <td class="row2">{$info['last_post']}<br>{$ibforums->lang['in']}: {$info['last_topic']}<br>{$ibforums->lang['by']}: {$info['last_poster']}</td>
</tr>

EOF;
}









function newslink($fid="", $title="", $tid="") {
global $ibforums;
return <<<EOF
<b>{$ibforums->lang['newslink']} <a href='{$ibforums->base_url}showtopic=$tid&view=getnewpost'><span class='voteprefix'>$title</span></a></b>

EOF;
}


function secondnewslink($fid="", $title="", $tid="") {
global $ibforums;
return <<<EOF

<b>{$ibforums->lang['secondnewslink']} <a href='{$ibforums->base_url}showtopic=$tid&view=getnewpost'><span style='color:blue'>$title</span></a></b><br>

EOF;
}


function our_poll_link($fid="", $title="", $tid="") {
global $ibforums;
return <<<EOF
 
<b>{$ibforums->lang['our_polls_link']} <a href='{$ibforums->base_url}showtopic=$tid&view=getnewpost'><span style='color:blue'>$title</span></a></b><br>
 
EOF;
}


function forum_img_with_link($img, $id) {
global $ibforums;
return <<<EOF

<a href='{$ibforums->base_url}act=Login&amp;CODE=04&amp;f={$id}' title='{$ibforums->lang['bi_markread']}' style='text-decoration:none'>{$img}</a>

EOF;
}


function calendar_events($events = "") {
global $ibforums;
return <<<EOF

<tr>
    <td class='pformstrip' colspan='2'>{$ibforums->lang['calender_f_title']}</td>
    	</tr>
    	<tr>
          <td class='row2' width='5%' valign='middle'><{F_ACTIVE}></td>
          <td class='row4' width='95%'>$events</td>
        </tr>

EOF;
}



function ShowStats($text) {
global $ibforums;
return <<<EOF

<tr>
  <td class='pformstrip' colspan='2'>{$ibforums->lang['board_stats']}</td>
</tr>
<tr>
  <td class='row2' width='5%' valign='middle'><{F_STATS}></td>
  <td class='row4' width="95%" align='left'>$text</td>
</tr>

EOF;
}








function TodayOnline($activity) {
global $ibforums;
return <<<EOF

<tr>
  <td class='pformstrip' colspan='2'>{$ibforums->lang['today_online']}</td>
</tr>
<tr>
  <td class='row2' width='5%' valign='middle'><{F_STATS}></td>
  <td class='row4' width="95%" align='left'>{$activity}<div class='thin'>
{$ibforums->lang['online_record']}<br>{$ibforums->lang['category_record']}</div>{$ibforums->lang['most_online']}</td>
</tr>

EOF;
}







function forumrow_lastunread_link($fid, $tid) {
global $ibforums;
return <<<EOF

<a href='{$ibforums->base_url}showtopic=$tid&amp;view=getlastpost' title='{$ibforums->lang['tt_golast']}'><{LAST_POST}></a>

EOF;
}


function end_all_cats() {
global $ibforums;
return <<<EOF



EOF;
}


function active_list_sep() {
global $ibforums;
return <<<EOF

,

EOF;
}




function end_this_cat() {
global $ibforums;
return <<<EOF

  <TR>
    <TD class=tablefooter colspan=6><!-- -->
    </td>
  </TR>

      </table>
    </div>
    <br>

EOF;
}




function subforum_img_with_link($img, $id) {
global $ibforums;
return <<<EOF

<a href='{$ibforums->base_url}act=Login&amp;CODE=04&amp;f={$id}&amp;i=1' title='{$ibforums->lang['bi_markallread']}' style='text-decoration:none'>{$img}</a>

EOF;
}


function subheader($fid) {
global $ibforums;
return <<<EOF

{$fid}
<br>
 <div class="tableborder">
   <table width="100%" border="0" cellspacing="1" cellpadding="4">
   <tr>
  <td colspan="2" align="center" class='titlemedium'><img border="0" src="{$ibforums->vars['img_url']}/spacer.gif" alt="" width="28" height="1"></td>
  <th align='left' width="59%" class='titlemedium'>{$ibforums->lang['cat_name']}</th>
  <th align="center" width="7%" class='titlemedium'>{$ibforums->lang['topics']}</th>
  <th align="center" width="7%" class='titlemedium'>{$ibforums->lang['replies']}</th>
  <th align='left' width="27%" class='titlemedium'>{$ibforums->lang['last_post_info']}</th>
   </tr>

EOF;
}


}
?>