<?php

class skin_mlist
{



    function no_results()
    {
        global $ibforums;
        return <<<EOF

No results

EOF;
    }


    function show_row($member)
    {
        global $ibforums;
        return <<<EOF
  <tr>
	 <td class='row4'><strong><a href="{$ibforums->base_url}showuser={$member['id']}">{$member['name']}</a></strong></td>
	 <td class='row4'>{$member['gicon']}{$member['title']}<br>{$member['sex']}{$member['pips']}</td>
	 <td class='row2' align="center" width="20%">{$member['group']}</td>
	 <td class='row4' align="center" width="10%">{$member['joined']}</td>
	 <td class='row4' align="center" width="10%">{$member['location']}</td>
	 <td class='row4' align="center" width="10%">{$member['rep']}</td>
	 <td class='row4' align="center" width="10%">{$member['fined']}</td>
	 <td class='row4' align="center" width="10%">{$member['posts']}</td>
	 <td class='row2' align="center">{$member['icq_status']}<br>{$member['icq_number']}</td>
	 <td class='row2' align="center">{$member['camera']}</td>
  </tr>

EOF;
    }


    function end($links)
    {
        global $ibforums;
        return <<<EOF

<br>
<div align="left">{$links[SHOW_PAGES]}</div>

EOF;
    }


    function start()
    {
        global $ibforums;
        return <<<EOF



EOF;
    }


    function Page_end($checked = "")
    {
        global $ibforums;
        return <<<EOF

  <tr>
    <td class='row3' colspan="10" align='center' valign='middle'>
      <strong>{$ibforums->lang['photo_only']}&nbsp;<input type="checkbox" value="1" name="photoonly" class="forminput" $checked></strong>
    </td>
  </tr>
  <tr>
    <td class='pformstrip' colspan="10" align='center' valign='middle'>
      <select class='forminput' name='name_box'>
	 <option value='begins'>{$ibforums->lang['ch_begins']}</option>
	 <option value='contains'>{$ibforums->lang['ch_contains']}</option>
	 <option value='all' selected="selected">{$ibforums->lang['ch_all']}</option>
	 </select>&nbsp;&nbsp;<input class='forminput' type='text' size='25' name='name' value='{$ibforums->input['name']}'>
    </td>
  </tr>
  <tr>
   <td class='darkrow1' colspan="10" align='center' valign='middle'>
     {$ibforums->lang['sorting_text']}&nbsp;<input type='submit' value='{$ibforums->lang['sort_submit']}' class='forminput'>
   </td>
 </tr>
</table>
</div>
</form>

EOF;
    }


    function Page_header($links)
    {
        global $ibforums;
        return <<<EOF

<form action='{$ibforums->base_url}' method='post'>
<input type='hidden' name='act' value='Members'>
<input type='hidden' name='s'   value='{$ibforums->session_id}'>
<div align="left">{$links['SHOW_PAGES']}</div>
<br>
<div class='tableborder'>
 <div class="maintitle">{$ibforums->lang['page_title']}</div>
 <table width="100%" border="0" cellspacing="1" cellpadding="4">
  <tr>
	<th class='pformstrip' width="20%">{$ibforums->lang['member_name']}</th>
	<th class='pformstrip' align="center" width="12%">{$ibforums->lang['member_level']}</th>
	<th class='pformstrip' align="center" width="10%">{$ibforums->lang['member_group']}</th>
	<th class='pformstrip' align="center" width="10%">{$ibforums->lang['member_joined']}</th>
	<th class='pformstrip' align="center" width="10%">{$ibforums->lang['member_location']}</th>
	<th class='pformstrip' align="center" width="10%">{$ibforums->lang['rep_name']}</th>
	<th class='pformstrip' align="center" width="5%">{$ibforums->vars['currency_name']}</th>
	<th class='pformstrip' align="center" width="10%">{$ibforums->lang['member_posts']}</th>
	<th class='pformstrip' align="center">{$ibforums->lang['member_icq']}</th>
	<th class='pformstrip' width="5%" align="center">{$ibforums->lang['member_photo']}</th>
  </tr>

EOF;
    }
}
