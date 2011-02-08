<?php

class skin_post {

function poll_options() {
global $ibforums;
$output = <<<EOF
<script language='JavaScript'>
function chk_multi() {
document.REPLIER.multi_poll.checked = true
document.REPLIER.weighted_poll.checked = false;
} 
function off_weighted() {
document.REPLIER.weighted_poll.checked = false;
} 
</script>

<tr>
 <td class='pformleft'><b>{$ibforums->lang['poll_only']}</b></td>
 <td class='pformright' colspan='2'><input type='checkbox' size='40' value='1' name='allow_disc' class='forminput' /> {$ibforums->lang['no_replies']}</td>
</tr>
<tr>
 <td class='pformleft'><b>{$ibforums->lang['pe_make_multi']}</b></td>
 <td class='pformright' colspan='2'>
 <input name='multi_poll' type='checkbox' class='forminput' id="multi_poll" value='1' size='40' onClick='javscript:off_weighted()'>
 {$ibforums->lang['pe_min']}: <select name='multi_poll_min' class='forminput' onChange='javscript:chk_multi()'>
EOF;
for ($i=1; $i<$ibforums->vars['max_poll_choices']; $i++) {
 $output .= "<option value='$i'>$i</option>";
}
$output .= <<<EOF
 </select>
 {$ibforums->lang['pe_max']}: <select name='multi_poll_max' class='forminput' onChange='javscript:chk_multi()'>
EOF;
for ($i=1; $i<($ibforums->vars['max_poll_choices']+1); $i++) {
 $output .= "<option value='$i'>$i</option>";
}
$output .= <<<EOF
 </select> {$ibforums->lang['pe_make_multi_def']}
</td>
</tr>

<script language='Javascript'>
function chk_weighted() {
document.REPLIER.weighted_poll.checked = true;
document.REPLIER.multi_poll.checked = false;
} 
function off_multi() {
document.REPLIER.multi_poll.checked = false;
} 
</script>
<tr>
 <td class='pformleft'><b>{$ibforums->lang['pe_make_weighted']}</b></td>
 <td class='pformright' colspan='2'>
 <input name='weighted_poll' type='checkbox' class='forminput' id="weighted_poll" value='1' size='40' onClick='javscript:off_multi()'>
{$ibforums->lang['pe_places']}: <select name='weighted_poll_places' class='forminput' onChange='javscript:chk_weighted()'>
EOF;
for ($i=2; $i<($ibforums->vars['max_poll_choices']+1); $i++) {
 $output .= "<option value='$i'>$i</option>";
}
$output .= <<<EOF
 </select> {$ibforums->lang['pe_make_weighted_def']}
</td>
</tr>
<tr>
<td class='pformleft'>{$ibforums->lang['poll_life_descr1']}</td>
<td class='pformright'><input type='text' size='10' name='life' class='textinput'>
<br><br>{$ibforums->lang['poll_life_descr2']}</td>
</tr>
EOF;
return $output;
}

function poll_end_form($data) {
global $ibforums;
return <<<EOF

<tr>
  <td class='pformstrip' align='center' style='text-align:center' colspan="2">
	<input type="submit" name="submit" value="$data" tabindex='4' class='forminput' accesskey='s'>&nbsp;
  </td>
</tr>
</table>
</form>
<br>
<br clear="all">

EOF;
}


function rights_options($checked) {
global $ibforums;
return <<<EOF

<tr>
   <td class='pformstrip' colspan='2'>{$ibforums->lang['rr_options']}</td>
  </tr>
  <tr>
    <td class='pformleft'>{$ibforums->lang['rrr_options']}</td>
    <td class='pformright'><input type='checkbox' name='club_only' value=1 $checked class='forminput'>&nbsp;{$ibforums->lang['rrr_club_options']}</td>
  </tr>

EOF;
}


function nameField_reg() {
global $ibforums;
return <<<EOF



EOF;
}


function mod_options($jump) {
global $ibforums;
return <<<EOF

<tr>
   <td class='pformstrip' colspan='2'>{$ibforums->lang['tt_options']}</td>
  </tr>
  <tr>
    <td class='pformleft'>{$ibforums->lang['mod_options']}</td>
    <td class='pformright'>$jump</select></td>
  </tr>

EOF;
}


function add_edit_box($checked="") {
global $ibforums;
return <<<EOF

<br><label><input type='checkbox' name='add_edit' class='checkbox' value='yes' $checked>&nbsp;{$ibforums->lang['append_edit']}</label>

EOF;
}


function table_structure() {
global $ibforums;
return <<<EOF

<!--FORUM RULES--><br>
<!--FOUND-->
<!--START TABLE-->
<!--NAME FIELDS-->
<!--TOPIC TITLE-->
<!--POLL BOX-->
<!--POST BOX-->
<!--QUOTE BOX-->
<!--POST ICONS-->
<!--RIGHTS OPTIONS-->
<!--UPLOAD FIELD-->
<!--MOD OPTIONS-->
<!--MERGE OPTIONS-->
<!--END TABLE-->

EOF;
}


function poll_box($data, $extra="") {
global $ibforums;
return <<<EOF

<tr>
  <td colspan='2' class='pformstrip'>{$ibforums->lang['tt_poll_settings']}</td>
</tr>
<tr>
  <td class='pformleft'><strong>{$ibforums->lang['poll_question']}</strong></td>
  <td class='pformright'><input type='text' size='40' maxlength='250' name='pollq' value='{$ibforums->input['pollq']}' class='textinput'></td>
</tr>
<tr>
  <td class='pformleft'>{$ibforums->lang['poll_choices']}<br><br>$extra</td>
  <td class='pformright'><textarea cols='60' rows='12' name='PollAnswers' class='textinput'>$data</textarea><!--IBF.POLL_OPTIONS--></td>
</tr>

EOF;
}


function TopicSummary_top() {
global $ibforums;
return <<<EOF

<br>
<div class="tableborder">
  <div class="pformstrip">{$ibforums->lang['last_posts']}</div>
  <table cellpadding='6' cellspacing='1' border='0' width='100%'>

EOF;
}


function quote_box($data) {
global $ibforums;
return <<<EOF

<tr>
  <td colspan='2' class='pformstrip'>{$ibforums->lang['post_to_quote']}</td>
</tr>
<tr>
  <td class='pformleft'>{$ibforums->lang['post_to_quote_txt']}</td>
  <td class='pformright'><textarea cols='60' rows='12' wrap='soft' name='QPost' class='textinput'>{$data['post']}</textarea><input type='hidden' name='QAuthor' value='{$data['author_id']}'><input type='hidden' name='QAuthorN' value='{$data['author_name']}'><input type='hidden' name='QDate'   value='{$data['post_date']}'></td>
</tr>

EOF;
}


function preview($data) {
global $ibforums;
return <<<EOF

<div class="tableborder">
  <div class="pformstrip">{$ibforums->lang['post_preview']}</div>
  <div class="row1" style="padding:6px"><div class='postcolor'>$data</div></div>
</div>
<br>

EOF;
}

/**
 * 
 * 
 * @param array $data
 * 0 => array(
 * 	 'time'    => 'Today 12:15',
 *   'member'  => 'ivan',
 *   'old_text'=> 'Hello',
 *   'new_text'=> 'Hello!'
 *  ) 
 */
function edit_history(array $data, $forum_id, $topic_id, $post_id) {
global $ibforums;
$res ='<div class="tableborder">';
$classes = array('post1', 'post2');
$i = 0;
if (count($data)){
foreach ($data as $history_item) {
  $class = $classes[++$i % 2]; 
  $res .= <<<EOF
  <table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr>
  <th class="row4">{$history_item['time']} by {$history_item['member']}<br>Было (<a href="{$ibforums->base_url}act=Post&amp;CODE=08&amp;f={$forum_id}&amp;t={$topic_id}&amp;p={$post_id}&amp;restore_id={$history_item['id']}&amp;preview=1">восстановить</a>)
  </td>
  <th valign="bottom" class="row4">Стало</td>
  </tr>
	<tr>
	<td width="50%" valign="top" class="$class">{$history_item['old_text']}</td>
	<td width="50%" valign="top" class="$class">{$history_item['new_text']}</td>
	</tr>
	</table>
	<div class="darkrow1" style="height:5px">
</div>
EOF;
}
}else{
  $res .= <<<EOF
  <table width="100%" border="0" cellspacing="1" cellpadding="3">
	<tr>
	<td width="100%" valign="top" class="row4">{$ibforums->lang['post_history_empty']}</td>
	</tr>
	</table>
	<div class="darkrow1" style="height:5px">
</div>
EOF;
	
}
$res .= '</div>';
return $res;
}


function topictitle_fields($data) {
global $ibforums;
return <<<EOF

<tr>
 <td colspan="2" class='pformstrip'>{$ibforums->lang['tt_topic_settings']}</td>
</tr>
<tr>
  <td class='pformleft'>{$ibforums->lang['topic_title']}</td>
  <td class='pformright'><input type='text' size='100%' maxlength='255' name='TopicTitle' value='{$data[TITLE]}' tabindex='1' class='forminput'></td>
</tr>
<tr>
   <td class='pformleft'>{$ibforums->lang['topic_desc']}</td>
   <td class='pformright'><input type='text' size='100%' maxlength='255' name='TopicDesc' value='{$data[DESC]}' tabindex='2' class='forminput'></td>
</tr>

EOF;
}


function TopicSummary_body($data) {
global $ibforums;
return <<<EOF

<tr>
    <td class='row4' valign='top' width='20%'><b>{$data['author']}</b></td>
    <td class='row4' valign='top' width='80%'>{$ibforums->lang['posted_on']} {$data['date']}</td>
  </tr>
  <tr>
    <td class='row1' valign='top' width='20%'>&nbsp;</td>
    <td class='row1' valign='top' width='80%'><span class='postcolor'>{$data['post']}</span></td>
  </tr>

EOF;
}


function smilie_table() {
global $ibforums;
return <<<EOF

<b>{$ibforums->lang['click_smilie']}</b>
<div class="tablefill" style="overflow: auto; height: 310px; width: 170px;">
<table cellpadding='0' align='center'>
<!--THE SMILIES-->
</table>
</div>
<b><a href='javascript:emo_pop()'>{$ibforums->lang['all_emoticons']}</a></b><br />
EOF;
}


function get_box_enableemo($checked) {
global $ibforums;
return <<<EOF

<input type='checkbox' name='enableemo' class='checkbox' value='yes' $checked>&nbsp;{$ibforums->lang['enable_emo']}

EOF;
}

function get_box_enabletrack($checked) {
global $ibforums;
return <<<EOF

<br><label><input type='checkbox' name='enabletrack' class='checkbox' value='1' $checked>&nbsp;{$ibforums->lang['enable_track']}</label>

EOF;
}

function get_box_enablefav($checked) {
global $ibforums;
return <<<EOF

<br><label><input type='checkbox' name='fav' class='checkbox' value='yes' $checked>&nbsp;{$ibforums->lang['enable_fav']}</label>

EOF;
}

function get_box_enable_offtop($checked) {
global $ibforums;
return <<<EOF

<br><label><input type='checkbox' name='offtop' class='checkbox' value='yes' $checked>&nbsp;{$ibforums->lang['enable_offtop']}</label>

EOF;
}

function get_box_bump($checked) {
global $ibforums;
return <<<EOF

<br><label><input type='checkbox' name='bump' class='checkbox' value='yes' $checked>&nbsp;{$ibforums->lang['do_not_bump']}</label>

EOF;
}

function get_box_alreadytrack() {
global $ibforums;
return <<<EOF

<br>{$ibforums->lang['already_sub']}

EOF;
}


function edit_upload_field($data, $files) {
global $ibforums;
$res = <<<EOF

<tr> 
          <td class="pformstrip" colspan='2'>{$ibforums->lang['upload_title']}</td>
        </tr>
        <tr> 
          <td class='pformleft'>{$ibforums->lang['upload_text']} $data</td>
          <td class='pformright'>
		<table cellpadding='4' cellspacing='0' width='100%' border='0'>
EOF;
foreach($files as $attach) {
	$res .=  <<<EOF
            <tr>
             <td>{$attach->filename()} <label><input type='radio' name='editupload[{$attach->attachId()}]' value='keep' checked="checked"><b>{$ibforums->lang['eu_keep']}</b></label>
             <label><input type='radio' name='editupload[{$attach->attachId()}]' value='delete'><b>{$ibforums->lang['eu_delete']}</b></label>
             </td>
            </tr>
EOF;
}

$res .= <<<EOF
       </table>
			{$ibforums->lang['upload_add_files_to_post']}
		    <div id="upload_container">
    		<span id='first_upload_container'>
		    <input class='textinput' type='file' size='30' name='FILE_UPLOAD[0]' id='first_upload_element'><button type="buton" onclick='clearFirstUploadField()'>-</button><button type='button' onclick='tag_attach(0)'>[attach]</button>
		    </span>
		    </div>
		    <button onclick="addUpload()" type="button">{$ibforums->lang['upload_add_one_file']}</button>
			</td>
        </tr>

EOF;
return $res;
}

function pm_postbox_buttons($data, $syntax_select = "") {
return $this->postbox_buttons($data, $syntax_select, '','');
}


function errors($data) {
global $ibforums;
return <<<EOF

<div class="tableborder">
  <div class="pformstrip">{$ibforums->lang['errors_found']}</div>
  <div class="tablepad"><span class='postcolor'>$data</span></div>
</div>
<br>

EOF;
}

function get_javascript() {
global $ibforums;
return <<<EOF

<script language='javascript' type='text/javascript'>
var base_url		= "{$ibforums->base_url}";
var tid			= "{$ibforums->input['t']}";
var session_id		= "{$ibforums->session_id}";
var error_no_url        = "{$ibforums->lang['jscode_error_no_url']}";
var error_no_title      = "{$ibforums->lang['jscode_error_no_title']}";
var error_no_email      = "{$ibforums->lang['jscode_error_no_email']}";
var error_no_width      = "{$ibforums->lang['jscode_error_no_width']}";
var error_no_height     = "{$ibforums->lang['jscode_error_no_height']}";
var text_enter_url      = "{$ibforums->lang['jscode_text_enter_url']}";
var text_enter_url_name = "{$ibforums->lang['jscode_text_enter_url_name']}";
var text_enter_image    = "{$ibforums->lang['jscode_text_enter_image']}";
var list_prompt         = "{$ibforums->lang['js_tag_list']}";
var prompt_start        = "{$ibforums->lang['js_text_to_format']}";
var tt_prompt		= "{$ibforums->lang['tt_prompt']}";
var js_del_1		= "{$ibforums->lang['js_del_1']}";
var js_del_2		= "{$ibforums->lang['js_del_2']}";
var msg_no_title	= "{$ibforums->lang['msg_no_title']}";
var js_no_message	= "{$ibforums->lang['js_no_message']}";
var js_max_length	= "{$ibforums->lang['js_max_length']}";
var js_characters	= "{$ibforums->lang['js_characters']}";
var js_current		= "{$ibforums->lang['js_current']}";
var error_no_url        = "{$ibforums->lang['jscode_error_no_url']}";
var error_no_title      = "{$ibforums->lang['jscode_error_no_title']}";
var error_no_email      = "{$ibforums->lang['jscode_error_no_email']}";
var error_no_width      = "{$ibforums->lang['jscode_error_no_width']}";
var error_no_height     = "{$ibforums->lang['jscode_error_no_height']}";
var text_enter_url      = "{$ibforums->lang['jscode_text_enter_url']}";
var text_enter_url_name = "{$ibforums->lang['jscode_text_enter_url_name']}";
var text_enter_image    = "{$ibforums->lang['jscode_text_enter_image']}";
var text_enter_spoiler    = "{$ibforums->lang['jscode_text_enter_spoiler']}";
var text_spoiler_hidden_text    = "{$ibforums->lang['spoiler']}";


var list_prompt         = "{$ibforums->lang['js_tag_list']}";
var prompt_start        = "{$ibforums->lang['js_text_to_format']}";
var MessageMax  	= "{$ibforums->lang['the_max_length']}";
var Override    	= "{$ibforums->lang['override']}";
var rusLet		= new Array("Э","Щ","Щ","Ч","Ч","Ш","Ш","Ё","Ё","Ё","Ё","Ю","Ю","Ю","Ю","Я","Я","Я","Я","Ж","Ж","А","Б","В","Г","Д","Е","З","ИЙ","ИЙ","ЫЙ","ЫЙ","И","Й","К","КС","Л","М","Н","О","П","Р","С","Т","У","Ф","Х","Ц","Щ","Ы","э","щ","ч","ш","ё","ё","ю","ю","я","я","ж","а","б","в","г","д","е","з","ий","ий","ый","ый","и","й","к","кс","л","м","н","о","п","р","с","т","у","ф","х","ц","щ","щ","ы","ъ","ъ","ь");
var engReg		= new Array(/E'/g,/SHCH/g,/Shch/g,/CH/g,/Ch/g,/SH/g,/Sh/g,/YO/g,/JO/g,/Yo/g,/Jo/g,/YU/g,/JU/g,/Yu/g,/Ju/g,/YA/g,/JA/g,/Ya/g,/Ja/g,/ZH/g,/Zh/g,/A/g,/B/g,/V/g,/G/g,/D/g,/E/g,/Z/g,/II/g,/IY/g,/YI/g,/YY/g,/I/g,/J/g,/K/g,/X/g,/L/g,/M/g,/N/g,/O/g,/P/g,/R/g,/S/g,/T/g,/U/g,/F/g,/H/g,/C/g,/W/g,/Y/g,/e'/g,/shch/g,/ch/g,/sh/g,/yo/g,/jo/g,/yu/g,/ju/g,/ya/g,/ja/g,/zh/g,/a/g,/b/g,/v/g,/g/g,/d/g,/e/g,/z/g,/ii/g,/iy/g,/yi/g,/yy/g,/i/g,/j/g,/k/g,/x/g,/l/g,/m/g,/n/g,/o/g,/p/g,/r/g,/s/g,/t/g,/u/g,/f/g,/h/g,/c/g,/w/g,/#/g,/y/g,/`/g,/~/g,/'/g);
MessageMax      	= parseInt(MessageMax);
if ( MessageMax < 0 ) MessageMax = 0;
</script>
<script type='text/javascript' src='{$ibforums->vars['html_url']}/topics.js?{$ibforums->vars['client_script_version']}'></script>
<script type='text/javascript' src='{$ibforums->vars['html_url']}/video.js?{$ibforums->vars['client_script_version']}'></script>

EOF;
}


function table_top($data) {
global $ibforums;
return <<<EOF

<table class='tableborder' cellpadding="0" cellspacing="0" width="100%">
<tr><td class='maintitle' colspan='2'>&nbsp;&nbsp;$data</td></tr>

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



function PostIcons() {
global $ibforums;
return <<<EOF

<tr>
  <td class='pformleft'>{$ibforums->lang['post_icon']}</td>
  <td class='pformright'>
	<input type="radio" class="radiobutton" name="iconid" value="1">&nbsp;&nbsp;<img src="{$ibforums->vars['img_url']}/icon1.gif"  align='middle' alt=''>&nbsp;&nbsp;&nbsp;<input type="radio" class="radiobutton" name="iconid" value="2" >&nbsp;&nbsp;<img src="{$ibforums->vars['img_url']}/icon2.gif"  align='middle' alt=''>&nbsp;&nbsp;&nbsp;<input type="radio" class="radiobutton" name="iconid" value="3" >&nbsp;&nbsp;<img src="{$ibforums->vars['img_url']}/icon3.gif"  align='middle' alt=''>&nbsp;&nbsp;&nbsp;<input type="radio" class="radiobutton" name="iconid" value="4" >&nbsp;&nbsp;<img src="{$ibforums->vars['img_url']}/icon4.gif"  align='middle' alt=''>&nbsp;&nbsp;&nbsp;<input type="radio" class="radiobutton" name="iconid" value="5" >&nbsp;&nbsp;<img src="{$ibforums->vars['img_url']}/icon5.gif"  align='middle' alt=''>&nbsp;&nbsp;&nbsp;<input type="radio" class="radiobutton" name="iconid" value="6" >&nbsp;&nbsp;<img src="{$ibforums->vars['img_url']}/icon6.gif"  align='middle' alt=''>&nbsp;&nbsp;&nbsp;<input type="radio" class="radiobutton" name="iconid" value="7" >&nbsp;&nbsp;<img src="{$ibforums->vars['img_url']}/icon7.gif"  align='middle' alt=''><br>
	<input type="radio" class="radiobutton" name="iconid" value="8">&nbsp;&nbsp;<img src="{$ibforums->vars['img_url']}/icon8.gif"  align='middle' alt=''>&nbsp;&nbsp;&nbsp;<input type="radio" class="radiobutton" name="iconid" value="9" >&nbsp;&nbsp;<img src="{$ibforums->vars['img_url']}/icon9.gif"  align='middle' alt=''>&nbsp;&nbsp;&nbsp;<input type="radio" class="radiobutton" name="iconid" value="10" >&nbsp;&nbsp;<img src="{$ibforums->vars['img_url']}/icon10.gif"  align='middle' alt=''>&nbsp;&nbsp;&nbsp;<input type="radio" class="radiobutton" name="iconid" value="11" >&nbsp;&nbsp;<img src="{$ibforums->vars['img_url']}/icon11.gif"  align='middle' alt=''>&nbsp;&nbsp;&nbsp;<input type="radio" class="radiobutton" name="iconid" value="12" >&nbsp;&nbsp;<img src="{$ibforums->vars['img_url']}/icon12.gif"  align='middle' alt=''>&nbsp;&nbsp;&nbsp;<input type="radio" class="radiobutton" name="iconid" value="13" >&nbsp;&nbsp;<img src="{$ibforums->vars['img_url']}/icon13.gif"  align='middle' alt=''>&nbsp;&nbsp;&nbsp;<input type="radio" class="radiobutton" name="iconid" value="14" >&nbsp;&nbsp;<img src="{$ibforums->vars['img_url']}/icon14.gif"  align='middle' alt=''><br>
    <input type="radio" class="radiobutton" name="iconid" value="0" checked="checked">&nbsp;&nbsp;[ Use None ]
  </td>
 </tr>

EOF;
}


function EndForm($data) {
global $ibforums;
return <<<EOF

<tr>
  <td class='pformstrip' align='center' style='text-align:center' colspan='2'>
	<input type="submit" name="submit" value="$data" tabindex='4' class='forminput' accesskey='s'>&nbsp;
	<input type="submit" name="preview" value="{$ibforums->lang['button_preview']}" tabindex='5' class='forminput'>
  </td>
</tr>
</table>
</form>
<br>
<br clear="all">

EOF;
}


function Upload_field($data) {
global $ibforums;
return <<<EOF

<tr>
    <td colspan='2' class='pformstrip'>{$ibforums->lang['upload_title']}</td>
  </tr>
  <tr>
    <td class='pformleft'>{$ibforums->lang['upload_text']} $data</td>
    <td class='pformright'>
    <div id="upload_container">
    <span id='first_upload_container'>
    <input class='textinput' type='file' size='30' name='FILE_UPLOAD[0]' id='first_upload_element'><button type="buton" onclick='clearFirstUploadField()'>-</button><button type='button' onclick='tag_attach(0)'>[attach]</button>
    </span>
    </div>
    <button onclick="addUpload()" type="button">{$ibforums->lang['upload_add_one_file']}</button>
    </td>
  </tr>

EOF;
}


function TopicSummary_bottom() {
global $ibforums;
return <<<EOF

</table>
  <div class="pformstrip"><a href="javascript:PopUp('{$ibforums->base_url}act=ST&amp;f={$ibforums->input['f']}&amp;t={$ibforums->input['t']}','TopicSummary',700,450,1,1)">{$ibforums->lang['review_topic']}</a></div>
</div>

EOF;
}


function add_merge_edit_box($checked="") {
global $ibforums;
return <<<EOF

<br><label><input type='checkbox' name='add_merge_edit' class='checkbox' value='yes' $checked>&nbsp;{$ibforums->lang['edit_merge_answer']}</label>

EOF;
}


function postbox_buttons($data, $syntax_select = "", $mod_buttons = "", $topic_decided = "") {
global $ibforums;
//$ipicture = $ibforums->vars['use_ipicture_button']
//	? "<a href='http://ipicture.ru/' target='ipicture' style='text-decoration:none' title='{$ibforums->lang['ipicture_title']}'><input type='button' value=' iPicture ' class='codebuttons' name='ipicture'></a>"
//	: "";
$ipicture = $ibforums->vars['use_ipicture_button']
	? "<input type='button' value=' iPicture ' class='codebuttons' name='ipicture' title='{$ibforums->lang['ipicture_title']}' onclick='PopUp(\"http://ipicture.ru/\", \"iPicture\", 640,480,1,1,1)'>"
	: "";
		
return <<<EOF

<tr><td class='pformstrip' colspan='2'>{$ibforums->lang['ib_code_buttons']}</td></tr>
<tr>
   <td class='pformright' align='center' colspan='2'>
     <script type='text/javascript' src='{$ibforums->vars['board_url']}/html/ibfcode.js?{$ibforums->vars['client_script_version']}'></script>
	   <select name='ffont' class='codebuttons' onchange="alterfont(this.options[this.selectedIndex].value, 'FONT')">
	   <option value='0'>{$ibforums->lang['ct_font']}</option>
	   <option value='Arial' style='font-family:Arial'>{$ibforums->lang['ct_arial']}</option>
	   <option value='Times' style='font-family:Times'>{$ibforums->lang['ct_times']}</option>
	   <option value='Courier' style='font-family:Courier'>{$ibforums->lang['ct_courier']}</option>
	   <option value='Impact' style='font-family:Impact'>{$ibforums->lang['ct_impact']}</option>
	   <option value='Geneva' style='font-family:Geneva'>{$ibforums->lang['ct_geneva']}</option>
	   <option value='Optima' style='font-family:Optima'>Optima</option>
	   </select>

	   <select name='fsize' class='codebuttons' onchange="alterfont(this.options[this.selectedIndex].value, 'SIZE')">
	   <option value='0'>{$ibforums->lang['ct_size']}</option>
	   <option value='1'>{$ibforums->lang['ct_sml']}</option>
	   <option value='7'>{$ibforums->lang['ct_lrg']}</option>
	   <option value='14'>{$ibforums->lang['ct_lest']}</option>
	   </select>

	   <select name='fcolor' class='codebuttons' onchange="alterfont(this.options[this.selectedIndex].value, 'COLOR')">
	   <option value='0'>{$ibforums->lang['ct_color']}</option>
	   <option value='black' style='color:black'>{$ibforums->lang['ct_black']}</option>
	   <option value='blue' style='color:blue'>{$ibforums->lang['ct_blue']}</option>
	   <option value='red' style='color:red'>{$ibforums->lang['ct_red']}</option>
	   <option value='purple' style='color:purple'>{$ibforums->lang['ct_purple']}</option>
	   <option value='orange' style='color:orange'>{$ibforums->lang['ct_orange']}</option>
	   <option value='yellow' style='color:yellow'>{$ibforums->lang['ct_yellow']}</option>
	   <option value='gray' style='color:gray'>{$ibforums->lang['ct_grey']}</option>
	   <option value='green' style='color:green'>{$ibforums->lang['ct_green']}</option>
	   </select>


	   <input type='button' accesskey='b' value=' B ' onclick='simpletag("B")' class='codebuttons' name='B' title='Bold' style="font-weight:bold">
	   <input type='button' accesskey='i' value=' I ' onclick='simpletag("I")' class='codebuttons' name='I' title='Italic' style="font-style:italic">
	   <input type='button' accesskey='u' value=' U ' onclick='simpletag("U")' class='codebuttons' name='U' title='Underline' style="text-decoration:underline">
	   <input type='button' accesskey='s' value=' S ' onclick='simpletag("S")' class='codebuttons' name='S' title='Strike' style="text-decoration:line-through">
	   <input type='button' accesskey='o' value=' O ' onclick='simpletag("O")' class='codebuttons' name='O' title='Overline' style="text-decoration:overline">

	   <!-- barazuk: [o] [sub] [sup] -->
	   <input type='button' value='sub' onclick='simpletag("sub")' class='codebuttons' name='sub' title='Subscript'>
           <input type='button' value='sup' onclick='simpletag("sup")' class='codebuttons' name='sup' title='Superscript'>

	   <input type='button' value=' L ' onclick='simpletag("L")' class='codebuttons' name='L' title='Left'">
	   <input type='button' accesskey='с' value=' C ' onclick='simpletag("C")' class='codebuttons' name='C' title='Center'">
	   <input type='button' accesskey='r' value=' R ' onclick='simpletag("R")' class='codebuttons' name='R' title='Rigth'">
	   
         <!-- e-moe: table buttons -->
         <input value='table' onclick='tag_table()' class='codebuttons' name='table' title='Таблица' type='button'>
	 <input value='tr' onclick='simpletag("tr")' class='codebuttons' name='tr' title='Ряд' type='button'>
	 <input value='td' onclick='simpletag("td")' class='codebuttons' name='td' title='Ячейка' type='button'>
	 <input value='th' onclick='simpletag("th")' class='codebuttons' name='th' title='Шапка' type='button'>
			 
	   <!-- barazuk: [HR] -->
	   <input type='button' value='hr' onclick='doInsert("[HR]", true)' class='codebuttons' name='hr' title='Horizontal Line'>&nbsp;

			 

	   {$syntax_select}

	   <!--div style="height:3px"--><!-- --><!--/div-->

	   <input type='button' accesskey='l' value=' LIST ' onclick='tag_list()' class='codebuttons' name="LIST">&nbsp;
	   <input type='button' accesskey='q' value='QUOTE' onclick='simpletag("QUOTE")' class='codebuttons' name='QUOTE'>
	   <input type='button' accesskey='p' value='Spoiler' onclick='tag_spoiler()' class='codebuttons' name='spoiler'>
	   <input type='button' accesskey='h' value=' http:// ' onclick='tag_url()' class='codebuttons' name='url'>
	   <input type='button' accesskey='g' value=' IMG ' onclick='tag_image()' class='codebuttons' name='img'>

           <!-- iPicture.ru Button -->
           {$ipicture}
	   
           <input type='button' accesskey='y' value='TRANSLIT' onClick='rusLang()'class='codebuttons' name="TRANSLIT">
           <input type='button' accesskey='r' value='Русская клавиатура' onclick='javascript:keyb_pop()' class='codebuttons'>

	   &nbsp;&nbsp;
	   <input type='button' value='{$ibforums->lang['js_close_all_tags']}' onclick='javascript:closeall();' class='codebuttons'>
	   {$mod_buttons}
   </td>
</tr>
{$topic_decided}
<tr>
   <td class='pformstrip' colspan='2'>{$ibforums->lang['post']}</td>
</tr>
<tr>
   <td class='pformleft' align='center' width='10%'>
	   <!--SMILIE TABLE-->
	   <br>
           <div class='desc'><strong>&middot; <a href='javascript:bbc_pop()'>{$ibforums->lang['bbc_help']}</a> &middot;</strong></div>
   </td>
   <td class="pformright" valign='top'>
    <textarea name='Post' rows='24'
        onKeyPress='if (event.keyCode==10 || ((event.metaKey || event.ctrlKey) && event.keyCode==13))
	this.form.submit.click()' tabindex='3' class='textinput' style='width:99%;'>$data</textarea>
   </td>
</tr>
<tr>
    <td class='pformleft'><b>{$ibforums->lang['po_options']}</b>
    </td>
    <td class='pformright'>
	 <!--IBF.EMO-->
	 <!--IBF.TRACK-->
	 <!--IBF.FAV-->
	 <!--IBF.OFFTOP-->
	 <!--IBF.BUMP-->
	 <!--IBF.MOD_ADD_EDIT_LABEL-->
	 <!--IBF.MERGE_POST_LABEL-->
	 </td>
</tr>

EOF;
}


}
