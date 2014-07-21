<?php

class skin_poll {

function poll_javascript($tid,$fid) {
global $ibforums;
return <<<EOF
<script type="text/javascript">
function go_gadget_show()
{
	window.location = "{$ibforums->base_url}&act=ST&f=$fid&t=$tid&mode=show&st={$ibforums->input['start']}";
}
function go_gadget_vote()
{
	window.location = "{$ibforums->base_url}&act=ST&f=$fid&t=$tid&st={$ibforums->input['start']}";
}
</script>
EOF;
}

function button_vote() {
global $ibforums;
return <<<EOF
<input type='submit' name='submit' value='{$ibforums->lang['poll_add_vote']}' title="{$ibforums->lang['tt_poll_vote']}" class='forminput'>
EOF;
}

function button_null_vote() {
global $ibforums;
return <<<EOF
<input type='submit' name='nullvote' value='{$ibforums->lang['poll_null_vote']}' title="{$ibforums->lang['tt_poll_null']}" class='forminput'>
EOF;
}

function button_show_results() {
global $ibforums;
return <<<EOF
<input type='button' value='{$ibforums->lang['pl_show_results']}' class='forminput' title="{$ibforums->lang['tt_poll_show']}" onclick='go_gadget_show()'>
EOF;
}

function button_show_voteable() {
global $ibforums;
return <<<EOF
<input type='button' name='viewresult' value='{$ibforums->lang['pl_show_vote']}'  title="{$ibforums->lang['tt_poll_svote']}" class='forminput' onclick='go_gadget_vote()'>
EOF;
}

function edit_link($tid, $fid, $key="") {
global $ibforums;
return <<<EOF
[ <a href="{$ibforums->base_url}act=Mod&amp;CODE=20&amp;f=$fid&amp;t=$tid&amp;auth_key=$key">{$ibforums->lang['ba_edit']}</a> ]
EOF;
}

function delete_link($tid, $fid, $key="") {
global $ibforums;
return <<<EOF
[ <a href="{$ibforums->base_url}act=Mod&amp;CODE=22&amp;f=$fid&amp;t=$tid&amp;auth_key=$key">{$ibforums->lang['ba_delete']}</a> ]
EOF;
}

function close_link($tid, $fid, $key="") {
global $ibforums;
return <<<EOF
[ <a href="{$ibforums->base_url}act=Mod&amp;CODE=24&amp;f=$fid&amp;t=$tid&amp;auth_key=$key">{$ibforums->lang['ba_close']}</a> ]
EOF;
}

function restore_link($tid, $fid, $key="") {
global $ibforums;
return <<<EOF
[ <a href="{$ibforums->base_url}act=Mod&amp;CODE=25&amp;f=$fid&amp;t=$tid&amp;auth_key=$key">{$ibforums->lang['ba_open']}</a> ]
EOF;
}

function Render_row_form($votes, $id, $answer, $type, $name) {
global $ibforums;
return <<<EOF
    <tr>
     <td align='left' class='row1' colspan='3'><input type="$type" name="$name" value="$id" />&nbsp;<strong>$answer</strong></td>
    </tr>
EOF;
}

function poll_expired_row() {
global $ibforums;
return <<<EOF

<div align='center' class='pformstrip'>{$ibforums->lang['poll_life_descr3']}</div>

EOF;
}

function poll_header($tid, $poll_q, $edit, $delete, $close, $min_max = "", $expired = "") {
global $ibforums;
return <<<EOF

<!--IBF.POLL_JS-->
<form action='{$ibforums->base_url}' method='POST' name='poll'>
<input type=hidden name=act value='Poll'>
<input type=hidden name=t value=$tid>
<div align='right' class='pformstrip'>$edit &nbsp; $delete &nbsp; $close</div>
{$expired}
<div class='tablepad' align='center'>
<table class="b-poll" cellpadding='5'>
<tr>
 <td colspan='3' align='center'><b>$poll_q</b> $min_max</td>
</tr>
EOF;
}

function ShowPoll_footer() {
global $ibforums;
return <<<EOF
</table>
</div>
<div align="center" class="pformstrip"><!--IBF.VOTE-->&nbsp;<!--IBF.SHOW--></div>
</form>

EOF;
}

function Render_row_results($votes, $id, $answer, $procent_bar) {
global $ibforums;
return <<<EOF
    <tr>
    <td align='left' class='row1'>$answer</td>
    <td class='row1'>[&nbsp;<b>$votes</b>&nbsp;]</td>
    <td class='row1' align='left'>
     {$procent_bar}
    </tr>
EOF;
}

function show_total_votes($votes,$total_votes) {
global $ibforums;
return <<<EOF
    <tr>
    <td class='row1' colspan='3' align='center'><strong>{$ibforums->lang['pv_total_votes']}{$votes} / {$ibforums->lang['pv_total_voters']}{$total_votes}</strong></td>
    </tr>
EOF;
}

function weighted_js($count) {
$output=<<<EOF

<script language='JavaScript' type="text/javascript">
function doDropdown(myDropdown){
if(myDropdown.selectedIndex != 0){
 for(x = 0; x < $count;x++){
  if(document.forms.poll.elements[x].name != myDropdown.name){
   if(document.forms.poll.elements[x].selectedIndex == myDropdown.selectedIndex){
    document.forms.poll.elements[x].selectedIndex = 0;
   };
  };
 };
};
}
</script>
EOF;
return $output;
}

function Render_row_form_weighted($id, $choice, $name, $places) {
global $ibforums;
$output = "<tr><td class='row1' colspan='3' align='left'><select name='$name' class='forminput' onChange='doDropdown(this)'>";
$places++;
$output .= "<option value=''>Не голосую";
for ($i=1; $i<$places; $i++) {
 $output .= "<option value='$i'>$i";
 if ($i == 1) { $output .= "-е место"; }
 else if ($i == 2) { $output .= "-е место"; }
 else if ($i == 3) { $output .= "-е место"; }
 else { $output .= "-е место"; }
}

$output .= "</select> $choice</td></tr>\n";
return $output;
}


}
?>
