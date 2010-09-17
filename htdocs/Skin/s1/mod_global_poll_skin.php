<?php

class mod_global_poll_skin {



function Render_row_results($votes, $id, $answer, $percentage, $width) {
global $ibforums;
return <<<EOF
    <tr>
    <td class='row1'>$answer</td>
    <td class='row1'>[&nbsp;<b>$votes</b>&nbsp;]</td>
    <td class='row1'><img src='{$ibforums->vars['img_url']}/bar_left.gif' border='0' width='4' height='11' align='middle' alt=''><img src='{$ibforums->vars['img_url']}/bar.gif' border='0' width='$width' height='11' align='middle' alt=''><img src='{$ibforums->vars['img_url']}/bar_right.gif' border='0' width='4' height='11' align='middle' alt=''>&nbsp;[$percentage%]</td>
EOF;
}


function Render_row_form($votes, $id, $answer) {
global $ibforums;
return <<<EOF
    <tr>
    <td class='row1' colspan='3'><INPUT type="radio" name="poll_vote" value="$id">&nbsp;<b>$answer</b></td>
    </tr>

EOF;
}


function global_poll_table_header($data) {
global $ibforums;
return <<<EOF
    <br>
    <form action="{$data["form_url"]}" method="post">
	<div class="tableborder">
	<div class="maintitle"><{CAT_IMG}>&nbsp;{$data["global_poll"]} - {$data["poll_question"]}</div>
	<table cellpadding='4' cellspacing='1' border='0' width='100%'>
	<tr>
           <td class='pformstrip' colspan='3'>{$data["header_text"]}</td>
    	</tr>
	<tr>
           <td class='row4' colspan='3'>{$data["poll_text"]}</td>
    	</tr>


EOF;
}


function show_total_votes($total_votes) {
global $ibforums;
return <<<EOF
    <tr>
    <td class='row1' colspan='3' align='center'><strong>$total_votes</strong></td>
    </tr>

EOF;
}


function global_poll_table_footer($footerText) {
global $ibforums;
return <<<EOF
	 <tr>
           <td class='pformstrip' colspan='3'>$footerText</td>
    	</tr>
	 </table>
	 </div>
	 </form>


EOF;
}


}
?>