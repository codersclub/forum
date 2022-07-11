<?php

class skin_online {



function show_row($session) {
global $ibforums;
return <<<EOF
              <tr>
                <td class='row2'>{$session['member_name']}</td>
                <td class='row2'>{$session['where_line']}</td>
                <td class='row2' align='center'>{$session['running_time']}</td>
                <td class='row2' align='center'>{$session['msg_icon']}</td>
              </tr>

EOF;
}


function Page_end($show_mem, $sort_order, $sort_key, $links) {
global $ibforums;
return <<<EOF

            <tr>
            <td colspan='4' class='darkrow1' align='center' valign='middle'>
             <form method='post' action='{$ibforums->base_url}act=Online&amp;CODE=listall'>
             <b>{$ibforums->lang['s_by']}&nbsp;</b>
             <select class='forminput' name='sort_key'>{$sort_key}</select>
             <select class='forminput' name='show_mem'>&nbsp;{$show_mem}</select>
             <select class='forminput' name='sort_order'>&nbsp;{$sort_order}</select>
             <input type='submit' value='{$ibforums->lang['s_go']}' class='forminput'>
             <form>
            </td>
            </tr>
            </table>
           </div>
          <br>
          <div align='left'>$links</div>

EOF;
}


function Page_header($links) {
global $ibforums;
return <<<EOF
    <div align='left'>$links</div>
    <br>
    <div class="tableborder">
      <div class='maintitle'>&nbsp;&nbsp;{$ibforums->lang['page_title']}</div>
	  <table cellspacing='1'>
		<tr>
		   <th align='left' width='30%' class='titlemedium'>{$ibforums->lang['member_name']}</th>
		   <th align='left' width='30%' class='titlemedium'>{$ibforums->lang['where']}</th>
		   <th align='center' width='20%' class='titlemedium'>{$ibforums->lang['time']}</th>
		   <th align='left' width='10%' class='titlemedium'>&nbsp;</th>
		</tr>
EOF;
}


}
