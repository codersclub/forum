<?php

class skin_topic {

protected $post;
/**
 * @return skin_post
 */
protected function post()
{
	global $std;
	!$this->post && !$this->post = $std->load_template("skin_post");
	return $this->post;
}


function Show_attachments_img($post_id, $attach_id) {
global $ibforums;
return <<<EOF


<br>
<br>
<strong><span class="edit">{$ibforums->lang["pic_attach"]}</span></strong>
<br>
<img src="{$ibforums->base_url}act=Attach&amp;type=post&amp;id=$post_id&amp;attach_id=$attach_id" class="attach" alt="{$ibforums->lang["pic_attach"]}">

EOF;
}


function nameField_reg() {
global $ibforums;
return <<<EOF



EOF;
}


function warn_level_warn($id, $percent) {
global $ibforums;
return <<<EOF


{$ibforums->lang["tt_warn"]} (<a href="javascript:PopUp('{$ibforums->base_url}act=warn&amp;mid={$id}&amp;CODE=view','Pager','500','450','0','1','1','1')">{$percent}</a>%)


EOF;
}


function Show_attachments_img_thumb($file_name, $width, $height, $post_id, $attach_id) {
global $ibforums;
return <<<EOF


<br>
<br>
<strong><span class="edit">{$ibforums->lang["pic_attach_thumb"]}</span></strong>
<br>
<a href="{$ibforums->base_url}act=Attach&amp;type=post&amp;id=$post_id&amp;attach_id=$attach_id" title="{$ibforums->lang["pic_attach_thumb"]}" target="_blank"><img src="{$ibforums->vars["upload_url"]}/$file_name" width="$width" height="$height" class="attach" alt="{$ibforums->lang["pic_attach"]}"></a>

EOF;
}


function rep_options_links($stuff) {
global $ibforums;
return <<<EOF


<a href="{$ibforums->base_url}act=rep&CODE=02&mid=$stuff[mid]&f=$stuff[f]&t=$stuff[t]&p=$stuff[p]"><{WARN_MINUS}></a><a href="{$ibforums->base_url}act=rep&CODE=01&mid=$stuff[mid]&f=$stuff[f]&t=$stuff[t]&p=$stuff[p]"><{WARN_ADD}></a>


EOF;
}


function get_box_enableemo($checked) {
global $ibforums;
return <<<EOF


<input type="checkbox" name="enableemo" class="checkbox" value="yes" $checked>&nbsp;{$ibforums->lang["enable_emo"]}


EOF;
}


function quick_reply_box_closed() {
global $ibforums;
return <<<EOF
	<a href="javascript:ShowHide('qr_open','qr_closed');" title="{$ibforums->lang["qr_open"]}" accesskey="f"><{T_QREPLY}></a>
EOF;
}


function start_poll_link($fid, $tid) {
global $ibforums;
return <<<EOF
<a href="{$ibforums->base_url}act=Post&amp;CODE=14&amp;f=$fid&amp;t=$tid">{$ibforums->lang["new_poll_link"]}</a>
EOF;
}

function start_poll_button($fid, $tid) {
	return '<li class="b-action-button b-add_poll-button">' . $this->start_poll_link($fid, $tid) . '</li>';
}

function mm_end() {
global $ibforums;
return <<<EOF
</select>&nbsp;<input type="submit" value="{$ibforums->lang["mm_submit"]}" class="forminput"></form>
EOF;
}


function RenderRow($post, $author) {
global $ibforums;
$add_post_classes = "";
foreach(["pinned", "queued", "deleting", "added_to_faq"] as $property)
	if ($post[$property])
		$add_post_classes .= " b-post-" . $property;

return <<<EOF
	<div class="b-post-wrapper">
    <table class="b-post b-post-{$post["pid"]} b-post-author-{$post["author_id"]} {$add_post_classes}" id="post_{$post["pid"]}" data-post-id="{$post["pid"]}" data-post-author-id="{$post["author_id"]}">
    <tr class="b-post-headers-row">
       <td class="b-post-author-name row4">{$author["member_group_img"]} <a name="entry{$post["pid"]}"></a>
        <span class="e-post-author {$post["name_css"]}" data-author-id="{$post['author_id']}">{$author["name"]}</span>{$author["online"]}
       </td>
       <td class="row4 b-post-header">
        <div class="b-post-info row4">{$post["checkbox"]}
         {$post["post_icon"]}<span class="postdetails"><span class="b-post-title"><span class="e-post-title-prefix">{$post["pinned_title"]}</span> <a class="e-post-title-number" title="{$ibforums->lang["tt_link"]}" href="#" onclick="link_to_post({$post["pid"]}); return false;">{$ibforums->lang["entry_num"]}{$author["postcount"]}</a></span><span class="e-post-date-prefix">{$ibforums->lang["posted_on"]}</span><time class="e-post-date" datetime="{$post["std_post_date"]}">{$post["post_date"]}</time></span>
         </div>
         {$post["html_actions"]}

      </td>
    </tr>
    <tr class="b-post-data-row">
      <td class="b-post-author-info {$post["post_css"]}">
        <div class="postdetails">
        <div class="b-post-author-avatar">{$author["avatar"]}</div>
        <div class="b-post-author-title">{$author["sex"]}{$author["title"]}</div>
        <div class="b-post-author-rank">{$author["member_rank_img"]}</div>
        <div class="b-post-author-profile">{$author["profile"]}</div>
        <div class="b-post-author-member_points">{$author["member_points"]}</div>
        <div class="b-post-author-rep">{$author["rep"]}</div>
        <div class="b-post-author-warns">{$author["warn_text"]}</div>
        </div>
		{$post["ip_address"]}
      </td>
      <td class="b-post-body {$post["post_css"]}">
        <div class="postcolor">{$post["post"]} {$post["attachment"]}</div>
        {$post["signature"]}
      </td>
    </tr>

    </table>
    </div>
EOF;
}

function RenderDeletedRow($post, $author, $preview) {
global $ibforums, $skin_universal;

if ($post["use_sig"] == 1)
{
	$e_time = $skin_universal->renderTime($post['decline_time']);
	$dtext = sprintf($ibforums->lang["permited_by"], $post["edit_name"], $e_time);
} else
{
	$e_time = $skin_universal->renderTime($post["edit_time"]);
	$dtext = $ibforums->lang["del_by_user"]." - ".$e_time;
}

$trpost = "";
if ($preview)
	$trpost = <<<EOF
	<tr class="b-post-data-row">
      <td class="b-post-author-info {$post["post_css"]}">&nbsp;
      </td>
      <td class="b-post-body {$post["post_css"]}">
        <div class="postcolor">{$post["post"]} {$post["attachment"]}</div>
      </td>
    </tr>
EOF;

return <<<EOF

    <table class="b-post b-post-{$post["pid"]} b-post-deleted" id="post_{$post["pid"]}" data-id="{$post["pid"]}">
    <tr class="b-post-headers-row">
       <td class="b-post-author-name row4" ><a name="entry{$post["pid"]}"></a>
       <span class="e-post-author {$post["name_css"]}" data-author-id="{$post['author_id']}">{$author["name"]}</span>
        <div class="b-post-author-warns">{$author["warn_text"]}</div>
        {$post["ip_address"]}
       </td>
       <td class="row4 b-post-header">
        <div class="row4 b-post-info">{$post["checkbox"]}
         {$post["post_icon"]}<span class="postdetails"><span class="b-post-title"><span class="e-post-title-prefix">{$post["pinned_title"]}</span> <a class="e-post-title-number" title="{$ibforums->lang["tt_link"]}" href="#" onclick="link_to_post({$post["pid"]}); return false;">{$ibforums->lang["entry_num"]}{$author["postcount"]}</a></span><span class="e-post-date-prefix">{$ibforums->lang["posted_on"]}</span><time class="e-post-date" datetime="{$post["std_post_date"]}">{$post["post_date"]}</time><span class="e-post-delete-text">$dtext</span></span>
         </div>
		{$post['html_actions']}

      </td>
    </tr>
    $trpost
    </table>
EOF;
}

function RowSeparator() {
return <<<EOF
    <div class="e-posts-separator"></div>
EOF;

}



function ip_show($data) {
global $ibforums;
return <<<EOF
<div class="b-post-author-ip"><span class="e-ip-title">{$ibforums->lang["ip"]}</span> <span class="e-ip-value">$data</span></div>
EOF;
}


function PageTop($data) {
global $ibforums;

$wrapper_classes = "b-topic-posts";
foreach(["pinned", "hidden", "decided", "club", "closed", "mirror", "deleted"] as $key)
	if ($data["TOPIC"][$key])
		$wrapper_classes .= " topic-{$key}-posts";

return <<<EOF

<script language="javascript" type="text/javascript">
var
base_url	    = "{$ibforums->base_url}",
tid		    = "{$ibforums->input["t"]}",
session_id	    = "{$ibforums->session_id}",
error_no_url        = "{$ibforums->lang["jscode_error_no_url"]}",
error_no_title      = "{$ibforums->lang["jscode_error_no_title"]}",
error_no_email      = "{$ibforums->lang["jscode_error_no_email"]}",
error_no_width      = "{$ibforums->lang["jscode_error_no_width"]}",
error_no_height     = "{$ibforums->lang["jscode_error_no_height"]}",
text_enter_url      = "{$ibforums->lang["jscode_text_enter_url"]}",
text_enter_url_name = "{$ibforums->lang["jscode_text_enter_url_name"]}",
text_enter_image    = "{$ibforums->lang["jscode_text_enter_image"]}",
prompt_start        = "{$ibforums->lang["js_text_to_format"]}",
tt_prompt	    = "{$ibforums->lang["tt_prompt"]}",
js_del_1	    = "{$ibforums->lang["js_del_1"]}",
js_del_2	    = "{$ibforums->lang["js_del_2"]}",
msg_no_title	    = "{$ibforums->lang["msg_no_title"]}",
js_no_message	    = "{$ibforums->lang["js_no_message"]}",
js_max_length	    = "{$ibforums->lang["js_max_length"]}",
js_characters	    = "{$ibforums->lang["js_characters"]}",
js_current	    = "{$ibforums->lang["js_current"]}",
error_no_url        = "{$ibforums->lang["jscode_error_no_url"]}",
error_no_title      = "{$ibforums->lang["jscode_error_no_title"]}",
error_no_email      = "{$ibforums->lang["jscode_error_no_email"]}",
error_no_width      = "{$ibforums->lang["jscode_error_no_width"]}",
error_no_height     = "{$ibforums->lang["jscode_error_no_height"]}",
text_enter_url      = "{$ibforums->lang["jscode_text_enter_url"]}",
text_enter_url_name = "{$ibforums->lang["jscode_text_enter_url_name"]}",
text_enter_image    = "{$ibforums->lang["jscode_text_enter_image"]}",
prompt_start        = "{$ibforums->lang["js_text_to_format"]}",
MessageMax  	    = "{$ibforums->lang["the_max_length"]}",
Override    	    = "{$ibforums->lang["override"]}",
rusLet		    = new Array("Э","Щ","Щ","Ч","Ч","Ш","Ш","Ё","Ё","Ё","Ё","Ю","Ю","Ю","Ю","Я","Я","Я","Я","Ж","Ж","А","Б","В","Г","Д","Е","З","ИЙ","ИЙ","ЫЙ","ЫЙ","И","Й","К","КС","Л","М","Н","О","П","Р","С","Т","У","Ф","Х","Ц","Щ","Ы","э","щ","ч","ш","ё","ё","ю","ю","я","я","ж","а","б","в","г","д","е","з","ий","ий","ый","ый","и","й","к","кс","л","м","н","о","п","р","с","т","у","ф","х","ц","щ","щ","ы","ъ","ъ","ь"),
engReg		    = new Array(/E"/g,/SHCH/g,/Shch/g,/CH/g,/Ch/g,/SH/g,/Sh/g,/YO/g,/JO/g,/Yo/g,/Jo/g,/YU/g,/JU/g,/Yu/g,/Ju/g,/YA/g,/JA/g,/Ya/g,/Ja/g,/ZH/g,/Zh/g,/A/g,/B/g,/V/g,/G/g,/D/g,/E/g,/Z/g,/II/g,/IY/g,/YI/g,/YY/g,/I/g,/J/g,/K/g,/X/g,/L/g,/M/g,/N/g,/O/g,/P/g,/R/g,/S/g,/T/g,/U/g,/F/g,/H/g,/C/g,/W/g,/Y/g,/e"/g,/shch/g,/ch/g,/sh/g,/yo/g,/jo/g,/yu/g,/ju/g,/ya/g,/ja/g,/zh/g,/a/g,/b/g,/v/g,/g/g,/d/g,/e/g,/z/g,/ii/g,/iy/g,/yi/g,/yy/g,/i/g,/j/g,/k/g,/x/g,/l/g,/m/g,/n/g,/o/g,/p/g,/r/g,/s/g,/t/g,/u/g,/f/g,/h/g,/c/g,/w/g,/#/g,/y/g,/`/g,/~/g,/"/g),
decline1 	    = "<{P_DECLINE}>",
decline2 	    = "<{P_RESTORE}>",
post_delete1 	    = "<{P_X}>",
post_delete2 	    = "{$ibforums->lang["delete_through"]}".replace("%s","{$data["FORUM"]["days_off"]}"),
solve1 		    = "{$ibforums->lang['topic_decided']}",
solve2 		    = "{$ibforums->lang['topic_not_decided']}",
fav1		    = "{$ibforums->lang["fav_add"]}",
fav2		    = "{$ibforums->lang["fav_remove"]}",
scroll_to	    = {$ibforums->member["show_wp"]},
MessageMax          = parseInt(MessageMax);
if ( MessageMax < 0 ) MessageMax = 0;
</script>

<script type="text/javascript" src="{$ibforums->vars["board_url"]}/html/topics.js?{$ibforums->vars["client_script_version"]}"></script>
<script type="text/javascript" src="{$ibforums->vars["board_url"]}/html/video.js?{$ibforums->vars["client_script_version"]}"></script>

<a name="top"></a>
<!--IBF.FORUM_RULES-->
<div class="b-topic-moderators-list">{$data["FORUM"]["moderators"]}</div>
<div class="topic-attached-links-contained">{$data["TOPIC"]["links"]}</div>
<div class="b-topic-close-reason">{$data["TOPIC"]["why_close"]}</div>
<table class="b-topic-header-buttons-row">
<tr>
 <td class="topic-pager pager b-top-pager-wrapper">{$data["TOPIC"]["SHOW_PAGES"]}&nbsp;{$data["TOPIC"]["go_new"]}&nbsp;{$data["TOPIC"]["go_last"]}</td>
 <td class="forum-buttons b-top-forum-buttons-wrapper"><!--IBF.TOPIC_HEADER_BUTTONS--></td>
</tr>
</table>
<div id="PostsWrapper" class="tableborder {$wrapper_classes}" data-topic-id="{$data["TOPIC"]["tid"]}" data-topic-author-id="{$data["TOPIC"]["starter_id"]}">
  <div class="maintitle"><span class="topic-image"><{CAT_IMG}></span>&nbsp;<span class="e-posts-topic-title">{$data["TOPIC"]["title"]}</span><span class="e-posts-topic-description">{$data["TOPIC"]["description"]}{$data["TOPIC"]["club"]}</span></div>
  <div class="b-poll-wrapper"><!--{IBF.POLL}--></div>
  {$data["TOPIC"]["modform_open"]}
  <div class="topic-links">
    <ul class="b-action-buttons b-action-buttons-vline b-topic-actions">
        <!--{IBF.START_NEW_POLL}-->
		<li class="b-action-button b-subscribe-button">{$data["TOPIC"]["subscribe"]}</li>
        <li class="b-action-button b-forward-button"><a class="forward-topic" href="{$ibforums->base_url}act=Forward&amp;f={$data["FORUM"]["id"]}&amp;t={$data["TOPIC"]["tid"]}">{$ibforums->lang["forward"]}</a></li>
        <li class="b-action-button b-print-button"><a class="print-topic" href="{$ibforums->base_url}act=Print&amp;client=choose&amp;f={$data["FORUM"]["id"]}&amp;t={$data["TOPIC"]["tid"]}">{$ibforums->lang["av_title"]}</a></li>
		{$data["TOPIC"]["fav_text"]}
	</ul>

</div>

EOF;
}

function quick_reply_box_open($fid="",$tid="",$show="hide", $warning = "", $key="", $syntax_select = "", $mod_buttons = "", $topic_decided = "") {
global $ibforums, $std;
$out = <<<EOF
{$warning}
<div align="left" class="quick_reply_form-container" id="qr_open" style="display:$show;position:relative;">
<form name="REPLIER" action="{$ibforums->base_url}" method="post" onsubmit="return ValidateForm()" enctype="multipart/form-data" class="quick_reply_form">
<input type="hidden" name="act" value="Post">
<input type="hidden" name="CODE" value="03">
<input type="hidden" name="f" value="$fid">
<input type="hidden" name="t" value="$tid">
<input type="hidden" name="st" value="{$ibforums->input["st"]}">
<input type="hidden" name="auth_key" value="$key">
<input type="hidden" name="add_merge_edit" value="1">
<div class="tableborder">
<table class="quick_reply" cellpadding="0" cellspacing="0" width="100%">
<!--IBF.NAME_FIELD-->
EOF;
$out .= $this->post()->postbox_buttons("", $syntax_select, $mod_buttons, $topic_decided);
$out .= "<!--UPLOAD FIELD-->";
$out .= $this->post()->EndForm($ibforums->lang["submit_reply"]);
$out .= <<<EOF
</div>
</table>
</form>
</div>
EOF;
return $out;
}

function Upload_field($data) {
	return $this->post()->Upload_field($data);
}

function mod_wrapper($id="", $text="") {
global $ibforums;
return <<<EOF


<option value="$id">$text</option>


EOF;
}


function get_box_alreadytrack() {
global $ibforums;
return <<<EOF


<br>{$ibforums->lang["already_sub"]}


EOF;
}


function warn_title($id, $title) {
global $ibforums;
return <<<EOF


<a href="javascript:PopUp('{$ibforums->base_url}act=warn&amp;mid={$id}&amp;CODE=view','Pager','500','450','0','1','1','1')">{$title}</a>:


EOF;
}


function mm_start($tid) {
global $ibforums;
return <<<EOF

<form action="{$ibforums->base_url}act=mmod&amp;t=$tid" method="post">
<input type="hidden" name="check" value="1">
<select name="mm_id" class="forminput">
<option value="-1">{$ibforums->lang["mm_title"]}</option>


EOF;
}


function new_report_link($data) {
global $ibforums;
return <<<EOF

<a href="{$ibforums->base_url}act=report&amp;f={$data["FORUM"]["id"]}&amp;t={$data["TOPIC"]["tid"]}&amp;st={$ibforums->input["st"]}">{$ibforums->lang["report_link"]}</a>

EOF;
}


function nameField_unreg($data) {
global $ibforums;
return <<<EOF


<tr>
 <td colspan="2" class="pformstrip">{$ibforums->lang["unreg_namestuff"]}</td>
</tr>
<tr>
  <td class="pformleft">{$ibforums->lang["guest_name"]}</td>
  <td class="pformright"><input type="text" size="40" maxlength="40" name="UserName" value="$data" class="textinput"></td>
</tr>


EOF;
}


function Mod_Panel($data, $fid, $tid, $key="") {
global $ibforums;
return <<<EOF

<div align="left" class="mode_panel_form-container b-modpanel">
<form method="POST" style="display:inline" name="modform" action="{$ibforums->base_url}">
<div style="display:none;" id="wc"><input type="text" class="textinput" style="width:250px;" name="why_close" value="Введите причину закрытия темы здесь" onclick="this.select();"></div>
<div style="display:none;" id="w2m"><input type="text" class="textinput" style="width:250px;" name="where2move" value="{$ibforums->lang["where2move"]}" onclick="this.select();"/></div>
<input type="hidden" name="t" value="$tid">
<input type="hidden" name="f" value="$fid">
<input type="hidden" name="st" value="{$ibforums->input["st"]}">
<input type="hidden" name="auth_key" value="$key">
<input type="hidden" name="act" value="Mod">
<select name="CODE" class="forminput" style="font-weight:bold;color:red" onchange=
"var w2m, wc;
 if (document.getElementById)
  {
   w2m=document.getElementById('w2m');
   wc=document.getElementById('wc');
  } else if (document.all)
  {
   w2m=document.all['w2m'];
   wc=document.all['wc'];
  } else if (document.layers)
  {
   w2m=document.layers['w2m'];
   wc=document.layers['wc'];
  }
 if (this.value=='00')
  {
   w2m.style.display = 'none';
   wc.style.display = '';
  } else if (this.value=='67')
  {
   wc.style.display = 'none';
   w2m.style.display = '';
  } else
  {
   wc.style.display = 'none';
   w2m.style.display = 'none';
  }">
<option value="-1" style="color:black">{$ibforums->lang["moderation_ops"]}</option>
$data
</select>&nbsp;<input type="submit" value="{$ibforums->lang["jmp_go"]}" class="forminput"></form>
</div>

EOF;
}


function Show_attachments($data) {
global $ibforums;
return <<<EOF

<br>
<br>
<strong><span class="edit">{$ibforums->lang["attached_file"]} ( {$ibforums->lang["attach_hits"]}: {$data["hits"]} )</span></strong>
<br>
<a href="{$ibforums->base_url}act=Attach&amp;type=post&amp;id={$data["pid"]}&amp;attach_id={$data["attach_id"]}" title="{$ibforums->lang["attach_dl"]}" target="_blank">{$data["image"]}</a>
&nbsp;<a href="{$ibforums->base_url}act=Attach&amp;type=post&amp;id={$data["pid"]}&amp;attach_id={$data["attach_id"]}" title="{$ibforums->lang["attach_dl"]}" target="_blank">{$data["name"]}</a>
&nbsp;<span class="edit">({$data["size"]})</span>

EOF;
}


function smilie_table() {
global $ibforums;
return <<<EOF

<b>{$ibforums->lang["click_smilie"]}</b>
<div class="tablefill" style="overflow: auto; height: 310px; width: 170px;">
<table cellpadding="0" align="center">
<!--THE SMILIES-->
</table>
</div>
<b><a href="javascript:emo_pop()">{$ibforums->lang["all_emoticons"]}</a></b><br />
EOF;
}




function warn_level_rating($id, $level,$min=0,$max=10) {
global $ibforums;
return <<<EOF


&nbsp;[ <a href="javascript:PopUp('{$ibforums->base_url}act=warn&amp;mid={$id}&amp;CODE=view','Pager','500','450','0','1','1','1')">{$level}</a> ]


EOF;
}


function report_link($data) {
global $ibforums;
return <<<EOF


&middot; <a href="{$ibforums->base_url}act=report&amp;f={$data["forum_id"]}&amp;t={$data["topic_id"]}&amp;p={$data["pid"]}&amp;st={$ibforums->input["st"]}"><{P_REPORT}></a>


EOF;
}


function topic_active_users($active=array()) {
global $ibforums;
return <<<EOF
<div class="activeuserstrip b-active-users-summary">{$ibforums->lang["active_users_title"]} ({$ibforums->lang["active_users_detail"]})</div>
<div class="row2 b-active-users-detailed">{$ibforums->lang["active_users_members"]} {$active["names"]}</div>
EOF;
}


function gonewpost_link($fid, $tid) {
global $ibforums;
return <<<EOF


( <a href="{$ibforums->base_url}act=ST&amp;f=$fid&amp;t=$tid&amp;view=getnewpost">{$ibforums->lang["go_new_post"]}</a> )


EOF;
}


function golastpost_link($fid, $tid) {
global $ibforums;
return <<<EOF

( <a href="{$ibforums->base_url}showtopic={$tid}&amp;view=getlastpost">{$ibforums->lang["go_last_post"]}</a> )

EOF;
}


function get_box_enabletrack($checked) {
global $ibforums;
return <<<EOF


<br><input type="checkbox" name="enabletrack" class="checkbox" value="1" $checked>&nbsp;{$ibforums->lang["enable_track"]}


EOF;
}

function get_box_enable_offtop($checked) {
global $ibforums;
return <<<EOF


<br><input type="checkbox" name="offtop" class="checkbox" value="1" $checked>&nbsp;{$ibforums->lang["enable_offtop"]}


EOF;
}


function get_box_enablesig($checked) {
global $ibforums;
return <<<EOF


<br><input type="checkbox" name="enablesig" class="checkbox" value="yes" $checked>&nbsp;{$ibforums->lang["enable_sig"]}


EOF;
}


function mm_entry($id, $name) {
global $ibforums;
return <<<EOF


<option value="$id">$name</option>


EOF;
}


function TableFooter($data, $report_link) {
global $ibforums;
return <<<EOF
<!--IBF.TOPIC_ACTIVE-->
<div class="activeuserstrip b-topic-bottom-navigation-buttons-wrapper">
<ul class="b-topic-bottom-navigation-buttons b-action-buttons b-action-buttons-vline">
<li class="b-action-button b-topic-navigation-previous"><a href="{$ibforums->base_url}showtopic={$data[TOPIC]["tid"]}&amp;view=old">{$ibforums->lang["t_old"]}</a></li>
<li class="b-action-button b-topic-navigation-current"><a href="{$ibforums->base_url}showforum={$data[FORUM]["id"]}">{$data[FORUM]["name"]}</a></li>
<li class="b-action-button b-topic-navigation-next"><a href="{$ibforums->base_url}showtopic={$data[TOPIC]["tid"]}&amp;view=new">{$ibforums->lang["t_new"]}</a></li>
</ul>
</div></div>
<div class="b-posts-bottom-row clearfix">
	<div class="b-bottom-breadcrumbs-wrapper"><!--IBF.NAVIGATION--></div>
	<div class="b-report-wrapper b-block-right">$report_link</div>
	<div class="b-bottom-pages-wrapper">{$data[TOPIC][SHOW_PAGES]}</div>
	<div class="b-bottom-topic-actions-wrapper"><!--IBF.TOPIC_BOTTOM_BUTTONS--></div>
</div>
<div id="bottom-close-reason-wrapper" class="b-close-reason-wrapper">{$data["TOPIC"]["why_close"]}</div>
<div class="b-modpanel-wrapper"><!--IBF.MOD_PANEL--></div>
<div class="b-multimod_panel-wrapper"><!--IBF.MULTIMOD--></div>
{$data["TOPIC"]["modform_close"]}
<!--IBF.QUICK_REPLY_OPEN-->
<br>
<div align="right">{$data[FORUM]["JUMP"]}</div>
<br>
EOF;
}

function renderElementOnline(){
	return '<span class="e-author-online-text">Online</span>';
}

function renderNewWarnNotice() {
	return '<span class="e-new_warning_notice">(new!)</span>';
}

function favoriteButton($tid, $text){
	$ibf = Ibf::app();
	return <<<EOF
		<li class="b-action-button b-favourite-button"><a href="{$ibf->vars['base_url']}index.php?act=fav&amp;topic={$tid}&amp;js=1" onclick="return JSRequest(this.href,unique_id(this));">{$text}</a></li>
EOF;
}

function approveTopicLink($fid, $tid){
	$ibforums = Ibf::app();
	return "<a href='{$ibforums->base_url}act=modcp&amp;CODE=domodtopics&amp;f={$fid}&amp;TID_{$tid}=approve'>{$ibforums->lang['modcp_accept']}</a>";
}

function rejectTopicLink($fid, $tid){
	$ibforums = Ibf::app();
	return "<a href='{$ibforums->base_url}act=modcp&amp;CODE=domodtopics&amp;f={$fid}&amp;TID_{$tid}=remove'>{$ibforums->lang['modcp_reject']}</a>";
}

function approvePostLink($fid, $tid, $pid){
	$ibforums = Ibf::app();
	return "<a href='{$ibforums->base_url}act=modcp&amp;CODE=domodposts&amp;f={$fid}&amp;tid={$tid}&amp;PID_{$pid}=approve&amp;alter={$pid}'>{$ibforums->lang['modcp_accept']}</a>";
}

function renderEditedPostMessage($message){
return <<<EOF
	<div class="post-edited-message edit">{$message}</div>
EOF;
}

}
