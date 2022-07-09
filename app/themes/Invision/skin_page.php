<?php
// Skin for Article Page
//-------------------------
use Views\View;

class skin_page {

function Show_attachments_img($aid) {
global $ibforums;
return <<<EOF


<br>
<br>
<strong><span class='edit'>{$ibforums->lang['pic_attach']}</span></strong>
<br>
<img src='{$ibforums->base_url}act=Attach&amp;type=post&amp;id=$aid' class='attach' alt='{$ibforums->lang['pic_attach']}'>

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


{$ibforums->lang['tt_warn']} (<a href="javascript:PopUp('{$ibforums->base_url}act=warn&amp;mid={$id}&amp;CODE=view','Pager','500','450','0','1','1','1')">{$percent}</a>%)


EOF;
}


function Show_attachments_img_thumb($file_name, $width, $height, $aid) {
global $ibforums;
return <<<EOF


<br>
<br>
<strong><span class='edit'>{$ibforums->lang['pic_attach_thumb']}</span></strong>
<br>
<a href='{$ibforums->base_url}act=Attach&amp;type=post&amp;id=$aid' title='{$ibforums->lang['pic_attach_thumb']}' target='_blank'><img src='{$ibforums->vars['upload_url']}/$file_name' width='$width' height='$height' class='attach' alt='{$ibforums->lang['pic_attach']}'></a>

EOF;
}


function rep_options_links($stuff) {
global $ibforums;
return <<<EOF


<a href='{$ibforums->base_url}act=rep&CODE=02&mid={$stuff['mid']}&f={$stuff['f']}&t={$stuff['t']}&p={$stuff['p']}'><{WARN_MINUS}></a><a href='{$ibforums->base_url}act=rep&CODE=01&mid={$stuff['mid']}&f={$stuff['f']}&t={$stuff['t']}&p={$stuff['p']}'><{WARN_ADD}></a>


EOF;
}


function get_box_enableemo($checked) {
global $ibforums;
return <<<EOF


<input type='checkbox' name='enableemo' class='checkbox' value='yes' $checked>&nbsp;{$ibforums->lang['enable_emo']}


EOF;
}


function quick_reply_box_closed() {
global $ibforums;
return <<<EOF

	<a href="javascript:ShowHide('qr_open','qr_closed');" title="{$ibforums->lang['qr_open']}" accesskey="f"><{T_QREPLY}></a> ·


EOF;
}


function start_poll_link($fid, $tid) {
global $ibforums;
return <<<EOF


<a href="{$ibforums->base_url}act=Post&amp;CODE=14&amp;f=$fid&amp;t=$tid">{$ibforums->lang['new_poll_link']}</a> &#124;&nbsp;


EOF;
}


function mm_end() {
global $ibforums;
return <<<EOF


</select>&nbsp;<input type='submit' value='{$ibforums->lang['mm_submit']}' class='forminput'></form>


EOF;
}


//-------------------------------------
function PageTop($data) {
global $ibforums, $print;
$print->js->addLocal('keyb.js');
$print->js->addLocal('video.js');
$print->js->addLocal('topics.js');
    $print->exportJSLang([
            'error_no_url',
            'error_no_url',
            'error_no_title',
            'error_no_email',
            'error_no_width',
            'error_no_height',
            'text_enter_url',
            'text_enter_url_name',
            'text_enter_image',
            'tt_prompt',
            'js_del_1',
            'js_del_2',
            'msg_no_title',
            'js_no_message',
            'js_max_length',
            'js_characters',
            'js_current',
            'error_no_url',
            'error_no_title',
            'error_no_email',
            'error_no_width',
            'error_no_height',
            'text_enter_url',
            'text_enter_url_name',
            'text_enter_image',
        ]);

$print->js->addVariable('MessageMax', Ibf::app()->lang['the_max_length']);
return <<<EOF

<script>
var
tid		    = "{$ibforums->input['t']}",
list_prompt         = "{$ibforums->lang['js_tag_list']}",
prompt_start        = "{$ibforums->lang['js_text_to_format']}",
list_prompt         = "{$ibforums->lang['js_tag_list']}",
prompt_start        = "{$ibforums->lang['js_text_to_format']}",
Override    	    = "{$ibforums->lang['override']}",
decline1 	    = "<{P_DECLINE}>",
decline2 	    = "<{P_RESTORE}>",
post_delete1 	    = "<{P_X}>",
post_delete2 	    = "{$ibforums->lang['delete_through']}".replace("%s","{$data['FORUM']['days_off']}"),
solve1 		    = "{$ibforums->lang['topic_decided']}",
solve2 		    = "{$ibforums->lang['topic_not_decided']}",
fav1		    = "{$ibforums->lang['fav_add']}",
fav2		    = "{$ibforums->lang['fav_remove']}",
scroll_to	    = {$ibforums->member['show_wp']},

<a name='top'></a>
{$data['FORUM']['moderators']}
<br>
<!--IBF.FORUM_RULES-->
{$data['TOPIC']['links']}
<br>
{$data['TOPIC']['why_close']}

<!--table width='100%' cellpadding='0' cellspacing='0' border='0'>
<tr>
 <td align='left' width='20%' nowrap='nowrap'>{$data['TOPIC']['SHOW_PAGES']}&nbsp;{$data['TOPIC']['go_new']}&nbsp;{$data['TOPIC']['go_last']}</td>
 <td align='right' width='80%'>{$data['TOPIC']['REPLY_BUTTON']}{$data['TOPIC']['TOPIC_BUTTON']}{$data['TOPIC']['POLL_BUTTON']}{$data['TOPIC']['SOLVE_UPPER_BUTTON']}</td>
</tr>
</table-->

<!--{IBF.POLL}-->

{$data['TOPIC']['modform_open']}

<div align='right' class='postlinksbar'>
  <b>
  <!--{IBF.START_NEW_POLL}-->
  {$data['TOPIC']['TOPIC_BUTTON']}
  {$data['TOPIC']['subscribe']} |
  {$data['TOPIC']['POLL_BUTTON']}
  <a href='{$ibforums->base_url}act=Forward&amp;f={$data['FORUM']['id']}&amp;t={$data['TOPIC']['tid']}'>{$ibforums->lang['forward']}</a> |
  <a href='{$ibforums->base_url}act=Print&amp;client=choose&amp;f={$data['FORUM']['id']}&amp;t={$data['TOPIC']['tid']}'>{$ibforums->lang['av_title']}</a>
  {$data['TOPIC']['fav_text']}
  {$data['TOPIC']['SOLVE_UPPER_BUTTON']}
  {$data['TOPIC']['REPLY_BUTTON']}
  </b>
</div>

<br>


<H1>{$data['TOPIC']['title']}</H1>
<div>{$data['TOPIC']['description']}{$data['TOPIC']['club']}</div>


EOF;
}



//---------------------------------------------------------------
function RenderRow($post, $author) {
global $ibforums;
return <<<EOF

    {$post['checkbox']}
      {$post['post_icon']}<span class='postdetails'><b>{$post['pinned_title']}</b> <a title="{$ibforums->lang['tt_link']}" href="#" onclick="link_to_post({$post['pid']}); return false;" style="text-decoration:underline"><b>{$ibforums->lang['entry_num']}</b>{$author['postcount']}</a>{$post['post_date']}</span>
    <br>
    {$author['avatar']}
    {$author['member_group_img']} <a name='entry{$post["pid"]}'></a> <span class='{$post["name_css"]}'>{$author['name']}</span> {$author['online']}

    <b>{$author['sex']}{$author['title']}</b>
    {$author['member_rank_img']}
    {$author['profile']}
    {$author['member_points']}
    {$author['rep']}
    {$author['warn_text']}
    <b>{$post['ip_address']}</b>
    <br>

    <div align='right'>
    {$post['queued_link']}{$post['quick_quote']} {$post['add_to_faq']} {$post['restore_decline']}{$post['report_link']} {$post['delete_button']} {$post['edit_button']} {$post['quote']} {$post['delete_delayed']}
    </div>
    <br>


<div class='tableborder'>
    <div class='{$post['post_css']}'>
      <div class='postcolor' style='padding:8px'>

        {$post['post']}
        {$post['attachment']}
      </div>
      {$post['signature']}
    </div>


    <div class='darkrow1' style='height:5px'><!-- --></div>


EOF;
}


function ip_show($data) {
global $ibforums;
return <<<EOF


<span class='desc'><br>{$ibforums->lang['ip']}: $data</span>


EOF;
}


function quick_reply_box_open($fid="",$tid="",$show="hide", $warning = "", $key="", $syntax_select = "", $mod_buttons = "", $topic_decided = "") {
global $ibforums, $std;
$out = <<<EOF
{$warning}
<div align='left' id='qr_open' style="display:$show;position:relative;">
<form name='REPLIER' action="{$ibforums->base_url}" method='post' onsubmit='return ValidateForm()'>
<input type='hidden' name='act' value='Post'>
<input type='hidden' name='CODE' value='03'>
<input type='hidden' name='f' value='$fid'>
<input type='hidden' name='t' value='$tid'>
<input type='hidden' name='st' value='{$ibforums->input['st']}'>
<input type='hidden' name='auth_key' value='$key'>
<div class="tableborder">
<table cellpadding="0" cellspacing="0" width="100%">
<!--IBF.NAME_FIELD-->
EOF;
$out .= View::make(
    'post.postbox_buttons',
    ['data' => '', 'syntax_select' => $syntax_select, 'mod_buttons' => $mod_buttons, 'topic_decided' => $topic_decided]
);
$out .= View::make('post.Upload_field', ['data' => $std->size_format($ibforums->member['g_attach_max'] * 1024)]);
$out .= View::make('post.EndForm', ['data' => $ibforums->lang['submit_reply']]);
$out .= <<<EOF
</div>
</table>
</form>
</div>
EOF;
return $out;
}



function mod_wrapper($id="", $text="") {
global $ibforums;
return <<<EOF


<option value='$id'>$text</option>


EOF;
}


function get_box_alreadytrack() {
global $ibforums;
return <<<EOF


<br>{$ibforums->lang['already_sub']}


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

<form action='{$ibforums->base_url}act=mmod&amp;t=$tid' method='post'>
<input type='hidden' name='check' value='1'>
<select name='mm_id' class='forminput'>
<option value='-1'>{$ibforums->lang['mm_title']}</option>


EOF;
}


function new_report_link($data) {
global $ibforums;
return <<<EOF

<a href='{$ibforums->base_url}act=report&amp;f={$data['FORUM']['id']}&amp;t={$data['TOPIC']['tid']}&amp;st={$ibforums->input['st']}'>{$ibforums->lang['report_link']}</a>

EOF;
}


function nameField_unreg($data) {
global $ibforums;
return <<<EOF


<tr>
 <td colspan='2' class='pformstrip'>{$ibforums->lang['unreg_namestuff']}</td>
</tr>
<tr>
  <td class='pformleft'>{$ibforums->lang['guest_name']}</td>
  <td class='pformright'><input type='text' size='40' maxlength='40' name='UserName' value='$data' class='textinput'></td>
</tr>


EOF;
}


function Mod_Panel($data, $fid, $tid, $key="") {
global $ibforums;
return <<<EOF

<div align='left' style='float:left;width:auto'>
<form method='POST' style='display:inline' name='modform' action='{$ibforums->base_url}'>
<div style='display:none;' id='wc'><input type='text' class='textinput' style='width:250px;' name='why_close' value="Введите причину закрытия темы здесь" onclick="this.select();"></div>
<div style='display:none;' id='w2m'><input type='text' class='textinput' style='width:250px;' name='where2move' value="{$ibforums->lang['where2move']}" onclick="this.select();"/></div>
<input type='hidden' name='t' value='$tid'>
<input type='hidden' name='f' value='$fid'>
<input type='hidden' name='st' value='{$ibforums->input['st']}'>
<input type='hidden' name='auth_key' value='$key'>
<input type='hidden' name='act' value='Mod'>
<select name='CODE' class='forminput' style="font-weight:bold;color:red" onchange=
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
<option value='-1' style='color:black'>{$ibforums->lang['moderation_ops']}</option>
$data
</select>&nbsp;<input type='submit' value='{$ibforums->lang['jmp_go']}' class='forminput'></form>
</div>

EOF;
}


function Show_attachments($data) {
global $ibforums;
return <<<EOF

<br>
<br>
<strong><span class='edit'>{$ibforums->lang['attached_file']} ( {$ibforums->lang['attach_hits']}: {$data['hits']} )</span></strong>
<br>
<a href='{$ibforums->base_url}act=Attach&amp;type=post&amp;id={$data['pid']}' title='{$ibforums->lang['attach_dl']}' target='_blank'>{$data['image']}</a>
&nbsp;<a href='{$ibforums->base_url}act=Attach&amp;type=post&amp;id={$data['pid']}' title='{$ibforums->lang['attach_dl']}' target='_blank'>{$data['name']}</a>
&nbsp;<span class='edit'>({$data['size']})</span>

EOF;
}


function smilie_table() {
global $ibforums;
return <<<EOF


<table class='tablefill' cellpadding='4' align='center'>
<tr>
<td align="center" colspan="{$ibforums->vars['emo_per_row']}"><b>{$ibforums->lang['click_smilie']}</b></td>
</tr>
<!--THE SMILIES-->
<tr>
<td align="center" colspan="{$ibforums->vars['emo_per_row']}"><b><a href='javascript:emo_pop()'>{$ibforums->lang['all_emoticons']}</a></b></td>
</tr>
</table>


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


&middot; <a href='{$ibforums->base_url}act=report&amp;f={$data['forum_id']}&amp;t={$data['topic_id']}&amp;p={$data['pid']}&amp;st={$ibforums->input['st']}'><{P_REPORT}></a>


EOF;
}


function topic_active_users($active=array()) {
global $ibforums;
return <<<EOF


<div class="activeuserstrip">{$ibforums->lang['active_users_title']} ({$ibforums->lang['active_users_detail']})</div>
	  <div class='row2' style='padding:6px'>{$ibforums->lang['active_users_members']} {$active['names']}</div>


EOF;
}


function gonewpost_link($fid, $tid) {
global $ibforums;
return <<<EOF


( <a href='{$ibforums->base_url}act=ST&amp;f=$fid&amp;t=$tid&amp;view=getnewpost'>{$ibforums->lang['go_new_post']}</a> )


EOF;
}


function golastpost_link($fid, $tid) {
global $ibforums;
return <<<EOF

( <a href='{$ibforums->base_url}showtopic={$tid}&amp;view=getlastpost'>{$ibforums->lang['go_last_post']}</a> )

EOF;
}


function get_box_enabletrack($checked) {
global $ibforums;
return <<<EOF


<br><input type='checkbox' name='enabletrack' class='checkbox' value='1' $checked>&nbsp;{$ibforums->lang['enable_track']}


EOF;
}

function get_box_enable_offtop($checked) {
global $ibforums;
return <<<EOF


<br><input type='checkbox' name='offtop' class='checkbox' value='1' $checked>&nbsp;{$ibforums->lang['enable_offtop']}


EOF;
}


function get_box_enablesig($checked) {
global $ibforums;
return <<<EOF


<br><input type='checkbox' name='enablesig' class='checkbox' value='yes' $checked>&nbsp;{$ibforums->lang['enable_sig']}


EOF;
}


function mm_entry($id, $name) {
global $ibforums;
return <<<EOF


<option value='$id'>$name</option>


EOF;
}


function TableFooter($data, $report_link) {
global $ibforums;
return <<<EOF
<!--IBF.TOPIC_ACTIVE-->
<div class='activeuserstrip' align='center'>&laquo;
<a href='{$ibforums->base_url}showtopic={$data['TOPIC']['tid']}&amp;view=old'>{$ibforums->lang['t_old']}</a> |
<strong><a href='{$ibforums->base_url}showforum={$data['FORUM']['id']}'>{$data['FORUM']['name']}</a></strong> |
<a href='{$ibforums->base_url}showtopic={$data['TOPIC']['tid']}&amp;view=new'>{$ibforums->lang['t_new']}</a> &raquo;
</div></div><br>
<table width='100%' cellpadding='0' cellspacing='0' border='0'>
<tr>
<td width='60%' valign='top'><!--IBF.NAVIGATION--><br><br>{$data['TOPIC']['why_close']}</td>
<td width='40%' align='right' valign='top'>$report_link</td>
</tr>
<tr>
 <td colspan='2'>
  <table width='100%' cellpadding='0' cellspacing='0' border='0'>
   <tr>
    <td align='left' width='20%' nowrap='nowrap'>{$data['TOPIC']['SHOW_PAGES']}</td>
    <td align='right' width='80%'><!--IBF.QUICK_REPLY_CLOSED--> {$data['TOPIC']['REPLY_BUTTON']}{$data['TOPIC']['TOPIC_BUTTON]'}{$data['TOPIC']['POLL_BUTTON']}{$data['TOPIC']['SOLVE_DOWN_BUTTON']}
   </tr>
  </table>
 </td>
</td>
</tr>
<tr><td><!--IBF.MOD_PANEL--></td></tr>
<tr><td><!--IBF.MULTIMOD--></td></tr>
</table>
{$data['TOPIC']['modform_close']}
<!--IBF.QUICK_REPLY_OPEN-->
<br>
skin_page used!
<div align='right'>{$data['FORUM']['JUMP']}</div>
<br>


EOF;
}


}
