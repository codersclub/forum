<?php

class skin_msg {



function address_add($mem_to_add) {
global $ibforums;
return <<<EOF

<form class="b-address-add-form" action="{$ibforums->base_url}" method="post">
<input type='hidden' name='act' value='Msg'>
<input type='hidden' name='CODE' value='09'>
<h3 class="b-title">{$ibforums->lang['member_add']}</h3>
<table class="b-address-add-form__fields-wrapper">
<tr>
 <td class="b-form-element b-form-element_name"><label>{$ibforums->lang['enter_a_name']}</label><input type='text' name='mem_name' size='20' maxlength='40' value='$mem_to_add' class='forminput'></td>
 <td class="b-form-element b-form-element_description"><label>{$ibforums->lang['enter_desc']}</label><input type='text' name='mem_desc' size='30' maxlength='60' value='' class='forminput'></td>
 <td class="b-form-element b-form-element_show-online"><label>{$ibforums->lang['show_online']}</label><select name='show_online' class='forminput'><option value='yes'>{$ibforums->lang['yes']}<option value='no' selected="selected">{$ibforums->lang['no']}</select></td>
 <td class="b-form-element b-form-element_allow-messages"><label>{$ibforums->lang['allow_msg']}</label><select name='allow_msg' class='forminput'><option value='yes' selected="selected">{$ibforums->lang['yes']}<option value='no'>{$ibforums->lang['no']}</select></td>
</tr>
</table>
<div class="b-buttons-wrapper pformstrip"><input type="submit" value="{$ibforums->lang['submit_address']}" class='forminput'></div>
</form>

EOF;
}


function Render_msg($data) {
global $ibforums;
return <<<EOF

<script language='javascript' type="text/javascript">
<!--
function PopUp(url, name, width,height,center,resize,scroll,posleft,postop) {
if (posleft != 0) { x = posleft }
if (postop  != 0) { y = postop  }
if (!scroll) { scroll = 1 }
if (!resize) { resize = 1 }
if ((parseInt (navigator.appVersion) >= 4 ) && (center)) {
  X = (screen.width  - width ) / 2;
  Y = (screen.height - height) / 2;
}
if (scroll != 0) { scroll = 1 }
var Win = window.open( url, name, 'width='+width+',height='+height+',top='+Y+',left='+X+',resizable='+resize+',scrollbars='+scroll+',location=no,directories=no,status=no,menubar=no,toolbar=no');
}
//-->
</script>
<h3>{$data['msg']['title']}</h3>
<div align="right" style="padding:6px;font-weight:bold">
  [ <a href='{$ibforums->base_url}CODE=04&amp;act=Msg&amp;MSID={$data['msg']['msg_id']}&amp;MID={$data['member']['id']}&amp;fwd=1'>{$ibforums->lang['vm_forward_pm']}</a> | <a href='{$ibforums->base_url}CODE=04&amp;act=Msg&amp;MID={$data['member']['id']}&amp;MSID={$data['msg']['msg_id']}'>{$ibforums->lang['pm_reply_link']}</a> ]
</div>
<div class="tableborder">
 <div class="titlemedium">&nbsp;&nbsp;{$ibforums->lang['m_pmessage']}</div>
 <table width='100%' cellpadding='3' cellspacing='1'>
  <tr>
      <td valign='middle' class='row4'><span class='normalname'><a href="{$ibforums->base_url}showuser={$data['member']['id']}">{$data['member']['name']}</a></span> {$data['online']}</td>
        <td class='row4' valign='top'>

        <div align='left' class='row4' style='float:left;padding-top:4px;padding-bottom:4px'>
        {$data[POST]['post_icon']}<span class='postdetails'>{$data['msg']['msg_date']}</span>
        </div>

        <div align='right'>
          <a href='{$ibforums->base_url}CODE=05&amp;act=Msg&amp;MSID={$data['msg']['msg_id']}&amp;VID={$data['member']['VID']}'><{P_DELETE}></a>
          &nbsp;<a href='{$ibforums->base_url}CODE=04&amp;act=Msg&amp;MID={$data['member']['id']}&amp;MSID={$data['msg']['msg_id']}'><{P_QUOTE}></a>
        </div>

      </td>
    </tr>
    <tr>
      <td valign='top' class='post1'>
        <span class='postdetails'>
        {$data['member']['avatar']}
        <br>{$data['member']['title']}
        <br>{$data['member']['member_rank_img']}<br>
        <br>{$data['member']['member_group']}
        <br>{$data['member']['member_posts']}
        <br>{$data['member']['member_joined']}
        <br>
        </span>
        <img src='{$ibforums->skin['ImagesPath']}/spacer.gif' alt='' width='160' height='1'><br>
      </td>
      <td width='100%' valign='top' class='post1'><span class='postcolor'>{$data['msg']['message']}</span><span class="signature">{$data['member']['signature']}</span></td>
    </tr>
    <tr>
      <td class='darkrow3' align='left'>[ <a href='{$ibforums->base_url}CODE=02&amp;act=Msg&amp;MID={$data['member']['id']}'>{$ibforums->lang['add_to_book']}</a> ]</td>
      <td class='darkrow3' nowrap="nowrap" align='left'>

        <div align='left' class='darkrow3' style='float:left;'>
        {$data['member']['addresscard']}{$data['member']['message_icon']}{$data['member']['email_icon']}{$data['member']['website_icon']}{$data['member']['integ_icon']}{$data['member']['icq_icon']}{$data['member']['aol_icon']}{$data['member']['yahoo_icon']}{$data['member']['msn_icon']}
      </div>

        <div align='right'><a href='javascript:scroll(0,0);'><img src='{$ibforums->skin['ImagesPath']}/p_up.gif' alt='Top' border='0'></a></div>
      </td>
    </tr>
</table>
</div>
<div style="float:left;width:auto;padding:6px">
<form action="{$ibforums->base_url}" name='jump' method="post">
<input type='hidden' name='act' value='Msg'>
<input type='hidden' name='CODE' value='01'>
{$ibforums->lang[goto_folder]}:</b>&nbsp; {$data['jump']}
<input type='submit' name='submit' value='{$ibforums->lang[goto_submit]}' class='forminput'>
</form>
</div>
<div align="right" style="padding:6px;font-weight:bold">
  [ <a href='{$ibforums->base_url}CODE=04&amp;act=Msg&amp;MSID={$data['msg']['msg_id']}&amp;MID={$data['member']['id']}&amp;fwd=1'>{$ibforums->lang['vm_forward_pm']}</a> | <a href='{$ibforums->base_url}CODE=04&amp;act=Msg&amp;MID={$data['member']['id']}&amp;MSID={$data['msg']['msg_id']}'>{$ibforums->lang['pm_reply_link']}</a> ]
</div>

EOF;
}


function mass_pm_box($names="") {
global $ibforums;
return <<<EOF

<h3 class="b-send_pm__cc-title">{$ibforums->lang['carbon_copy_title']}</h3>
<table class="b-send_pm__cc">
<tr>
<td class='pformleft'>{$ibforums->lang['carbon_copy_desc']}</td>
<td class='pformright'>
 <div class="b-send_pm__cc-names-wrapper"><textarea class="b-send_pm__cc-names" name='carbon_copy' rows='5' cols='40'>$names</textarea></div>
 <input type='button' class='forminput b-send_pm__cc-names-submit' name='findusers' onclick='find_users()' value='{$ibforums->lang['find_user_names']}'>
</td>
</tr>
</table>

EOF;
}


function Send_form($data) {
global $ibforums;
return <<<EOF

<script language='javascript' type="text/javascript">
<!--
function find_users()
{
  url = "index.{$ibforums->vars['php_ext']}?act=legends&CODE=finduser_one&s={$ibforums->session_id}&entry=textarea&name=carbon_copy&sep=line";
  window.open(url,'FindUsers','width=400,height=250,resizable=yes,scrollbars=yes');
}
//-->
</script>
<form class="b-send-pm-form" name='REPLIER' action="{$ibforums->base_url}" method="post" onsubmit='return ValidateForm(1)'>
<input type='hidden' name='act' value='Msg'>
<input type='hidden' name='CODE' value='04'>
<input type='hidden' name='MODE' value='01'>
<input type='hidden' name='OID'  value='{$data['OID']}'>

<div class='b-send-pm-wrapper tableborder'>
<h3 class="b-receiver__header">{$ibforums->lang['to_whom']}</h3>
<table class="b-receiver">
<tr>
  <td class='b-receiver__select-user__title pformleft'><label>{$ibforums->lang['address_list']}</label></td>
  <td class='b-receiver__select-user pformright'>{$data[CONTACTS]}</td>
</tr>
<tr>
  <td class='pformleft b-receiver__username__title'><label>{$ibforums->lang['enter_name']}</label></td>
  <td class='pformright b-receiver__username'><input type='text' name='entered_name' size='50' value='{$data[N_ENTER]}' tabindex="1" class='forminput'></td>
</tr>
</table>
<!--IBF.MASS_PM_BOX-->

<h3 class="b-message-title__header">{$ibforums->lang['enter_message']}</h3>
<table class="b-message-title">
<tr>
  <td class='pformleft b-message-title__title'><label>{$ibforums->lang['msg_title']}</label></td>
  <td class='pformright b-message-title'><input type='text' name='msg_title' size='90' tabindex="2" maxlength='128' value='{$data[O_TITLE]}' class='forminput'></td>
</tr>
</table>
EOF;
}

	function renderAddToContactsBtn($id){
		$ibforums = Ibf::app();
		return <<<EOF
[ <a class="b-button_add-to-contacts" href='{$ibforums->base_url}act=Msg&amp;CODE=02&amp;MID={$id}'>{$ibforums->lang['add_to_book']}</a> ]
EOF;
	}

	function renderSenderLink($id, $name){
		$ibf = Ibf::app();
		return <<<EOF
<a href='{$ibf->base_url}showuser={$id}'>$name</a>
EOF;

	}

	function inbox_row($data) {
		global $ibforums;
		if ($data['msg']['member_deleted']){
			$sender = $data['msg']['from_name'];
		}else{
			$sender = $this->renderSenderLink($data['msg']['from_id'], $data['msg']['from_name']) . ' ' . $this->renderAddToContactsBtn($data['msg']['from_id']);
		}
return <<<EOF

  <tr class="b-row" data-msg-id="{{$data['msg']['msg_id']}}" data-read="{$data['msg']['read_state']}" >
	<td class="b-column_icon">{$data['msg']['icon']}</td>
	<td class="b-column_title" ><a href='{$ibforums->base_url}act=Msg&amp;CODE=03&amp;VID={$data['stat']['current_id']}&amp;MSID={$data['msg']['msg_id']}'>{$data['msg']['title']}</a></td>
	<td class="b-column_sender">{$sender}</td>
	<td class="b-column_date">{$data['msg']['date']}</td>
	<td class="b-column_checkbox"><input type='checkbox' name='msgid_{$data['msg']['msg_id']}' value='yes' class='forminput'></td>
  </tr>

EOF;
}


function end_inbox($vdi_html, $amount_info="", $pages="") {
global $ibforums;
return <<<EOF
</tbody>
<tfoot>
	<tr class="b-row">
		<td class='b-column' colspan='5'>
			<input type='submit' name='move' value='{$ibforums->lang['move_button']}' class='forminput'> $vdi_html {$ibforums->lang['move_or_delete']} <input type='submit' name='delete' value='{$ibforums->lang['delete_button']}' class='forminput'> {$ibforums->lang['selected_msg']}
		</td>
	</tr>
</tfoot>
</table>
</div>
</form>
<div class="wrapmini b-pm-list__legend" style="padding:6px"><{M_READ}>&nbsp;{$ibforums->lang['icon_read']}<br><{M_UNREAD}>&nbsp;{$ibforums->lang['icon_unread']}</div>
<div align="right" style="padding:6px"><div class="b-pm-list__pages-bottom-wrapper">$pages</div><br><div class="b-pm-list__amount-info-wrapper">$amount_info</div></div>

EOF;
}


function send_form_footer() {
global $ibforums;
return <<<EOF
<h3>{$ibforums->lang['msg_options']}</h3>
<table>
<tr>
 <td class='pformleft'>&nbsp;</td>
 <td class='pformright'>
	<div class="b-send_pm__options-add_sent-wrapper"><input type='checkbox' name='add_sent' value='yes' CHECKED>&nbsp;<b>{$ibforums->lang['auto_sent_add']}</b></div>
	<div class="b-send_pm__options-track-wrapper"><input type='checkbox' name='add_tracking' value='1'>&nbsp;<b>{$ibforums->lang['vm_track_msg']}</b></div>
 </td>
</tr>
</table>
<div class="pformstrip b-buttons-wrapper">
  <input type="submit" value="{$ibforums->lang['submit_send']}" tabindex="4" accesskey="s" class='forminput' name='submit'>
  <input type="submit" value="{$ibforums->lang['pm_pre_button']}" tabindex="5" class='forminput' name='preview'>
  <input type="submit" value="{$ibforums->lang['pms_send_later']}" tabindex="6" class='forminput' name='save'>
</div>
</form>
</div>

EOF;
}


function unsent_end() {
global $ibforums;
return <<<EOF
	</tbody>
	<tfoot>
		<tr class="b-footer-row">
			<td class="b-column" colspan='6'><input type='submit' name='delete' value='{$ibforums->lang['delete_button']}' class='forminput'> <span class="b-pm-list-footer-suffix">{$ibforums->lang['selected_msg']}</span></td>
		</tr>
	</tfoot>
</table>
</div>
</form>


EOF;
}


function inbox_table_header($dirname, $info, $vdi_html="", $pages="") {
global $ibforums;
return <<<EOF

<script language='JavaScript' type="text/javascript">
<!--

function select_read(context) {
	$('.b-row[data-read="1"] .b-column_checkbox input:checkbox', context).prop('checked', true);
	$('.b-row[data-read="1"] .b-column_checkbox input:checkbox', context).trigger('change');
}
function unselect_all(context) {
	$('.b-row .b-column_checkbox input:checkbox', context).prop('checked', false);
	$('.b-row .b-column_checkbox input:checkbox', context).trigger('change');
}

$(document).ready(function(){
	$('.b-row .b-column_checkbox input:checkbox').change(function(){
		if ($(this).is(':checked'))
		{
			$(this).closest('tr').addClass('selected');
		}else
		{
			$(this).closest('tr').removeClass('selected');
		}
		//find unchecked
		if ( $(this).closest('tbody').find('.b-column_checkbox input:checkbox:not(:checked)').length > 0) {
			$(this).closest('table').find('thead .b-column_checkbox input:checkbox').prop('checked', false);
		}else {
			$(this).closest('table').find('thead .b-column_checkbox input:checkbox').prop('checked', true);
		}
	});

	$('.b-header-row .b-column_checkbox input:checkbox').click(function(){
		$(this).closest('table').find('tbody .b-column_checkbox input:checkbox').prop('checked', $(this).prop('checked'));
		$(this).closest('table').find('tbody .b-column_checkbox input:checkbox').trigger('change');
	});
});
//-->
</script>
<h3>$dirname</h3>
<table class="b-pm-list__content-top" >
<tr>
 <td valign="middle" class="b-pm-list__stats-wrapper">
  <table class="b-pm-list__stats" style="width:250px" cellspacing="1" class="tableborder">
   <tr>
	<td class='row1' align='left' colspan='3'>{$info['full_messenger']}</td>
   </tr>
   <tr>
	<td align='left' valign='middle' class='row2' colspan='3'><img src='{$ibforums->skin['ImagesPath']}/bar_left.gif' border='0' width='4' height='11' align='middle' alt=''><img src='{$ibforums->skin['ImagesPath']}/bar.gif' border='0' width='{$info['img_width']}' height='11' align='middle' alt=''><img src='{$ibforums->skin['ImagesPath']}/bar_right.gif' border='0' width='4' height='11' align='middle' alt=''></td>
   </tr>
   <tr>
	 <td class='row1' width='33%' align='left' valign='middle'>0%</td>
	 <td class='row1' width='33%' align='center' valign='middle'>50%</td>
	 <td class='row1' width='33%' align='right' valign='middle'>100%</td>
   </tr>
  </table>
 </td>
 <td class="b-pm-list__control_buttons-wrapper" align="right" valign="bottom" style="line-height:100%;">
  <div class="b-pm-list__pages-top-wrapper">$pages</div>
  <div class="b-pm-list__selection_control_buttons">
  <a href="javascript:select_read('.b-pm-list');">{$ibforums->lang['pmpc_mark_read']}</a> :: <a href="javascript:unselect_all('.b-pm-list')">{$ibforums->lang['pmpc_unmark_all']}</a>
  </div>
 </td>
</tr>
</table>
<form action="{$ibforums->base_url}CODE=06&amp;act=Msg" name='mutliact' method="post" class="b-pm-list-form">
<div class="tableborder b-pm-list-wrapper">
  <table class="b-pm-list">
  <thead>
  <tr class="b-header-row">
	<th width='5%'  class='b-column_icon'>&nbsp;</th>
	<th width='35%' class='b-column_title'><a href='{$ibforums->base_url}act=Msg&amp;CODE=01&amp;VID={$info['vid']}&amp;sort=title&amp;st={$ibforums->input['st']}'><b>{$ibforums->lang['message_title']}</b></a></th>
	<th width='30%' class='b-column_sender'><a href='{$ibforums->base_url}act=Msg&amp;CODE=01&amp;VID={$info['vid']}&amp;sort=name&amp;st={$ibforums->input['st']}'><b>{$ibforums->lang['message_from']}</b></a></th>
	<th width='25%' class='b-column_date'><a href='{$ibforums->base_url}act=Msg&amp;CODE=01&amp;VID={$info['vid']}&amp;sort={$info['date_order']}&amp;st={$ibforums->input['st']}'><b>{$ibforums->lang['message_date']}</b></a></th>
	<th width='5%'  class='b-column_checkbox'><input name="allbox" type="checkbox" value="Check All"></th>
  </tr>
  </thead>
  <tbody>
EOF;
}


function unsent_row($data) {
global $ibforums;
return <<<EOF

<tr class="b-row pm-unsent-list-row">
  <td class='row2 b-column_icon'>{$data['msg']['icon']}</td>
  <td class='row2 b-column_title'><a href='{$ibforums->base_url}act=Msg&amp;CODE=21&amp;MSID={$data['msg']['msg_id']}'>{$data['msg']['title']}</a></td>
  <td class='row2 b-column_sender'><a href='{$ibforums->base_url}showuser={$data['msg']['recipient_id']}'>{$data['msg']['to_name']}</a></td>
  <td class='row2 b-column_date'>{$data['msg']['date']}</td>
  <td class='row2 b-column_cc'>{$data['msg']['cc_users']}</td>
  <td class='row2 b-column_checkbox'><input type='checkbox' name='msgid_{$data['msg']['msg_id']}' value='yes' class='forminput'></td>
</tr>

EOF;
}


function trackUNread_end() {
global $ibforums;
return <<<EOF
</tbody>
<tfoot>
<tr class="b-footer-row">
 <td class='b-column titlemedium' colspan='5'><input type='submit' name='delete' value='{$ibforums->lang['delete_button']}' class='forminput'> {$ibforums->lang['selected_msg']}</td>
</tr>
</tfoot>
</table>
</div>
</form>


EOF;
}


function unsent_table_header() {
global $ibforums;
return <<<EOF

<script language='JavaScript' type="text/javascript">
<!--
$(document).ready(function(){
	$('.b-row .b-column_checkbox input:checkbox').change(function(){
		if ($(this).is(':checked'))
		{
			$(this).closest('tr').addClass('selected');
		}else
		{
			$(this).closest('tr').removeClass('selected');
		}
		//find unchecked
		if ( $(this).closest('tbody').find('.b-column_checkbox input:checkbox:not(:checked)').length > 0) {
			$(this).closest('table').find('thead .b-column_checkbox input:checkbox').prop('checked', false);
		}else {
			$(this).closest('table').find('thead .b-column_checkbox input:checkbox').prop('checked', true);
		}
	});

	$('.b-header-row .b-column_checkbox input:checkbox').click(function(){
		$(this).closest('table').find('tbody .b-column_checkbox input:checkbox').prop('checked', $(this).prop('checked'));
		$(this).closest('table').find('tbody .b-column_checkbox input:checkbox').trigger('change');
	});
});
//-->
</script>
<form action="{$ibforums->base_url}CODE=06&amp;act=Msg&amp;saved=1" name='mutliact' method="post">
<h3>{$ibforums->lang['pms_saved_title']}</h3>
<div class="tableborder b-pm-list-wrapper pm-unsent-list-wrapper">
<table class="pm-unsent-list b-pm-list">
<thead>
<tr class="b-header-row">
  <th width='5%' class='b-column_icon'>&nbsp;</td>
  <th width='30%' class='b-column_title'><b>{$ibforums->lang['message_title']}</b></th>
  <th width='30%' class='b-column_sender'><b>{$ibforums->lang['pms_message_to']}</b></th>
  <th width='20%' class='b-column_date'><b>{$ibforums->lang['pms_saved_date']}</b></th>
  <th width='10%' class='b-column_cc'><b>{$ibforums->lang['pms_cc_users']}</b></th>
  <th width='5%' class='b-column_checkbox'><input name="allbox" type="checkbox" value="Check All"></th>
</tr>
</thead>
<tbody>
EOF;
}


function trackread_end() {
global $ibforums;
return <<<EOF
</tbody>
<tfoot>
<tr>
 <td class='b-column titlemedium' colspan='5'><input type='submit' name='endtrack' value='{$ibforums->lang['tk_untrack_button']}' class='forminput'> {$ibforums->lang['selected_msg']}</td>
</tr>
</tfoot>
</table>
</div>
</form>
<br>

EOF;
}


function trackUNread_table_header() {
global $ibforums;
return <<<EOF

<form class="b-tracking-form b-tracking-form_unread" action="{$ibforums->base_url}CODE=32&amp;act=Msg" name='trackunread' method="post">
<h3>{$ibforums->lang['tk_unread_messages']}</h3>
<p class="b-description">{$ibforums->lang['tk_unread_desc']}</p>
<div class="tableborder b-tracking-list_unread__wrapper">
<table class="b-tracking-list_unread">
<thead>
<tr class="b-header-row">
  <th width='5%' class='b-column b-column_icon'>&nbsp;</td>
  <th width='30%' class='b-column b-column_title'><b>{$ibforums->lang['message_title']}</b></th>
  <th width='30%' class='b-column b-column_receiver'><b>{$ibforums->lang['pms_message_to']}</b></th>
  <th width='20%' class='b-column b-column_date'><b>{$ibforums->lang['tk_unread_date']}</b></th>
  <th width='5%' class='b-column b-column_checkbox'><input name="allbox" type="checkbox" value="Check All"></th>
</tr>
</thead>
<tbody>
EOF;
}


function trackUNread_row($data) {
global $ibforums;
return <<<EOF

<tr class="b-row">
  <td class='b-column b-column_icon'>{$data['icon']}</td>
  <td class='b-column b-column_title'>{$data['title']}</td>
  <td class='b-column b-column_receiver'><a href='{$ibforums->base_url}showuser={$data['memid']}'>{$data['to_name']}</a></td>
  <td class='b-column b-column_date'>{$data['date']}</td>
  <td class='b-column b-column_checkbox'><input type='checkbox' name='msgid_{$data['msg_id']}' value='yes' class='forminput'></td>
</tr>

EOF;
}


function trackread_row($data) {
global $ibforums;
return <<<EOF

<tr class="b-row">
  <td class='b-column b-column_icon'>{$data['icon']}</td>
  <td class='b-column b-column_title'>{$data['title']}</td>
  <td class='b-column b-column_receiver'><a href='{$ibforums->base_url}showuser={$data['memid']}'>{$data['to_name']}</a></td>
  <td class='b-column b-column_date'>{$data['date']}</td>
  <td class='b-column b-column_checkbox'><input type='checkbox' name='msgid_{$data['msg_id']}' value='yes' class='forminput'></td>
</tr>

EOF;
}


function trackread_table_header() {
global $ibforums;
return <<<EOF

<script language='JavaScript' type="text/javascript">
<!--

$(document).ready(function(){
	$('.b-row .b-column_checkbox input:checkbox').change(function(){
		if ($(this).is(':checked'))
		{
			$(this).closest('tr').addClass('selected');
		}else
		{
			$(this).closest('tr').removeClass('selected');
		}
		//find unchecked
		if ( $(this).closest('tbody').find('.b-column_checkbox input:checkbox:not(:checked)').length > 0) {
			$(this).closest('table').find('thead .b-column_checkbox input:checkbox').prop('checked', false);
		}else {
			$(this).closest('table').find('thead .b-column_checkbox input:checkbox').prop('checked', true);
		}
	});

	$('.b-header-row .b-column_checkbox input:checkbox').click(function(){
		$(this).closest('table').find('tbody .b-column_checkbox input:checkbox').prop('checked', $(this).prop('checked'));
		$(this).closest('table').find('tbody .b-column_checkbox input:checkbox').trigger('change');
	});
});
//-->
</script>
<form class="b-tracking-form" action="{$ibforums->base_url}CODE=31&amp;act=Msg" name='trackread' method="post">
<h3>{$ibforums->lang['tk_read_messages']}</h3>
<p class="b-description">{$ibforums->lang['tk_read_desc']}</p>
<div class="tableborder b-tracking-list_read__wrapper">
<table class="b-tracking-list_read">
<thead>
<tr class="b-header-row">
  <th class="b-column b-column_icon" width='5%'>&nbsp;</th>
  <th class="b-column b-column_title" width='30%'><b>{$ibforums->lang['message_title']}</b></th>
  <th class="b-column b-column_receiver" width='30%'><b>{$ibforums->lang['pms_message_to']}</b></th>
  <th class="b-column b-column_date" width='20%'><b>{$ibforums->lang['tk_read_date']}</b></th>
  <th class="b-column b-column_checkbox" width='5%'><input name="allbox" type="checkbox" value="Check All"></th>
</tr>
</thead>
<tbody>
EOF;
}


function Address_none() {
global $ibforums;
return <<<EOF
<p class="b-address-list__none">{$ibforums->lang['address_none']}</p>
EOF;
}


function end_address_table() {
return <<<EOF

</table>
</div>

EOF;
}


function address_edit($data) {
global $ibforums;
return <<<EOF

<form action="{$ibforums->base_url}" method="post" class="b-address-edit-form">
<input type='hidden' name='act' value='Msg'>
<input type='hidden' name='CODE' value='12'>
<input type='hidden' name='MID' value='{$data[MEMBER]['contact_id']}'>
<h3>{$ibforums->lang['member_edit']}</h3>
<table>
<tr>
 <td class="b-form-element b-form-element__contact-name">{$data[MEMBER]['contact_name']}</td>
 <td class="b-form-element b-form-element__desc"><label>{$ibforums->lang['enter_desc']}</label><input type='text' name='mem_desc' size='30' maxlength='60' value='{$data[MEMBER]['contact_desc']}' class='forminput'></td>
 <td class="b-form-element b-form-element__show-online"><label>{$ibforums->lang['show_online']}</label>{$data[SHOW_ONLINE]}</td>
 <td class="b-form-element b-form-element__allow-msg"><label>{$ibforums->lang['allow_msg']}</label>{$data[SELECT]}</td>
</tr>
</table>
<div class="b-buttons-wrapper pformstrip"><input type="submit" value="{$ibforums->lang['submit_address_edit']}" class='forminput'></div>
</form>

EOF;
}


function Address_header() {
global $ibforums;
return <<<EOF

<h3>{$ibforums->lang['address_current']}</h3>

EOF;
}


function Address_table_header() {
global $ibforums;
return <<<EOF

<div class="b-address-list__wrapper tableborder">
<table class="b-address-list">
<thead>
<tr class="b-header-row">
  <th class="b-address-list__column b-address-list__column_name titlemedium"><b>{$ibforums->lang['member_name']}</b></th>
  <th class="b-address-list__column b-address-list__column_options titlemedium"><b>{$ibforums->lang['enter_block']}</b></th>
</tr>
</thead>

EOF;
}


function render_address_row($entry) {
global $ibforums;
return <<<EOF

<tr class="b-address-list__row">
  <td class='b-address-list__column b-address-list__column_name row1'>
    <a class="b-profile-link" href='{$ibforums->base_url}act=Profile&amp;CODE=03&amp;MID={$entry['contact_id']}'>{$entry['contact_name']}</a><span class="b-address-description">{$entry['contact_desc']}</span></td>
  <td class='b-address-list__column b-address-list__column_options row1'>
	[ <a class="b-address-list__action b-address-list__action_pm" href='{$ibforums->base_url}act=Msg&amp;CODE=4&amp;MID={$entry['contact_id']}'>PM</a> ] ::
	[ <a class="b-address-list__action b-address-list__action_edit" href='{$ibforums->base_url}act=Msg&amp;CODE=11&amp;MID={$entry['contact_id']}'>{$ibforums->lang['edit']}</a> ] ::
	[ <a class="b-address-list__action b-address-list__action_delete" href='{$ibforums->base_url}act=Msg&amp;CODE=10&amp;MID={$entry['contact_id']}'>{$ibforums->lang['delete']}</a> ]
	<span class="b-address-list__options">( {$entry['text']} )</span>
  </td>
</tr>

EOF;
}


function empty_folder_footer() {
global $ibforums;
return <<<EOF
</tbody>
</table>
<div class="b-folder-clear__buttons pformstrip" ><input type='submit' value='{$ibforums->lang['fd_continue']}' class='forminput'></div>
</div>
</form>

EOF;
}


function No_msg_inbox() {
global $ibforums;
return <<<EOF

      <tr>
      <td class='row1' colspan='5' align='center'><b>{$ibforums->lang['inbox_no_msg']}</b></td>
      </tr>

EOF;
}


function empty_folder_header() {
global $ibforums;
return <<<EOF

<form action="{$ibforums->base_url}" method="post" class="b-folder-clear">
<input type='hidden' name='act' value='Msg'>
<input type='hidden' name='CODE' value='dofolderdelete'>
<h3>{$ibforums->lang['mi_prune_msg']}</h3>
<p class="b-description">{$ibforums->lang['fd_text']}</p>
<div class="b-folder-clear__folders-list-wrapper tableborder">
<table class="b-folder-clear__folders-list">
<thead>
<tr class="b-header-row">
  <th class="b-column b-column_title titlemedium">{$ibforums->lang['fd_name']}</th>
  <th class="b-column b-column_messages titlemedium">{$ibforums->lang['fd_count']}</th>
  <th class="b-column b-column_checkbox titlemedium">{$ibforums->lang['fd_empty']}</th>
</tr>
</thead>
<tbody>
EOF;
}


function prefs_footer() {
global $ibforums;
return <<<EOF

<div class="b-folder-rename__buttons pformstrip"><input type='submit' value='{$ibforums->lang['prefs_submit']}' class='forminput'></div>
</form>

EOF;
}


function prefs_row($data) {
global $ibforums;
return <<<EOF

<p class="b-folder-rename__row"><input type='text' name='{$data[ID]}' value='{$data[REAL]}' class='forminput'>{$data[EXTRA]}</p>

EOF;
}


function prefs_add_dirs() {
global $ibforums;
return <<<EOF

<h3 class="b-folder-rename__title_added">{$ibforums->lang['prefs_new']}</h3>
<p class="b-folder-rename__description_added">{$ibforums->lang['prefs_text_b']}</p>

EOF;
}


function prefs_header() {
global $ibforums;
return <<<EOF

<form class="b-folder-rename" action="{$ibforums->base_url}" method="post">
<input type='hidden' name='act' value='Msg'>
<input type='hidden' name='CODE' value='08'>
<h3 class="b-folder-rename__title">{$ibforums->lang['prefs_current']}</h3>
<p class="b-folder-rename__description">{$ibforums->lang['prefs_text_a']}</p>

EOF;
}


function preview($data) {
global $ibforums;
return <<<EOF
<div class='tableborder'>
<h3>{$ibforums->lang['pm_preview']}</h3>
<p>$data</p>
</div>
EOF;
}


function pm_errors($data) {
global $ibforums;
return <<<EOF

<h3>{$ibforums->lang['err_errors']}</h3>
<span class='postcolor'><p>$data<br><br>{$ibforums->lang['pme_none_sent']}</p></span>

EOF;
}


function pm_popup($text, $mid) {
global $ibforums;
return <<<EOF

<script language='javascript'>
<!--
 function goto_inbox() {
 	opener.document.location.href = '{$ibforums->base_url}act=Msg&amp;CODE=01';
 	window.close();
 }

 function goto_this_inbox() {
 	window.resizeTo('700','500');
 	document.location.href = '{$ibforums->base_url}&act=Msg&CODE=01';
 }

 function go_read_msg() {
 	window.resizeTo('700','500');
 	document.location.href = '{$ibforums->base_url}&act=Msg&CODE=03&VID=in&MSID=$mid';
 }

//-->
</script>
<table cellspacing='1' cellpadding='10' width='100%' height='100%' align='center' class='row1'>
<tr>
   <td id='phototitle' align='center'>{$ibforums->lang['pmp_title']}</td>
</tr>
<tr>
   <td align='center'>$text</td>
</tr>
<tr>
   <td align='center' style='font-size:12px;font-weight:bold'>
   <a href='javascript:go_read_msg();'>{$ibforums->lang['pmp_get_last']}</a>
   <br><br>
   <a href='javascript:goto_inbox();'>{$ibforums->lang['pmp_go_inbox']}</a> ( <a href='javascript:goto_this_inbox();'>{$ibforums->lang['pmp_thiswindow']}</a> )<br><br><a href='javascript:window.close();'>{$ibforums->lang['pmp_ignore']}</a></td>
</tr>
</table>

EOF;
}


function archive_html_header() {
global $ibforums;
return <<<EOF

<html>
 <head>
  <title>Private Message Archive</title>
 </head>
 <style type='text/css'>
	 BODY { font-family: Verdana, Tahoma, Arial, sans-serif;
			font-size: 11px;
			color: #000;
			margin:0px;
			padding:0px;
			background-color:#FFF;
			text-align:center
		   }

	#ipbwrapper { text-align:left; width:95%; margin-left:auto;margin-right:auto }

	html { overflow-x: auto; }

	a:link, a:visited, a:active { text-decoration: underline; color: #000 }
	a:hover { color: #465584; text-decoration:underline }
	img        { vertical-align:middle; border:0px }

	.post1 { background-color: #F5F9FD }
	.post2 { background-color: #EEF2F7 }

	/* Common elements */
	.row1 { background-color: #F5F9FD }
	.row2 { background-color: #DFE6EF }
	.row3 { background-color: #EEF2F7 }
	.row4 { background-color: #E4EAF2 }

	/* tableborders gives the white column / row lines effect */
	.plainborder { border:1px solid #345487;background-color:#F5F9FD }
	.tableborder { border:1px solid #345487;background-color:#FFF; padding:0; margin:0 }
	.tablefill   { border:1px solid #345487;background-color:#F5F9FD;padding:6px;  }
	.tablepad    { background-color:#F5F9FD;padding:6px }
	.tablebasic  { width:100%; padding:0px 0px 0px 0px; margin:0px; border:0px }

	h3, .pformstrip { background-color: #D1DCEB; color:#3A4F6C;font-weight:bold;padding:7px;margin-top:1px }
	.quote { font-family: Verdana, Arial; font-size: 11px; color: #465584; background-color: #FAFCFE; border: 1px solid #000; padding-top: 2px; padding-right: 2px; padding-bottom: 2px; padding-left: 2px }
	.code  { font-family: Courier, Courier New, Verdana, Arial;  font-size: 11px; color: #465584; background-color: #FAFCFE; border: 1px solid #000; padding-top: 2px; padding-right: 2px; padding-bottom: 2px; padding-left: 2px }

	/* Main table top (dark blue gradient by default) */
	.maintitle { vertical-align:middle;font-weight:bold; color:#FFF; background-color:D1DCEB;padding:8px 0px 8px 5px; background-image: url({$ibforums->vars['board_url']}/style_images/<#IMG_DIR#>/tile_back.gif) }
	.maintitle a:link, .maintitle  a:visited, .maintitle  a:active { text-decoration: none; color: #FFF }
	.maintitle a:hover { text-decoration: underline }

    /* Topic View elements */
	.signature   { font-size: 10px; color: #339; line-height:150% }
	.postdetails { font-size: 10px }
	.postcolor   { font-size: 12px; line-height: 160% }
 </style>
 <body>
 <div id='ipbwrapper'>

EOF;
}


function archive_html_entry($info) {
global $ibforums;
return <<<EOF

<div class='tableborder'>
 <div class='maintitle'><img src="{$ibforums->vars['board_url']}/style_images/<#IMG_DIR#>/f_norm.gif" alt='PM'>&nbsp;PM: {$info['msg_title']}</div>
 <div class='tablefill'><div class='postcolor'>{$info['msg_content']}</div></div>
 <div class='pformstrip'>Sent by <b>{$info['msg_sender']}</b> on {$info['msg_date']}</div>
</div>
<br>

EOF;
}


function archive_html_entry_sent($info) {
global $ibforums;
return <<<EOF

<div class='tableborder'>
 <div class='maintitle'><img src="{$ibforums->vars['board_url']}/style_images/<#IMG_DIR#>/f_moved.gif" alt='PM'>&nbsp;PM: {$info['msg_title']}</div>
 <div class='tablefill'><div class='postcolor'>{$info['msg_content']}</div></div>
 <div class='pformstrip'>Sent to <b>{$info['msg_sender']}</b> on {$info['msg_date']}</div>
</div>
<br>

EOF;
}


function empty_folder_save_unread() {
global $ibforums;
return <<<EOF
</tbody>
<tfoot>
<tr class="b-row">
  <td class="b-column b-folder-clear__options row2" colspan='3'><input type="checkbox" class="checkbox" name="save_unread" value="1" checked="checked"> <strong>{$ibforums->lang['fd_save_unread']}</strong></td>
</tr>
</tfoot>

EOF;
}


function empty_folder_row($real, $id, $cnt) {
global $ibforums;
return <<<EOF

<tr class="b-row">
  <td class="b-column b-column_title row1">$real</td>
  <td class="b-column b-column_messages row1">$cnt</td>
  <td class="b-column b-column_checkbox row1"><input type="checkbox" class="checkbox" name="its_$id" value="1"></td>
</tr>

EOF;
}


function archive_html_footer() {
global $ibforums;
return <<<EOF

  </div>
 </body>
</html>

EOF;
}


function archive_complete() {
global $ibforums;
return <<<EOF

<h3>{$ibforums->lang['arc_comp_title']}</h3>
<p>{$ibforums->lang['arc_complete']}</p>

EOF;
}


function archive_form($jump_html="") {
global $ibforums;
return <<<EOF

<form class="b-archive-form" action="{$ibforums->base_url}" method="post">
<input type='hidden' name='act' value='Msg'>
<input type='hidden' name='CODE' value='15'>
<h3>{$ibforums->lang['archive_title']}</h3>
<p class="b-description">{$ibforums->lang['archive_text']}</p>
<table class="b-archive-form__options">
<tr class="b-option b-option__arc-folders">
   <td class="b-option__label">{$ibforums->lang['arc_folders']}</td>
   <td class="b-option__value">$jump_html</td>
</tr>
<tr class="b-option b-option__dateline">
   <td class="b-option__label">{$ibforums->lang['arc_dateline']}</td>
   <td class="b-option__value"><select name='dateline' class='forminput'>
	 <option value='1'>1</option>
	 <option value='7'>7</option>
	 <option value='30' selected='selected'>30</option>
	 <option value='90'>90</option>
	 <option value='365'>365</option>
	 <option value='all'>{$ibforums->lang['arc_alldays']}</option>
	 </select>&nbsp;&nbsp;{$ibforums->lang['arc_days']}
	 <select name='oldnew' class='forminput'>
	  <option value='newer' selected='selected'>{$ibforums->lang['arch_new']}</option>
	  <option value='older'>{$ibforums->lang['arch_old']}</option>
	 </select>
   </td>
</tr>
<tr class="b-option b-option__max_messages">
   <td class="b-option__label">{$ibforums->lang['arc_max']}</td>
   <td class="b-option__value"><select name='number' class='forminput'><option value='5'>5</option><option value='10'>10</option><option value='20' selected>20</option><option value='30'>30</option><option value='40'>40</option><option value='50'>50</option></select></td>
</tr>
<tr class="b-option b-option__delete">
   <td class="b-option__label">{$ibforums->lang['arc_delete']}</td>
   <td class="b-option__value"><select name='delete' class='forminput'><option value='yes'>{$ibforums->lang['arc_yes']}</option><option value='no' selected='selected'>{$ibforums->lang['arc_no']}</option></select></td>
</tr>
<tr class="b-option b-option__export-type">
   <td class="b-option__label">{$ibforums->lang['arc_type']}</td>
   <td class="b-option__value"><select name='type' class='forminput'><option value='xls' selected>{$ibforums->lang['arc_xls']}</option><option value='html'>{$ibforums->lang['arc_html']}</option></select></td>
</tr>
</table>
<div class="b-buttons-wrapper pformstrip"><input type="submit" value="{$ibforums->lang['arc_submit']}" class='forminput'></div>
</form>

EOF;
}


}
?>
