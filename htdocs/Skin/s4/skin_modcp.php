<?php

class skin_modcp {

function mm_start() {
global $ibforums;
return <<<EOF
<option value='-1'>------------------------------</option>
<option value='-1'>{$ibforums->lang['mm_title']}</option>
<option value='-1'>------------------------------</option>
EOF;
}


function mm_entry($id, $title) {
global $ibforums;
return <<<EOF
<option value='t_{$id}'>--  $title</option>
EOF;
}

function mm_end() {
global $ibforums;
return <<<EOF

EOF;
}


function mod_cp_start() {
global $ibforums;
return <<<EOF
<div class='tableborder'>
 <div align='center' class='pformstrip'>
  <a href='{$ibforums->base_url}act=modcp&amp;CODE=showforums'>{$ibforums->lang['menu_forums']}</a> &middot;
  <a href='{$ibforums->base_url}act=modcp&amp;CODE=members'>{$ibforums->lang['menu_users']}</a> &middot;
  <a href='{$ibforums->base_url}act=modcp&amp;CODE=ip'>{$ibforums->lang['menu_ip']}</a> &middot;
  <a href='{$ibforums->base_url}act=modcp&amp;CODE=highlight'>{$ibforums->lang['cp_syntax']}</a> &middot;
  <a href='{$ibforums->base_url}act=modcp&amp;CODE=rules_edit'>{$ibforums->lang['rules_edit']}</a>
 </div>
</div>
<br>
EOF;
}


function modtopicview_start($tid,$forumname, $fid, $title) {
global $ibforums;
return <<<EOF

<form name='ibform' action='{$ibforums->base_url}' method='POST'>
<input type='hidden' name='s' value='{$ibforums->session_id}'>
<input type='hidden' name='act' value='modcp'>
<input type='hidden' name='CODE' value='domodposts'>
<input type='hidden' name='f' value='{$fid}'>
<input type='hidden' name='tid' value='{$tid}'>
<strong>{$ibforums->lang['cp_mod_posts_title2']} $forumname</strong>
<br>$pages


<div class='tableborder'>
  <div class='maintitle'>$title</div>

                
EOF;
}


function modpost_topicstart($forumname, $fid) {
global $ibforums;
return <<<EOF
<div class='tableborder'>
  <div class='maintitle'>{$ibforums->lang['cp_mod_posts_title2']} $forumname</div>
  <table width='100%' cellpadding='4' cellspacing='1'>
  <tr>
	<th class='pformstrip' width='40%' align='left'>{$ibforums->lang['cp_3_title']}</th>
	<th class='pformstrip' width='20%' align='center'>{$ibforums->lang['cp_3_replies']}</th>
	<th class='pformstrip' width='20%' align='center'>{$ibforums->lang['cp_3_approveall']}</th>
	<th class='pformstrip' width='20%' align='center'>{$ibforums->lang['cp_3_viewall']}</th>
  </tr>
	 
EOF;
}

function modpost_topicentry($title, $tid, $replies, $fid) {
global $ibforums;
return <<<EOF

   <tr>
	 <td class='row1' width='40%' align='left'><b><a href='{$ibforums->base_url}act=ST&amp;f=$fid&amp;t=$tid' target='_blank'>$title</a></b></td>
	 <td class='row1' width='20%' align='center'>$replies</td>
	 <td class='row1' width='20%' align='center'><a href='{$ibforums->base_url}act=modcp&amp;f=$fid&amp;tid=$tid&amp;CODE=modtopicapprove'>{$ibforums->lang['cp_3_approveall']}</a></td>
	 <td class='row1' width='20%' align='center'><a href='{$ibforums->base_url}act=modcp&amp;f=$fid&amp;tid=$tid&amp;CODE=modtopicview'>{$ibforums->lang['cp_3_viewall']}</a></td>
   </tr>
	 
EOF;
}

function modpost_topicend() {
global $ibforums;
return <<<EOF

   </table>
</div>
	 
EOF;
}



function modtopics_start($pages,$forumname, $fid) {
global $ibforums;
return <<<EOF

<form name='ibform' action='{$ibforums->base_url}' method='POST'>
<input type='hidden' name='s' value='{$ibforums->session_id}'>
<input type='hidden' name='act' value='modcp'>
<input type='hidden' name='CODE' value='domodtopics'>
<input type='hidden' name='f' value='{$fid}'>
<strong>{$ibforums->lang['cp_mod_topics_title2']} $forumname</strong>
<br>$pages	 
EOF;
}

function modtopics_end() {
global $ibforums;
return <<<EOF

<div class='tableborder'>
  <div class='pformstrip' align='center'><input type='submit' value='{$ibforums->lang['cp_1_go']}' class='forminput'></div>
</div>
</form>
	 
EOF;
}


function mod_topic_title($title, $topic_id) {
global $ibforums;
return <<<EOF

<div class='tableborder'>
  <div class='titlemedium'><select name='TID_$topic_id' class='forminput'><option value='approve'>{$ibforums->lang['cp_1_approve']}</option><option value='remove'>{$ibforums->lang['cp_1_remove']}</option><option value='leave'>{$ibforums->lang['cp_1_leave']}</option></select>&nbsp;&nbsp; $title</div>
                
EOF;
}


function mod_postentry($data) {
global $ibforums;
return <<<EOF
   <table width='100%' cellspacing='1'>	
   <tr>
	   <td valign='top' class='row1' nowrap="nowrap"><span class='normalname'>{$data['member']['name']}</span><br><br>{$data['member']['avatar']}<span class='postdetails'><br>{$data['member']['MEMBER_GROUP']}<br>{$data['member']['MEMBER_POSTS']}<br>{$data['member']['MEMBER_JOINED']}</span></td>
	   <td valign='top' class='row1' width='100%'>
		   <b>{$ibforums->lang['posted_on']} {$data['msg']['post_date']}</b><br><br>
		   <span class='postcolor'>
			{$data['msg']['post']}
		   </span>
	   </td>
	</tr>
	</table>		  

EOF;
}

function mod_postentry_checkbox($pid) {
global $ibforums;
return <<<EOF
 <div class='pformstrip' align='right'><select name='PID_$pid' class='forminput'><option value='approve'>{$ibforums->lang['cp_1_approve']}</option><option value='remove'>{$ibforums->lang['cp_1_remove']}</option><option value='leave'>{$ibforums->lang['cp_1_leave']}</option></select>&nbsp;&nbsp;{$ibforums->lang['cp_3_postno']}&nbsp;$pid</div>
EOF;
}


function mod_topic_spacer() {
global $ibforums;
return <<<EOF

</div>
<br>

EOF;
}

function results($text) {
global $ibforums;
return <<<EOF

<tr>
  <td colspan='2'>
    <table cellpadding='2' cellspacing='1' border='0' width='100%' class='fancyborder' align='center'>
     <tr>
       <td><span class='pagetitle'>{$ibforums->lang['cp_results']}</span>
       </td>
     </tr>
	  <tr>
	    <td colspan='2'><b>$text</b></td>
	  </tr>
	 </table>
   </td>
  </tr>

EOF;
}


function prune_confirm($tcount, $count, $link, $link_text, $key) {
global $ibforums;
return <<<EOF
<div class='tableborder'>
  <div class='maintitle'>{$ibforums->lang['mpt_confirm']}</div>
  <div class='pformstrip'>{$ibforums->lang['cp_check_result']}</div>
  <table width='100%' cellspacing='0'>
   <tr>
	<td class='pformleftw'><strong>{$ibforums->lang['cp_total_topics']}</strong></td>
	<td class='pformright'>$tcount</td>
   </tr>
   <tr>
	<td class='pformleftw'><span style='color:red;font-weight:bold;'>{$ibforums->lang['cp_total_match']}</span></td>
	<td class='pformright'><span style='color:red;font-weight:bold;'>$count</span></td>
   </tr>
   </table>
   <form action='{$ibforums->base_url}$link' method='post'>
   <input type='hidden' name='key' value='$key'>
   <div class='pformstrip' align='center'><input type='submit' class='forminput' value='$link_text'></div>
   </form>
</div>
<br>

EOF;
}

function prune_splash($forum, $forums, $select) {
global $ibforums;
return <<<EOF

<!-- IBF.CONFIRM -->
<div class='tableborder'>
  <div class='maintitle'>{$ibforums->lang['cp_prune']} {$forum['name']}</div>
  <div class='pformstrip'>{$ibforums->lang['mpt_help']}</div>
  <div class='tablepad'>{$ibforums->lang['cp_prune_text']}</div>
  <form name='ibform' action='{$ibforums->base_url}' method='POST'>
  <input type='hidden' name='s' value='{$ibforums->session_id}'>
  <input type='hidden' name='act' value='modcp'>
  <input type='hidden' name='CODE' value='prune'>
  <input type='hidden' name='f' value='{$forum['id']}'>
  <input type='hidden' name='check' value='1'>
  <div class='pformstrip'>{$ibforums->lang['mpt_title']}</div>
   <table width='100%' cellspacing='0'>
   <tr>
	<td class='pformleftw'>{$ibforums->lang['cp_action']}</td>
	<td class='pformright'><select name='df' class='forminput'>$forums</select></td>
   </tr>
   <tr>
	<td class='pformleftw'>{$ibforums->lang['cp_prune_days']}</td>
	<td class='pformright'><input type='text' size='40' name='dateline' value='{$ibforums->input['dateline']}' class='forminput'></td>
   </tr>
   <tr>
	<td class='pformleftw'>{$ibforums->lang['cp_prune_type']}</td>
	<td class='pformright'>$select &nbsp; <input type='checkbox' id='cbox' name='ignore_pin' value='1' checked='checked' class='checkbox'>&nbsp;<label for='cbox'>{$ibforums->lang['mps_ignorepin']}</label></td>
   </tr>
   <tr>
	<td class='pformleftw'>{$ibforums->lang['cp_prune_replies']}</td>
	<td class='pformright'><input type='text' size='40' name='posts' value='{$ibforums->input['posts']}' class='forminput'></td>
   </tr>
   <tr>
	<td class='pformleftw'>{$ibforums->lang['cp_prune_member']}</td>
	<td class='pformright'><input type='text' size='40' name='member' value='{$ibforums->input['member']}' class='forminput'></td>
   </tr>
   </table>
  <div class='pformstrip' align='center'><input type='submit' value='{$ibforums->lang['cp_prune_sub1']}' class='forminput'></div>
  </form>
</div>

EOF;
}




function edit_user_form($profile) {
global $ibforums;
return <<<EOF

<form name='ibform' action='{$ibforums->base_url}act=modcp&amp;CODE=compedit&amp;memberid={$profile['id']}' method='post'>
<div class='tableborder'>
  <div class='maintitle'>{$ibforums->lang['cp_edit_user']}: {$profile['name']}</div>
  <table class='tablebasic'>
  <tr>
   <td class='pformleft'>{$ibforums->lang['cp_remove_av']}</td>
   <td class='pformright'><select name='avatar' class='forminput'><option value='0'>{$ibforums->lang['no']}</option><option value='1'>{$ibforums->lang['yes']}</option></select></td>
  </tr>
  <tr>
   <td class='pformleft'>{$ibforums->lang['cp_remove_photo']}</td>
   <td class='pformright'><select name='photo' class='forminput'><option value='0'>{$ibforums->lang['no']}</option><option value='1'>{$ibforums->lang['yes']}</option></select></td>
  </tr>
  <tr>
   <td class='pformleft'>{$ibforums->lang['cp_edit_website']}</td>
   <td class='pformright'><input type='text' size='40' name='website' value='{$profile['website']}' class='forminput'></td>
  </tr>
  <tr>
   <td class='pformleft'>{$ibforums->lang['cp_edit_location']}</td>
   <td class='pformright'><input type='text' size='40' name='location' value='{$profile['location']}' class='forminput'></td>
  </tr>
  <tr>
   <td class='pformleft'>{$ibforums->lang['cp_edit_interests']}</td>
   <td class='pformright'><textarea cols='50' rows='3' name='interests' class='forminput'>{$profile['interests']}</textarea></td>
  </tr>
   <tr>
   <td class='pformleft'>{$ibforums->lang['cp_edit_signature']}</td>
   <td class='pformright'><textarea cols='50' rows='5' name='signature' class='forminput'>{$profile['signature']}</textarea></td>
  </tr>
  </table>
  <div class='pformstrip' align='center'><input type='submit' value='{$ibforums->lang['cp_find_2_submit']}' class='forminput'></div>
</div>
</form>

EOF;
}


function find_two($select) {
global $ibforums;
return <<<EOF
<form name='ibform' action='{$ibforums->base_url}act=modcp&amp;CODE=doedituser' method='post'>
<div class='tableborder'>
  <div class='maintitle'>{$ibforums->lang['cp_edit_user']}</div>
  <table class='tablebasic' cellspacing="1" cellpadding="3">
  <tr>
   <td width='40%' class='row1'>{$ibforums->lang['cp_find_2_user']}</td>
   <td class='row1'>$select</td>
  </tr>
  </table>
  <div class='pformstrip' align='center'><input type='submit' value='{$ibforums->lang['cp_find_2_submit']}' class='forminput'></div>
</div>
</form>
EOF;
}


function find_user() {
global $ibforums;
return <<<EOF

<form name='ibform' action='{$ibforums->base_url}act=modcp&amp;CODE=dofinduser' method='post'>
<div class='tableborder'>
  <div class='maintitle'>{$ibforums->lang['cp_edit_user']}</div>
  <table class='tablebasic' cellspacing="1" cellpadding="3">
  <tr>
   <td width='40%' class='row1'>{$ibforums->lang['cp_find_user']}</td>
   <td class='row1'><input type='text' size='40' name='name' value='' class='forminput'></td>
  </tr>
  </table>
  <div class='pformstrip' align='center'><input type='submit' value='{$ibforums->lang['cp_find_submit']}' class='forminput'></div>
</div>
</form>

EOF;
}


function ip_select_region($forum = "") {
global $ibforums;
return <<<EOF

<td class='row1'>
 <input type='radio' name='region' class='forminput' value='0' checked='checked'>{$ibforums->lang['ip_select_forum']} {$forum}<br>
 <input type='radio' name='region' class='forminput' value='1'>{$ibforums->lang['ip_select_all']}<br>

EOF;
}

function add_ip( $ip_addr, $select ) {
global $ibforums;
return <<<EOF

<br>
<form name='ibform' action='{$ibforums->base_url}' method='post'>
<input type='hidden' name='f' value='{$ibforums->input['f']}'>
<input type='hidden' name='s' value='{$ibforums->session_id}'>
<input type='hidden' name='act' value='modcp'>
<input type='hidden' name='CODE' value='add_ip'>
<div class='tableborder'>
 <div class='maintitle'>{$ibforums->lang['menu_add_ip']}</div>
 <table class='tablebasic'>
 <tr>
   <td width='40%' class='row1'>{$ibforums->lang['ip_enter']}<br>
	 <input type='text' size='3' maxlength='3' name='ip1' value='{$ip_addr[0]}' class='forminput'><b>.</b>
	 <input type='text' size='3' maxlength='3' name='ip2' value='{$ip_addr[1]}' class='forminput'><b>.</b>
	 <input type='text' size='3' maxlength='3' name='ip3' value='{$ip_addr[2]}' class='forminput'><b>.</b>
	 <input type='text' size='3' maxlength='3' name='ip4' value='{$ip_addr[3]}' class='forminput'>&nbsp;
   </td>
   {$select}
    {$ibforums->lang['menu_add_ip_comment']}<br>
    <input type='text' size='50' maxlength='50' name='comment' class='forminput'>
   </td>
  </tr>
  </table>
  <div class='pformstrip' align='center'><input type='submit' value='{$ibforums->lang['ip_add_submit']}' class='forminput'></div>
</div>
</form>

EOF;
}

function add_ip_no() {
global $ibforums;
return <<<EOF

<br>
<div class='tableborder'>
 <div class='maintitle'>{$ibforums->lang['menu_add_ip']}</div>
 <table class='tablebasic' cellpadding='10'>
 <tr><td class='row1' align='center'>
 {$ibforums->lang['prem_no']}
 </td></tr>
 </table>
</div>

EOF;
}

function search_ip_checkboxes() {
global $ibforums;
return <<<EOF

<td class='row1'>
 <input type='checkbox' name='ip_sub' value='1'> {$ibforums->lang['ip_sub']}<br>
 <input type='checkbox' name='ip_sub_include' value='1'> {$ibforums->lang['ip_sub_include']}
</td>

EOF;
}

function ip_start_form($ip_addr, $add_ip, $ip = "", $select = "", $checkboxes = "") {
global $ibforums;
return <<<EOF

<form name='ibform' action='{$ibforums->base_url}' method='post'>
<input type='hidden' name='s' value='{$ibforums->session_id}'>
<input type='hidden' name='act' value='modcp'>
<input type='hidden' name='CODE' value='doip'>
<input type='hidden' name='f' value='{$ibforums->input['f']}'>
<div class='tableborder'>
 <div class='maintitle'>{$ibforums->lang['menu_ip']}</div>
 <table class='tablebasic'>
 <tr>
   <td width='40%' class='row1'>{$ibforums->lang['ip_enter']}</td>
   <td class='row1' width='40%'>
	 <input type='text' size='3' maxlength='3' name='ip1' value='{$ip_addr[0]}' class='forminput'><b>.</b>
	 <input type='text' size='3' maxlength='3' name='ip2' value='{$ip_addr[1]}' class='forminput'><b>.</b>
	 <input type='text' size='3' maxlength='3' name='ip3' value='{$ip_addr[2]}' class='forminput'><b>.</b>
	 <input type='text' size='3' maxlength='3' name='ip4' value='{$ip_addr[3]}' class='forminput'>&nbsp;
	 <select name='iptool' class='forminput'>
		 <option value='resolve'>{$ibforums->lang['ip_resolve']}</option>
		 <option value='posts'>{$ibforums->lang['ip_posts']}</option>
		 <option value='members'>{$ibforums->lang['ip_members']}</option>
	 </select>
   </td>
   {$checkboxes}
  </tr>
  </table>
  <div class='pformstrip' align='center'><input type='submit' value='{$ibforums->lang['ip_submit']}' class='forminput'></div>
</div>
</form>
{$add_ip}
<br>
<form name='ibform' action='{$ibforums->base_url}' method='post'>
<input type='hidden' name='s' value='{$ibforums->session_id}'>
<input type='hidden' name='act' value='modcp'>
<input type='hidden' name='CODE' value='remove_ip'>
<div class='tableborder'>
 <div class='maintitle'>{$ibforums->lang['menu_remove_ip']}</div>
 <table class='tablebasic'>
 <tr>
   <td width='40%' class='row1'>{$ibforums->lang['ip_remove']}</td>
   <td class='row1'>
	 <select name='ip_select' class='forminput'>{$ip}</select>
   </td>
  </tr>
  </table>
  <div class='pformstrip' align='center'><input type='submit' value='{$ibforums->lang['ip_remove_submit']}' class='forminput'></div>
</div>
</form>
<br>
<div class='tableborder'>
 <div class='maintitle'>{$ibforums->lang['iph_title']}</div>
 <div class='tablepad' style='line-height:150%'>{$ibforums->lang['ip_desc_text']}<br><br>{$ibforums->lang['ip_warn_text']}</div>
</div>
EOF;
}

function ip_member_start($pages) {
global $ibforums;
return <<<EOF

<div align='left'>$pages</div>
<br>
<div class='tableborder'>
 <div class='maintitle'>{$ibforums->lang['ipm_title']}</div>
 <table cellpadding='6' class='tablebasic'>
 <tr>
  <th class='pformstrip' width='20%'>{$ibforums->lang['ipm_name']}</th>
  <th class='pformstrip' width='20%'>{$ibforums->lang['ipm_ip']}</th>
  <th class='pformstrip' width='10%'>{$ibforums->lang['ipm_posts']}</th>
  <th class='pformstrip' width='20%'>{$ibforums->lang['ipm_reg']}</th>
  <th class='pformstrip' width='30%'>{$ibforums->lang['ipm_options']}</th>
 </tr>

EOF;
}

function ip_member_row($row) {
global $ibforums;
return <<<EOF

	 <tr>
	  <td class='row2'>{$row['name']}</td>
	  <td class='row2'>{$row['ip_address']}</td>
	  <td class='row2'>{$row['posts']}</td>
	  <td class='row2'>{$row['joined']}</td>
	  <td class='row2' align='center'><a href='{$ibforums->base_url}showuser={$row['id']}' target='_blank'>{$ibforums->lang['ipm_view']}</a>
	  | <a href='{$ibforums->base_url}act=modcp&amp;CODE=doedituser&amp;memberid={$row['id']}'>{$ibforums->lang['ipm_edit']}</a></td>
	 </tr>

EOF;
}

function ip_member_end($pages) {
global $ibforums;
return <<<EOF

	 </table>
</div>
<br>
<div align='left'>$pages</div>
EOF;
}

function splash($tcount, $pcount, $forum) {
global $ibforums;
return <<<EOF

 <tr>
  <td class='pagetitle'>{$ibforums->lang['cp_welcome']}</td>
 </tr>
 <tr>
  <td>{$ibforums->lang['cp_welcome_text']}</td>
 </tr>
 <tr>
  <td>
    <table cellpadding='2' cellspacing='1' border='0' width='75%' class='fancyborder' align='center'>
	  <tr>
	    <td><b>{$ibforums->lang['cp_mod_in']}</b></td>
	    <td>$forum</td>
	  </tr>
	  <tr>
	    <td><b>{$ibforums->lang['cp_topics_wait']}</b></td>
	    <td>$tcount</td>
	  </tr>
	  <tr>
	    <td><b>{$ibforums->lang['cp_posts_wait']}</b></td>
	    <td>$pcount</td>
	  </tr>
	 </table>
   </td>
  </tr>

EOF;
}







function mod_exp($words) {
global $ibforums;
return <<<EOF



                <tr>
                <td class='row1' colspan='2'>$words</td>
                </tr>


EOF;
}

function end_form($action) {
global $ibforums;
return <<<EOF


                <tr>
                <td class='row2' align='center' colspan='2'>
                <input type="submit" name="submit" value="$action" class='forminput'>
                </td></tr></table>
                </td></tr></table>
                </form>


EOF;
}

function forum_row($info) {
global $ibforums;
return <<<EOF
  <tr>
	<td class='row4' align='center' width='5%'>{$info['folder_icon']}</td>
	<td class="row4" colspan=2><b><a href="{$ibforums->base_url}act=modcp&amp;CODE=showtopics&amp;f={$info['id']}">{$info['name']}</a></b><br><span class='desc'>{$info['description']}</span><br>{$info['moderator']}</td>
	<td class="row2" align="center">{$info['q_topics']}</td>
	<td class="row2" align="center">{$info['q_posts']}</td>
	<td class="row2">{$info['last_post']}<br>{$ibforums->lang['in']}: {$info['last_topic']}<br>{$ibforums->lang['by']}: {$info['last_poster']}</td>
	<td class="row2" align="center">{$info['select_button']}</td>        
  </tr>
EOF;
}

function subforum_row($info) {
global $ibforums;
return <<<EOF
  <tr>
	<td class='row4' align='center' width='5%'>&nbsp;</td>
	<td class='row4' align='center' width='5%'>{$info['folder_icon']}</td>
	<td class="row2"><b><a href="{$ibforums->base_url}act=modcp&amp;CODE=showtopics&amp;f={$info['id']}">{$info['name']}</a></b><br><span class='desc'>{$info['description']}</span><br>{$info['moderator']}</td>
	<td class="row2" align="center">{$info['q_topics']}</td>
	<td class="row2" align="center">{$info['q_posts']}</td>
	<td class="row2">{$info['last_post']}<br>{$ibforums->lang['in']}: {$info['last_topic']}<br>{$ibforums->lang['by']}: {$info['last_poster']}</td>
	<td class="row2" align="center"><input type='radio' name='f' value='{$info['id']}'></td>        
  </tr>
EOF;
}

function forum_page_start() {
global $ibforums;
return <<<EOF
<form action='{$ibforums->base_url}act=modcp&amp;CODE=fchoice' method='post'>
<div class='tableborder'>
  <table class='tablebasic' cellspacing="1" cellpadding="3">
EOF;
}


function cat_row($cat_name) {
global $ibforums;
return <<<EOF
  <tr>
	<td colspan='7' class='maintitle'>$cat_name</td>
  </tr>
  <tr> 
	<th class='titlemedium' align='left' width='5%'>&nbsp;</th>
	<th width="35%" class='titlemedium' colspan='2'>{$ibforums->lang['cat_name']}</th>
	<th width="15%" class='titlemedium'>{$ibforums->lang['f_q_topics']}</th>
	<th width="15%" class='titlemedium'>{$ibforums->lang['f_q_posts']}</th>
	<th width="25%" class='titlemedium'>{$ibforums->lang['last_post_info']}</th>
	<th width="5%"  class='titlemedium'>{$ibforums->lang['f_select']}</th>
  </tr>
EOF;
}

function forum_page_end() {
global $ibforums;
return <<<EOF
  <tr>
   <td colspan='7' class='row2' align='right'><b>{$ibforums->lang['f_w_selected']}</b>
   <select class='forminput' name='fact'>
   <option value='mod_topic'>{$ibforums->lang['cp_mod_topics']}</option>
   <option value='mod_post'>{$ibforums->lang['cp_mod_posts']}</option>
   <option value='prune_move'>{$ibforums->lang['cp_prune_posts']}</option>
   </select>&nbsp;<input type='submit' value='{$ibforums->lang['f_go']}' class='forminput'>
   </td>
  </tr>
  </table>
</div>
</form>
EOF;
}

function mod_simple_page($title="",$msg="") {
global $ibforums;
return <<<EOF
<div class='tableborder'>
  <div class='maintitle'>$title</div>
  <div class='tablepad'>$msg</div>
</div>

EOF;
}

function ip_post_results($uid="",$count="") {
global $ibforums;
return <<<EOF
{$ibforums->lang['ipp_found']} $count
<br>
<br>
<a target='_blank' href='{$ibforums->base_url}act=Search&amp;CODE=show&amp;searchid=$uid&amp;search_in=posts&amp;result_type=posts'>{$ibforums->lang['ipp_click']}</a>

EOF;
}

function start_topics($pages,$info) {
global $ibforums;
return <<<EOF

<script language='javascript'>
<!--
 function checkdelete() {
 
   isDelete = document.topic.tact.options[document.topic.tact.selectedIndex].value;
   
   msg = '';
   
   if (isDelete == 'delete')
   {
	   msg = "{$ibforums->lang['cp_js_delete']}";
	   
	   formCheck = confirm(msg);
	   
	   if (formCheck == true)
	   {
		   return true;
	   }
	   else
	   {
		   return false;
	   }
   }
 }
//-->
</script>
<form action='{$ibforums->base_url}act=modcp&amp;f={$info['id']}&amp;CODE=topicchoice' method='post' name='topic' onsubmit='return checkdelete();'>
<div class='pagelinks'>$pages</div>
<div align='right'>
  <a href='{$ibforums->base_url}act=modcp&amp;fact=prune_move&amp;CODE=fchoice&amp;f={$info['id']}'>{$ibforums->lang['cp_prune_posts']}</a>
</div>
<br>
<div class='tableborder'>
  <div class='maintitle'>{$info['name']} [ <a target='_blank' href='{$ibforums->base_url}showforum={$info['id']}'>{$ibforums->lang['new_show_forum']}</a> ]</div>
  <table width='100%' border='0' cellspacing='1' cellpadding='4'>
  <tr> 
	<td class='titlemedium' style='width:5px'>&nbsp;</td>
	<td class='titlemedium' style='width:5px'>&nbsp;</td>
	<td width='40%' class='titlemedium'>{$ibforums->lang['h_topic_title']}</td>
	<td width='15%' align='center' nowrap="nowrap" class='titlemedium'>{$ibforums->lang['h_topic_starter']}</td>
	<td width='7%' align='center' nowrap="nowrap" class='titlemedium'>{$ibforums->lang['h_replies']}</td>
	<td width='8%' align='center' nowrap="nowrap" class='titlemedium'>{$ibforums->lang['h_hits']}</td>
	<td width='25%' nowrap="nowrap" class='titlemedium'>{$ibforums->lang['h_last_action']}</td>
	<td width='5%' nowrap="nowrap" class='titlemedium'>{$ibforums->lang['f_select']}</td>
  </tr>

EOF;
}

function show_no_topics() {
global $ibforums;
return <<<EOF
  <tr> 
	<td class='row4' colspan='8' align='center'>
		<br>
		 <b>{$ibforums->lang['fv_no_topics']}</b>
		<br><br>
	</td>
  </tr>
EOF;
}

function topic_row($data) {
global $ibforums;
return <<<EOF
    <tr> 
	  <td align='center' class='row4'>{$data['folder_img']}</td>
      <td align='center' class='row2'>{$data['topic_icon']}</td>
      <td class='row4'>{$data['prefix']} <a target='_blank' href='{$ibforums->base_url}showtopic={$data['tid']}' title='{$ibforums->lang['topic_started_on']} {$data['start_date']}'>{$data['title']}</a><br><span class='desc'>{$data['description']}</span></td>
      <td align='center' class='row2'>{$data['starter']}</td>
      <td align='center' class='row4'>{$data['posts']}</td>
      <td align='center' class='row2'>{$data['views']}</td>
      <td class='row2'>{$data['last_post']}<br>{$data['last_text']} <b>{$data['last_poster']}</b></td>
      <td align='center' class='row2'><input type='checkbox' name='TID_{$data['real_tid']}' value='1'></td>
    </tr>
EOF;
}

function topics_end($data) {
global $ibforums;
return <<<EOF
  </table>
  <div class='pformstrip' align='center'>
     {$ibforums->lang['t_w_selected']}
	 <select class='forminput' name='tact'>
	 <option value='close'>{$ibforums->lang['cpt_close']}</option>
	 <option value='open'>{$ibforums->lang['cpt_open']}</option>
	 <option value='pin'>{$ibforums->lang['cpt_pin']}</option>
	 <option value='unpin'>{$ibforums->lang['cpt_unpin']}</option>
	 <option value='move'>{$ibforums->lang['cpt_move']}</option>
	 <option value='delete'>{$ibforums->lang['cpt_delete']}</option>
	 <!--IBF.MMOD-->
	 </select> &nbsp;<input type='submit' value='{$ibforums->lang['f_go']}' class='forminput'>
  </div>
</div>
</form>

EOF;
}




function move_checked_form_start($forum_name, $fid) {
global $ibforums;
return <<<EOF
<form action='{$ibforums->base_url}act=modcp&amp;CODE=topicchoice&amp;tact=domove&amp;f=$fid' method='post'>
<div class='tableborder'>
 <div class='maintitle'>{$ibforums->lang['cp_tmove_start']} $forum_name</div>
 <table class='tablebasic'>
EOF;
}

function move_checked_form_entry($tid, $title) {
global $ibforums;
return <<<EOF
  <tr>
   <td class='row1' width='10%' align='center'><input type='checkbox' name='TID_$tid' value='1' checked="checked"></td>
   <td class='row1' width='90%' align='left'><strong>$title</strong></td>
  </tr>
EOF;
}

function move_checked_form_end($jump_html) {
global $ibforums;
return <<<EOF

   </table>
   <div align='center' class='tablepad'>{$ibforums->lang['cp_tmove_to']}&nbsp;&nbsp;<select class='forminput' name='df'>$jump_html</select></div>
   <div align='center' class='pformstrip'><input type='submit' value='{$ibforums->lang['cp_tmove_end']}' class='forminput'></div>
 </div>
</form>
EOF;
}

function syntax_set_form ($syntax_id)
{
	global $DB, $ibforums;
	
	$syntax_set_title			= $ibforums->lang['syntax_set_title'];
	$syntax_set_quest			= $ibforums->lang['syntax_set_quest'];
	$syntax_set_error			= $ibforums->lang['syntax_set_error'];
	$syntax_set_submit			= $ibforums->lang['syntax_set_submit'];
	$syntax_set_action			= 'syntax_set';
	
	$syntax_set_form			= "
		<form name='ibform' action='{$ibforums->base_url}' method='post'>
		<input type='hidden' name='s' value='{$ibforums->session_id}'>
		<input type='hidden' name='act' value='modcp'>
		<input type='hidden' name='CODE' value='syntax_set'>
		<div class='tableborder'>
		<div class='maintitle'>{$syntax_set_title}</div>
		<table class='tablebasic'><tr>
		<td width='50%' class='row1'>{$syntax_set_quest}</td>
		<td width='50%' class='row1'>
		<select name='syntax_set' class='forminput'>";
	
	$DB->query("select l.id, l.syntax, l.description
                   from ibf_syntax_access a
                   left join ibf_syntax_list l
                     on l.id = a.syntax_id
                   where a.member_id = ".$ibforums->member['id']);
	$records					= 0;
	while($row = $DB->fetch_row())
	{
		$id						= $row['id'];
		$code					= $row['syntax'];
		$description			= $row['description'];
		
		if($syntax_id == $id) $syntax_set_form	.= "<option value='{$id}' selected>{$code} - {$description}</option>";
		else  $syntax_set_form	.= "<option value='{$id}'>{$code} - {$description}</option>";
		
		$records ++;
	}
	
	$syntax_set_form			.= "
		</select>		 
		</td></tr></table>
		<div class='pformstrip' align='center'><input type='submit' value='{$syntax_set_submit}' class='forminput'></div>
		</div></form><br>";
		
	if($records == 0)
	{
		$syntax_set_form		= "
			<form name='ibform' action='{$ibforums->base_url}' method='post'>
			<input type='hidden' name='s' value='{$ibforums->session_id}'>
			<input type='hidden' name='act' value='modcp'>
			<input type='hidden' name='CODE' value='syntax_set'>
			<div class='tableborder'>
			<div class='maintitle'>{$syntax_set_title}</div>
			<table class='tablebasic'><tr>
			<td width='50%' class='row1'>{$syntax_set_error}</td>
			<td width='50%' class='row1'>
			</td></tr></table>
			</div></form><br>";
	}

	return $syntax_set_form;
}

function syntax_rule_set_form ($syntax_id, $rule)
{
	global $DB, $ibforums;

	$syntax_rule_title			= $ibforums->lang['syntax_rule_title'];
	$syntax_rule_quest			= $ibforums->lang['syntax_rule_quest'];
	$suntax_rule_new			= $ibforums->lang['suntax_rule_new'];
	$syntax_rule_submit			= $ibforums->lang['syntax_rule_submit'];
	$suntax_rule_action			= 'syntax_rule';
	
	$syntax_rule_form			= '';

	if($syntax_id != '')
	{
		$syntax_rule_form		.=
			"<form name='ibform' action='{$ibforums->base_url}' method='post'>
			<input type='hidden' name='s' value='{$ibforums->session_id}'>
			<input type='hidden' name='act' value='modcp'>
			<input type='hidden' name='CODE' value='syntax_rule'>
			<input type='hidden' name='syntax_set' value='{$syntax_id}'>
			<div class='tableborder'>
			<div class='maintitle'>{$syntax_rule_title}</div>
			<table class='tablebasic'><tr>
			<td width='50%' class='row1'>{$syntax_rule_quest}</td>
			<td width='50%' class='row1'>
			<select name='syntax_rule' class='forminput'>";

		$DB->query("select record, description from ibf_syntax_rules where syntax_id = '".$syntax_id."' order by record");
		while($row = $DB->fetch_row())
		{
			$record					= $row['record'];
			$description			= $row['description'];
			
			if($record == $rule) $syntax_rule_form		.= "<option value='{$record}' selected>{$record}. {$description}</option>";
			else  $syntax_rule_form	.= "<option value='{$record}'>{$record}. {$description}</option>";
		}

		if(($rule == '') || ($rule == 'new')) $syntax_rule_form		.= "<option value='new' selected>{$suntax_rule_new}</option>";
		else  $syntax_rule_form	.= "<option value='new'>{$suntax_rule_new}</option>";

		$syntax_rule_form		.=
			"</select>		 
			</td></tr></table>
			<div class='pformstrip' align='center'><input type='submit' value='{$syntax_rule_submit}' class='forminput'></div>
			</div></form><br>";
	}
	
	return $syntax_rule_form;
}

function syntax_rule_edit_form ($syntax_id, $rule)
{
	global $DB, $std, $ibforums;

	$syntax_edit_title			= $ibforums->lang['syntax_edit_title'];
	$syntax_new_title			= $ibforums->lang['syntax_new_title'];
	
	$syntax_edit_submit			= $ibforums->lang['syntax_edit_submit'];
	$syntax_new_submit			= $ibforums->lang['syntax_new_submit'];
	
	$syntax_edit_description	= $ibforums->lang['syntax_edit_description'];
	$syntax_edit_regexp			= $ibforums->lang['syntax_edit_regexp'];
	
	$syntax_edit_tag			= $ibforums->lang['syntax_edit_tag'];
	$syntax_edit_action			= $ibforums->lang['syntax_edit_action'];
	
	$syntax_edit_form			= '';
	if($syntax_id != '')
	{
		$syntax_edit_form		.= "<form name='ibform' action='{$ibforums->base_url}' method='post'>
		<input type='hidden' name='s' value='{$ibforums->session_id}'>
		<input type='hidden' name='act' value='modcp'>
		<input type='hidden' name='CODE' value='syntax_edit'>
		<input type='hidden' name='syntax_set' value='{$syntax_id}'>";
		
		if(($rule != '') && ($rule != 'new'))
		{
			$syntax_edit_form	.= "
				<input type='hidden' name='syntax_rule' value='{$rule}'>
				<div class='tableborder'><form name='ibform' action='' method='post'>
				<div class='maintitle'>{$syntax_edit_title}</div>";

			$DB->query("select * from ibf_syntax_rules where syntax_id = '".$syntax_id."' and record = ".$rule);
			if($row = $DB->fetch_row())
			{
				$record					= $row['record'];
				$description			= $row['description'];
				$reg_exp				= $std->sql_to_html($row['reg_exp']);

				$syntax_edit_form		.= "<div class='pformstrip' align='left'><b>{$record}. {$description}.</b></div>";
				$syntax_edit_form		.= "<table class='tablebasic'>";
			
				$syntax_edit_form		.= "
					<tr>
						<td width='50%' class='row2'><b>{$syntax_edit_description}</b></td>
						<td width='50%' class='row2'><b>{$syntax_edit_regexp}</b></td>
					</tr>";

				$syntax_edit_form		.= "
					<tr>
						<td width='50%' class='row1'>&nbsp;&nbsp;&nbsp;&nbsp;<input type='text' size='50' maxlength='63' name='description' value='{$description}' class='forminput'><br></td>
						<td width='50%' class='row1'><input type='text' size='60' maxlength='4095' name='reg_exp' value='{$reg_exp}' class='forminput'><br></td>
					</tr>";
				
				$syntax_edit_form		.= "
					<tr>
						<td width='50%' class='row2'><b>{$syntax_edit_tag}</b></td>
						<td width='50%' class='row2'><b>{$syntax_edit_action}</b></td>
					</tr>";
				
				for ($n = 0; $n < 10; $n ++)
				{

						$name			= "tag_".$n;
						$tag			= $row["tag_".$n];
						$syntax_edit_form .= "<tr><td width='50%' class='row1'>{$n}.&nbsp;<input type='text' size='50' maxlength='255' name='{$name}' value='{$tag}' class='forminput'></td>";
				
						$name			= "action_".$n;
						$action			= $row["action_".$n];
						$syntax_edit_form .= "<td width='50%' class='row1'><select name='{$name}' class='forminput'>";
								
						if($action == 'none') $syntax_edit_form .= "<option value='none' selected>none</option>";
						else $syntax_edit_form .= "<option value='none'>none</option>";
						
						if($action == 'tag') $syntax_edit_form .= "<option value='tag' selected>tag</option>";
						else $syntax_edit_form .= "<option value='tag'>tag</option>";

						if($action == 'value') $syntax_edit_form .= "<option value='value' selected>value</option>"; // вот тут добавление
						else $syntax_edit_form .= "<option value='value'>value</option>"; // вот тут добавление

						if($action == 'count') $syntax_edit_form .= "<option value='count' selected>count</option>";
						else $syntax_edit_form .= "<option value='count'>count</option>";
						
						if($action == '') $syntax_edit_form .= "<option value='' selected></option>";
						else $syntax_edit_form .= "<option value=''></option>";

						$syntax_edit_form .= "</select></td></tr>";
				}
			
				$syntax_edit_form		.= "</table>";
				$syntax_edit_form		.= "<div class='pformstrip' align='center'><input type='submit' value='{$syntax_edit_submit}' class='forminput'></div></form></div><br>";
			}
		}
		else
		{
			$syntax_edit_form		.= "
			<input type='hidden' name='syntax_rule' value='new'>
			<div class='tableborder'><form name='ibform' action='' method='post'>
			<div class='maintitle'>{$syntax_new_title}</div>";

			$record					= 0;
			$description			= $ibforums->lang['syntax_rule_description'];

			$syntax_edit_form		.= "<div class='pformstrip' align='left'><b>{$record}. {$description}.</b></div>";
			$syntax_edit_form		.= "<table class='tablebasic'>";
		
			$syntax_edit_form		.= "
				<tr>
					<td width='50%' class='row2'><b>{$syntax_edit_description}</b></td>
					<td width='50%' class='row2'><b>{$syntax_edit_regexp}</b></td>
				</tr>";

			$syntax_edit_form		.= "
				<tr>
					<td width='50%' class='row1'>&nbsp;&nbsp;&nbsp;&nbsp;<input type='text' size='50' maxlength='63' name='description' value='{$description}' class='forminput'><br></td>
					<td width='50%' class='row1'><input type='text' size='60' maxlength='4095' name='reg_exp' value='' class='forminput'><br></td>
				</tr>";
			
			$syntax_edit_form		.= "
				<tr>
					<td width='50%' class='row2'><b>{$syntax_edit_tag}</b></td>
					<td width='50%' class='row2'><b>{$syntax_edit_action}</b></td>
				</tr>";
			
			for ($n = 0; $n < 10; $n ++)
			{
				$syntax_edit_form .= "
					<tr><td width='50%' class='row1'>{$n}.&nbsp;<input type='text' size='50' maxlength='255' name='tag_{$n}' value='' class='forminput'></td>
					<td width='50%' class='row1'><select name='action_{$n}' class='forminput'>
					<option value='none'>none</option>
					<option value='tag'>tag</option>
					<option value='value'>value</option>
					<option value='count'>count</option>
					<option value='' selected></option>
					</select></td></tr>";
			}
		
			$syntax_edit_form		.= "</table>";
			$syntax_edit_form		.= "<div class='pformstrip' align='center'><input type='submit' value='{$syntax_new_submit}' class='forminput'></div></form></div><br>";
		}
	}
	return $syntax_edit_form;
}

function syntax_order_form ($syntax_id)
{
	global $DB, $ibforums;

	$syntax_order_title			= $ibforums->lang['syntax_order_title'];
	
	$syntax_order_apply			= $ibforums->lang['syntax_order_apply'];
	$syntax_order_action		= $ibforums->lang['syntax_order_action'];
	$syntax_delete_action		= $ibforums->lang['syntax_delete_action'];
	$syntax_order_submit		= $ibforums->lang['syntax_order_submit'];
	
	$syntax_order_description	= $ibforums->lang['syntax_order_description'];
	$syntax_order_position		= $ibforums->lang['syntax_order_position'];
	$syntax_order_delete		= $ibforums->lang['syntax_order_delete'];

	$syntax_order_form			= "";

	if($syntax_id != '')
	{
		$syntax_order_form		.= "
			<form name='ibform' action='{$ibforums->base_url}' method='post'>
			<input type='hidden' name='s' value='{$ibforums->session_id}'>
			<input type='hidden' name='act' value='modcp'>
			<input type='hidden' name='CODE' value='syntax_order'>
			<input type='hidden' name='syntax_set' value='{$syntax_id}'>
			<input type='hidden' name='syntax_rule' value='{$rule}'>
			<div class='tableborder'><form name='ibform' action='' method='post'>
			<div class='maintitle'>{$syntax_order_title}</div>";
			
		$syntax_order_form		.= "
			<table class='tablebasic'><tr>
			<td width='50%' class='row2'><b>{$syntax_order_description}</b></td>
			<td width='45%' class='row2'><b>{$syntax_order_position}</b></td>
			<td width='5%' class='row2' align='center'><b>{$syntax_order_delete}</b>
			</td></tr>";
	
		$DB->query("select * from ibf_syntax_rules where syntax_id = '".$syntax_id."' order by record");
		while ($row = $DB->fetch_row())
		{
			$record				= $row['record'];
			$description		= $row['description'];
			$reg_exp			= $row['reg_exp'];
			$order				= "order_".$record;
			$delete				= "delete_".$record;

			$syntax_order_form	.= "
				<tr>
				<td width='50%' class='row1'>{$record}. {$description}.</td>
				<td width='45%' class='row1'><input type='text' size='3' maxlength='3' name='{$order}' value='{$record}' class='forminput'></td>
				<td width='5%' class='row1' align='center'><input type='checkbox' name='{$delete}' value='checked' class='forminput'></td>
				</tr>";
		
		}

		$syntax_order_form		.= "</table>";

		$syntax_order_form		.= "
			<div class='pformstrip' align='center'>
				{$syntax_order_apply}
				<select name='action' class='forminput'>
					<option value='order' selected>{$syntax_order_action}</option>
					<option value='delete'>{$syntax_delete_action}</option>
				</select>
				<input type='submit' value='{$syntax_order_submit}' class='forminput'>
			</div>
			</form></div><br>";

	}

	return $syntax_order_form;
}

function highlight_start_form($syntax_id, $rule) 
{
	global $ibforums, $DB;
	
	$syntax_set_form			= $this->syntax_set_form($syntax_id);
	$syntax_rule_form			= $this->syntax_rule_set_form($syntax_id, $rule);
	$syntax_edit_form			= $this->syntax_rule_edit_form($syntax_id, $rule);
	$syntax_order_form			= $this->syntax_order_form($syntax_id);

	$syntax_help_title			= $ibforums->lang['syntax_help_title'];
	$syntax_help_text			= $ibforums->lang['syntax_help_text'];

	return <<<EOF

	{$syntax_set_form}
	{$syntax_rule_form}
	{$syntax_edit_form}
	{$syntax_order_form}

	<div class='tableborder'>
	<div class='maintitle'>{$syntax_help_title}</div>
	<div class='tablepad' style='line-height:150%'>{$syntax_help_text}</div>
	</div>
EOF;
}

function forum_rules($forum_rules = "" ) {
global $ibforums;
return <<<EOF

<form name='ibform' action='{$ibforums->base_url}' method='post'>
<input type='hidden' name='s' value='{$ibforums->session_id}'>
<input type='hidden' name='act' value='modcp'>
<input type='hidden' name='CODE' value='rules_select'>
<div class='tableborder'>
 <div class='maintitle'>{$ibforums->lang['menu_rules']}</div>
 <table class='tablebasic'>
 <tr>
   <td width='40%' class='row1'>{$ibforums->lang['rules_text']}</td>
   <td class='row1'><select name='f' class='forminput'>{$forum_rules}</select></td>
  </tr>
  </table>
  <div class='pformstrip' align='center'><input type='submit' value='{$ibforums->lang['syntax_set_submit']}' class='forminput'></div>
</div>
</form>
<br>

EOF;
}

function forum_rules_text($title,$txt,$style_no = "",$style_link = "",$style_txt = "",$border_check = "") {
global $ibforums;
return <<<EOF

<form name='ibform' action='{$ibforums->base_url}' method='post'>
<input type='hidden' name='s' value='{$ibforums->session_id}'>
<input type='hidden' name='act' value='modcp'>
<input type='hidden' name='CODE' value='do_rules_apply'>
<input type='hidden' name='f' value='{$ibforums->input['f']}'>
<div class='tableborder'>
 <table class='tablebasic'>
 <tr>
   <td width='40%' class='row1'>{$ibforums->lang['txt_rules_title']}</td>
   <td class='row1'><input type='text' size='80' name='title' value='{$title}' class='forminput'></td>
  </tr>
 <tr>
  <td width='40%' class='row1'>{$ibforums->lang['txt_rules_text']}</td>
  <td class='row1'><textarea cols='80' rows='20' name='rules_txt' class='forminput'
       onKeyPress='if (event.keyCode==10 || ((event.metaKey || event.ctrlKey) && event.keyCode==13))
	this.form.go.click()'>{$txt}</textarea></td>
 </tr>
 <tr>
  <td width='40%' class='row1'>{$ibforums->lang['rules_style']}</td>
  <td class='row1'><select name='style' class='forminput'>
  <option value='0' $style_no>{$ibforums->lang['rules_no_style']}</option>
  <option value='1' $style_link>{$ibforums->lang['rules_link_style']}</option>
  <option value='2' $style_txt>{$ibforums->lang['rules_text_style']}</option></select></td>
 </tr>
 <tr>
  <td width='40%' class='row1'>{$ibforums->lang['rules_checktext']}</td>
  <td class='row1'><input type='checkbox' name='border' value=1 $border_check class='forminput'>&nbsp;{$ibforums->lang['rules_checkbox']}</td>
 </tr>
 </table>
  <div class='pformstrip' align='center'><input type='submit' name='go' value='{$ibforums->lang['syntax_edit_submit']}' class='forminput'></div>
</div>
</form>

EOF;
}


}

