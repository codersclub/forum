<?php

class skin_csite {

//-------------------------------------
//  member bar templates
//-------------------------------------

function Member_bar($msg, $ad_link, $mod_link, $val_link, $return) {
global $ibforums;
return <<<EOF

<table width="100%" id="userlinks" cellspacing="6">
  <tr>
    <td><strong>{$ibforums->lang['logged_in_as']} <a href='{$ibforums->base_url}showuser={$ibforums->member['id']}'>{$ibforums->member['name']}</a></strong> ( <a href='{$ibforums->vars['dynamiclite']}act=Login&amp;CODE=03&amp;return={$return}'>{$ibforums->lang['log_out']}</a>$ad_link $mod_link $val_link · <a href="http://forum.sources.ru/index.php?showtopic=50223"><b>FAQ форума</b></a> )</td>
    <td align='right'>
      <b><a href='{$ibforums->base_url}act=UserCP&amp;CODE=00' title='{$ibforums->lang['cp_tool_tip']}'>{$ibforums->lang['your_cp']}</a></b> &middot; <a href='{$ibforums->base_url}act=Msg&amp;CODE=01'>{$msg[TEXT]}</a>
      &middot; <a href='{$ibforums->base_url}act=Search&amp;CODE=getnew'>{$ibforums->lang['view_new_posts']}</a> &middot; <a href='javascript:buddy_pop();' title='{$ibforums->lang['bb_tool_tip']}'>{$ibforums->lang['l_qb']}</a></td>
  </tr>
</table>
<img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' width='20' height='3'>
EOF;
}

function Guest_bar() {
global $ibforums;
return <<<EOF

<table width="100%" id="userlinks" cellspacing="6">
  <tr>
    <td>{$ibforums->lang['guest_stuff']} ( <a href='{$ibforums->base_url}act=Login&amp;CODE=00'>{$ibforums->lang['log_in']}</a> | <a href='{$ibforums->base_url}act=Reg&amp;CODE=00'>{$ibforums->lang['register']}</a> )</td>
    <td align='right'><a href='{$ibforums->base_url}act=Reg&amp;CODE=reval'>{$ibforums->lang['ml_revalidate']}</a></td>
  </tr><tr><td><a href="http://forum.sources.ru/index.php?showtopic=50223">Что даёт регистрация на форуме?</a></td></tr>
</table>
<img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' width='20' height='3'>
EOF;
}

function mod_link() {
global $ibforums;
return <<<EOF

&middot; <b><a href='{$ibforums->base_url}act=modcp&amp;forum={$ibforums->input['f']}'>{$ibforums->lang['mod_cp']}</a></b>

EOF;
}

function admin_link() {
global $ibforums;
return <<<EOF

&nbsp;&middot; <b><a href='{$ibforums->vars['board_url']}/admin.{$ibforums->vars['php_ext']}' target='_blank'>{$ibforums->lang['admin_cp']}</a></b>

EOF;
}

function rules_link($url="", $title="")
{
global $ibforums;
return <<<EOF
&nbsp;&middot; <a href="$url">$title</a>
EOF;
}





function tmpl_author_insert_file_lnk($params) {
global $ibforums;
return <<<EOF
<a href="javascript:editor_insertHTML('Post', '[IMG=$params]','', 0);">Вставить как картинку</a>
EOF;
}


function validating_link() {
global $ibforums;
return <<<EOF

&nbsp;&middot; <a href='{$ibforums->base_url}act=Reg&amp;CODE=reval'>{$ibforums->lang['ml_revalidate']}</a>

EOF;
}





function csite_javascript() {
global $ibforums;
return <<<EOF
<script type='text/javascript' src='{$ibforums->vars['html_url']}/global.js?{$ibforums->vars['client_script_version']}'></script>
EOF;
}

function csite_css_inline($css="") {
global $ibforums;
return <<<EOF
<style type='text/css'>
{$css}
</style>
EOF;
}

function csite_sep_char() {
global $ibforums;
return <<<EOF
,
EOF;
}

function csite_css_external($css, $img) {
global $ibforums;
return <<<EOF
<style type='text/css' media="all">
<!--
<link href="{$ibforums->vars['board_url']}/cache/css_{$css}.css?{$ibforums->vars['client_script_version']}" rel="stylesheet" type="text/css">
-->
</style>
EOF;
}


function tmpl_links_wrap($link="", $name="") {
global $ibforums;
return <<<EOF
&middot; <a href='$link' style='text-decoration:none'>$name</a><br>
EOF;
}

function tmpl_welcomebox_member($pm_string="",$last_visit="", $name="", $return="", $block_caption) {
global $ibforums;
return <<<EOF
<div class='tableborder'>
 <div class='maintitle'><{CAT_IMG}> {$block_caption}, $name</div>
 <div class='tablepad'>
  <span class='desc'>$last_visit</span>
  <br>&middot; <a href="{$ibforums->base_url}act=Search&amp;CODE=getnew" style='text-decoration:none'>{$ibforums->lang['wbox_getnewposts']}</a>
  <br>&middot; <a href="{$ibforums->base_url}act=UserCP" style='text-decoration:none'>{$ibforums->lang['wbox_mycontrols']}</a>
  <br>&middot; <a href="javascript:buddy_pop();" style='text-decoration:none'>{$ibforums->lang['wbox_myassistant']}</a>
  <br>&middot; <a href="{$ibforums->vars['dynamiclite']}act=Login&amp;CODE=03&amp;return={$ibforums->vars['dynamiclite']}" style='text-decoration:none'>{$ibforums->lang['wbox_logout']}</a>
 </div>
</div>
EOF;
}

function tmpl_welcomebox_guest($top_string, $return, $block_caption) {
global $ibforums;
return <<<EOF
<form action="{$ibforums->base_url}act=Login&amp;CODE=01&amp;CookieDate=1&amp;return=$return" method="post">
<div class='tableborder'>
 <div class='maintitle'><{CAT_IMG}> {$block_caption}, {$ibforums->lang['wbox_guest_name']}</div>
 <div class='tablepad'>
         <span class='desc'>$top_string</span>
         <br><br>&middot; <strong><a href="{$ibforums->base_url}act=Search&amp;CODE=getactive" style='text-decoration:none'>{$ibforums->lang['wbox_getnewposts']}</a></strong>
         <br><br><span class='desc'>{$ibforums->lang['wbox_g_username']}</span>
         <br><input type='text' class='textinput' size='15' name='UserName'>
         <br><span class='desc'>{$ibforums->lang['wbox_g_password']}</span>
         <br><input type='password' class='textinput' size='15' name='PassWord'>
         <br><input type='submit' class='textinput' value='{$ibforums->lang['wbox_g_login']}'>
 </div>
</div>
</form>
EOF;
}


function tmpl_search($block_caption) {
global $ibforums;
return <<<EOF
<form action='{$ibforums->base_url}act=Search&amp;CODE=01&amp;forums=all' method='post' name='search'>
<div class='tableborder'>
 <div class='maintitle'><{CAT_IMG}> {$block_caption}</div>
 <div class='tablepad' align='center'>
  <input type='text' name='keywords' value='' size='10' class='textinput'><input type='submit' value='{$ibforums->lang['search_go']}'>
  <br><a href='{$ibforums->base_url}act=Search&amp;mode=adv'>{$ibforums->lang['search_advanced']}</a>
 </div>
</div>
</form>
EOF;
}

function tmpl_sitenav($links) {
global $ibforums;
return <<<EOF
<div class='tableborder'>
 <div class='maintitle'><{CAT_IMG}> {$ibforums->lang['links_title']}</div>
 <div class='tablepad'>
  $links
 </div>
</div>
EOF;
}

function tmpl_rawnav($links, $block_caption) {
global $ibforums;
return <<<EOF
<div class='tableborder'>
 <div class='maintitle'><{CAT_IMG}> {$block_caption}</div>
 <div class='tablepad'>
  $links
 </div>
</div>
EOF;
}

function tmpl_affiliates($links, $block_caption) {
global $ibforums;
return <<<EOF
<div class='tableborder'>
 <div class='maintitle'><{CAT_IMG}> {$block_caption}</div>
 <div class='tablepad'>
  $links
 </div>
</div>
EOF;
}

function tmpl_changeskin($select, $block_caption) {
global $ibforums;
return <<<EOF
<form action="{$ibforums->vars['dynamiclite']}&amp;s={$ibforums->session_id}&amp;setskin=1" method="post">
<div class='tableborder'>
 <div class='maintitle'><{CAT_IMG}> {$block_caption}</div>
 <div class='tablepad' align="center">
  <span class='desc'>{$ibforums->lang['cskin_text']}</span>
  <br>
  $select
  <br>
  <input type='submit' value='{$ibforums->lang['cskin_go']}'>
 </div>
</div>
</form>
EOF;
}

function tmpl_onlineusers($breakdown, $split, $names) {
global $ibforums;
return <<<EOF
<div class='tableborder'>
 <div class='maintitle'><{CAT_IMG}> <a href="{$ibforums->base_url}act=Online">{$ibforums->lang['online_title']}</a></div>
 <div class='tablepad'>
  <span class='desc'>$breakdown<br>$split<br>$names</span>
 </div>
</div>
EOF;
}

function tmpl_poll_header($question,$tid, $block_caption) {
global $ibforums;
return <<<EOF
<form action="{$ibforums->base_url}act=Poll&amp;t=$tid" method="post">
<div class='tableborder'>
 <div class='maintitle'><{CAT_IMG}> <a href="{$ibforums->base_url}act=Online">{$block_caption}</a></div>
 <div class='pformstrip'>$question</div>
 <div class='tablepad'>
EOF;
}

function tmpl_poll_result_row($votes, $id, $choice, $percent, $width) {
global $ibforums;
return <<<EOF
  $choice
  <br><img src='{$ibforums->vars['img_url']}/bar_left.gif' border='0' width='4' height='11' align='middle' alt=''><img src='{$ibforums->vars['img_url']}/bar.gif' border='0' width='$width' height='11' align='middle' alt=''><img src='{$ibforums->vars['img_url']}/bar_right.gif' border='0' width='4' height='11' align='middle' alt=''>
  <br>
EOF;
}

function tmpl_poll_choice_row($id, $choice) {
global $ibforums;
return <<<EOF
  <input type='radio' name='poll_vote' value='$id' class='radiobutton'>&nbsp;<strong>$choice</strong>
  <br>
EOF;
}

function tmpl_poll_footer($vote, $total, $tid) {
global $ibforums;
return <<<EOF
  <span class='desc'>
   &middot; <strong>$total</strong>
   <br>&middot; $vote
   <br>&middot; <a href="{$ibforums->base_url}showtopic=$tid">{$ibforums->lang['poll_discuss']}</a>
  </span>
  </div>
</div>
</form>
EOF;
}

function tmpl_poll_vote() {
global $ibforums;
return <<<EOF
<input type='submit' value='{$ibforums->lang['poll_vote']}' class='codebuttons'>
EOF;
}

function tmpl_latestposts($posts, $block_caption) {
global $ibforums;
return <<<EOF
<div class='tableborder'>
 <div class='maintitle'><{CAT_IMG}> {$block_caption}</div>
 $posts
</div>
EOF;
}

function tmpl_topic_row($tid, $title, $forum_title = null, $forum_id = null) {
global $ibforums;
return <<<EOF
<div class='row2' style='padding:3px'><a href='{$ibforums->base_url}showforum={$forum_id}'>{$forum_title}</a></div>
<div class='descs' style='padding:3px'>
  <strong><a href='{$ibforums->base_url}showtopic=$tid' style='text-decoration:none;font-size:10px'>$title</a></strong>
</div>
EOF;
}

function tmpl_recentarticles($articles, $block_caption) {
global $ibforums;
return <<<EOF
<div class='tableborder'>
 <div class='maintitle'><{CAT_IMG}> {$block_caption}</div>
 $articles
</div>
EOF;
}

function tmpl_favorites_links($block_caption) {
global $ibforums;
return <<<EOF
<div class='tableborder'>
 <div class='maintitle'><{CAT_IMG}> {$block_caption}</div>
  <div class='tablepad'>
<!--   &middot;&nbsp;<a href="{$ibforums->vars['dynamiclite']}cat={$ibforums->input['cat']}&FAV=5&ACTION=15">{$ibforums->lang['subscribe']}</a><br />
   &middot;&nbsp;<a href="{$ibforums->vars['dynamiclite']}cat={$ibforums->input['cat']}&FAV=6&ACTION=15">{$ibforums->lang['favorites']}</a><br />
   <hr> -->
   &middot;&nbsp;<a href="{$ibforums->vars['csite_cms_url']}/subscriptions.html">Мои подписки</a><br />
   &middot;&nbsp;<a href="{$ibforums->vars['csite_cms_url']}/favorites.html">Мое избранное</a>
  </div>
</div>
EOF;
}

function tmpl_author_links($links, $block_caption) {
global $ibforums;
return <<<EOF
<div class='tableborder'>
 <div class='maintitle'><{CAT_IMG}> {$block_caption}</div>
  <div class='tablepad'>
   $links
  </div>
</div>
EOF;
}

function tmpl_author_add_article_lnk() {
global $ibforums;
return <<<EOF
&middot; <a href='create.html'>{$ibforums->lang['add_article']}</a>
EOF;
}

function tmpl_search_author_link($id = 0, $author = "") {
global $ibforums;
return <<<EOF
&middot; <a href='{$ibforums->vars['dynamiclite']}act=search&CODE=author&id={$id}'>{$author}</a>
EOF;
}

function tmpl_authors_header($entry) {
global $ibforums;
return <<<EOF
<div class='tableborder'>
 <div class='maintitle'><{CAT_IMG}> {$ibforums->lang['author_title']}</div>
</div>
<br>
<table width='100%' class='tableborder'>
<tr>
    <td width='70%' class='row2' colspan="2" style='padding:5px'>{$ibforums->lang['author_title_name']}</td>
    <td class='row2' colspan="2" style='padding:5px' align='center'>{$ibforums->lang['author_title_articles_count']}</td>
</tr>
<tr>
    {$entry['content']}
</tr>
</table>
EOF;
}

function tmpl_authors_row($entry) {
global $ibforums;
return <<<EOF
<tr class='row3'>
    <td class='desc' colspan="2" style='padding:5px' align='left'>{$entry['profile']}</td>
    <td class='desc' colspan="2" style='padding:5px' align='center'>{$entry['articles']}</td>
</tr>
EOF;
}


function tmpl_skin_select_top() {
global $ibforums;
return <<<EOF
<select name="skinid" class="forminput">
EOF;
}

function tmpl_skin_select_row($sid, $name, $used) {
global $ibforums;
return <<<EOF
<option value="$sid" $used>$name</option>
EOF;
}

function tmpl_skin_select_bottom() {
global $ibforums;
return <<<EOF
</select>
EOF;
}

function tmpl_wrap_avatar($avatar) {
global $ibforums;
return <<<EOF
$avatar
EOF;
}

function tmpl_debug($queries, $time) {
global $ibforums;
return <<<EOF
  <div class='desc'>[ DB Queries: $queries ] [ Execution Time: $time ]</div>
EOF;
}

function block_left($entry) {
global $ibforums;
return <<<EOF
<td valign="top" width="{$entry['width']}">
{$entry['data']}
</td>
EOF;
}

function block_center($entry) {
global $ibforums;
return <<<EOF
<td width="{$entry['width']}" valign="top">
{$entry['data']}
</td>
EOF;
}

function block_right($entry) {
global $ibforums;
return <<<EOF
<td width="{$entry['width']}" valign="top">
{$entry['data']}
</td>
EOF;
}



function csite_skeleton_template() {
global $ibforums;
return <<<EOF

<!--CS.TEMPLATE.MODERATOR-->

<table width="100%" class='tablebasic' cellspacing="0" cellpadding="6" border="0">
<tr>

<!--CS.TEMPLATE.BLOCK_LEFT-->

<!--CS.TEMPLATE.BLOCK_CENTER-->

<!--CS.TEMPLATE.BLOCK_RIGHT-->

</tr>
 <tr>
 <td  colspan = '3'>
    <!--CS.TEMPLATE.STATS-->
 </td>
 </tr>
<tr>
 <td colspan='4'  class='' align='center'>
 <!--CS.TEMPLATE.RIGHT-->
 <!--CS.TEMPLATE.DEBUG-->
 </td>
</tr>
</table>
EOF;
}


}
?>
