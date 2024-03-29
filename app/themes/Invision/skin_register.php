<?php

class skin_register {



function show_lostpass_form_auto($aid,$uid) {
global $ibforums;
return <<<EOF

  <input type='hidden' name='uid' value='$uid' >
  <input type='hidden' name='aid' value='$aid' >
  <table class="tablebasic">

EOF;
}


function show_dumb_form($type="reg") {
global $ibforums;
return <<<EOF
<form action="{$ibforums->base_url}" method="post" name='REG' onsubmit='return Validate()'>
<input type='hidden' name='act' value='Reg'>
<input type='hidden' name='CODE' value='03'>
<input type='hidden' name='type' value='$type'>
<div class="tableborder">
  <div class="titlemedium">{$ibforums->lang['dumb_header']}</div>
  <div class="tablepad">{$ibforums->lang['dumb_text']}</div>
  <div class="pformstrip">{$ibforums->lang['complete_form']}</div>
  <table class="tablebasic">
   <tr>
	<td class="pformleft">{$ibforums->lang['user_id']}</td>
	<td class="pformright"><input type='text' size='32' maxlength='32' name='uid' class='forminput'></td>
   </tr>
   <tr>
	<td class="pformleft">{$ibforums->lang['val_key']}</td>
	<td class="pformright"><input type='text' size='32' maxlength='50' name='aid' class='forminput'></td>
   </tr>
  </table>
  <div class="pformstrip" align="center"><input type="submit" value="{$ibforums->lang['dumb_submit']}" class='forminput'></div>
</div>
</form>

EOF;
}


function field_dropdown($name, $options) {
global $ibforums;
return <<<EOF

<select name='$name' class='forminput'>$options</select>

EOF;
}


function field_textarea($name, $value) {
global $ibforums;
return <<<EOF

<textarea cols='60' rows='5' name='$name' class='forminput'>$value</textarea>

EOF;
}


function field_textinput($name, $value="") {
global $ibforums;
return <<<EOF

<input type='text' size='30' name='$name' value='$value' class='forminput'>

EOF;
}


function field_entry($title, $desc="", $content) {
global $ibforums;
return <<<EOF

<tr>
  <td class='pformleft' valign='top'><b>$title</b><br>$desc</td>
  <td class='pformright'>$content</td>
</tr>

EOF;
}


function ShowForm($data) {
global $ibforums;
return <<<EOF
<script src='https://www.google.com/recaptcha/api.js'></script>

<form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" method="post" name='REG' onsubmit='return Validate()'>
<input type='hidden' name='act' value='Reg'>
<input type='hidden' name='CODE' value='02'>
<input type='hidden' name='coppa_user' value='{$data['coppa_user']}'>
<b>{$ibforums->lang['reg_header']}</b><br><br>{$data['TEXT']}
<br>
<br>
<div class="tableborder">
  <div class="maintitle"><{CAT_IMG}>&nbsp;{$ibforums->lang['registration_form']}</div>
  <div class="pformstrip">{$ibforums->lang['complete_form']}</div>
  <table class="tablebasic">
  <tr>
    <td class="pformleft">{$ibforums->lang['user_name']}</td>
    <td class="pformright"><input type='text' size='32' maxlength='64' value='{$ibforums->input['UserName']}' name='UserName' class='forminput' required></td>
  </tr>
  <tr>
    <td class="pformleft">{$ibforums->lang['pass_word']}</td>
    <td class="pformright"><input type='password' size='32' maxlength='32' value='{$ibforums->input['PassWord']}' name='PassWord' class='forminput' required></td>
  </tr>
  <tr>
    <td class="pformleft">{$ibforums->lang['re_enter_pass']}</td>
    <td class="pformright"><input type='password' size='32' maxlength='32' value='{$ibforums->input['PassWord_Check']}'  name='PassWord_Check' class='forminput' required onfocusout="passwordsIsEquals()"></td>
  </tr>
  <tr>
    <td class="pformleft">{$ibforums->lang['email_address']}</td>
    <td class="pformright"><input type='email' size='32' maxlength='50' value='{$ibforums->input['EmailAddress']}'  name='EmailAddress' class='forminput' required></td>
  </tr>
  <tr>
    <td class="pformleft">{$ibforums->lang['email_address_two']}</td>
    <td class="pformright"><input type='email' size='32' maxlength='50'  value='{$ibforums->input['EmailAddress_two']}' name='EmailAddress_two' class='forminput' required></td>
  </tr>
  <!--{REQUIRED.FIELDS}-->
  <!--{OPTIONAL.FIELDS}-->
  <!--IBF.MODULES.EXTRA-->
  <!--{REG.ANTISPAM}-->
  <tr class='reg_website'>
    <td class='pformleft'><b>{$ibforums->lang['website']}</b></td>
    <td class='pformright'><input type='text' maxlength='250' name='WebSite' value='' class='forminput w100'></td>
  </tr>
  <tr class='reg_location'>
    <td class="pformleft"><b>Location</b><br>Enter your country, i.e. USA, Brasilia, Netherlands, etc.</td>
    <td class="pformright"><input type='text' size='32' maxlength='50' value='' name='Location' class='forminput'></td>
  </tr>
  </table>
</div>
<br>
<br>
<div class="tableborder">
  <div class="pformstrip">{$ibforums->lang['terms_service']}</div>
  <div class="tablepad" align="center">
   <b>{$ibforums->lang['agree_submit']}</b>&nbsp;<input type='checkbox' name='agree' value='1'>
  </div>
  <div class="pformstrip" align="center"><input type="submit" value="{$ibforums->lang['submit_form']}" class='forminput'></div>
</div>
</form>

EOF;
}


function tmpl_form($action, $hidden, $title, $content) {
global $ibforums;
return <<<EOF

<form method="post" action="$action">
$hidden
<div class="tableborder">
  <div class="maintitle">$title</div>
  <table class="tablebasic">
  $content
  </table>
  <div class="pformstrip" align="center"><input type="submit" class='forminput'></div>
</div>
</form>

EOF;
}


function optional_title() {
global $ibforums;
return <<<EOF

<tr>
  <td colspan='2' class='pformstrip'>{$ibforums->lang['cf_optional']}</td>
</tr>

EOF;
}


function bot_antispam_gd($regid) {
global $ibforums;
return <<<EOF

   <tr>
	 <td class='row1' width='40%'>{$ibforums->lang['las_numbers']}</td>
	 <td class='row1'>
	   <input type='hidden' name='regid' value='$regid'>
	   <img src='{$ibforums->base_url}act=Reg&amp;CODE=image&amp;rc={$regid}' alt='Loading Image'>
	 </td>
	 </tr>
	 <tr>
	 <td class='row1' width='40%'>{$ibforums->lang['las_text']}</td>
	 <td class='row1'><input type='text' size='32' maxlength='32' name='reg_code' class='forminput'></td>
	 </tr>

EOF;
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


function bot_antispam($regid) {
global $ibforums;
return <<<EOF

	 </tr>
     <td class='row1' width='40%'>{$ibforums->lang['las_numbers']}</td>
	 <td class='row1'>
	   <input type='hidden' name='regid' value='$regid'>
	   <img src='{$ibforums->base_url}act=Reg&amp;CODE=image&amp;rc={$regid}&amp;p=1' alt='Code Bit'>
	   &nbsp;<img src='{$ibforums->base_url}act=Reg&amp;CODE=image&amp;rc={$regid}&amp;p=2' alt='Code Bit'>
	   &nbsp;<img src='{$ibforums->base_url}act=Reg&amp;CODE=image&amp;rc={$regid}&amp;p=3' alt='Code Bit'>
	   &nbsp;<img src='{$ibforums->base_url}act=Reg&amp;CODE=image&amp;rc={$regid}&amp;p=4' alt='Code Bit'>
	   &nbsp;<img src='{$ibforums->base_url}act=Reg&amp;CODE=image&amp;rc={$regid}&amp;p=5' alt='Code Bit'>
	   &nbsp;<img src='{$ibforums->base_url}act=Reg&amp;CODE=image&amp;rc={$regid}&amp;p=6' alt='Code Bit'>
	 </td>
	 </tr>
	 <tr>
	 <td class='row1' width='40%'>{$ibforums->lang['las_text']}</td>
	 <td class='row1'><input type='text' size='32' maxlength='32' name='reg_code' class='forminput'></td>
	 </tr>

EOF;
}

function bot_antispam_recapthca($regid) {
global $ibforums;
return <<<EOF
  	<tr>
	<td class='pformleft'>Вы же не робот?</td>
  	<td class='pformright'>
	   <input type='hidden' name='regid' value='$regid'>
	 		<div class="g-recaptcha" data-sitekey="{$ibforums->vars['recaptcha_site_key']}"></div>
	</td>
	</tr>
EOF;
}


function show_preview($member) {
global $ibforums;
return <<<EOF

<div class="tableborder">
  <div class="maintitle"><!-- --></div>
  <div class="pformstrip">{$ibforums->lang['registration_process']}</div>
  <div class="tablepad">{$ibforums->lang['thank_you']} {$member['name']}. {$ibforums->lang['preview_reg_text']}</div>
</div>

EOF;
}


function show_revalidate_form($name="") {
global $ibforums;
return <<<EOF

<form action="{$ibforums->base_url}" method="post" name='REG'>
<input type='hidden' name='act' value='Reg'>
<input type='hidden' name='CODE' value='reval2'>
<div>{$ibforums->lang['rv_ins']}</div>
<br>
<div class="tableborder">
  <div class="maintitle"><{CAT_IMG}>&nbsp;{$ibforums->lang['rv_title']}</div>
  <table class="tablebasic">
   <tr>
	<td class="pformleft"><strong>{$ibforums->lang['rv_name']}</strong></td>
	<td class="pformright"><input type='text' size='32' maxlength='64' name='username' value='$name' class='forminput'></td>
   </tr>
  </table>
  <div class="pformstrip" align="center"><input type="submit" value="{$ibforums->lang['rv_go']}" class='forminput'></div>
</div>
</form>

EOF;
}


function show_lostpass_form() {
global $ibforums;
return <<<EOF
<form action="{$ibforums->base_url}" method="post" name='REG' onsubmit='return ValidateLostPass()'>
<input type='hidden' name='act' value='Reg'>
<input type='hidden' name='CODE' value='03'>
<input type='hidden' name='type' value='lostpass'>
<div class="tableborder">
  <div class="maintitle"><{CAT_IMG}>&nbsp;{$ibforums->lang['dumb_header']}</div>
  <div class="pformstrip">{$ibforums->lang['lpf_title']}</div>
   <!--IBF.INPUT_TYPE-->
   <tr>
	<td class="pformleft"><strong>{$ibforums->lang['lpf_pass1']}</strong><br><em>{$ibforums->lang['lpf_pass11']}</em></td>
	<td class="pformright"><input type='password' size='32' maxlength='32' name='pass1' class='forminput'></td>
   </tr>
   <tr>
	<td class="pformleft"><strong>{$ibforums->lang['lpf_pass2']}</strong><br><em>{$ibforums->lang['lpf_pass22']}</em></td>
	<td class="pformright"><input type='password' size='32' maxlength='32' name='pass2' class='forminput'></td>
   </tr>
  </table>

  <div class="pformstrip" align="center"><input type="submit" value="{$ibforums->lang['dumb_submit']}" class='forminput'></div>
</div>
</form>

EOF;
}


function show_lostpass_form_manual() {
global $ibforums;
return <<<EOF

  <div class="tablepad">{$ibforums->lang['dumb_text']}</div>
  <div class="pformstrip">{$ibforums->lang['complete_form']}</div>
  <table class="tablebasic">
   <tr>
	<td class="pformleft"><strong>{$ibforums->lang['user_id']}</strong></td>
	<td class="pformright"><input type='text' size='32' maxlength='32' name='uid' class='forminput'></td>
   </tr>
   <tr>
	<td class="pformleft"><strong>{$ibforums->lang['val_key']}</strong></td>
	<td class="pformright"><input type='text' size='32' maxlength='50' name='aid' class='forminput'></td>
   </tr>


EOF;
}


function coppa_form() {
global $ibforums;
return <<<EOF
<!DOCTYPE html>
     <html>
      <head>
       <title>{$ibforums->lang['cpf_title']}</title>
       <!--<link rel='stylesheet' href='style_sheets/stylesheet_<{css_id}>.css'>-->
      </head>
     <body bgcolor='white'>
     <table cellpadding='0' cellspacing='4' width='95%'>
     <tr>
        <td valign='middle' align='left'>
        	<span class='pagetitle'>{$ibforums->vars['board_name']}: {$ibforums->lang['cpf_title']}</span>
        	<br><br>
        	<b><span style='font-size:12px'>{$ibforums->lang['cpf_perm_parent']}</span></b>
        	<br><br>
        	{$ibforums->lang['cpf_fax']} {$ibforums->vars['coppa_fax']}
        	<br><br>
        	{$ibforums->lang['cpf_address']}
        	<br>
        	{$ibforums->vars['coppa_address']}

        </td>
     </tr>
     </table>
     <br>
     <table cellspacing='2' border='1' width='95%'>
      <tr>
		<td width='40%'>{$ibforums->lang['user_name']}</td>
		<td>&nbsp;</td>
		</tr>
		<tr>
		<td width='40%'>{$ibforums->lang['pass_word']}</td>
		<td>&nbsp;</td>
		</tr>
		<tr>
		<td width='40%'>{$ibforums->lang['email_address']}</td>
		<td>&nbsp;</td>
	  </tr>
     </table>
     <br>
     <table cellpadding='0' cellspacing='4' width='95%'>
     <tr>
        <td valign='middle' align='left'>
        	<b><span style='font-size:12px'>{$ibforums->lang['cpf_sign']}</span></b>
        </td>
     </tr>
     </table>
     <br>
     <table cellpadding='10' cellspacing='2' border='1' width='95%'>
      <tr>
		<td width='40%'>{$ibforums->lang['cpf_name']}</td>
		<td>&nbsp;</td>
		</tr>
		<tr>
		<td width='40%'>{$ibforums->lang['cpf_relation']}</td>
		<td>&nbsp;</td>
		</tr>
		<tr>
		<td width='40%'>{$ibforums->lang['cpf_signature']}</td>
		<td>&nbsp;</td>
	    </tr>
	    <tr>
		<td width='40%'>{$ibforums->lang['cpf_email']}</td>
		<td>&nbsp;</td>
		</tr>
		<tr>
		<td width='40%'>{$ibforums->lang['cpf_phone']}</td>
		<td>&nbsp;</td>
		</tr>
		<tr>
		<td width='40%'>{$ibforums->lang['cpf_date']}</td>
		<td>&nbsp;</td>
		</tr>
     </table>
    </body>
  </html>

EOF;
}


function show_lostpasswait($member) {
global $ibforums;
return <<<EOF

<div class="tableborder">
  <div class="maintitle"><{CAT_IMG}>&nbsp;{$ibforums->lang['lpf_title']}</div>
  <div class="pformstrip">{$ibforums->lang['registration_process']}</div>
  <div class="tablepad">{$ibforums->lang['lpass_text']}</div>
</div>

EOF;
}


function show_revalidated() {
global $ibforums;
return <<<EOF

<div class="tableborder">
  <div class="maintitle"><{CAT_IMG}>&nbsp;{$ibforums->lang['rv_title']}</div>
  <div class="pformstrip">{$ibforums->lang['rv_process']}</div>
  <div class="tablepad">{$ibforums->lang['rv_done']}</div>
</div>

EOF;
}


function show_authorise($member) {
global $ibforums;
return <<<EOF

<div class="tableborder">
  <div class="maintitle"><!-- --></div>
  <div class="pformstrip">{$ibforums->lang['registration_process']}</div>
  <div class="tablepad">{$ibforums->lang['thank_you']} {$member['name']}. {$ibforums->lang['auth_text']} {$member['email']}</div>
</div>

EOF;
}


function lost_pass_form($lasid="") {
global $ibforums;
return <<<EOF

<form action="{$ibforums->base_url}" method="post">
<input type='hidden' name='act' value='Reg'>
<input type='hidden' name='CODE' value='11'>
<div class="tableborder">
  <div class="maintitle"><{CAT_IMG}>&nbsp;{$ibforums->lang['lost_pass_form']}</div>
  <div class="pformstrip">{$ibforums->lang['lp_header']}</div>
  <div class="tablepad"><span style="line-height:140%">{$ibforums->lang['lp_text']}</span></div>
  <div class="pformstrip">{$ibforums->lang['complete_form']}</div>
  <table class="tablebasic">
  <tr>
   <td class="pformleft"><strong>{$ibforums->lang['lp_user_name']}</strong></td>
   <td class="pformright"><input type='text' size='32' maxlength='32' name='member_name' class='forminput'></td>
  </tr>
  </table>
</div>
<!--{REG.ANTISPAM}-->
<br>
<div class="tableborder">
   <div class="pformstrip" align="center"><input type="submit" value="{$ibforums->lang['lp_send']}" class='forminput'></div>
</div>
</form>

EOF;
}


function coppa_start($coppadate) {
global $ibforums;
return <<<EOF

<div class="tableborder">
  <div class="maintitle"><{CAT_IMG}>&nbsp;{$ibforums->lang['registration_form']}</div>
  <div class="pformstrip">{$ibforums->lang['coppa_info']}</div>
  <div class="tablepad"  align="center">
	<span class="postcolor">
	  <strong>
	  {$ibforums->lang['coppa_link']}
	  <br><br>
	  &lt; <a href='{$ibforums->base_url}act=Reg&amp;coppa_pass=1'>{$ibforums->lang['coppa_date_before']} $coppadate</a>
	  - <a href='{$ibforums->base_url}act=Reg&amp;CODE=coppa_two'>{$ibforums->lang['coppa_date_after']} $coppadate</a> &gt;
	  </strong>
	</span>
  </div>
  <div class="pformstrip">{$ibforums->lang['coppa_form']}</div>
  <div class="tablepad">{$ibforums->lang['coppa_form_text']} <a href='mailto:{$ibforums->vars['email_in']}'>{$ibforums->vars['email_in']}</a></div>
</div>


EOF;
}


function coppa_two() {
global $ibforums;
return <<<EOF

<div class='tableborder'>
  <div class='maintitle'>{$ibforums->lang['cp2_title']}</div>
   <table cellspacing='1'>
   <tr>
   <td class='row1' align='left'>
	   {$ibforums->lang['cp2_text']}
	   <br><br>
	   <div class='center'>
             <span style='font-weight:bold;font-size:12px'>
		&lt;&lt; <a href='{$ibforums->base_url}'>{$ibforums->lang['cp2_cancel']}</a>
	   - <a href='{$ibforums->base_url}act=Reg&amp;coppa_pass=1&amp;coppa_user=1'>{$ibforums->lang['cp2_continue']}</a> &gt;&gt;
	     </span>
           </div>
   </td>
   </tr>
   <tr>
   <td valign='left' class='titlemedium'>{$ibforums->lang['coppa_form']}</td>
   </tr>
   <tr>
	<td class='row1' align='left'>{$ibforums->lang['coppa_form_text']} <a href='mailto:{$ibforums->vars['email_in']}'>{$ibforums->vars['email_in']}</a></td>
   </tr>
   </table>
</div>

EOF;
}


}
?>
