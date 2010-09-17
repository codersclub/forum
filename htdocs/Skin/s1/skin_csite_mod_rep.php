<?php

/*
+--------------------------------------------------------------------------
|   D-Site Reputations module skin sets
|   ========================================
|   (c) 2004 - 2006 Anton
|   anton@sources.ru
|   ========================================
+---------------------------------------------------------------------------
*/


class skin_csite_mod_rep {

//------------------------------------------------------------------------------
// show the form
//------------------------------------------------------------------------------

function tmpl_show_rep_form( $entry ) {
global $ibforums;
return <<<EOF
 <div class='tableborder'>
 <div class='maintitle'><{CAT_IMG}> {$entry['title']}</div>
</div>
<br />
<div class="tableborder">
<div class=row2>
<form name="rep_form" method="post" action='{$ibforums->vars['dynamiclite']}REP={$entry['rep_id']}&CODE={$entry['rep_cd']}'>
<input name="cat_id" value="{$entry['cat_id']}" type="hidden">
<input name="art_id" value="{$entry['art_id']}" type="hidden">
<input name="mem_id" value="{$entry['mem_id']}" type="hidden">
<input name="ver_id" value="{$entry['ver_id']}" type="hidden">

<table cellspacing="0" width="100%" border="0">
<tr valign="center">
<td width="15%">Действие: </td>
<td align="left">
<select size="1" name="rep_type">
  <option value="1">Увеличение рейтинга</option>
  <option value="2">Уменьшение рейтинга</option>
</select>
</td>
</tr>
<tr><td>&nbsp;</td></tr>
<tr valign="center">
<td>Введите причину: </td>
<td align="left" class="row2">
<textarea cols='3' rows='3' name='Post'  style='width:99%' class='textinput' ></textarea>
</td>
</tr>
<tr valign="top">
<td align="center" valign="middle" colspan=2>
<input type="submit" value="Добавить">
</td>
</tr>
</table>

</form>
</div>
</div>
EOF;
}

}

?>