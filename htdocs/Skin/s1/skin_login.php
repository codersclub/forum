<?php

class skin_login {


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

function ShowForm($message, $referer="") {
global $ibforums;
return <<<EOF
<script  type="text/javascript" src='html/login.js'></script>
<script language='JavaScript' type="text/javascript">
<!--
$(window).load(function() {onAuthMethodChange();});
//-->
</script>     
{$ibforums->lang['login_text']}
<br>
<br>
<b>{$ibforums->lang['forgot_pass']} <a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?act=Reg&amp;CODE=10'>{$ibforums->lang['pass_link']}</a></b>
<br>
<br>
<form action="{$ibforums->base_url}act=Login&amp;CODE=01" method="post" name='LOGIN' onsubmit='return ValidateForm()'>
<input type='hidden' name='referer' value="$referer">
<div class="tableborder">
  <div class="maintitle"><{CAT_IMG}>&nbsp;{$ibforums->lang['log_in']}</div>
  <div class='pformstrip'>$message</div>
  <table class="tablebasic" cellspacing="1">
  <tr>
    <td class='pformleftw'>{$ibforums->lang['auth_method']}</td>
    <td class='pformright'>
    <select name='auth_method' onchange="onAuthMethodChange()">
    	<option value='password'>Password</option>
    	<option value='openid'>OpenId</option>    	
    </select>
    </td>
  </tr>
  <tr>
    <td class='pformleftw'>{$ibforums->lang['enter_name']}</td>
    <td class='pformright'><input type='text' size='20' maxlength='64' name='UserName' class='forminput'></td>
  </tr>
  <tr id="auth_password">
    <td class='pformleftw'>{$ibforums->lang['enter_pass']}</td>
    <td class='pformright'><input type='password' size='20' name='PassWord' class='forminput'></td>
  </tr>
  <tr id="auth_openid">
    <td class='pformleftw'>OpenId URL</td>
    <td class='pformright'><input type='url' size='200' name='openid_url' class='forminput'></td>
  </tr>
  </table>
  <div class="pformstrip">{$ibforums->lang['options']}</div>		
  <table class="tablebasic" cellspacing="1">
  <tr>
    <td class='pformleftw'>{$ibforums->lang['cookies']}</td>
    <td class='pformright'><input type="radio" name="CookieDate" value="1" checked="checked">{$ibforums->lang['cookie_yes']}<br><input type="radio" name="CookieDate" value="0">{$ibforums->lang['cookie_no']}</td>
  </tr>
  <tr>
    <td class='pformleftw'>{$ibforums->lang['privacy']}</td>
    <td class='pformright'><input type="checkbox" name="Privacy" value="1">{$ibforums->lang['anon_name']}</td>
  </tr>
  </table>		
  <div class="pformstrip" align="center"><input type="submit" name='submit' value="{$ibforums->lang['log_in_submit']}" class='forminput'></div>
</div>
</form>
EOF;
}


}
?>