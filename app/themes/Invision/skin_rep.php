<?php

class skin_rep {



function ShowHeader() {
global $ibforums;
return <<<EOF
	<th align='center' class='pformstrip' width='15%'>{$ibforums->lang['who']}</th>
	<th align='center' class='pformstrip'>{$ibforums->lang['where']}</th>
	<th align='center' class='pformstrip'>{$ibforums->lang['where_forum']}</th>
	<th align='center' class='pformstrip'>{$ibforums->lang['why']}</th>
	<th align='center' class='pformstrip' width='5%'>{$ibforums->lang['code']}</th>
	<th align='center' class='pformstrip' width='15%'>{$ibforums->lang['when']}</th>
	</tr>

EOF;
}


function ShowTitle($i) {
global $ibforums;
return <<<EOF

<div class='tableborder'>
 <div class="maintitle" align='center'>
 {$ibforums->lang['rep_name']} {$ibforums->lang['user']} <b>{$i['name']}</b>: {$i['rep']} [ +{$i['ups']} | -{$i['downs']} ]
 </div>

 <table width='100%' cellpadding='4' cellspacing='1' border='0'>
  <tr>

EOF;
}


function ShowRow($i) {
global $ibforums;
return <<<EOF
	<tr>
		<td class='row2' width='15%' align='center'>{$i['name']}</td>
		<td class='row2' width='25%'>{$i['title']}</td>
		<td class='row2' width='25%'>{$i['forum']}</td>
		<td class='row4'>{$i['message']}</td>
		<td align='center' class='row2' width='5%'>{$i['img']}</td>
		<td align='center' class='row4' width='10%'>{$i['date']}{$i['admin_undo']}</td>
	</tr>

EOF;
}


function ShowSelfHeader() {
global $ibforums;
return <<<EOF

		<th align='center' class='pformstrip' width='15%'>{$ibforums->lang['whom']}</th>
		<th align='center' class='pformstrip'>{$ibforums->lang['where']}</th>
		<th align='center' class='pformstrip'>{$ibforums->lang['where_forum']}</th>
		<th align='center' class='pformstrip'>{$ibforums->lang['why']}</th>
		<th align='center' class='pformstrip' width='5%'>{$ibforums->lang['code']}</th>
		<th align='center' class='pformstrip' width='15%'>{$ibforums->lang['when']}</th>
	</tr>

EOF;
}


function Page_end() {
global $ibforums;
return <<<EOF

	<form action='{$ibforums->base_url}act=rep&CODE=totals' method='POST'>
        <tr> 
          <td class='pformstrip' colspan="3" align='center' valign='middle'>
          {$ibforums->lang['sorting_text']}&nbsp;<input type='submit' value='{$ibforums->lang['sort_submit']}' class='forminput'></td>
        </tr>
	</form>

EOF;
}


function ShowTotalsRow($i) {
global $ibforums;
return <<<EOF

	<tr>
		<td class='row2' align='center' width='50%'>{$i['name']}</td>
		<td class='row4' align='center' width='25%'>{$i['rep']}</td>
		<td class='row4' align='center' width='25%'>{$i['times']}</td>
	</tr>

EOF;
}


function ShowNone() {
global $ibforums;
return <<<EOF

<tr>
	<td align='center' colspan='7' class='row4'>{$ibforums->lang['no_changes']}</td>
</tr>

EOF;
}


function Links($links) {
global $ibforums;
return <<<EOF

<div align="left">{$links}</div>

EOF;
}


function ShowForm($i) {
global $ibforums;
return <<<EOF

<script>
<!--
	function Validate() {
		var Max = {$ibforums->vars['rep_msg_length']};
		Length = document.Reput.message.value.length;
		if (( Length > Max) && ( Max > 0 )) {
			alert("{$ibforums->lang['len_max']}" + Max + "{$ibforums->lang['len_current']}" + Length + "{$ibforums->lang['len_symbols']}");
			return false;
		} else {
			document.Reput.go.disabled = true;
			return true;
		}
	}
// -->
</script>
     <br>
     <form action="{$ibforums->base_url}" method="post" name='Reput' onSubmit='return Validate()'>
     <input type='hidden' name='CODE' value='{$i['code']}'>
     <input type='hidden' name='s' value='{$ibforums->session_id}'>
     <input type='hidden' name='rep_level' value="{$i['level']}">
     <input type='hidden' name='mid' value="{$i['memid']}">
     <input type='hidden' name='process' value='yep'>
     <input type='hidden' name='act' value='rep'>
     <input type='hidden' name='f' value='{$i['f']}'>
     <input type='hidden' name='t' value='{$i['t']}'>
     <input type='hidden' name='p' value='{$i['p']}'>
     <div class='tableborder'>
      <div class="maintitle">{$ibforums->lang['fill']}</div>

      <table cellpadding='4' cellspacing='1' border='0' width='100%' align='center'>
	<tr>
		<td class='row4' width='30%'>{$ibforums->lang['yourname']}</td> 
		<td class='row4'>{$ibforums->member['name']} {$i['anon']}</td>
	</tr>
	<tr>
		<td class='row4' width='30%'>{$ibforums->lang['whosename']}</td> 
		<td class='row4'>{$ibforums->who_name}</td>
	</tr>
	<tr>
		<td class='row4' width='30%'>{$ibforums->lang['reason']}</td>
		<td class='row4'><textarea cols='60' rows='10' wrap='soft' name='message' class='textinput'
       		  onKeyPress='if (event.keyCode==10 || ((event.metaKey || event.ctrlKey) && event.keyCode==13))
		  this.form.go.click()'></textarea></td>
	</tr>
        <tr>
		<td class='row4' width='30%'>{$ibforums->lang['act']}</td>
		<td class='row4'>{$i['action']}</td>
	</tr>
        <tr>
            <td class='darkrow1' width='20%'></td>
            <td class='darkrow1'><input type='submit' value='{$ibforums->lang['go']}' name='go' class='forminput' accesskey='s'></td>
        </tr>
     </table>
    </div>
    </form>

EOF;
}


function ShowSelfTitle($i) {
global $ibforums;
return <<<EOF

<div class='tableborder'>
 <div class="maintitle" align='center'>
 <b>{$i['name']}</b> {$ibforums->lang['has_changed']} {$i['times']} {$ibforums->lang['has_times']} [ +{$i['ups']} | -{$i['downs']} ]
 </div>

 <table width='100%' cellpadding='4' cellspacing='1' border='0'>
  <tr>

EOF;
}


function ShowFooter($link) {
global $ibforums;
return <<<EOF

	<tr>
		<td align='center' colspan='7' class='darkrow1'><a href='$link'>{$ibforums->lang['back']}</a></td>
	</tr>
	</table>
     </div>

EOF;
}


function StatsLinks() {
global $ibforums;
return <<<EOF

<div class='tableborder'>
 <div class="maintitle" align='center'>
 {$ibforums->lang['rep_name']}, {$ibforums->lang['btitle']}
 </div>
 <table width="100%" border="0" cellspacing="1" cellpadding="4">
  <tr>
   <th align='center' class='pformstrip' width='50%'>{$ibforums->lang['member']}</th>
   <th align='center' class='pformstrip' width='25%'>{$ibforums->lang['rep_name']}</th>
   <th align='center' class='pformstrip' width='25%'>{$ibforums->lang['rep_name']}{$ibforums->lang['given']}</th>
  </tr>

EOF;
}


}
